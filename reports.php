<?php
// reports.php
session_start();
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['doctor_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';

// Fetch summary data
$total_patients = $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
$total_doctors = $pdo->query('SELECT COUNT(*) FROM doctors')->fetchColumn();
$total_readings = $pdo->query('SELECT COUNT(*) FROM bp_readings')->fetchColumn();
$avg_systolic = $pdo->query('SELECT AVG(systolic) FROM bp_readings')->fetchColumn();
$avg_diastolic = $pdo->query('SELECT AVG(diastolic) FROM bp_readings')->fetchColumn();
$recent_patients = $pdo->query('SELECT fullname, email, created_at FROM patients ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recent_doctors = $pdo->query('SELECT fullname, email, created_at FROM doctors ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recent_readings = $pdo->query('SELECT r.*, p.fullname FROM bp_readings r JOIN patients p ON r.patient_id = p.id ORDER BY r.reading_time DESC LIMIT 5')->fetchAll();
// Additional analytics
$total_assignments = $pdo->query('SELECT COUNT(*) FROM assignments')->fetchColumn();
$assignments_per_doctor = $pdo->query('SELECT d.fullname, COUNT(a.id) AS total FROM doctors d LEFT JOIN assignments a ON d.id = a.doctor_id GROUP BY d.id')->fetchAll();
$bp_trend = $pdo->query('SELECT DATE(reading_time) as date, AVG(systolic) as avg_s, AVG(diastolic) as avg_d FROM bp_readings GROUP BY DATE(reading_time) ORDER BY date DESC LIMIT 14')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar Toggle Button for Mobile -->
        <button id="sidebarToggle"
            class="md:hidden fixed top-4 left-4 z-50 bg-white/80 dark:bg-gray-900/80 p-2 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 focus:outline-none">
            <i class="fa fa-bars text-2xl text-blue-600 dark:text-teal-400"></i>
        </button>
        <!-- Sidebar -->
        <div id="reportsSidebar" class="hidden md:block">
            <?php include __DIR__ . '/components/sidebar.php'; ?>
        </div>
        <!-- Main Content -->
        <div class="flex-1 md:ml-64">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <div class="max-w-7xl mx-auto py-8 px-4">
                <h2 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-8 flex items-center gap-3">
                    <i class="fa-solid fa-file-chart-line"></i> Reports Overview
                </h2>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <!-- Patients -->
                    <div
                        class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl shadow-lg p-6 hover:scale-105 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm opacity-80">Patients</h3>
                                <p class="text-3xl font-bold"><?php echo $total_patients; ?></p>
                            </div>
                            <i class="fa-solid fa-users text-3xl opacity-80"></i>
                        </div>
                    </div>
                    <!-- Doctors -->
                    <div
                        class="bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-2xl shadow-lg p-6 hover:scale-105 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm opacity-80">Doctors</h3>
                                <p class="text-3xl font-bold"><?php echo $total_doctors; ?></p>
                            </div>
                            <i class="fa-solid fa-user-md text-3xl opacity-80"></i>
                        </div>
                    </div>
                    <!-- BP Readings -->
                    <div
                        class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg p-6 hover:scale-105 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm opacity-80">BP Readings</h3>
                                <p class="text-3xl font-bold"><?php echo $total_readings; ?></p>
                            </div>
                            <i class="fa-solid fa-heartbeat text-3xl opacity-80"></i>
                        </div>
                    </div>
                    <!-- Assignments -->
                    <div
                        class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl shadow-lg p-6 hover:scale-105 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm opacity-80">Assignments</h3>
                                <p class="text-3xl font-bold"><?php echo $total_assignments; ?></p>
                            </div>
                            <i class="fa-solid fa-clipboard-check text-3xl opacity-80"></i>
                        </div>
                    </div>
                </div>

                <!-- BP Trend Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-10">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">BP Trend (Last 14 Days)</h3>
                    <canvas id="bpTrendChart" height="120"></canvas>
                </div>

                <!-- Recent Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Patients -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold mb-3 text-blue-600 dark:text-blue-300 flex items-center gap-2">
                            <i class="fa-solid fa-users"></i> Recent Patients
                        </h3>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <th class="px-3 py-2 text-left">Name</th>
                                    <th class="px-3 py-2">Email</th>
                                    <th class="px-3 py-2">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_patients as $p): ?>
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($p['fullname']); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($p['email']); ?></td>
                                    <td class="px-3 py-2"><?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Recent Doctors -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold mb-3 text-teal-600 dark:text-teal-300 flex items-center gap-2">
                            <i class="fa-solid fa-user-md"></i> Recent Doctors
                        </h3>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <th class="px-3 py-2 text-left">Name</th>
                                    <th class="px-3 py-2">Email</th>
                                    <th class="px-3 py-2">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_doctors as $d): ?>
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($d['fullname']); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($d['email']); ?></td>
                                    <td class="px-3 py-2"><?php echo date('M d, Y', strtotime($d['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Patients Table with Search, Print, PDF Export -->
                <div class="bg-white dark:bg-gray-800 rounded-lg mt-4 shadow-lg p-6 mb-8">
                    <h3 class="font-semibold text-blue-700 dark:text-blue-300 mb-2">Patients List</h3>
                    <input type="text" id="patientSearch" placeholder="Search patients..."
                        class="mb-4 px-3 py-2 rounded border w-full max-w-xs">
                    <div class="flex gap-2 mb-4">
                        <button id="printPatientsBtn"
                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs flex items-center gap-1"><i
                                class="fa-solid fa-print"></i> Print</button>
                        <button id="pdfPatientsBtn"
                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs flex items-center gap-1"><i
                                class="fa-solid fa-file-pdf"></i> Export PDF</button>
                    </div>
                    <table id="patientsTable" class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-blue-100 dark:bg-gray-700">
                                <th class="px-4 py-2">ID</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Age</th>
                                <th class="px-4 py-2">Gender</th>
                                <th class="px-4 py-2">Email</th>
                                <th class="px-4 py-2">Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pdo->query('SELECT * FROM patients ORDER BY fullname ASC') as $p): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo $p['id']; ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($p['fullname']); ?></td>
                                <td class="px-4 py-2"><?php echo $p['age']; ?></td>
                                <td class="px-4 py-2"><?php echo $p['gender']; ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($p['email']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($p['phone']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Doctors Table with Search, Print, PDF Export -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
                    <h3 class="font-semibold text-teal-700 dark:text-teal-300 mb-2">Doctors List</h3>
                    <input type="text" id="doctorSearch" placeholder="Search doctors..."
                        class="mb-4 px-3 py-2 rounded border w-full max-w-xs">
                    <div class="flex gap-2 mb-4">
                        <button id="printDoctorsBtn"
                            class="px-3 py-1 bg-teal-600 text-white rounded hover:bg-teal-700 text-xs flex items-center gap-1"><i
                                class="fa-solid fa-print"></i> Print</button>
                        <button id="pdfDoctorsBtn"
                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs flex items-center gap-1"><i
                                class="fa-solid fa-file-pdf"></i> Export PDF</button>
                    </div>
                    <table id="doctorsTable" class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-teal-100 dark:bg-gray-700">
                                <th class="px-4 py-2">ID</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Specialty</th>
                                <th class="px-4 py-2">Email</th>
                                <th class="px-4 py-2">Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pdo->query('SELECT * FROM doctors ORDER BY fullname ASC') as $d): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo $d['id']; ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($d['fullname']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($d['specialty']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($d['email']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($d['phone']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Doctor-specific report section -->
                <?php
                $is_doctor = isset($_SESSION['doctor_id']);
                if ($is_doctor) {
                    $doctor_id = $_SESSION['doctor_id'];
                    // Fetch assigned patients
                    $stmt = $pdo->prepare('SELECT p.id, p.fullname FROM assignments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY p.fullname ASC');
                    $stmt->execute([$doctor_id]);
                    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    // Fetch BP readings for assigned patients
                    $stmt = $pdo->prepare('SELECT r.*, p.fullname FROM bp_readings r JOIN patients p ON r.patient_id = p.id JOIN assignments a ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY r.reading_time DESC');
                    $stmt->execute([$doctor_id]);
                    $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                ?>

                <?php if ($is_doctor): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold mb-2 text-blue-700 flex items-center gap-2"><i
                            class="fa-solid fa-file-medical"></i> Patient Blood Pressure Report</h2>
                    <p class="mb-4 text-gray-600 dark:text-gray-300">Detailed report of blood pressure readings for
                        assigned patients. Select a patient to generate a report for a single patient.</p>
                    <form method="GET" class="mb-4 flex gap-2 items-center">
                        <label for="single_patient" class="font-semibold">Select Patient:</label>
                        <select name="single_patient" id="single_patient" class="px-2 py-1 rounded border">
                            <option value="">All Patients</option>
                            <?php foreach ($patients as $p): ?>
                            <option value="<?php echo $p['id']; ?>"
                                <?php if (isset($_GET['single_patient']) && $_GET['single_patient'] == $p['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($p['fullname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"><i
                                class="fa fa-search"></i> Generate</button>
                    </form>
                    <?php
                    $filtered_readings = $readings;
                    if (isset($_GET['single_patient']) && $_GET['single_patient']) {
                        $pid = intval($_GET['single_patient']);
                        $filtered_readings = array_filter($readings, function($r) use ($pid) {
                            return $r['patient_id'] == $pid;
                        });
                    }
                    ?>
                    <table id="doctorPatientsTable" class="w-full table-auto mb-4">
                        <thead class="bg-blue-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3">Patient</th>
                                <th class="px-4 py-3">Systolic</th>
                                <th class="px-4 py-3">Diastolic</th>
                                <th class="px-4 py-3">Pulse</th>
                                <th class="px-4 py-3">Date & Time</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Doctor's Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_readings as $r): ?>
                            <tr class="border-b">
                                <td class="px-4 py-3"><?php echo htmlspecialchars($r['fullname']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($r['systolic']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($r['diastolic']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($r['pulse']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($r['reading_time']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($r['status']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($r['doctor_comment']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button onclick="printTable('doctorPatientsTable')"
                        class="px-4 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700 mr-2"
                        id="printDoctorPatientsBtn"><i class="fa fa-print"></i> Print</button>
                    <button onclick="exportPDF('doctorPatientsTable', 'doctor_patients_report.pdf')"
                        class="px-4 py-2 bg-green-600 text-white rounded font-bold hover:bg-green-700"
                        id="pdfDoctorPatientsBtn"><i class="fa fa-file-pdf"></i> Export PDF</button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Chart.js -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
            const ctx = document.getElementById('bpTrendChart').getContext('2d');
            const bpTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($bp_trend, 'date')); ?>,
                    datasets: [{
                            label: 'Avg Systolic',
                            data: <?php echo json_encode(array_column($bp_trend, 'avg_s')); ?>,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.2)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Avg Diastolic',
                            data: <?php echo json_encode(array_column($bp_trend, 'avg_d')); ?>,
                            borderColor: '#14b8a6',
                            backgroundColor: 'rgba(20, 184, 166, 0.2)',
                            fill: true,
                            tension: 0.3,
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
                            beginAtZero: false
                        }
                    }
                }
            });
            </script>
        </div>
    </div>
    <!-- Footer -->
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>

</html>
<?php
if (isset($_GET['print'])) {
    if ($_GET['print'] === 'patients') {
        $patients = $pdo->query('SELECT * FROM patients ORDER BY fullname ASC')->fetchAll();
        echo '<!DOCTYPE html><html><head><title>Patients List</title><style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;} th{background:#f3f4f6;} h2{margin-bottom:1rem;}</style></head><body>';
        echo '<h2>Patients List</h2><table><tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Email</th><th>Phone</th></tr>';
        foreach ($patients as $p) {
            echo '<tr><td>'.$p['id'].'</td><td>'.htmlspecialchars($p['fullname']).'</td><td>'.$p['age'].'</td><td>'.$p['gender'].'</td><td>'.htmlspecialchars($p['email']).'</td><td>'.htmlspecialchars($p['phone']).'</td></tr>';
        }
        echo '</table><script>window.print();</script></body></html>';
        exit;
    }
    if ($_GET['print'] === 'doctors') {
        $doctors = $pdo->query('SELECT * FROM doctors ORDER BY fullname ASC')->fetchAll();
        echo '<!DOCTYPE html><html><head><title>Doctors List</title><style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;} th{background:#f3f4f6;} h2{margin-bottom:1rem;}</style></head><body>';
        echo '<h2>Doctors List</h2><table><tr><th>ID</th><th>Name</th><th>Specialty</th><th>Email</th><th>Phone</th></tr>';
        foreach ($doctors as $d) {
            echo '<tr><td>'.$d['id'].'</td><td>'.htmlspecialchars($d['fullname']).'</td><td>'.htmlspecialchars($d['specialty']).'</td><td>'.htmlspecialchars($d['email']).'</td><td>'.htmlspecialchars($d['phone']).'</td></tr>';
        }
        echo '</table><script>window.print();</script></body></html>';
        exit;
    }
    if ($_GET['print'] === 'patient_record' && isset($_GET['patient_id'])) {
        $id = intval($_GET['patient_id']);
        $patient = $pdo->prepare('SELECT * FROM patients WHERE id = ?');
        $patient->execute([$id]);
        $p = $patient->fetch();
        if ($p) {
            echo '<!DOCTYPE html><html><head><title>Patient Record</title><style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;} th{background:#f3f4f6;} h2{margin-bottom:1rem;}</style></head><body>';
            echo '<h2>Patient Record</h2>';
            echo '<p><strong>Name:</strong> '.htmlspecialchars($p['fullname']).'</p>';
            echo '<p><strong>Age:</strong> '.$p['age'].'</p>';
            echo '<p><strong>Gender:</strong> '.$p['gender'].'</p>';
            echo '<p><strong>Email:</strong> '.htmlspecialchars($p['email']).'</p>';
            echo '<p><strong>Phone:</strong> '.htmlspecialchars($p['phone']).'</p>';
            $readings = $pdo->prepare('SELECT * FROM bp_readings WHERE patient_id = ? ORDER BY reading_time DESC');
            $readings->execute([$id]);
            echo '<h3 style="margin-top:1rem;">BP Readings</h3><table><tr><th>Date</th><th>Systolic</th><th>Diastolic</th></tr>';
            foreach ($readings as $r) {
                echo '<tr><td>'.date('M d, Y H:i', strtotime($r['reading_time'])).'</td><td>'.$r['systolic'].'</td><td>'.$r['diastolic'].'</td></tr>';
            }
            echo '</table><script>window.print();</script></body></html>';
        } else {
            echo '<!DOCTYPE html><html><body><p>Patient not found.</p></body></html>';
        }
        exit;
    }
}
if (isset($_GET['view_bp'])) {
    $id = intval($_GET['view_bp']);
    $patient = $pdo->prepare('SELECT * FROM patients WHERE id = ?');
    $patient->execute([$id]);
    $p = $patient->fetch();
    if ($p) {
        echo '<h2 style="font-size:1.5rem;">BP History for '.htmlspecialchars($p['fullname']).'</h2>';
        $readings = $pdo->prepare('SELECT * FROM bp_readings WHERE patient_id = ? ORDER BY reading_time DESC');
        $readings->execute([$id]);
        echo '<table border="1" cellpadding="8" style="width:100%;margin-top:1rem;"><tr><th>Date</th><th>Systolic</th><th>Diastolic</th></tr>';
        foreach ($readings as $r) {
            echo '<tr><td>'.date('M d, Y H:i', strtotime($r['reading_time'])).'</td><td>'.$r['systolic'].'</td><td>'.$r['diastolic'].'</td></tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Patient not found.</p>';
    }
    exit;
}
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$_GET['export'].'_report.csv"');
    $out = fopen('php://output', 'w');
    if ($_GET['export'] === 'patients') {
        fputcsv($out, ['ID','Name','Age','Gender','Email','Phone']);
        $patients = $pdo->query('SELECT * FROM patients ORDER BY fullname ASC')->fetchAll();
        foreach ($patients as $p) {
            fputcsv($out, [$p['id'],$p['fullname'],$p['age'],$p['gender'],$p['email'],$p['phone']]);
        }
    }
    if ($_GET['export'] === 'doctors') {
        fputcsv($out, ['ID','Name','Specialty','Email','Phone']);
        $doctors = $pdo->query('SELECT * FROM doctors ORDER BY fullname ASC')->fetchAll();
        foreach ($doctors as $d) {
            fputcsv($out, [$d['id'],$d['fullname'],$d['specialty'],$d['email'],$d['phone']]);
        }
    }
    if ($_GET['export'] === 'bp') {
        fputcsv($out, ['ID','Patient','Systolic','Diastolic','Date','Doctor Comment']);
        $readings = $pdo->query('SELECT r.*, p.fullname FROM bp_readings r JOIN patients p ON r.patient_id = p.id ORDER BY r.reading_time DESC')->fetchAll();
        foreach ($readings as $r) {
            fputcsv($out, [$r['id'],$r['fullname'],$r['systolic'],$r['diastolic'],$r['reading_time'],$r['doctor_comment']]);
        }
    }
    fclose($out);
    exit;
}
?>
<!-- Add html2canvas for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
// Sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('reportsSidebar');
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

function printTable(tableId, heading, subheading) {
    var printContents = document.getElementById(tableId).outerHTML;
    var win = window.open('', '', 'height=700,width=900');
    win.document.write(
        '<html><head><title>Print Table</title><style>table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:8px;}th{background:#f3f4f6;}</style></head><body>'
    );
    win.document.write('<h2 style="text-align:center;">' + heading + '</h2>');
    win.document.write('<h4 style="text-align:center;">' + subheading + '</h4>');
    win.document.write(printContents);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() {
        win.print();
        win.close();
    }, 500);
}

function exportPDF(tableId, filename, heading, subheading) {
    var table = document.getElementById(tableId);
    html2canvas(table).then(function(canvas) {
        var imgData = canvas.toDataURL('image/png');
        var pdf = new jspdf.jsPDF('l', 'pt', 'a4');
        var pageWidth = pdf.internal.pageSize.getWidth();
        var pageHeight = pdf.internal.pageSize.getHeight();
        pdf.setFontSize(22);
        pdf.text(heading, pageWidth / 2, 40, {
            align: 'center'
        });
        pdf.setFontSize(14);
        pdf.text(subheading, pageWidth / 2, 65, {
            align: 'center'
        });
        var imgWidth = pageWidth - 40;
        var imgHeight = canvas.height * imgWidth / canvas.width;
        pdf.addImage(imgData, 'PNG', 20, 80, imgWidth, imgHeight);
        pdf.save(filename);
    });
}

// Update button event handlers to pass headings
function setupReportButtons() {
    document.getElementById('printPatientsBtn').onclick = function() {
        printTable('patientsTable', 'Patient Report',
            'Detailed report of all registered patients in Afyacheck Solution Management System');
    };
    document.getElementById('pdfPatientsBtn').onclick = function() {
        exportPDF('patientsTable', 'patients_report.pdf', 'Patient Report',
            'Detailed report of all registered patients in Afyacheck Solution Management System');
    };
    document.getElementById('printDoctorsBtn').onclick = function() {
        printTable('doctorsTable', 'Doctor Report', 'Afyacheck Solution Management System');
    };
    document.getElementById('pdfDoctorsBtn').onclick = function() {
        exportPDF('doctorsTable', 'doctors_report.pdf', 'Doctor Report', 'Afyacheck Solution Management System');
    };
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('patientSearch').addEventListener('keyup', function() {
        filterTable('patientSearch', 'patientsTable');
    });
    document.getElementById('doctorSearch').addEventListener('keyup', function() {
        filterTable('doctorSearch', 'doctorsTable');
    });
    setupReportButtons();
    // Setup doctor patient report buttons if present
    var printDoctorBtn = document.getElementById('printDoctorPatientsBtn');
    var pdfDoctorBtn = document.getElementById('pdfDoctorPatientsBtn');
    if (printDoctorBtn && pdfDoctorBtn) {
        printDoctorBtn.onclick = function() {
            var select = document.getElementById('single_patient');
            var selected = select ? select.options[select.selectedIndex] : null;
            var heading = 'Afyacheck Solution Management System';
            var subheading = 'Kombewa Sub County Hospital';
            var title = '';
            if (selected && selected.value) {
                title = 'BP Report for ' + selected.text;
            } else {
                title = 'Blood Pressure Report for All Assigned Patients';
            }
            printTable('doctorPatientsTable', heading + '<br><span style="font-size:16px;">' + subheading +
                '</span><br><span style="font-size:18px;font-weight:bold;">' + title + '</span>', '');
        };
        pdfDoctorBtn.onclick = function() {
            var select = document.getElementById('single_patient');
            var selected = select ? select.options[select.selectedIndex] : null;
            var heading = 'Afyacheck Solution Management System';
            var subheading = 'Kombewa Sub County Hospital';
            var title = '';
            if (selected && selected.value) {
                title = 'BP Report for ' + selected.text;
            } else {
                title = 'Blood Pressure Report for All Assigned Patients';
            }
            exportPDF('doctorPatientsTable', 'doctor_patients_report.pdf', heading + '\n' + subheading +
                '\n' + title, '');
        };
    }
});
</script>