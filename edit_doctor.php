<?php
// edit_doctor.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$stmt = $pdo->prepare('SELECT * FROM doctors WHERE id = ?');
$stmt->execute([$id]);
$doctor = $stmt->fetch();
if (!$doctor) {
    die('Doctor not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    if ($fullname && $email) {
        $stmt = $pdo->prepare('UPDATE doctors SET fullname = ?, email = ?, phone = ? WHERE id = ?');
        if ($stmt->execute([$fullname, $email, $phone, $id])) {
            $message = 'Doctor updated successfully!';
        } else {
            $message = 'Error updating doctor.';
        }
    } else {
        $message = 'Please fill all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <div class="max-w-md mx-auto py-8 px-4">
        <h2 class="text-2xl font-bold text-teal-600 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-user-edit"></i> Edit Doctor
        </h2>
        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="post" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col gap-4">
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($doctor['fullname']); ?>" placeholder="Full Name" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" placeholder="Email" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" placeholder="Phone" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 font-semibold flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save Changes
            </button>
        </form>
    </div>
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
