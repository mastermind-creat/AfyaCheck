<?php
// edit_patient.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$stmt = $pdo->prepare('SELECT * FROM patients WHERE id = ?');
$stmt->execute([$id]);
$patient = $stmt->fetch();
if (!$patient) {
    die('Patient not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    if ($fullname && $age && $gender && $email) {
        $stmt = $pdo->prepare('UPDATE patients SET fullname = ?, age = ?, gender = ?, email = ?, phone = ? WHERE id = ?');
        if ($stmt->execute([$fullname, $age, $gender, $email, $phone, $id])) {
            $message = 'Patient updated successfully!';
        } else {
            $message = 'Error updating patient.';
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
    <title>Edit Patient | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <!-- Sidebar Toggle Button for Mobile -->
    <button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 bg-white/80 dark:bg-gray-900/80 p-2 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 focus:outline-none">
        <i class="fa fa-bars text-2xl text-blue-600 dark:text-teal-400"></i>
    </button>
    <!-- Sidebar -->
    <div id="editSidebar" class="hidden md:block">
        <?php include __DIR__ . '/components/sidebar.php'; ?>
    </div>
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <div class="max-w-md mx-auto py-8 px-4">
        <h2 class="text-2xl font-bold text-blue-600 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-user-edit"></i> Edit Patient
        </h2>
        <?php if ($message): ?>
        <div class="mb-4 p-3 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        <form method="post" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col gap-4">
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($patient['fullname']); ?>"
                placeholder="Full Name" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
            <input type="number" name="age" value="<?php echo htmlspecialchars($patient['age']); ?>" placeholder="Age"
                class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
            <select name="gender" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
                <option value="Male" <?php if($patient['gender']==='Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if($patient['gender']==='Female') echo 'selected'; ?>>Female</option>
            </select>
            <input type="email" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>"
                placeholder="Email" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>"
                placeholder="Phone" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save Changes
            </button>
        </form>
    </div>
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>
    <script>
    // Sidebar toggle for mobile
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.getElementById('editSidebar');
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