<?php
// add_patient.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    if ($fullname && $age && $gender && $email) {
        $stmt = $pdo->prepare('INSERT INTO patients (fullname, age, gender, email, phone) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$fullname, $age, $gender, $email, $phone])) {
            $message = 'Patient added successfully!';
        } else {
            $message = 'Error adding patient.';
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
    <title>Add Patient | Afyacheck</title>
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
            <div class="max-w-md mx-auto py-8 px-4">
                <h2 class="text-2xl font-bold text-blue-600 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Add Patient
                </h2>
                <?php if ($message): ?>
                <div class="mb-4 p-3 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                <form method="post" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col gap-4">
                    <input type="text" name="fullname" placeholder="Full Name"
                        class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
                    <input type="number" name="age" placeholder="Age"
                        class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
                    <select name="gender" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600"
                        required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <input type="email" name="email" placeholder="Email"
                        class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600" required>
                    <input type="text" name="phone" placeholder="Phone"
                        class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Add Patient
                    </button>
                </form>
            </div>
            <?php include __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
</body>

</html>