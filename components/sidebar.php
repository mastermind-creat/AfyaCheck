<?php
// components/sidebar.php
session_start();
$is_admin = isset($_SESSION['admin_id']);
$is_doctor = isset($_SESSION['doctor_id']);
?>
<aside
    class="w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col py-8 px-4 fixed top-0 left-0 h-screen z-40 md:w-64 w-full">
    <div class="flex items-center gap-2 mb-8">
        <?php if ($is_admin): ?>
        <i class="fa-solid fa-user-shield text-2xl text-blue-600 dark:text-teal-400"></i>
        <span class="text-xl font-bold text-blue-600 dark:text-teal-400">Admin Panel</span>
        <?php elseif ($is_doctor): ?>
        <i class="fa-solid fa-user-md text-2xl text-blue-600 dark:text-teal-400"></i>
        <span class="text-xl font-bold text-blue-600 dark:text-teal-400">Doctor Panel</span>
        <?php endif; ?>
    </div>
    <nav class="flex flex-col gap-4">
        <?php if ($is_admin): ?>
        <a href="admin_dashboard.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium"><i
                class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="manage_patients.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium"><i
                class="fa-solid fa-users"></i> Manage Patients</a>
        <a href="manage_doctors.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium"><i
                class="fa-solid fa-user-md"></i> Manage Doctors</a>
        <a href="add_patient.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium"><i
                class="fa-solid fa-user-plus"></i> Add Patient</a>
        <a href="assignments.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium"><i
                class="fa-solid fa-link"></i> Assignments</a>
        <a href="reports.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900 transition text-purple-700 dark:text-purple-300 font-medium"><i
                class="fa-solid fa-file-chart-line"></i> Reports</a>
        <a href="critical_alerts.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900 transition text-red-700 dark:text-red-300 font-medium"><i
                class="fa-solid fa-exclamation-triangle"></i> Critical Alerts</a>
        <?php elseif ($is_doctor): ?>
        <a href="doctor_dashboard.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium"><i
                class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="doctor_assignments.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-green-100 dark:hover:bg-green-900 transition text-green-700 dark:text-green-300 font-medium"><i
                class="fa-solid fa-link"></i> Assigned Patients</a>
        <a href="bp_readings.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900 transition text-purple-700 dark:text-purple-300 font-medium"><i
                class="fa-solid fa-heartbeat"></i> BP Readings</a>
        <a href="appointments.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900 transition text-blue-700 dark:text-blue-300 font-medium"><i
                class="fa-solid fa-calendar-check"></i> Appointments</a>
        <a href="reports.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900 transition text-purple-700 dark:text-purple-300 font-medium"><i
                class="fa-solid fa-file-chart-line"></i> Reports</a>
        <?php endif; ?>
        <a href="logout.php"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900 transition text-red-700 dark:text-red-300 font-medium mt-8"><i
                class="fa-solid fa-sign-out-alt"></i> Logout</a>
    </nav>
</aside>