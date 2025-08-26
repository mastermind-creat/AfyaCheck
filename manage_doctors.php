<?php
// manage_doctors.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$stmt = $pdo->prepare('SELECT * FROM doctors ORDER BY created_at DESC');
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <div class="flex min-h-screen">
        <!-- Admin Sidebar -->
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        <!-- Main Content -->
        <div class="flex-1">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <div class="max-w-6xl mx-auto py-8 px-4">
                <h2 class="text-2xl font-bold text-teal-600 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-user-md"></i> Manage Doctors
                </h2>
                <div class="mb-6 flex justify-end">
                    <a href="add_doctor.php"
                        class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Add Doctor
                    </a>
                </div>
                <table class="w-full table-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                    <thead class="bg-teal-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $d): ?>
                        <tr class="border-b">
                            <td class="px-4 py-3"><?php echo htmlspecialchars($d['fullname']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($d['email']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($d['phone']); ?></td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="edit_doctor.php?id=<?php echo $d['id']; ?>"
                                    class="text-yellow-600 hover:text-yellow-500"><i class="fa-solid fa-edit"></i></a>
                                <a href="delete_doctor.php?id=<?php echo $d['id']; ?>" class="text-red-600 hover:text-red-500"
                                    onclick="return confirm('Delete this doctor?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php include __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
</body>

</html>