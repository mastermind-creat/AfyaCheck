<?php
// assignments.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';

// Handle assignment form submission
$assign_message = '';
if (isset($_POST['assign_patient'])) {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    if ($doctor_id && $patient_id) {
        // Prevent duplicate assignments
        $check = $pdo->prepare('SELECT id FROM assignments WHERE doctor_id = ? AND patient_id = ?');
        $check->execute([$doctor_id, $patient_id]);
        if ($check->fetch()) {
            $assign_message = "This patient is already assigned to this doctor.";
        } else {
            $stmt = $pdo->prepare('INSERT INTO assignments (doctor_id, patient_id, assigned_at) VALUES (?, ?, NOW())');
            if ($stmt->execute([$doctor_id, $patient_id])) {
                $assign_message = "Patient assigned successfully!";
            } else {
                $assign_message = "Failed to assign patient. Please try again.";
            }
        }
    } else {
        $assign_message = "Please select both doctor and patient.";
    }
}

// Fetch assignments
$stmt = $pdo->query('SELECT a.id, a.assigned_at, d.fullname AS doctor_name, p.fullname AS patient_name FROM assignments a JOIN doctors d ON a.doctor_id = d.id JOIN patients p ON a.patient_id = p.id ORDER BY a.assigned_at DESC');
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch doctors and patients for dropdowns
$doctors = $pdo->query('SELECT id, fullname FROM doctors ORDER BY fullname ASC')->fetchAll();
$patients = $pdo->query('SELECT id, fullname FROM patients ORDER BY fullname ASC')->fetchAll();

// Check for critical BP readings and alert admin
$critical_patients = [];
$stmt = $pdo->query('SELECT r.*, p.fullname, p.age, p.gender FROM bp_readings r JOIN patients p ON r.patient_id = p.id WHERE r.systolic > 180 OR r.systolic < 80 OR r.diastolic > 120 OR r.diastolic < 50 ORDER BY r.reading_time DESC');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $critical_patients[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments Management | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col py-8 px-4 sticky top-0 h-screen z-10">
            <div class="flex items-center gap-2 mb-8">
                <img src="assets/logo.png" alt="Afyacheck Logo" class="h-8 w-8 rounded-full shadow-md">
                <span class="text-xl font-bold text-blue-600 dark:text-teal-400">Admin Panel</span>
            </div>
            <nav class="flex flex-col gap-4">
                <a href="admin_dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="manage_patients.php" class="nav-link"><i class="fa-solid fa-users"></i> Manage Patients</a>
                <a href="manage_doctors.php" class="nav-link"><i class="fa-solid fa-user-md"></i> Manage Doctors</a>
                <a href="add_patient.php" class="nav-link"><i class="fa-solid fa-user-plus"></i> Add Patient</a>
                <a href="reports.php" class="nav-link purple"><i class="fa-solid fa-file-chart-line"></i> Reports</a>
                <a href="bp_readings.php" class="nav-link purple"><i class="fa-solid fa-heartbeat"></i> BP Readings</a>
                <a href="assignments.php" class="nav-link green"><i class="fa-solid fa-link"></i> Assignments</a>
                <a href="logout.php" class="nav-link red mt-8"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
            <style>
            .nav-link {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1rem;
                border-radius: 0.5rem;
                font-weight: 500;
                transition: background 0.2s;
                color: #2563eb;
                /* blue-700 */
            }

            .nav-link:hover {
                background: #e0f2fe;
            }

            .nav-link.purple {
                color: #7c3aed;
            }

            .nav-link.purple:hover {
                background: #ede9fe;
            }

            .nav-link.green {
                color: #059669;
            }

            .nav-link.green:hover {
                background: #d1fae5;
            }

            .nav-link.red {
                color: #dc2626;
            }

            .nav-link.red:hover {
                background: #fee2e2;
            }

            .nav-link.mt-8 {
                margin-top: 2rem;
            }

            @media (max-width: 768px) {
                aside {
                    display: none;
                }
            }
            </style>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 flex flex-col gap-8 px-4 py-8 max-w-6xl mx-auto">
            <!-- Assign Patients to Doctors -->
            <section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4 text-teal-600 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Assign Patient to Doctor
                </h3>
                <?php if ($assign_message): ?>
                <div class="mb-4 p-3 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                    <?php echo htmlspecialchars($assign_message); ?>
                </div>
                <?php endif; ?>
                <form method="post" class="flex flex-col md:flex-row gap-4 items-center">
                    <select name="doctor_id" class="form-select" required>
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $d): ?>
                        <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['fullname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="patient_id" class="form-select" required>
                        <option value="">Select Patient</option>
                        <?php foreach ($patients as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['fullname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_patient"
                        class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Assign
                    </button>
                </form>
                <style>
                .form-select {
                    padding: 0.5rem 1rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    /* gray-300 */
                    background: #f9fafb;
                    /* gray-50 */
                    min-width: 200px;
                }

                .form-select:focus {
                    outline: 2px solid #14b8a6;
                }
                </style>
            </section>
            <!-- Assignments Table -->
            <section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-green-600 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-link"></i> Assignments Management
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto rounded-xl">
                        <thead class="bg-green-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">Doctor</th>
                                <th class="px-4 py-3 text-left">Patient</th>
                                <th class="px-4 py-3 text-left">Assigned At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                            <tr class="border-b hover:bg-green-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3"><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($a['patient_name']); ?></td>
                                <td class="px-4 py-3"><?php echo date('M d, Y H:i', strtotime($a['assigned_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No
                                    assignments found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- Footer -->
        </main>
    </div>
    <script>
    <?php if (!empty($critical_patients)): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Critical BP Alert!',
        html: `<?php foreach ($critical_patients as $cp) {
                echo '<b>Patient:</b> '.htmlspecialchars($cp['fullname']).'<br>';
                echo '<b>Age:</b> '.htmlspecialchars($cp['age']).'<br>';
                echo '<b>Gender:</b> '.htmlspecialchars($cp['gender']).'<br>';
                echo '<b>Systolic:</b> '.htmlspecialchars($cp['systolic']).' mmHg<br>';
                echo '<b>Diastolic:</b> '.htmlspecialchars($cp['diastolic']).' mmHg<br>';
                echo '<b>Date:</b> '.date('M d, Y H:i', strtotime($cp['reading_time'])).'<hr>';
            } ?>`,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Acknowledge'
    });
    <?php endif; ?>
    </script>
</body>

</html>