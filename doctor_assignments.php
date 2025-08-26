<?php
// doctor_assignments.php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header('Location: doctor_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$doctor_id = $_SESSION['doctor_id'];
// Fetch assigned patients
$stmt = $pdo->prepare('SELECT p.id, p.fullname, p.age, p.gender, a.assigned_at FROM assignments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY a.assigned_at DESC');
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll();
// Fetch count of new assignments (for demo, count all assigned today)
$new_count = 0;
foreach ($patients as $p) {
    if (date('Y-m-d', strtotime($p['assigned_at'])) == date('Y-m-d')) {
        $new_count++;
    }
}
// Mark notifications as read by default when page is opened
$new_count = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Patients | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        <!-- Main Content -->
        <div class="flex-1">
            <main class="max-w-4xl mx-auto py-8 px-4">
                <h2 class="text-2xl font-bold mb-6 text-green-700 dark:text-green-300 flex items-center gap-2"><i
                        class="fa-solid fa-link"></i> Assigned Patients</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-green-100 dark:bg-gray-700">
                                <th class="px-4 py-2">Full Name</th>
                                <th class="px-4 py-2">Age</th>
                                <th class="px-4 py-2">Gender</th>
                                <th class="px-4 py-2">Assigned At</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p) { ?>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($p['fullname']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($p['age']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($p['gender']); ?></td>
                                <td class="px-4 py-2">
                                    <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($p['assigned_at']))); ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?php
                                    // Status: check if patient has recent BP reading
                                    $stmt = $pdo->prepare('SELECT reading_time FROM bp_readings WHERE patient_id = ? ORDER BY reading_time DESC LIMIT 1');
                                    $stmt->execute([$p['id']]);
                                    $last_reading = $stmt->fetchColumn();
                                    if ($last_reading) {
                                        $days = (time() - strtotime($last_reading)) / 86400;
                                        if ($days < 7) {
                                            echo '<span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs">Active</span>';
                                        } else {
                                            echo '<span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700 text-xs">Inactive</span>';
                                        }
                                    } else {
                                        echo '<span class="px-2 py-1 rounded bg-gray-200 text-gray-700 text-xs">No Data</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-4 py-2 flex gap-2">
                                    <a href="bp_readings.php?patient_id=<?php echo $p['id']; ?>"
                                        class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs"><i
                                            class="fa fa-eye"></i> View BP</a>
                                    <a href="doctor_assignments.php?note_patient_id=<?php echo $p['id']; ?>"
                                        class="px-2 py-1 bg-teal-600 text-white rounded hover:bg-teal-700 text-xs"><i
                                            class="fa fa-comment"></i> Add Note</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php if (count($patients) === 0) { ?>
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <i class="fa-solid fa-info-circle"></i> No patients assigned yet.
                    </div>
                    <?php } ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>