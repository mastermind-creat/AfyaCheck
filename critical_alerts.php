<?php
// critical_alerts.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
// Fetch critical BP alerts
$alerts = $pdo->query('SELECT a.*, p.fullname, p.age, p.gender FROM alerts a JOIN patients p ON a.patient_id = p.id ORDER BY a.created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Critical Alerts | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        <!-- Main Content -->
        <div class="flex-1">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <main class="max-w-5xl mx-auto py-8 px-4">
                <h2 class="text-2xl font-bold mb-6 text-red-700 dark:text-red-300 flex items-center gap-2"><i
                        class="fa-solid fa-exclamation-triangle"></i> Critical BP Alerts</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <?php if (count($alerts) > 0): ?>
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
                            <?php foreach ($alerts as $alert): ?>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($alert['fullname']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($alert['age']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($alert['gender']); ?></td>
                                <td class="px-4 py-2 text-red-700"><?php echo htmlspecialchars($alert['message']); ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($alert['created_at']))); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <i class="fa fa-info-circle"></i> No critical alerts at the moment.
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>

</html>