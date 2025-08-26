<!-- footer.php -->
<?php
// Allow pages to set $hasSidebar = true before including footer.php
if (!isset($hasSidebar)) {
    $hasSidebar = false;
}
?>
<footer class="bg-white dark:bg-gray-900 shadow-lg py-6 mt-12 <?php echo $hasSidebar ? 'md:ml-64' : ''; ?>">
    <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8">
            <div class="flex items-center gap-2">
                <img src="assets/logo.png" alt="Afyacheck Logo" class="h-8 w-8 rounded-full shadow-md">
                <span class="text-lg font-bold text-blue-600 dark:text-teal-400">Afyacheck</span>
            </div>
            <span class="text-gray-500 dark:text-gray-400 text-sm">Empowering health through technology.</span>
        </div>
        <div
            class="flex flex-col md:flex-row items-center gap-4 md:gap-8 <?php echo $hasSidebar ? 'justify-center flex-1' : 'justify-end'; ?>">
            <div class="flex gap-4">
                <a href="index.php"
                    class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-teal-400 text-sm">Home</a>
                <a href="#about"
                    class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-teal-400 text-sm">About</a>
                <a href="#features"
                    class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-teal-400 text-sm">Features</a>
                <a href="admin_dashboard.php"
                    class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-teal-400 text-sm">Dashboard</a>
                <a href="#contact"
                    class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-teal-400 text-sm">Contact</a>
            </div>
            <div class="flex gap-3 mt-2 md:mt-0">
                <a href="#" class="text-gray-400 hover:text-blue-500"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-gray-400 hover:text-blue-700"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="#" class="text-gray-400 hover:text-blue-800"><i class="fab fa-linkedin fa-lg"></i></a>
            </div>
        </div>
        <div class="text-gray-500 dark:text-gray-400 text-xs text-center w-full mt-4 md:mt-0">
            &copy; <?php echo date('Y'); ?> Afyacheck. All rights reserved.
        </div>
    </div>
</footer>