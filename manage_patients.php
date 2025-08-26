<?php
// manage_patients.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$stmt = $pdo->prepare('SELECT * FROM patients ORDER BY created_at DESC');
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar Toggle Button for Mobile -->
        <button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 bg-white/80 dark:bg-gray-900/80 p-2 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 focus:outline-none">
            <i class="fa fa-bars text-2xl text-blue-600 dark:text-teal-400"></i>
        </button>
        <!-- Sidebar -->
        <div id="patientsSidebar" class="hidden md:block">
            <?php include __DIR__ . '/components/sidebar.php'; ?>
        </div>
        <!-- Main Content -->
        <div class="flex-1">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <div class="max-w-6xl mx-auto py-8 px-4">
                <h2 class="text-2xl font-bold text-blue-600 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-users"></i> Manage Patients
                </h2>
                <div class="mb-6 flex justify-end">
                    <a href="add_patient.php"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Add Patient
                    </a>
                </div>
                <table class="w-full table-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                    <thead class="bg-blue-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Age</th>
                            <th class="px-4 py-3">Gender</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $p): ?>
                        <tr class="border-b">
                            <td class="px-4 py-3"><?php echo htmlspecialchars($p['fullname']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($p['age']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($p['gender']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($p['email']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($p['phone']); ?></td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="edit_patient.php?id=<?php echo $p['id']; ?>"
                                    class="text-yellow-600 hover:text-yellow-500"><i class="fa-solid fa-edit"></i></a>
                                <a href="delete_patient.php?id=<?php echo $p['id']; ?>"
                                    class="text-red-600 hover:text-red-500"
                                    onclick="return confirm('Delete this patient?')"><i
                                        class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>
    <script>
    // Sidebar toggle for mobile
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.getElementById('patientsSidebar');
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