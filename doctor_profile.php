<?php
// doctor_profile.php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header('Location: doctor_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$doctor_id = $_SESSION['doctor_id'];
$stmt = $pdo->prepare('SELECT fullname, email, phone FROM doctors WHERE id = ?');
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <div class="max-w-md mx-auto py-8 px-4">
        <h2 class="text-2xl font-bold text-teal-600 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-user-md"></i> Doctor Profile
        </h2>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col gap-4">
            <div><strong>Full Name:</strong> <?php echo htmlspecialchars($doctor['fullname']); ?></div>
            <div><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></div>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone']); ?></div>
        </div>
    </div>
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
