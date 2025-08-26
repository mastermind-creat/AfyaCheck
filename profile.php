<?php
// profile.php
session_start();
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$patient_id = $_SESSION['patient_id'];
$success = false;
$error = '';
// Fetch patient info
$stmt = $pdo->prepare('SELECT fullname, age, gender, email, phone FROM patients WHERE id = ?');
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    if (!$fullname || !$age || !$gender || !$email || !$phone) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($age < 1) {
        $error = 'Invalid age.';
    } else {
        $stmt = $pdo->prepare('UPDATE patients SET fullname = ?, age = ?, gender = ?, email = ?, phone = ? WHERE id = ?');
        if ($stmt->execute([$fullname, $age, $gender, $email, $phone, $patient_id])) {
            $success = true;
            // Refresh patient info
            $stmt = $pdo->prepare('SELECT fullname, age, gender, email, phone FROM patients WHERE id = ?');
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch();
        } else {
            $error = 'Failed to update profile.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">
    <div class="flex min-h-screen">
        <!-- Patient Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col py-8 px-4 sticky top-0 h-screen z-10">
            <div class="flex items-center gap-2 mb-8">
                <i class="fa-solid fa-user text-2xl text-blue-600 dark:text-teal-400"></i>
                <span class="text-xl font-bold text-blue-600 dark:text-teal-400">Patient Panel</span>
            </div>
            <nav class="flex flex-col gap-4">
                <a href="profile.php"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium">
                    <i class="fa-solid fa-user-cog"></i> Profile
                </a>
                <a href="bp_readings.php"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900 transition text-purple-700 dark:text-purple-300 font-medium">
                    <i class="fa-solid fa-heartbeat"></i> BP Readings
                </a>
                <a href="logout.php"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900 transition text-red-700 dark:text-red-300 font-medium mt-8">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>
        <!-- Main Content -->
        <div class="flex-1">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <main class="flex flex-col items-center justify-center py-16 px-4 min-h-[80vh]">
                <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-8">
                    <h2
                        class="text-2xl font-bold mb-6 text-blue-600 dark:text-teal-400 text-center flex items-center gap-2">
                        <i class="fa fa-user-cog"></i> Profile Settings</h2>
                    <form method="POST" action="profile.php" class="space-y-4">
                        <div>
                            <label for="fullname" class="block mb-1 font-medium"><i
                                    class="fa fa-user mr-2 text-blue-500"></i>Full Name</label>
                            <input type="text" id="fullname" name="fullname"
                                value="<?php echo htmlspecialchars($patient['fullname']); ?>" required
                                class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="age" class="block mb-1 font-medium"><i
                                        class="fa fa-calendar mr-2 text-blue-500"></i>Age</label>
                                <input type="number" id="age" name="age" min="1"
                                    value="<?php echo htmlspecialchars($patient['age']); ?>" required
                                    class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div>
                                <label for="gender" class="block mb-1 font-medium"><i
                                        class="fa fa-venus-mars mr-2 text-blue-500"></i>Gender</label>
                                <select id="gender" name="gender" required
                                    class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                    <option value="Male" <?php if($patient['gender']==='Male') echo 'selected'; ?>>Male
                                    </option>
                                    <option value="Female" <?php if($patient['gender']==='Female') echo 'selected'; ?>>
                                        Female
                                    </option>
                                    <option value="Other" <?php if($patient['gender']==='Other') echo 'selected'; ?>>
                                        Other
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block mb-1 font-medium"><i
                                    class="fa fa-envelope mr-2 text-blue-500"></i>Email</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($patient['email']); ?>" required
                                class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div>
                            <label for="phone" class="block mb-1 font-medium"><i
                                    class="fa fa-phone mr-2 text-blue-500"></i>Phone</label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($patient['phone']); ?>" required
                                class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <button type="submit"
                            class="w-full py-3 bg-teal-600 text-white font-semibold rounded-lg shadow hover:bg-teal-700 transition flex items-center justify-center gap-2"><i
                                class="fa fa-save"></i> Save Changes</button>
                    </form>
                </div>
                <!-- Recent BP Readings -->
                <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-8"></div>
                <h3 class="text-xl font-semibold mb-4 text-teal-600 flex items-center gap-2"><i
                        class="fa fa-history"></i>
                    Recent BP Readings</h3>
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-teal-100 dark:bg-gray-700">
                            <th class="px-4 py-2">Date & Time</th>
                            <th class="px-4 py-2">Systolic</th>
                            <th class="px-4 py-2">Diastolic</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $stmt = $pdo->prepare('SELECT systolic, diastolic, reading_time FROM bp_readings WHERE patient_id = ? ORDER BY reading_time DESC LIMIT 5');
                            $stmt->execute([$patient_id]);
                            foreach ($stmt as $r): ?>
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-2"><?php echo date('M d, Y H:i', strtotime($r['reading_time'])); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($r['systolic']); ?> mmHg</td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($r['diastolic']); ?> mmHg</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>
        </main>
        <?php include __DIR__ . '/components/footer.php'; ?>
    </div>
    </div>
    <script>
    <?php if ($success): ?>
    Swal.fire({
        icon: 'success',
        title: 'Profile Updated',
        text: 'Your profile has been updated successfully.',
        confirmButtonColor: '#14b8a6'
    });
    <?php elseif ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Update Error',
        text: '<?php echo addslashes($error); ?>',
        confirmButtonColor: '#14b8a6'
    });
    <?php endif; ?>
    </script>
</body>

</html>