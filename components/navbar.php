<?php
// Allow pages to set $hasSidebar = true before including navbar.php
if (!isset($hasSidebar)) {
    $hasSidebar = false;
}
?>
<nav class="bg-white/60 dark:bg-gray-900/60 shadow-lg fixed w-full z-40 top-0 left-0 transition-colors duration-500 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 <?php echo $hasSidebar ? 'md:ml-64' : ''; ?>"
    style="backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
    <div class="w-full px-6 md:px-10">
        <div class="flex justify-between h-16 items-center w-full">
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 hover:scale-105 transition-transform">
                <img src="assets/logo.png" alt="Afyacheck Logo" class="h-10 w-10 rounded-full shadow-md">
                <span class="text-2xl font-extrabold text-blue-600 dark:text-teal-400 tracking-wide">Afyacheck</span>
            </a>

            <!-- Desktop Menu -->
            <div
                class="hidden md:flex gap-8 items-center <?php echo $hasSidebar ? 'justify-center flex-1' : 'justify-end'; ?>">
                <a href="index.php"
                    class="relative text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition group">
                    Home
                    <span
                        class="absolute left-0 -bottom-1 w-0 h-[2px] bg-blue-600 dark:bg-teal-400 transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="#about"
                    class="relative text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition group">
                    About
                    <span
                        class="absolute left-0 -bottom-1 w-0 h-[2px] bg-blue-600 dark:bg-teal-400 transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="#features"
                    class="relative text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition group">
                    Features
                    <span
                        class="absolute left-0 -bottom-1 w-0 h-[2px] bg-blue-600 dark:bg-teal-400 transition-all duration-300 group-hover:w-full"></span>
                </a>

                <!-- Dashboard Links -->
                <?php if (isset($_SESSION['admin_id'])): ?>
                <a href="admin_dashboard.php"
                    class="text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition"><i
                        class="fa fa-tachometer-alt mr-1"></i> Dashboard</a>
                <?php elseif (isset($_SESSION['doctor_id'])): ?>
                <a href="doctor_dashboard.php"
                    class="text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition"><i
                        class="fa fa-tachometer-alt mr-1"></i> Dashboard</a>
                <?php endif; ?>

                <!-- Auth Buttons -->
                <?php if (isset($_SESSION['patient_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['doctor_id'])): ?>
                <a href="logout.php"
                    class="px-5 py-2 bg-red-600 text-white rounded-xl shadow hover:shadow-lg hover:bg-red-700 transition flex items-center gap-2"><i
                        class="fa fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                <a href="register.php"
                    class="px-5 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl shadow-md hover:shadow-lg hover:scale-105 transition">Register</a>
                <div class="relative">
                    <button id="loginDropdownBtn"
                        class="px-5 py-2 bg-white text-blue-600 border border-blue-600 rounded-xl shadow hover:bg-blue-50 transition flex items-center gap-2 focus:outline-none hover:scale-105">
                        <i class="fa fa-sign-in-alt"></i> Login <i class="fa fa-caret-down"></i>
                    </button>
                    <div id="loginDropdown"
                        class="absolute right-0 mt-2 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 opacity-0 scale-95 pointer-events-none transition-all duration-300">
                        <a href="login.php"
                            class="block px-4 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition">Patient
                            Login</a>
                        <a href="admin_login.php"
                            class="block px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-gray-700 transition">Admin
                            Login</a>
                        <a href="doctor_login.php"
                            class="block px-4 py-2 text-teal-600 hover:bg-teal-50 dark:hover:bg-gray-700 transition">Doctor
                            Login</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn"
                class="md:hidden text-blue-600 dark:text-teal-400 focus:outline-none hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu"
        class="md:hidden bg-white dark:bg-gray-900 px-6 py-4 hidden flex-col gap-4 shadow-xl animate-slideDown">
        <a href="index.php" class="text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition">Home</a>
        <a href="#about" class="text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition">About</a>
        <a href="#features"
            class="text-lg font-medium hover:text-blue-600 dark:hover:text-teal-300 transition">Features</a>
        <a href="register.php"
            class="block py-2 px-4 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl shadow hover:scale-105 transition">Register</a>

        <div class="relative">
            <button id="mobileLoginDropdownBtn"
                class="block py-2 px-4 bg-white text-blue-600 border border-blue-600 rounded-xl shadow hover:bg-blue-100 transition w-full text-left">Login
                <i class="fa fa-caret-down"></i></button>
            <div id="mobileLoginDropdown"
                class="absolute left-0 mt-2 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 opacity-0 scale-95 pointer-events-none transition-all duration-300">
                <a href="login.php"
                    class="block px-4 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition">Patient
                    Login</a>
                <a href="admin_login.php"
                    class="block px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-gray-700 transition">Admin
                    Login</a>
                <a href="doctor_login.php"
                    class="block px-4 py-2 text-teal-600 hover:bg-teal-50 dark:hover:bg-gray-700 transition">Doctor
                    Login</a>
            </div>
        </div>
    </div>
</nav>

<style>
@keyframes slideDown {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slideDown {
    animation: slideDown 0.3s ease;
}
</style>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });

    // Desktop login dropdown
    const loginBtn = document.getElementById('loginDropdownBtn');
    const loginDropdown = document.getElementById('loginDropdown');
    if (loginBtn && loginDropdown) {
        loginBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            loginDropdown.classList.toggle('opacity-0');
            loginDropdown.classList.toggle('scale-95');
            loginDropdown.classList.toggle('pointer-events-none');
            loginDropdown.classList.toggle('opacity-100');
            loginDropdown.classList.toggle('scale-100');
        });
        document.addEventListener('click', function(e) {
            if (!loginBtn.contains(e.target) && !loginDropdown.contains(e.target)) {
                loginDropdown.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                loginDropdown.classList.remove('opacity-100', 'scale-100');
            }
        });
    }

    // Mobile login dropdown
    const mobileLoginBtn = document.getElementById('mobileLoginDropdownBtn');
    const mobileLoginDropdown = document.getElementById('mobileLoginDropdown');
    if (mobileLoginBtn && mobileLoginDropdown) {
        mobileLoginBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileLoginDropdown.classList.toggle('opacity-0');
            mobileLoginDropdown.classList.toggle('scale-95');
            mobileLoginDropdown.classList.toggle('pointer-events-none');
            mobileLoginDropdown.classList.toggle('opacity-100');
            mobileLoginDropdown.classList.toggle('scale-100');
        });
        document.addEventListener('click', function(e) {
            if (!mobileLoginBtn.contains(e.target) && !mobileLoginDropdown.contains(e.target)) {
                mobileLoginDropdown.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                mobileLoginDropdown.classList.remove('opacity-100', 'scale-100');
            }
        });
    }
});
</script>

<div class="h-16"></div> <!-- Spacer for fixed navbar -->
<?php if (isset($_SESSION['admin_id'])): ?>
<!-- Show admin sidebar if admin is logged in -->
<?php endif; ?>