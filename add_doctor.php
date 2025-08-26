<?php
// add_doctor.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (!$fullname || !$email || !$phone || !$password || !$confirm_password) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM doctors WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO doctors (fullname, email, phone, password) VALUES (?, ?, ?, ?)');
            if ($stmt->execute([$fullname, $email, $phone, $hashed])) {
                $success = true;
            } else {
                $error = 'Failed to add doctor.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <div class="flex min-h-[80vh]">
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        <main class="flex flex-col items-center justify-center py-16 px-4 w-full">
            <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <h2
                    class="text-2xl font-bold mb-6 text-blue-600 dark:text-teal-400 text-center flex items-center gap-2">
                    <i class="fa fa-user-md"></i> Add Doctor
                </h2>
                <form method="POST" action="add_doctor.php" class="space-y-4">
                    <div>
                        <label for="fullname" class="block mb-1 font-medium"><i
                                class="fa fa-user mr-2 text-blue-500"></i>Full Name</label>
                        <input type="text" id="fullname" name="fullname" required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label for="email" class="block mb-1 font-medium"><i
                                class="fa fa-envelope mr-2 text-blue-500"></i>Email</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label for="phone" class="block mb-1 font-medium"><i
                                class="fa fa-phone mr-2 text-blue-500"></i>Phone</label>
                        <input type="tel" id="phone" name="phone" required maxlength="10" pattern="^07[0-9]{8}$"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block mb-1 font-medium"><i
                                    class="fa fa-lock mr-2 text-blue-500"></i>Password</label>
                            <input type="password" id="password" name="password" required minlength="6"
                                class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div>
                            <label for="confirm_password" class="block mb-1 font-medium"><i
                                    class="fa fa-lock mr-2 text-blue-500"></i>Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                                class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition flex items-center justify-center gap-2"><i
                            class="fa fa-user-plus"></i> Add Doctor</button>
                </form>
            </div>
        </main>

        <script>
        <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: 'Doctor Added',
            text: 'Doctor account has been created successfully.',
            confirmButtonColor: '#2563eb'
        });
        <?php elseif ($error): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#2563eb'
        });
        <?php endif; ?>
        </script>
    </div>
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>

</html>