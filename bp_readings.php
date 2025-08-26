<?php
// bp_readings.php
session_start();
$is_admin = isset($_SESSION['admin_id']);
$is_doctor = isset($_SESSION['doctor_id']);
if (!$is_admin && !$is_doctor) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db.php';
// Handle doctor comments
$comment_submitted = false;
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && $is_doctor && isset($_POST['reading_id'], $_POST['comment'])
) {
    $reading_id = intval($_POST['reading_id']);
    $comment = trim($_POST['comment']);
    if ($comment) {
        // Update the BP reading with the doctor's comment
        $stmt = $pdo->prepare('UPDATE bp_readings SET doctor_comment = ? WHERE id = ?');
        $stmt->execute([$comment, $reading_id]);
        $comment_submitted = true;

        // Fetch patient_id and reading_time for the alert
        $stmt = $pdo->prepare('SELECT patient_id, reading_time FROM bp_readings WHERE id = ?');
        $stmt->execute([$reading_id]);
        $reading = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($reading) {
            $alert_msg = 'Doctor added a recommendation/comment to your BP reading on ' . date('M d, Y H:i', strtotime($reading['reading_time'])) . '.';
            $alert_stmt = $pdo->prepare('INSERT INTO alerts (patient_id, message, created_at, status) VALUES (?, ?, ?, ?)');
            $alert_stmt->execute([$reading['patient_id'], $alert_msg, $reading['reading_time'], 'new']);
        }
    }
}

