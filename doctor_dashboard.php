<?php
// doctor_dashboard.php
// Handle mark as read for alerts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_alert_read']) && isset($_SESSION['doctor_id'])) {
    $alert_id = intval($_POST['mark_alert_read']);
    $stmt = $pdo->prepare('UPDATE alerts SET status = "read" WHERE id = ?');
    if ($stmt->execute([$alert_id]) && $stmt->rowCount() > 0) {
        header('Location: doctor_dashboard.php');
        exit;
    }
}
// Handle mark as read for alerts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_alert_read']) && isset($_SESSION['doctor_id'])) {
    $alert_id = intval($_POST['mark_alert_read']);
    $stmt = $pdo->prepare('UPDATE alerts SET status = "read" WHERE id = ?');
    if ($stmt->execute([$alert_id])) {
        header('Location: doctor_dashboard.php');
        exit;
    } else {
        echo '<div style="color:red;">Failed to mark alert as read.</div>';
    }
}
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header('Location: doctor_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$doctor_id = $_SESSION['doctor_id'];
// Fetch assigned patients (for demo, show all patients)
// Fetch only patients assigned to this doctor
$stmt = $pdo->prepare('SELECT p.id, p.fullname, p.age, p.gender FROM assignments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY p.fullname ASC');
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll();
// Fetch BP readings for all patients
$stmt = $pdo->query('SELECT r.*, p.fullname FROM bp_readings r JOIN patients p ON r.patient_id = p.id ORDER BY r.reading_time DESC');
$readings = $stmt->fetchAll();
// Fetch total assignments
$stmt = $pdo->prepare('SELECT COUNT(*) FROM assignments WHERE doctor_id = ?');
$stmt->execute([$doctor_id]);
$total_assignments = $stmt->fetchColumn();
// Prepare data for BP readings graph
$bp_dates = [];
$bp_systolic = [];
$bp_diastolic = [];
foreach ($readings as $r) {
    $bp_dates[] = date('M d', strtotime($r['reading_time']));
    $bp_systolic[] = $r['systolic'];
    $bp_diastolic[] = $r['diastolic'];
}

// Fetch unread critical alerts for assigned patients
// Fetch unread critical alerts for assigned patients
$stmt = $pdo->prepare('SELECT a.*, p.fullname FROM alerts a JOIN patients p ON a.patient_id = p.id JOIN assignments ass ON ass.patient_id = a.patient_id WHERE ass.doctor_id = ? AND a.status = "new" ORDER BY a.created_at DESC');
$stmt->execute([$doctor_id]);
$critical_alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$unread_alerts_count = count($critical_alerts);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">
    <div class="flex min-h-screen">
        <!-- Sidebar Toggle Button for Mobile -->
        <button id="sidebarToggle"
            class="md:hidden fixed top-4 left-4 z-50 bg-white/80 dark:bg-gray-900/80 p-2 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 focus:outline-none">
            <i class="fa fa-bars text-2xl text-blue-600 dark:text-teal-400"></i>
        </button>
        <!-- Doctor Sidebar -->
        <div id="doctorSidebar" class="hidden md:block">
            <?php include __DIR__ . '/components/sidebar.php'; ?>
        </div>
        <!-- Main Content -->
        <div class="flex-1 w-full min-w-0 md:ml-64">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <div class="flex flex-wrap justify-end items-center mb-6 relative w-full">
                <button id="notifBell" class="relative px-4 py-2 focus:outline-none">
                    <i class="fa fa-bell text-2xl text-yellow-500"></i>
                    <?php if (!empty($critical_alerts)): ?>
                    <span
                        class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full px-2 py-0.5 font-bold animate-pulse">
                        <?php echo count($critical_alerts); ?>
                    </span>
                    <?php endif; ?>
                </button>
                <div id="notifDropdown"
                    class="hidden absolute right-0 mt-12 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                    <div class="p-4 border-b font-bold text-lg text-red-700 flex items-center gap-2"><i
                            class="fa fa-bell"></i> Notifications</div>
                    <?php if (!empty($critical_alerts)): ?>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($critical_alerts as $alert): ?>
                        <li class="p-4 flex items-center gap-3">
                            <i class="fa fa-exclamation-triangle text-red-600 fa-lg"></i>
                            <span class="flex-1"><strong><?php echo htmlspecialchars($alert['fullname']); ?>:</strong>
                                <?php echo htmlspecialchars($alert['message']); ?></span>
                            <form method="POST" style="display:inline; margin-left:auto;">
                                <input type="hidden" name="mark_alert_read" value="<?php echo $alert['id']; ?>">
                                <button type="submit"
                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-1"><i
                                        class="fa fa-check"></i></button>
                            </form>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="p-4 text-gray-500 dark:text-gray-400 text-center">No new notifications.</div>
                    <?php endif; ?>
                </div>
            </div>
            <main class="max-w-7xl mx-auto py-8 px-4">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-10 w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center">
                        <h3 class="text-lg font-semibold text-blue-600 flex items-center gap-2"><i
                                class="fa-solid fa-users"></i> Patients</h3>
                        <div class="text-3xl font-bold mb-2"><?php echo count($patients); ?></div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total assigned patients</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center">
                        <h3 class="text-lg font-semibold text-teal-600 flex items-center gap-2"><i
                                class="fa-solid fa-history"></i> Recent Readings</h3>
                        <div class="text-3xl font-bold mb-2"><?php echo count($readings); ?></div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total BP readings</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center">
                        <h3 class="text-lg font-semibold text-green-600 flex items-center gap-2"><i
                                class="fa-solid fa-link"></i> Assignments</h3>
                        <div class="text-3xl font-bold mb-2"><?php echo $total_assignments; ?></div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total assignments</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-center">
                        <h3 class="text-lg font-semibold text-indigo-600 flex items-center gap-2"><i
                                class="fa-solid fa-calendar-check"></i> Appointments</h3>
                        <div class="text-3xl font-bold mb-2">
                            <?php 
                            $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE doctor_id = ?');
                            $stmt->execute([$doctor_id]);
                            echo $stmt->fetchColumn(); 
                            ?>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total appointments</p>
                    </div>
                </div>
                <!-- BP Readings Graph -->
                <section class="mb-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 text-purple-600 flex items-center gap-2"><i
                            class="fa-solid fa-chart-line"></i> BP Readings Trend</h3>
                    <canvas id="bpChart" height="120"></canvas>
                </section>
                <!-- Assigned Patients Section -->
                <section class="mb-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 text-green-600 flex items-center gap-2"><i
                            class="fa-solid fa-users"></i> Assigned Patients</h3>
                    <table class="w-full table-auto mb-6">
                        <thead class="bg-green-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Age</th>
                                <th class="px-4 py-3">Gender</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                            <tr class="border-b">
                                <td class="px-4 py-3 font-semibold text-blue-700 dark:text-blue-300">
                                    <?php echo htmlspecialchars($p['fullname']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($p['age']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($p['gender']); ?></td>
                                <td class="px-4 py-3">
                                    <button class="px-3 py-1 bg-purple-600 text-white rounded hover:bg-purple-700"
                                        onclick="showPatientModal(<?php echo $p['id']; ?>)"><i class="fa fa-eye"></i>
                                        Review</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <!-- Critical Alerts Section -->
                <?php if (!empty($critical_alerts)): ?>
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg flex flex-col gap-2">
                    <h3 class="font-bold text-red-700 mb-2"><i class="fa fa-exclamation-triangle"></i> Critical BP
                        Alerts
                    </h3>
                    <?php foreach ($critical_alerts as $alert): ?>
                    <div class="flex items-center justify-between">
                        <span><strong><?php echo htmlspecialchars($alert['fullname']); ?>:</strong>
                            <?php echo htmlspecialchars($alert['message']); ?></span>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="mark_alert_read" value="<?php echo $alert['id']; ?>">
                            <button type="submit"
                                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 ml-2"><i
                                    class="fa fa-check"></i> Mark as Read</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <!-- Patient Review Modal -->
                <div id="patientModal"
                    class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-8 w-full max-w-2xl relative">
                        <button onclick="closePatientModal()"
                            class="absolute top-4 right-4 text-gray-500 hover:text-red-600"><i
                                class="fa fa-times text-xl"></i></button>
                        <h3 id="modalPatientName" class="text-2xl font-bold mb-2 text-blue-600"></h3>
                        <div id="modalPatientInfo" class="mb-4 text-gray-600 dark:text-gray-300"></div>
                        <canvas id="modalBpChart" height="80" class="mb-6"></canvas>
                        <div id="modalReadings"></div>
                    </div>
                </div>
            </main>
            <?php include __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
    <script>
    // SweetAlert for comment submission (demo only)
    document.querySelectorAll('form[action="doctor_dashboard.php"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'success',
                title: 'Comment Added',
                text: 'Your comment has been added (demo only).',
                confirmButtonColor: '#14b8a6'
            });
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // BP Readings Chart
    const ctx = document.getElementById('bpChart').getContext('2d');
    const bpChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($bp_dates); ?>,
            datasets: [{
                    label: 'Systolic',
                    data: <?php echo json_encode($bp_systolic); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Diastolic',
                    data: <?php echo json_encode($bp_diastolic); ?>,
                    borderColor: '#14b8a6',
                    backgroundColor: 'rgba(20,184,166,0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                },
                title: {
                    display: false
                }
            }
        }
    });
    // Prepare patient data for modal
    const patients = <?php echo json_encode($patients); ?>;
    const allReadings = <?php echo json_encode($readings); ?>;

    function showPatientModal(patientId) {
        const modal = document.getElementById('patientModal');
        modal.style.display = 'flex';
        const patient = patients.find(p => p.id == patientId);
        document.getElementById('modalPatientName').textContent = patient.fullname;
        document.getElementById('modalPatientInfo').innerHTML =
            `<span class='font-semibold'>Age:</span> ${patient.age} &nbsp; <span class='font-semibold'>Gender:</span> ${patient.gender}`;
        // Filter readings for this patient
        const readings = allReadings.filter(r => r.patient_id == patientId);
        // Prepare chart data
        const dates = readings.map(r => new Date(r.reading_time).toLocaleDateString());
        const systolic = readings.map(r => r.systolic);
        const diastolic = readings.map(r => r.diastolic);
        // Render chart
        const ctx = document.getElementById('modalBpChart').getContext('2d');
        if (window.modalBpChartInstance) window.modalBpChartInstance.destroy();
        window.modalBpChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                        label: 'Systolic',
                        data: systolic,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Diastolic',
                        data: diastolic,
                        borderColor: '#14b8a6',
                        backgroundColor: 'rgba(20,184,166,0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
        // Show readings and notes
        let readingsHtml =
            `<h4 class='text-lg font-semibold mb-2 text-purple-600'><i class='fa fa-heartbeat'></i> BP Readings</h4>`;
        readingsHtml +=
            `<table class='w-full table-auto mb-4'><thead><tr><th>Date</th><th>Systolic</th><th>Diastolic</th><th>Doctor's Note</th><th>Action</th></tr></thead><tbody>`;
        readings.forEach(r => {
            readingsHtml +=
                `<tr><td>${new Date(r.reading_time).toLocaleString()}</td><td>${r.systolic}</td><td>${r.diastolic}</td><td>${r.doctor_comment ? r.doctor_comment : ''}</td><td><button class='px-2 py-1 bg-teal-600 text-white rounded' onclick='showNoteForm(${r.id}, "${r.doctor_comment ? r.doctor_comment.replace(/"/g, '&quot;') : ''}")'><i class='fa fa-edit'></i> Add/Edit Note</button></td></tr>`;
        });
        readingsHtml += `</tbody></table>`;
        document.getElementById('modalReadings').innerHTML = readingsHtml;
    }

    function closePatientModal() {
        document.getElementById('patientModal').style.display = 'none';
    }
    // Show note form for a reading
    function showNoteForm(readingId, currentNote) {
        Swal.fire({
            title: 'Add/Edit Doctor Note',
            input: 'text',
            inputValue: currentNote,
            showCancelButton: true,
            confirmButtonText: 'Save',
            preConfirm: (note) => {
                // AJAX to save note
                return fetch('bp_readings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `reading_id=${readingId}&comment=${encodeURIComponent(note)}`
                }).then(response => {
                    if (!response.ok) throw new Error(response.statusText);
                    return response.text();
                }).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Note Saved',
                        confirmButtonColor: '#14b8a6'
                    });
                    setTimeout(() => location.reload(), 1000);
                }).catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Could not save note.'
                    });
                });
            }
        });
    }

    document.getElementById('notifBell').addEventListener('click', function(e) {
        e.stopPropagation();
        var dropdown = document.getElementById('notifDropdown');
        dropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', function(e) {
        var dropdown = document.getElementById('notifDropdown');
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
        }
    });
    </script>
</body>
<script>
// Notification bell dropdown for alerts
document.addEventListener('DOMContentLoaded', function() {
    var bell = document.getElementById('notifBell');
    var dropdown = document.getElementById('notifDropdown');
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
// Sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('doctorSidebar');
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

</html>