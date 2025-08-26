<?php
// doctor_login.php
session_start();
if (isset($_SESSION['doctor_id'])) {
    header('Location: doctor_dashboard.php');
    exit;
}
require_once __DIR__ . '/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password FROM doctors WHERE email = ?');
        $stmt->execute([$email]);
        $doctor = $stmt->fetch();
        if ($doctor && password_verify($password, $doctor['password'])) {
            $_SESSION['doctor_id'] = $doctor['id'];
            header('Location: doctor_dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <main class="flex flex-col items-center justify-center py-16 px-4 min-h-[80vh]">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-6 text-blue-600 dark:text-teal-400 text-center flex items-center gap-2"><i
                    class="fa fa-user-md"></i> Doctor Login</h2>
            <form method="POST" action="doctor_login.php" class="space-y-4">
                <div>
                    <label for="email" class="block mb-1 font-medium"><i
                            class="fa fa-envelope mr-2 text-blue-500"></i>Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label for="password" class="block mb-1 font-medium"><i
                            class="fa fa-lock mr-2 text-blue-500"></i>Password</label>
                    <input type="password" id="password" name="password" required minlength="6"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <button type="submit"
                    class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition flex items-center justify-center gap-2"><i
                        class="fa fa-sign-in-alt"></i> Login</button>
            </form>
        </div>
    </main>
    <?php include __DIR__ . '/components/footer.php'; ?>
    <script>
    <?php if ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Login Error',
        text: '<?php echo addslashes($error); ?>',
        confirmButtonColor: '#2563eb'
    });
    <?php endif; ?>
    </script>
</body>

</html>