if ($is_doctor && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bp'])) {
    $patient_id = intval($_POST['patient_id']);
    $systolic = intval($_POST['systolic']);
    $diastolic = intval($_POST['diastolic']);
    $pulse = intval($_POST['pulse']);
    $reading_time = $_POST['reading_time'];
    $doctor_comment = trim($_POST['notes']);
    // Determine BP status
    $bp_status = 'Normal';
    if ($systolic > 180 || $diastolic > 120) {
        $bp_status = 'Critical';
    } elseif (($systolic >= 160 && $systolic <= 180) || ($diastolic >= 100 && $diastolic <= 120)) {
        $bp_status = 'Stage 2 Hypertension';
    } elseif (($systolic >= 140 && $systolic < 160) || ($diastolic >= 90 && $diastolic < 100)) {
        $bp_status = 'Stage 1 Hypertension';
    } elseif (($systolic < 90 && $systolic >= 80) || ($diastolic < 60 && $diastolic >= 50)) {
        $bp_status = 'Low';
    }
    if ($patient_id && $systolic && $diastolic && $pulse && $reading_time) {
        $stmt = $pdo->prepare('INSERT INTO bp_readings (patient_id, systolic, diastolic, pulse, reading_time, doctor_comment, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$patient_id, $systolic, $diastolic, $pulse, $reading_time, $doctor_comment, $bp_status]);
        // Record alert only for critical
        if ($bp_status === 'Critical') {
            $alert_msg = 'Critical BP reading: Systolic ' . $systolic . ', Diastolic ' . $diastolic . ' at ' . $reading_time . ' [Critical]';
            $alert_stmt = $pdo->prepare('INSERT INTO alerts (patient_id, message, created_at, systolic, diastolic, status) VALUES (?, ?, ?, ?, ?, ?)');
            $alert_stmt->execute([$patient_id, $alert_msg, $reading_time, $systolic, $diastolic, 'new']);
        }
        echo "<script>Swal.fire({icon:'success',title:'BP Reading Saved',confirmButtonColor:'#14b8a6'});</script>";
    }
}

// Fetch patients for dropdown
$patients_list = [];
if ($is_doctor) {
    $stmt = $pdo->prepare('SELECT p.id, p.fullname FROM assignments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY p.fullname ASC');
    $stmt->execute([$_SESSION['doctor_id']]);
    $patients_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare('SELECT r.*, p.fullname FROM bp_readings r JOIN patients p ON r.patient_id = p.id ORDER BY r.reading_time DESC');
$stmt->execute();
$readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BP Readings | Afyacheck</title>
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
            <div class="max-w-6xl mx-auto py-8 px-4">
                <h2 class="text-2xl font-bold text-purple-600 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-heartbeat"></i> BP Readings
                </h2>
                <?php if ($is_doctor): ?>
                <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold mb-4 text-blue-600 flex items-center gap-2"><i
                            class="fa-solid fa-pen"></i> Record BP Reading</h3>
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="hidden" name="add_bp" value="1">
                        <div>
                            <label class="block mb-1 font-semibold">Select Patient</label>
                            <select name="patient_id" required
                                class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                                <option value="">-- Select Patient --</option>
                                <?php foreach ($patients_list as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['fullname']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 font-semibold">Systolic (mmHg)</label>
                            <input type="number" name="systolic" required min="50" max="250"
                                class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block mb-1 font-semibold">Diastolic (mmHg)</label>
                            <input type="number" name="diastolic" required min="30" max="150"
                                class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block mb-1 font-semibold">Pulse Rate (bpm)</label>
                            <input type="number" name="pulse" required min="30" max="200"
                                class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block mb-1 font-semibold">Reading Date & Time</label>
                            <input type="datetime-local" name="reading_time" required
                                class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-semibold">Notes</label>
                            <textarea name="notes" rows="2"
                                class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600"
                                placeholder="Additional observations..."></textarea>
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700"><i
                                    class="fa-solid fa-save"></i> Save Reading</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                <div class="mb-4 flex justify-end">
                    <input type="text" id="bpSearch" placeholder="Search by patient, date, or note..."
                        class="px-3 py-2 rounded border w-64 dark:bg-gray-700 dark:border-gray-600" />
                </div>
                <table id="bpTable" class="w-full table-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                    <thead class="bg-purple-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3">Patient</th>
                            <th class="px-4 py-3">Systolic</th>
                            <th class="px-4 py-3">Diastolic</th>
                            <th class="px-4 py-3">Date & Time</th>
                            <?php if ($is_doctor) { ?>
                            <th class="px-4 py-3">Doctor's Note</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($readings as $r): ?>
                        <tr class="border-b">
                            <td class="px-4 py-3"><?php echo htmlspecialchars($r['fullname']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($r['systolic']); ?> mmHg</td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($r['diastolic']); ?> mmHg</td>
                            <td class="px-4 py-3"><?php echo date('M d, Y H:i', strtotime($r['reading_time'])); ?></td>
                            <?php if ($is_doctor) { ?>
                            <td class="px-4 py-3">
                                <form method="POST" action="bp_readings.php" class="flex gap-2 items-center">
                                    <input type="hidden" name="reading_id" value="<?php echo $r['id']; ?>">
                                    <input type="text" name="comment"
                                        value="<?php echo htmlspecialchars($r['doctor_comment'] ?? ''); ?>"
                                        placeholder="Add note"
                                        class="px-2 py-1 rounded border dark:bg-gray-700 dark:border-gray-600">
                                    <button type="submit"
                                        class="px-2 py-1 bg-teal-600 text-white rounded hover:bg-teal-700"><i
                                            class="fa fa-comment"></i></button>
                                </form>
                            </td>
                            <?php } ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="pagination" class="flex justify-center items-center mt-4 gap-2"></div>
            </div>
            <?php include __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php if (isset($comment_submitted) && $comment_submitted): ?>
    Swal.fire({
        icon: 'success',
        title: 'Comment Added',
        text: 'Your note has been saved.',
        confirmButtonColor: '#14b8a6'
    });
    <?php endif; ?>
    <?php if (isset($_SESSION['bp_success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'BP Reading Saved',
        confirmButtonColor: '#14b8a6'
    });
    <?php unset($_SESSION['bp_success']); endif; ?>

    const rowsPerPage = 10;

    function paginateTable() {
        const table = document.getElementById('bpTable');
        const trs = Array.from(table.getElementsByTagName('tr')).slice(1); // skip header
        const pagination = document.getElementById('pagination');
        let currentPage = 1;

        function showPage(page) {
            currentPage = page;
            let start = (page - 1) * rowsPerPage;
            let end = start + rowsPerPage;
            trs.forEach((tr, i) => {
                tr.style.display = (i >= start && i < end) ? '' : 'none';
            });
            renderPagination();
        }

        function renderPagination() {
            let totalPages = Math.ceil(trs.length / rowsPerPage);
            let html = '';
            if (totalPages > 1) {
                html +=
                    `<button ${currentPage==1?'disabled':''} class='px-2 py-1 rounded bg-gray-200 mx-1' onclick='window.showBpPage(1)'>First</button>`;
                html +=
                    `<button ${currentPage==1?'disabled':''} class='px-2 py-1 rounded bg-gray-200 mx-1' onclick='window.showBpPage(${currentPage-1})'>Prev</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    html +=
                        `<button class='px-2 py-1 rounded ${i==currentPage?'bg-purple-600 text-white':'bg-gray-200'} mx-1' onclick='window.showBpPage(${i})'>${i}</button>`;
                }
                html +=
                    `<button ${currentPage==totalPages?'disabled':''} class='px-2 py-1 rounded bg-gray-200 mx-1' onclick='window.showBpPage(${currentPage+1})'>Next</button>`;
                html +=
                    `<button ${currentPage==totalPages?'disabled':''} class='px-2 py-1 rounded bg-gray-200 mx-1' onclick='window.showBpPage(${totalPages})'>Last</button>`;
            }
            pagination.innerHTML = html;
        }
        window.showBpPage = showPage;
        showPage(1);
    }
    document.addEventListener('DOMContentLoaded', paginateTable);
    document.getElementById('bpSearch').addEventListener('keyup', function() {
        var filter = this.value.toLowerCase();
        var table = document.getElementById('bpTable');
        var trs = Array.from(table.getElementsByTagName('tr')).slice(1);
        trs.forEach(tr => {
            let show = false;
            Array.from(tr.getElementsByTagName('td')).forEach(td => {
                if (td.innerText.toLowerCase().indexOf(filter) > -1) show = true;
            });
            tr.style.display = show ? '' : 'none';
        });
        paginateTable();
    });
    </script>
</body>

</html>