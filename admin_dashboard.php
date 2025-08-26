<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';

// Function to fetch count with prepared statements
function fetchCount(PDO $pdo, string $table, string $where = '1=1', array $params = []): int {
    $query = "SELECT COUNT(*) FROM $table WHERE $where";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

// Fetch dashboard stats
// Dashboard stats
$total_patients = fetchCount($pdo, 'patients');
$total_doctors = fetchCount($pdo, 'doctors');
$total_alerts = fetchCount($pdo, 'alerts');
// Count unread critical alerts for all patients
$stmt = $pdo->prepare('SELECT COUNT(*) FROM alerts WHERE status = "new"');
$unread_critical_alerts = $stmt->fetchColumn();

// Fetch all patients
$stmt = $pdo->prepare('SELECT id, fullname, age, gender, email, phone FROM patients ORDER BY created_at DESC');
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch BP readings
$stmt = $pdo->prepare('SELECT r.*, p.fullname FROM bp_readings r JOIN patients p ON r.patient_id = p.id ORDER BY r.reading_time DESC');
$stmt->execute();
$readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch average BP stats
$stmt = $pdo->prepare('SELECT AVG(systolic) AS avg_systolic, AVG(diastolic) AS avg_diastolic FROM bp_readings');
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch gender distribution for pie chart
$stmt = $pdo->prepare('SELECT gender, COUNT(*) as count FROM patients GROUP BY gender');
$stmt->execute();
$gender_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
$gender_labels = array_column($gender_distribution, 'gender');
$gender_counts = array_column($gender_distribution, 'count');

// Age group distribution for pie chart
$stmt = $pdo->prepare('SELECT 
    COUNT(CASE WHEN age < 18 THEN 1 END) as under_18,
    COUNT(CASE WHEN age BETWEEN 18 AND 35 THEN 1 END) as age_18_35,
    COUNT(CASE WHEN age BETWEEN 36 AND 55 THEN 1 END) as age_36_55,
    COUNT(CASE WHEN age > 55 THEN 1 END) as over_55
FROM patients');
$stmt->execute();
$age_groups = $stmt->fetch(PDO::FETCH_ASSOC);

// BP status distribution
$stmt = $pdo->prepare('SELECT 
    COUNT(CASE WHEN systolic < 120 AND diastolic < 80 THEN 1 END) as normal,
    COUNT(CASE WHEN (systolic BETWEEN 120 AND 139) OR (diastolic BETWEEN 80 AND 89) THEN 1 END) as elevated,
    COUNT(CASE WHEN (systolic BETWEEN 140 AND 159) OR (diastolic BETWEEN 90 AND 99) THEN 1 END) as stage1,
    COUNT(CASE WHEN systolic >= 160 OR diastolic >= 100 THEN 1 END) as stage2
FROM bp_readings');
$stmt->execute();
$bp_status = $stmt->fetch(PDO::FETCH_ASSOC);

// Monthly BP readings trend (last 12 months)
$stmt = $pdo->prepare('SELECT DATE_FORMAT(reading_time, "%Y-%m") as month, COUNT(*) as count, AVG(systolic) as avg_systolic, AVG(diastolic) as avg_diastolic FROM bp_readings WHERE reading_time >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month ASC');
$stmt->execute();
$monthly_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Assign patient to doctor logic
$assign_message = '';
if (isset($_POST['assign_patient']) && isset($_POST['doctor_id']) && isset($_POST['patient_id'])) {
    $doctor_id = intval($_POST['doctor_id']);
    $patient_id = intval($_POST['patient_id']);
    // Create assignments table if not exists
    $pdo->exec('CREATE TABLE IF NOT EXISTS assignments (id INT AUTO_INCREMENT PRIMARY KEY, doctor_id INT, patient_id INT, assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
    // Check if already assigned
    $stmt = $pdo->prepare('SELECT * FROM assignments WHERE doctor_id = ? AND patient_id = ?');
    $stmt->execute([$doctor_id, $patient_id]);
    if ($stmt->fetch()) {
        $assign_message = 'Patient is already assigned to this doctor.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO assignments (doctor_id, patient_id) VALUES (?, ?)');
        if ($stmt->execute([$doctor_id, $patient_id])) {
            $assign_message = 'Patient assigned successfully!';
        } else {
            $assign_message = 'Error assigning patient.';
        }
    }
}

// Fetch critical BP alerts
$mark_alert_message = '';
if (isset($_POST['mark_alert_read'])) {
    $alert_id = intval($_POST['mark_alert_read']);
    $stmt = $pdo->prepare('UPDATE alerts SET status = "read" WHERE id = ?');
    if ($stmt->execute([$alert_id])) {
        $mark_alert_message = 'Alert marked as read.';
        // Optionally, you can add a redirect to avoid resubmission
        echo '<script>window.location.href=window.location.href;</script>';
        exit;
    } else {
        $mark_alert_message = 'Failed to mark alert as read.';
    }
}
$alerts = $pdo->query('SELECT a.*, p.fullname, p.age, p.gender FROM alerts a JOIN patients p ON a.patient_id = p.id ORDER BY a.created_at DESC LIMIT 10')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    /* Custom animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .hover-scale {
        transition: transform 0.3s ease;
    }

    .hover-scale:hover {
        transform: scale(1.05);
    }

    .table-row {
        transition: background-color 0.3s ease;
    }

    .table-row:hover {
        background-color: #1f2937;
    }

    .chart-container {
        max-width: 100%;
        margin: 0 auto;
        min-height: 220px;
    }
    </style>
</head>

<body
    class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar Toggle Button for Mobile -->
        <button id="sidebarToggle"
            class="md:hidden fixed top-4 left-4 z-50 bg-white/80 dark:bg-gray-900/80 p-2 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 focus:outline-none">
            <i class="fa fa-bars text-2xl text-blue-600 dark:text-teal-400"></i>
        </button>
        <!-- Admin Sidebar -->
        <div id="adminSidebar" class="hidden md:block">
            <?php include __DIR__ . '/components/sidebar.php'; ?>
        </div>
        <!-- Main Content -->
        <div class="flex-1 w-full min-w-0 md:ml-64">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <main class="max-w-7xl mx-auto py-8 px-2 sm:px-4 md:px-6 lg:px-8 w-full">
                <script>
                // Sidebar toggle for mobile
                document.addEventListener('DOMContentLoaded', function() {
                    var sidebar = document.getElementById('adminSidebar');
                    var toggleBtn = document.getElementById('sidebarToggle');
                    if (sidebar && toggleBtn) {
                        toggleBtn.addEventListener('click', function() {
                            sidebar.classList.toggle('hidden');
                            sidebar.classList.toggle('fixed');
                            sidebar.classList.toggle('top-0');
                            sidebar.classList.toggle('left-0');
                            sidebar.classList.toggle('h-screen');
                            sidebar.classList.toggle('z-50');
                            sidebar.classList.toggle('w-64');
                            sidebar.classList.toggle('bg-white');
                            sidebar.classList.toggle('dark:bg-gray-800');
                            sidebar.classList.toggle('shadow-lg');
                        });
                    }
                });
                </script>
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <h2
                        class="text-3xl font-bold text-blue-600 dark:text-teal-400 flex items-center gap-3 animate-fadeInUp">
                        <i class="fa-solid fa-user-shield"></i> Admin Dashboard
                    </h2>
                    <div class="relative">
                        <button id="adminNotifBell" class="relative px-4 py-2 focus:outline-none">
                            <i class="fa fa-bell text-2xl text-red-500"></i>
                            <?php if ($unread_critical_alerts > 0): ?>
                            <span
                                class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full px-2 py-0.5 font-bold animate-pulse">
                                <?php echo $unread_critical_alerts; ?>
                            </span>
                            <?php endif; ?>
                        </button>
                        <div id="adminNotifDropdown"
                            class="hidden absolute right-0 mt-12 w-96 bg-white dark:bg-gray-800 shadow-lg rounded-lg z-50">
                            <div class="p-4 border-b font-bold text-lg text-red-700 flex items-center gap-2"><i
                                    class="fa fa-exclamation-triangle"></i> Critical BP Alerts</div>
                            <?php 
                            $stmt = $pdo->prepare('SELECT a.*, p.fullname FROM alerts a JOIN patients p ON a.patient_id = p.id WHERE a.status = "new" ORDER BY a.created_at DESC LIMIT 10');
                            $stmt->execute();
                            $unread_alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!empty($unread_alerts)): ?>
                            <?php foreach ($unread_alerts as $alert): ?>
                            <div class="flex items-center justify-between px-4 py-3 border-b">
                                <span><strong><?php echo htmlspecialchars($alert['fullname']); ?>:</strong>
                                    <?php echo htmlspecialchars($alert['message']); ?></span>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="mark_alert_read" value="<?php echo $alert['id']; ?>">
                                    <button type="submit"
                                        class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 ml-2"><i
                                            class="fa fa-check"></i></button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="px-4 py-3 text-gray-500">No new critical alerts.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <script>
                    // Notification bell dropdown for admin
                    document.addEventListener('DOMContentLoaded', function() {
                        const bell = document.getElementById('adminNotifBell');
                        const dropdown = document.getElementById('adminNotifDropdown');
                        if (bell && dropdown) {
                            bell.addEventListener('click', function(e) {
                                e.stopPropagation();
                                dropdown.classList.toggle('hidden');
                            });
                            document.addEventListener('click', function(e) {
                                if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                                    dropdown.classList.add('hidden');
                                }
                            });
                        }
                    });
                    </script>
                    <!-- <a href="logout.php"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg shadow-lg hover:bg-red-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </a> -->
                </div>

                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-10 w-full">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center hover-scale animate-fadeInUp">
                        <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-2">
                            <i class="fa-solid fa-users"></i> Total Patients
                        </h3>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?php echo $total_patients; ?>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center hover-scale animate-fadeInUp"
                        style="animation-delay: 0.1s;">
                        <h3 class="text-lg font-semibold text-teal-600 dark:text-teal-400 flex items-center gap-2">
                            <i class="fa-solid fa-user-md"></i> Total Doctors
                        </h3>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?php echo $total_doctors; ?>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center hover-scale animate-fadeInUp"
                        style="animation-delay: 0.2s;">
                        <h3 class="text-lg font-semibold text-pink-600 dark:text-pink-400 flex items-center gap-2">
                            <i class="fa-solid fa-bell"></i> Alerts
                        </h3>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?php echo $total_alerts; ?>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center hover-scale animate-fadeInUp"
                        style="animation-delay: 0.3s;">
                        <h3 class="text-lg font-semibold text-green-600 dark:text-green-400 flex items-center gap-2">
                            <i class="fa-solid fa-heartbeat"></i> Avg Systolic
                        </h3>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            <?php echo round($stats['avg_systolic'], 1); ?> mmHg</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mb-8 flex justify-end gap-4">
                    <a href="add_doctor.php"
                        class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow-lg hover:bg-teal-700 transition flex items-center gap-2 animate-fadeInUp">
                        <i class="fa-solid fa-user-plus"></i> Add Doctor
                    </a>
                    <a href="add_patient.php"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-lg hover:bg-blue-700 transition flex items-center gap-2 animate-fadeInUp">
                        <i class="fa-solid fa-user-plus"></i> Add Patient
                    </a>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- BP Statistics Bar Chart -->
                        <section
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 animate-fadeInUp flex flex-col items-center">
                            <h3
                                class="text-xl font-semibold text-green-600 dark:text-green-400 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-chart-bar"></i> BP Statistics
                            </h3>
                            <div class="chart-container w-full">
                                <canvas id="bpStatsChart" height="120"></canvas>
                            </div>
                        </section>
                        <!-- BP Status Distribution Chart -->
                        <section
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 animate-fadeInUp flex flex-col items-center">
                            <h3
                                class="text-xl font-semibold text-red-600 dark:text-red-400 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-heartbeat"></i> BP Status Distribution
                            </h3>
                            <div class="chart-container w-full">
                                <canvas id="bpStatusChart" height="120"></canvas>
                            </div>
                        </section>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Gender Distribution Pie Chart -->
                        <section
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 animate-fadeInUp flex flex-col items-center">
                            <h3
                                class="text-xl font-semibold text-blue-600 dark:text-blue-400 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-chart-pie"></i> Gender Distribution
                            </h3>
                            <div class="chart-container w-full">
                                <canvas id="genderChart" height="120"></canvas>
                            </div>
                        </section>
                        <!-- Age Group Distribution Pie Chart -->
                        <section
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 animate-fadeInUp flex flex-col items-center">
                            <h3
                                class="text-xl font-semibold text-purple-600 dark:text-purple-400 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-chart-pie"></i> Age Group Distribution
                            </h3>
                            <div class="chart-container w-full">
                                <canvas id="ageChart" height="120"></canvas>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Monthly BP Readings Trend Chart -->
                <section
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 animate-fadeInUp mb-10 flex flex-col items-center">
                    <h3 class="text-xl font-semibold text-indigo-600 dark:text-indigo-400 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-alt"></i> BP Readings Trend (Last 12 Months)
                    </h3>
                    <div class="chart-container w-full">
                        <canvas id="bpTrendChart" height="120"></canvas>
                    </div>
                </section>

                <!-- Appointments Section -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center">
                        <h3 class="text-lg font-semibold text-indigo-600 flex items-center gap-2"><i
                                class="fa-solid fa-calendar-check"></i> Appointments</h3>
                        <div class="text-3xl font-bold mb-2">
                            <?php 
                            $stmt = $pdo->query('SELECT COUNT(*) FROM appointments');
                            echo $stmt->fetchColumn(); 
                            ?>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total appointments</p>
                    </div>
                </div>

                <!-- Alerts Section with Notification Icon and Pagination -->
                <section class="mb-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 text-red-600 flex items-center gap-2">
                        <i class="fa fa-bell"></i> Notifications
                    </h3>
                    <?php
                    // Pagination logic
                    $alerts_per_page = 10;
                    $page = isset($_GET['alerts_page']) ? max(1, intval($_GET['alerts_page'])) : 1;
                    $offset = ($page - 1) * $alerts_per_page;
                    $total_alerts_count = $pdo->query('SELECT COUNT(*) FROM alerts')->fetchColumn();
                    $total_pages = ceil($total_alerts_count / $alerts_per_page);

                    $stmt = $pdo->prepare('SELECT a.*, p.fullname, p.age, p.gender FROM alerts a JOIN patients p ON a.patient_id = p.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?');
                    $stmt->bindValue(1, $alerts_per_page, PDO::PARAM_INT);
                    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                    $stmt->execute();
                    $paged_alerts = $stmt->fetchAll();
                    ?>
                    <?php if (count($paged_alerts) > 0): ?>
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-red-100 dark:bg-gray-700">
                                <th class="px-4 py-2">Patient</th>
                                <th class="px-4 py-2">Age</th>
                                <th class="px-4 py-2">Gender</th>
                                <th class="px-4 py-2">Message</th>
                                <th class="px-4 py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paged_alerts as $alert): ?>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($alert['fullname']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($alert['age']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($alert['gender']); ?></td>
                                <td class="px-4 py-2">
                                    <?php
                                    // Highlight Doctor recommendation in green, BP reading in red
                                    $msg = htmlspecialchars($alert['message']);
                                    if (stripos($msg, 'doctor recommendation') !== false) {
                                        echo '<span class="text-green-600">' . $msg . '</span>';
                                    } elseif (stripos($msg, 'bp reading') !== false || stripos($msg, 'blood pressure') !== false) {
                                        echo '<span class="text-red-600">' . $msg . '</span>';
                                    } else {
                                        echo $msg;
                                    }
                                    ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($alert['created_at']))); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Pagination Controls -->
                    <div class="flex justify-center items-center mt-6 gap-2">
                        <?php if ($page > 1): ?>
                        <a href="?alerts_page=<?php echo $page - 1; ?>"
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                            <i class="fa fa-chevron-left"></i> Prev
                        </a>
                        <?php endif; ?>
                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        <?php if ($page < $total_pages): ?>
                        <a href="?alerts_page=<?php echo $page + 1; ?>"
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                            Next <i class="fa fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <i class="fa fa-info-circle"></i> No critical alerts at the moment.
                    </div>
                    <?php endif; ?>
                </section>
            </main>
            <?php include __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>

    <script>
    // SweetAlert2 for delete confirmation
    function confirmDelete() {
        return Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => result.isConfirmed);
    }

    // BP Statistics Bar Chart
    const bpCtx = document.getElementById('bpStatsChart').getContext('2d');
    new Chart(bpCtx, {
        type: 'bar',
        data: {
            labels: ['Avg Systolic', 'Avg Diastolic'],
            datasets: [{
                label: 'BP Levels (mmHg)',
                data: [<?php echo round($stats['avg_systolic'], 1); ?>,
                    <?php echo round($stats['avg_diastolic'], 1); ?>
                ],
                backgroundColor: ['#2563eb', '#14b8a6'],
                borderColor: ['#1e40af', '#0d9488'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: '#374151'
                    }
                },
                x: {
                    ticks: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Gender Distribution Pie Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($gender_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($gender_counts); ?>,
                backgroundColor: ['#60a5fa', '#f472b6', '#34d399', '#a78bfa'],
                borderColor: ['#ffffff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1000,
                easing: 'easeInOutQuad'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff'
                }
            }
        }
    });

    // Age Group Distribution Pie Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'pie',
        data: {
            labels: ['Under 18', '18-35', '36-55', 'Over 55'],
            datasets: [{
                data: [
                    <?php echo $age_groups['under_18']; ?>,
                    <?php echo $age_groups['age_18_35']; ?>,
                    <?php echo $age_groups['age_36_55']; ?>,
                    <?php echo $age_groups['over_55']; ?>
                ],
                backgroundColor: ['#fde68a', '#34d399', '#a78bfa', '#fca311'],
                borderColor: ['#ffffff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1000,
                easing: 'easeInOutQuad'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff'
                }
            }
        }
    });

    // BP Status Distribution Chart
    const bpStatusCtx = document.getElementById('bpStatusChart').getContext('2d');
    new Chart(bpStatusCtx, {
        type: 'bar',
        data: {
            labels: ['Normal', 'Elevated', 'Stage 1', 'Stage 2'],
            datasets: [{
                label: 'Number of Readings',
                data: [
                    <?php echo $bp_status['normal']; ?>,
                    <?php echo $bp_status['elevated']; ?>,
                    <?php echo $bp_status['stage1']; ?>,
                    <?php echo $bp_status['stage2']; ?>
                ],
                backgroundColor: ['#34d399', '#fde68a', '#fca311', '#ef4444'],
                borderColor: ['#ffffff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: '#374151'
                    }
                },
                x: {
                    ticks: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Monthly BP Readings Trend Chart
    const bpTrendCtx = document.getElementById('bpTrendChart').getContext('2d');
    new Chart(bpTrendCtx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($monthly_trend as $row) echo '"' . $row['month'] . '",'; ?>
            ],
            datasets: [{
                    label: 'Number of Readings',
                    data: [<?php foreach ($monthly_trend as $row) echo $row['count'] . ','; ?>],
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointRadius: 5,
                    pointHoverRadius: 8
                },
                {
                    label: 'Avg Systolic',
                    data: [
                        <?php foreach ($monthly_trend as $row) echo round($row['avg_systolic'], 1) . ','; ?>
                    ],
                    fill: false,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointRadius: 5,
                    pointHoverRadius: 8
                },
                {
                    label: 'Avg Diastolic',
                    data: [
                        <?php foreach ($monthly_trend as $row) echo round($row['avg_diastolic'], 1) . ','; ?>
                    ],
                    fill: false,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                    pointRadius: 5,
                    pointHoverRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });

    // Trigger animations on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.animate-fadeInUp').forEach(el => observer.observe(el));
    </script>
</body>

</html>

<?php
$pdo = null; // Close connection
?>