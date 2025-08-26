<?php
require_once __DIR__ . '/db.php';
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (!$fullname || !$age || !$gender || !$email || !$phone || !$password || !$confirm_password) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif ($age < 1) {
        $error = 'Invalid age.';
    } elseif (!preg_match('/^07[0-9]{8}$/', $phone)) {
        $error = 'Phone number must start with 07 and be exactly 10 digits.';
    } else {
        // Check if email or phone already exists
        $stmt = $pdo->prepare('SELECT id FROM patients WHERE email = ? OR phone = ?');
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $error = 'Email or phone number already registered.';
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO patients (fullname, age, gender, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt->execute([$fullname, $age, $gender, $email, $phone, $hashed])) {
                $success = true;
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Register | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="favicon.ico">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <main class="flex flex-col items-center justify-center py-16 px-4 min-h-[80vh]">
        <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-6 text-blue-600 dark:text-teal-400 text-center">Patient Registration</h2>
            <form id="registerForm" method="POST" action="register.php" class="space-y-4">
                <div>
                    <label for="fullname" class="block mb-1 font-medium"><i
                            class="fa fa-user mr-2 text-blue-500"></i>Full Name</label>
                    <input type="text" id="fullname" name="fullname" required
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="age" class="block mb-1 font-medium"><i
                                class="fa fa-calendar mr-2 text-blue-500"></i>Age</label>
                        <input type="number" id="age" name="age" min="1" required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label for="gender" class="block mb-1 font-medium"><i
                                class="fa fa-venus-mars mr-2 text-blue-500"></i>Gender</label>
                        <select id="gender" name="gender" required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
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
                    <div class="relative">
                        <input type="tel" id="phone" name="phone" required maxlength="10" pattern="^07[0-9]{8}$"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                            placeholder="07XXXXXXXX">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative">
                        <label for="password" class="block mb-1 font-medium"><i
                                class="fa fa-lock mr-2 text-blue-500"></i>Password</label>
                        <input type="password" id="password" name="password" required minlength="6"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 pr-10">
                        <span class="absolute right-3 top-9 cursor-pointer"
                            onclick="togglePassword('password', this)"><i class="fa fa-eye text-gray-400"></i></span>
                    </div>
                    <div class="relative">
                        <label for="confirm_password" class="block mb-1 font-medium"><i
                                class="fa fa-lock mr-2 text-blue-500"></i>Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 pr-10">
                        <span class="absolute right-3 top-9 cursor-pointer"
                            onclick="togglePassword('confirm_password', this)"><i
                                class="fa fa-eye text-gray-400"></i></span>
                    </div>
                </div>
                <button type="submit"
                    class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition flex items-center justify-center gap-2"><i
                        class="fa fa-user-plus"></i>Register</button>
            </form>
            <div id="registerAlert" class="mt-4 hidden"></div>
            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-300">Already have an account? <a
                    href="login.php" class="text-blue-600 dark:text-teal-400 font-medium hover:underline">Login</a></p>
        </div>
    </main>
    <?php include __DIR__ . '/components/footer.php'; ?>
    <script>
    // SweetAlert notifications for backend
    <?php if ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Registration Error',
        text: '<?php echo addslashes($error); ?>',
        confirmButtonColor: '#2563eb'
    });
    <?php elseif ($success): ?>
    Swal.fire({
        icon: 'success',
        title: 'Registration Successful!',
        text: 'Your account has been created. You can now login.',
        confirmButtonColor: '#2563eb'
    }).then(() => {
        window.location.href = 'login.php';
    });
    <?php endif; ?>

    // Password visibility toggle
    function togglePassword(fieldId, el) {
        var input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            el.querySelector('i').classList.remove('fa-eye');
            el.querySelector('i').classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            el.querySelector('i').classList.remove('fa-eye-slash');
            el.querySelector('i').classList.add('fa-eye');
        }
    }

    // Simple client-side validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        var pwd = document.getElementById('password').value;
        var cpwd = document.getElementById('confirm_password').value;
        var phone = document.getElementById('phone').value;
        if (pwd !== cpwd) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'Passwords do not match.',
                confirmButtonColor: '#2563eb'
            });
        } else if (!/^07[0-9]{8}$/.test(phone)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Phone Number',
                text: 'Phone number must start with 07 and be exactly 10 digits.',
                confirmButtonColor: '#2563eb'
            });
        }
    });
    </script>
</body>

</html>