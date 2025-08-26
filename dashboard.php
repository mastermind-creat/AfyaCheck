<?php
// dashboard.php
session_start();
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/db.php';
// Helper to get initials from name
function getInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($words as $w) {
        $initials .= strtoupper($w[0]);
    }
    return $initials;
}

// Helper for health recommendations
function getHealthRecommendation($status) {
    switch ($status) {
        case 'Normal':
            return 'Keep it up! Maintain healthy lifestyle.';
        case 'Low':
            return 'Monitor your BP. Eat a balanced diet.';
        case 'Stage 1 Hypertension':
        case 'Stage 2 Hypertension':
            return 'Consider seeing a doctor, lifestyle changes recommended.';
        case 'Critical':
            return 'Seek medical care immediately.';
        default:
            return 'Watch your diet, exercise regularly.';
    }
}

$patient_id = $_SESSION['patient_id'];
$name = $_SESSION['patient_name'];
$profile_updated = false;
$bp_success = false;
$bp_error = '';
// Handle BP entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['systolic'], $_POST['diastolic'], $_POST['reading_date'], $_POST['reading_time'])) {
    $sys = intval($_POST['systolic']);
    $dia = intval($_POST['diastolic']);
    $pulse = intval($_POST['pulse']);
    $reading_date = $_POST['reading_date'];
    $reading_time = $_POST['reading_time'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $reading_datetime = $reading_date . ' ' . $reading_time;
    // Determine BP status
    $bp_status = 'Normal';
    if ($sys > 180 || $dia > 120) {
        $bp_status = 'Critical';
    } elseif (($sys >= 160 && $sys <= 180) || ($dia >= 100 && $dia <= 120)) {
        $bp_status = 'Stage 2 Hypertension';
    } elseif (($sys >= 140 && $sys < 160) || ($dia >= 90 && $dia < 100)) {
        $bp_status = 'Stage 1 Hypertension';
    } elseif (($sys < 90 && $sys >= 80) || ($dia < 60 && $dia >= 50)) {
        $bp_status = 'Low';
    }
    if ($sys < 50 || $sys > 250 || $dia < 30 || $dia > 150 || $pulse < 30 || $pulse > 200) {
        $bp_error = 'Invalid BP or pulse values.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO bp_readings (patient_id, systolic, diastolic, pulse, reading_time, status, doctor_comment) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$patient_id, $sys, $dia, $pulse, $reading_datetime, $bp_status, $notes])) {
            $bp_success = true;
            // Record alert only for critical
            if ($bp_status === 'Critical') {
                $alert_msg = 'Critical BP reading: Systolic ' . $sys . ', Diastolic ' . $dia . ' at ' . $reading_datetime . ' [Critical]';
                $alert_stmt = $pdo->prepare('INSERT INTO alerts (patient_id, message, created_at, systolic, diastolic, status) VALUES (?, ?, ?, ?, ?, ?)');
                $alert_stmt->execute([$patient_id, $alert_msg, $reading_datetime, $sys, $dia, 'new']);
            }
        } else {
            $bp_error = 'Failed to save BP reading.';
        }
    }
}
// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_email = trim($_POST['profile_email']);
    $new_phone = trim($_POST['profile_phone']);
    $stmt = $pdo->prepare('UPDATE patients SET email = ?, phone = ? WHERE id = ?');
    if ($stmt->execute([$new_email, $new_phone, $patient_id])) {
        $profile_updated = true;
        $_SESSION['patient_name'] = $name; // keep name in session
    }
}
// Fetch BP readings
$stmt = $pdo->prepare('SELECT * FROM bp_readings WHERE patient_id = ? ORDER BY reading_time DESC');
$stmt->execute([$patient_id]);
$readings = $stmt->fetchAll();
// Fetch profile info
$stmt = $pdo->prepare('SELECT email, phone FROM patients WHERE id = ?');
$stmt->execute([$patient_id]);
$profile = $stmt->fetch();
// Fetch upcoming appointments
if (isset($_SESSION['patient_id'])) {
    $patient_id = $_SESSION['patient_id'];
    $stmt = $pdo->prepare('SELECT * FROM appointments WHERE patient_id = ? AND date >= CURDATE() ORDER BY date, time');
    $stmt->execute([$patient_id]);
    $upcoming_appts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Fetch alerts for patient (critical and doctor recommendations)
    $alert_stmt = $pdo->prepare('SELECT * FROM alerts WHERE patient_id = ? AND status = "new" ORDER BY created_at DESC');
    $alert_stmt->execute([$patient_id]);
    $alerts = $alert_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add upcoming appointment alerts to $alerts array
if (!empty($upcoming_appts)) {
    $now = date('Y-m-d');
    foreach ($upcoming_appts as $appt) {
        // Only show appointments within the next 7 days
        $days_diff = (strtotime($appt['date']) - strtotime($now)) / (60*60*24);
        if ($days_diff >= 0 && $days_diff <= 7) {
            $alerts[] = [
                'id' => 'appt_' . $appt['id'],
                'message' => 'Upcoming appointment on ' . date('M d, Y', strtotime($appt['date'])) . ' at ' . date('g:i A', strtotime($appt['time'])) . ' with ' . htmlspecialchars($appt['provider']),
            ];
        }
    }
}

// Handle mark as read (always runs for logged-in patient)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_alert_read']) && isset($_SESSION['patient_id'])) {
    $alert_id = intval($_POST['mark_alert_read']);
    $patient_id = $_SESSION['patient_id'];
    $stmt = $pdo->prepare('UPDATE alerts SET status = "read" WHERE id = ? AND patient_id = ?');
    if ($stmt->execute([$alert_id, $patient_id]) && $stmt->rowCount() > 0) {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <main class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-blue-600 dark:text-teal-400 flex items-center gap-2"><i
                    class="fa fa-tachometer-alt"></i> Welcome, <?php echo htmlspecialchars($name); ?>.</h2>
            <!-- Notification Bell for Alerts -->
            <div class="relative">
                <button id="notifBell" class="relative px-4 py-2 focus:outline-none">
                    <i class="fa fa-bell text-2xl text-blue-600 dark:text-teal-400"></i>
                    <?php if (!empty($alerts)): ?>
                    <span
                        class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold rounded-full px-2 py-0.5 animate-pulse">
                        <?php echo count($alerts); ?>
                    </span>
                    <?php endif; ?>
                </button>
                <!-- Dropdown for unread alerts -->
                <div id="notifDropdown"
                    class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 hidden">
                    <div
                        class="p-4 border-b border-gray-100 dark:border-gray-700 font-bold text-blue-600 dark:text-teal-400 flex items-center gap-2">
                        <i class="fa fa-bell"></i> Notifications
                    </div>
                    <?php if (!empty($alerts)): ?>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($alerts as $alert): ?>
                        <li class="p-4 flex items-center gap-3">
                            <i
                                class="fa <?php echo (strpos($alert['message'], 'Critical BP reading') !== false) ? 'fa-exclamation-triangle text-red-600' : 'fa-user-md text-green-600'; ?> fa-lg"></i>
                            <span class="flex-1"><?php echo htmlspecialchars($alert['message']); ?></span>
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
            <!-- <a href="logout.php"
                class="px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition flex items-center gap-2"><i
                    class="fa fa-sign-out-alt"></i> Logout</a> -->
        </div>
        <!-- Alerts -->
        <?php if (!empty($alerts)): ?>
        <?php foreach ($alerts as $alert): ?>
        <div
            class="mb-6 p-4 rounded-lg flex items-center gap-3 border-l-4 <?php echo (strpos($alert['message'], 'Critical BP reading') !== false) ? 'bg-red-100 border-red-500 text-red-700' : 'bg-green-100 border-green-500 text-green-700'; ?>">
            <i
                class="fa <?php echo (strpos($alert['message'], 'Critical BP reading') !== false) ? 'fa-exclamation-triangle' : 'fa-user-md'; ?> fa-lg"></i>
            <span><?php echo htmlspecialchars($alert['message']); ?></span>
            <form method="POST" style="display:inline; margin-left:auto;">
                <input type="hidden" name="mark_alert_read" value="<?php echo $alert['id']; ?>">
                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 ml-2"><i
                        class="fa fa-check"></i> Mark as Read</button>
            </form>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <!-- Cards Row: BP Entry & BP History -->
        <!-- Profile Modal -->
        <div id="profileModal"
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 w-full max-w-md relative">
                <button onclick="document.getElementById('profileModal').classList.add('hidden')"
                    class="absolute top-4 right-4 text-gray-500 hover:text-red-500"><i
                        class="fa fa-times fa-lg"></i></button>
                <h3 class="text-xl font-bold mb-4 text-blue-600 dark:text-teal-400 flex items-center gap-2"><i
                        class="fa fa-user-edit"></i> Edit Profile</h3>
                <form method="POST" action="dashboard.php" class="space-y-4">
                    <div>
                        <label for="profile_email" class="block mb-1 font-medium"><i
                                class="fa fa-envelope mr-2 text-blue-500"></i>Email</label>
                        <input type="email" id="profile_email" name="profile_email"
                            value="<?php echo htmlspecialchars($profile['email']); ?>" required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label for="profile_phone" class="block mb-1 font-medium"><i
                                class="fa fa-phone mr-2 text-blue-500"></i>Phone</label>
                        <input type="tel" id="profile_phone" name="profile_phone"
                            value="<?php echo htmlspecialchars($profile['phone']); ?>" required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <button type="submit" name="update_profile"
                        class="w-full py-3 bg-teal-600 text-white font-semibold rounded-lg shadow hover:bg-teal-700 transition flex items-center justify-center gap-2"><i
                            class="fa fa-save"></i> Save Changes</button>
                </form>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <!-- Profile Card -->
            <div
                class="bg-gradient-to-br from-blue-100 to-teal-100 dark:from-blue-900 dark:to-teal-900 rounded-xl shadow-lg p-6 flex flex-col items-center">
                <div
                    class="h-16 w-16 rounded-full mb-4 shadow-md flex items-center justify-center bg-blue-600 text-white text-2xl font-bold">
                    <?php echo getInitials($name); ?>
                </div>
                <h3 class="text-xl font-bold text-blue-700 dark:text-teal-300 mb-2 flex items-center gap-2"><i
                        class="fa fa-user"></i> <?php echo htmlspecialchars($name); ?></h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-1"><i class="fa fa-envelope"></i>
                    <?php echo htmlspecialchars($profile['email']); ?></p>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4"><i class="fa fa-phone"></i>
                    <?php echo htmlspecialchars($profile['phone']); ?></p>
                <button onclick="document.getElementById('profileModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 transition flex items-center gap-2"><i
                        class="fa fa-user-cog"></i> Edit Profile</button>
            </div>
            <!-- BP Entry Card -->
            <?php if (isset($_SESSION['patient_id'])): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full">
                <h3 class="text-lg font-bold mb-4 text-red-600 flex items-center gap-2"><i
                        class="fa-solid fa-heart-pulse"></i> Enter BP Reading</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="add_bp_patient" value="1">
                    <div>
                        <label class="block mb-1 font-semibold">Systolic (mmHg)</label>
                        <input type="number" name="systolic" required min="50" max="250"
                            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Diastolic (mmHg)</label>
                        <input type="number" name="diastolic" required min="30" max="150"
                            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Pulse Rate (bpm)</label>
                        <input type="number" name="pulse" required min="30" max="200"
                            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Reading Date</label>
                        <input type="date" name="reading_date" required
                            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Reading Time</label>
                        <input type="time" name="reading_time" required
                            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-1 font-semibold">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600"
                            placeholder="Additional observations..."></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700"><i
                                class="fa-solid fa-save"></i> Add Reading</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            <!-- BP Trends Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col justify-between">
                <h3 class="text-lg font-semibold mb-4 text-green-600 flex items-center gap-2"><i
                        class="fa fa-chart-line"></i> BP Trends</h3>
                <canvas id="bpChart" height="120"></canvas>
            </div>
        </div>
        <!-- BP History Table -->
        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold mb-4 flex items-center gap-2"><i class="fa fa-history text-teal-500"></i>
                BP History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-blue-100 dark:bg-gray-700">
                            <th class="px-4 py-2">Date & Time</th>
                            <th class="px-4 py-2">Systolic</th>
                            <th class="px-4 py-2">Diastolic</th>
                            <th class="px-4 py-2">Pulse</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Recommendation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($readings as $r): ?>
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-2">
                                <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($r['reading_time']))); ?></td>
                            <td
                                class="px-4 py-2 <?php echo ($r['systolic'] > 140 || $r['systolic'] < 90) ? 'text-red-500 font-bold' : ''; ?>">
                                <?php echo htmlspecialchars($r['systolic']); ?></td>
                            <td
                                class="px-4 py-2 <?php echo ($r['diastolic'] > 90 || $r['diastolic'] < 60) ? 'text-red-500 font-bold' : ''; ?>">
                                <?php echo htmlspecialchars($r['diastolic']); ?></td>
                            <td
                                class="px-4 py-2 <?php echo ($r['pulse'] > 100 || $r['pulse'] < 60) ? 'text-red-500 font-bold' : ''; ?>">
                                <?php echo htmlspecialchars($r['pulse']); ?></td>
                            <td class="px-4 py-2">
                                <?php
                                $status = isset($r['status']) ? $r['status'] : '';
                                $badge = 'bg-gray-200 text-gray-700';
                                if ($status === 'Critical') $badge = 'bg-red-100 text-red-700 font-bold';
                                elseif ($status === 'Stage 2 Hypertension') $badge = 'bg-yellow-100 text-yellow-700 font-bold';
                                elseif ($status === 'Stage 1 Hypertension') $badge = 'bg-orange-100 text-orange-700 font-bold';
                                elseif ($status === 'Low') $badge = 'bg-blue-100 text-blue-700 font-bold';
                                elseif ($status === 'Normal') $badge = 'bg-green-100 text-green-700 font-bold';
                                ?>
                                <span
                                    class="px-2 py-1 rounded <?php echo $badge; ?> text-xs"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300">
                                <?php
                                echo getHealthRecommendation($status);
                                if (!empty($r['doctor_comment'])) {
                                    echo '<br><span class="text-green-700 font-semibold">Doctor: ' . htmlspecialchars($r['doctor_comment']) . '</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <!-- Upcoming Appointments Section -->
        <?php if (!empty($upcoming_appts)): ?>
        <div class="bg-white dark:bg-gray-800 mt-4 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-bold mb-4 text-indigo-600 flex items-center gap-2"><i
                    class="fa-solid fa-calendar-check"></i> Upcoming Appointments</h3>
            <table class="w-full table-auto">
                <thead class="bg-indigo-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Provider</th>
                        <th class="px-4 py-3">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcoming_appts as $a): ?>
                    <tr class="border-b">
                        <td class="px-4 py-3"><?php echo htmlspecialchars($a['type']); ?></td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($a['date']); ?></td>
                        <td class="px-4 py-3"><?php echo date('g:i A', strtotime($a['time'])); ?></td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($a['provider']); ?></td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($a['notes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/components/footer.php'; ?>
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
    // Chart.js for BP trends
    if (!window.bpChartInitialized) {
        const ctx = document.getElementById('bpChart').getContext('2d');
        const bpChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php foreach ($readings as $r) echo "'" . date('M d H:i', strtotime($r['reading_time'])) . "',"; ?>
                ],
                datasets: [{
                        label: 'Systolic',
                        data: [<?php foreach ($readings as $r) echo $r['systolic'] . ','; ?>],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.1)',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Diastolic',
                        data: [<?php foreach ($readings as $r) echo $r['diastolic'] . ','; ?>],
                        borderColor: '#14b8a6',
                        backgroundColor: 'rgba(20,184,166,0.1)',
                        fill: false,
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
        window.bpChartInitialized = true;
    }
    // SweetAlert for BP entry
    // document.getElementById('bpForm').addEventListener('submit', function(e) {
    //     var sys = document.getElementById('systolic').value;
    //     var dia = document.getElementById('diastolic').value;
    //     if (sys < 90 || sys > 140 || dia < 60 || dia > 90) {
    //         e.preventDefault();
    //         Swal.fire({
    //             icon: 'warning',
    //             title: 'Critical BP Reading',
    //             text: 'Your BP reading is outside the normal range. Please consult your doctor.',
    //             confirmButtonColor: '#2563eb'
    //         });
    //     }
    // });
    // SweetAlert for BP save
    <?php if ($bp_success): ?>
    Swal.fire({
        icon: 'success',
        title: 'BP Reading Saved',
        text: 'Your BP reading has been saved successfully.',
        confirmButtonColor: '#2563eb'
    }).then(() => {
        window.location.href = 'dashboard.php';
    });
    <?php elseif ($bp_error): ?>
    Swal.fire({
        icon: 'error',
        title: 'BP Entry Error',
        text: '<?php echo addslashes($bp_error); ?>',
        confirmButtonColor: '#2563eb'
    });
    <?php endif; ?>
    // SweetAlert for profile update
    <?php if ($profile_updated): ?>
    Swal.fire({
        icon: 'success',
        title: 'Profile Updated',
        text: 'Your profile has been updated.',
        confirmButtonColor: '#2563eb'
    });
    <?php endif; ?>
    </script>
</body>

</html>