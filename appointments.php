<?php
// appointments.php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header('Location: doctor_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
$doctor_id = $_SESSION['doctor_id'];
// Handle appointment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    $patient_id = intval($_POST['patient_id']);
    $type = trim($_POST['type']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $provider = trim($_POST['provider']);
    $notes = trim($_POST['notes']);
    $reminder = isset($_POST['reminder']) ? 1 : 0;
    if ($patient_id && $type && $date && $time && $provider) {
        $stmt = $pdo->prepare('INSERT INTO appointments (doctor_id, patient_id, type, date, time, provider, notes, reminder) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$doctor_id, $patient_id, $type, $date, $time, $provider, $notes, $reminder]);
        $_SESSION['appt_success'] = true;
        header('Location: appointments.php');
        exit;
    }
}
// Fetch patients for dropdown
$stmt = $pdo->prepare('SELECT p.id, p.fullname FROM assignments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY p.fullname ASC');
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch doctors for provider dropdown
$stmt = $pdo->prepare('SELECT id, fullname FROM doctors ORDER BY fullname ASC');
$stmt->execute();
$providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch appointments for calendar and list
$stmt = $pdo->prepare('SELECT a.*, p.fullname FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY a.date, a.time');
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | Afyacheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        <!-- Main Content -->
        <div class="flex-1">
            <?php include __DIR__ . '/components/navbar.php'; ?>
            <main class="max-w-7xl mx-auto py-8 px-4 ml-64">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Appointment Calendar & List -->
                    <section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold mb-4 text-blue-600 dark:text-blue-300 flex items-center gap-2"><i
                                class="fa-solid fa-calendar-days"></i> Appointment Calendar</h3>
                        <!-- Tabs for filtering appointments -->
                        <?php
                        $tab = isset($_GET['appt_tab']) ? $_GET['appt_tab'] : 'upcoming';
                        $now = date('Y-m-d H:i:s');
                        ?>
                        <div class="mb-6 flex gap-4">
                            <a href="?appt_tab=upcoming"
                                class="px-4 py-2 rounded-lg font-semibold transition border-b-2 <?php echo $tab=='upcoming' ? 'border-blue-600 text-blue-600 bg-blue-50 dark:bg-blue-900' : 'border-transparent text-gray-600 dark:text-gray-300'; ?>">Upcoming</a>
                            <a href="?appt_tab=ongoing"
                                class="px-4 py-2 rounded-lg font-semibold transition border-b-2 <?php echo $tab=='ongoing' ? 'border-green-600 text-green-600 bg-green-50 dark:bg-green-900' : 'border-transparent text-gray-600 dark:text-gray-300'; ?>">Ongoing</a>
                            <a href="?appt_tab=past"
                                class="px-4 py-2 rounded-lg font-semibold transition border-b-2 <?php echo $tab=='past' ? 'border-gray-600 text-gray-600 bg-gray-100 dark:bg-gray-900' : 'border-transparent text-gray-600 dark:text-gray-300'; ?>">Past</a>
                        </div>
                        <div class="mb-4">
                            <?php
                            $filtered_appts = [];
                            foreach ($appointments as $a) {
                                $appt_datetime = $a['date'] . ' ' . $a['time'];
                                if ($tab == 'upcoming' && $appt_datetime > $now) {
                                    $filtered_appts[] = $a;
                                } elseif ($tab == 'ongoing' && $appt_datetime <= $now && $appt_datetime >= date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($now)))) {
                                    $filtered_appts[] = $a;
                                } elseif ($tab == 'past' && $appt_datetime < $now) {
                                    $filtered_appts[] = $a;
                                }
                            }
                            ?>
                            <?php if (count($filtered_appts) > 0): ?>
                            <?php foreach ($filtered_appts as $a): ?>
                            <div
                                class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 rounded-lg px-4 py-3 mb-2">
                                <div>
                                    <span
                                        class="font-bold text-blue-600 dark:text-blue-100 mr-2"><?php echo strtoupper(substr($a['fullname'],0,2)); ?></span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($a['fullname']); ?></span>
                                    <span
                                        class="text-gray-500 dark:text-gray-400 ml-2">#PT<?php echo str_pad($a['patient_id'],3,'0',STR_PAD_LEFT); ?>
                                        - <?php echo htmlspecialchars($a['type']); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-3 py-1 rounded bg-green-600 text-white font-bold text-sm"><?php echo date('g:i A', strtotime($a['time'])); ?></span>
                                    <button class="edit-appt-btn text-blue-600 dark:text-blue-400"
                                        data-appt='<?php echo json_encode($a); ?>'><i class="fa fa-edit"></i></button>
                                    <button class="text-red-600 dark:text-red-400"><i class="fa fa-times"></i></button>
                                    <button class="text-green-600 dark:text-green-400"><i
                                            class="fa fa-check"></i></button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="text-gray-500 dark:text-gray-400">No appointments found for this tab.</div>
                            <?php endif; ?>
                        </div>
                    </section>
                    <!-- Schedule Appointment Form -->
                    <section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold mb-4 text-blue-600 dark:text-blue-300 flex items-center gap-2"><i
                                class="fa-solid fa-calendar-plus"></i> Schedule Appointment</h3>
                        <form method="POST" class="grid grid-cols-1 gap-4">
                            <input type="hidden" name="add_appointment" value="1">
                            <div>
                                <label class="block mb-1 font-semibold">Select Patient</label>
                                <select name="patient_id" required
                                    class="w-full px-3 py-2 rounded border bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                                    <option value="">-- Select Patient --</option>
                                    <?php foreach ($patients as $p): ?>
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['fullname']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold">Appointment Type</label>
                                <select name="type" required
                                    class="w-full px-3 py-2 rounded border bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                                    <option value="">-- Select Type --</option>
                                    <option value="Initial Assessment">Initial Assessment</option>
                                    <option value="Follow-up">Follow-up</option>
                                    <option value="Medication Review">Medication Review</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold">Date</label>
                                <input type="date" name="date" required
                                    class="w-full px-3 py-2 rounded border bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold">Time</label>
                                <input type="time" name="time" required
                                    class="w-full px-3 py-2 rounded border bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold">Healthcare Provider</label>
                                <select name="provider" required
                                    class="w-full px-3 py-2 rounded border bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                                    <option value="">-- Select Provider --</option>
                                    <?php foreach ($providers as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['fullname']); ?>">
                                        <?php echo htmlspecialchars($d['fullname']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold">Notes</label>
                                <textarea name="notes" rows="2"
                                    class="w-full px-3 py-2 rounded border bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100"
                                    placeholder="Additional information..."></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="reminder" id="reminder" class="accent-blue-600">
                                <label for="reminder" class="font-semibold">Send SMS reminder to patient</label>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700"><i
                                        class="fa-solid fa-calendar-plus"></i> Schedule Appointment</button>
                            </div>
                        </form>
                        <?php if (isset($_SESSION['appt_success'])): ?>
                        <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Appointment Scheduled',
                            confirmButtonColor: '#14b8a6'
                        });
                        </script>
                        <?php unset($_SESSION['appt_success']); endif; ?>
                    </section>
                </div>
            </main>
            <?php include __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>

    <!-- Modal for editing appointment -->
    <div id="editApptModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 w-full max-w-md relative">
            <button onclick="document.getElementById('editApptModal').classList.add('hidden')"
                class="absolute top-4 right-4 text-gray-500 hover:text-red-500"><i
                    class="fa fa-times fa-lg"></i></button>
            <h3 class="text-xl font-bold mb-4 text-blue-600 dark:text-teal-400 flex items-center gap-2"><i
                    class="fa fa-calendar-edit"></i> Edit Appointment</h3>
            <form id="editApptForm" method="POST" class="space-y-4">
                <input type="hidden" name="edit_appointment_id" id="edit_appointment_id">
                <div>
                    <label class="block mb-1 font-medium">Patient</label>
                    <input type="text" id="edit_patient" name="edit_patient"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                        readonly>
                </div>
                <div>
                    <label class="block mb-1 font-medium">Type</label>
                    <input type="text" id="edit_type" name="edit_type"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block mb-1 font-medium">Date</label>
                    <input type="date" id="edit_date" name="edit_date"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block mb-1 font-medium">Time</label>
                    <input type="time" id="edit_time" name="edit_time"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block mb-1 font-medium">Provider</label>
                    <input type="text" id="edit_provider" name="edit_provider"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block mb-1 font-medium">Notes</label>
                    <textarea id="edit_notes" name="edit_notes" rows="2"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700"><i
                            class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    // Show modal and populate fields when edit button is clicked
    function showEditModal(appt) {
        document.getElementById('editApptModal').classList.remove('hidden');
        document.getElementById('edit_appointment_id').value = appt.id;
        document.getElementById('edit_patient').value = appt.fullname;
        document.getElementById('edit_type').value = appt.type;
        document.getElementById('edit_date').value = appt.date;
        document.getElementById('edit_time').value = appt.time;
        document.getElementById('edit_provider').value = appt.provider;
        document.getElementById('edit_notes').value = appt.notes;
    }
    // Attach click event to edit buttons
    window.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edit-appt-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var appt = JSON.parse(this.getAttribute('data-appt'));
                showEditModal(appt);
            });
        });
    });
    </script>
</body>

</html>