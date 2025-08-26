<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afyacheck | Smart Health Monitoring</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="icon" href="favicon.ico">
    <style>
    /* Custom animation classes */
    .fade-in {
        animation: fadeIn 1s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .slide-up {
        animation: slideUp 1s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(40px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">
    <?php include __DIR__ . '/components/navbar.php'; ?>
    <!-- Hero Section -->
    <section
        class="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-blue-500 to-teal-400 fade-in relative"
        style="background: url('assets/bp.jpeg') center center / cover no-repeat;">
        <div class="absolute inset-0 bg-blue-900 bg-opacity-60"></div>
        <div class="relative z-10 flex flex-col items-center justify-center h-full w-full">
            <h1 class="text-4xl md:text-6xl font-bold mb-4 text-white drop-shadow-lg">Smart Health Monitoring for Better
                Lives</h1>
            <p class="text-lg md:text-2xl mb-8 text-white/90">Afyacheck Solution Management System for Kombewa Sub
                County
                Hospital</p>
            <div class="flex gap-4">
                <a href="register.php"
                    class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg shadow-lg hover:bg-blue-100 transition slide-up flex items-center gap-2 hero-btn">
                    <i class="fa fa-user-plus text-xl"></i>
                    Register as Patient
                </a>
                <a href="login.php"
                    class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-700 transition slide-up flex items-center gap-2 hero-btn">
                    <i class="fa fa-sign-in-alt text-xl"></i>
                    Login
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="max-w-4xl mx-auto py-16 px-4 fade-in">
        <h2 class="text-3xl font-bold mb-4 text-blue-600">About Afyacheck</h2>
        <p class="text-lg mb-6">Afyacheck is a web-based solution management system designed to help patients and
            healthcare providers monitor and manage blood pressure readings efficiently. The system provides real-time
            insights, secure patient records, and tools for better health outcomes.</p>
    </section>

    <!-- Features Section -->
    <section class="max-w-5xl mx-auto py-16 px-4 fade-in">
        <h2 class="text-3xl font-bold mb-8 text-teal-600">Features</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <i class="fa fa-bell text-4xl text-yellow-500 mb-3"></i>
                <h3 class="text-xl font-semibold mb-2">Notifications</h3>
                <p>Get instant alerts for appointments, critical readings, and doctor recommendations.</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <i class="fa fa-lock text-4xl text-gray-500 mb-3"></i>
                <h3 class="text-xl font-semibold mb-2">Secure Data</h3>
                <p>All patient and doctor data is encrypted and securely stored for privacy and compliance.</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <i class="fa fa-heartbeat text-4xl text-red-500 mb-3"></i>
                <h3 class="text-xl font-semibold mb-2">BP Tracking</h3>
                <p>Monitor and record blood pressure readings with ease.</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <i class="fa fa-user text-4xl text-blue-500 mb-3"></i>
                <h3 class="text-xl font-semibold mb-2">Patient Records</h3>
                <p>Securely store and manage patient information and history.</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <i class="fa fa-user-md text-4xl text-teal-500 mb-3"></i>
                <h3 class="text-xl font-semibold mb-2">Doctor Monitoring</h3>
                <p>Doctors can view, comment, and recommend actions for patients.</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <i class="fa fa-chart-bar text-4xl text-purple-500 mb-3"></i>
                <h3 class="text-xl font-semibold mb-2">Hospital Insights</h3>
                <p>Admins can generate reports and view system statistics.</p>
            </div>
        </div>
    </section>

    <!-- Dark/Light Mode Toggle -->
    <button id="toggleMode"
        class="fixed bottom-6 right-6 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 p-4 rounded-full shadow-lg hover:scale-110 transition-transform duration-300 z-50 flex items-center justify-center text-2xl">
        <i id="themeIcon" class="fa fa-moon"></i>
    </button>

    <script>
    // Fade-in animation on scroll
    document.querySelectorAll('.fade-in').forEach(el => {
        el.style.opacity = 0;
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.classList.add('fade-in');
                }
            });
        }, {
            threshold: 0.2
        });
        observer.observe(el);
    });

    // Dark/Light mode toggle with icon
    const toggleBtn = document.getElementById('toggleMode');
    const themeIcon = document.getElementById('themeIcon');

    function updateThemeIcon() {
        if (document.body.classList.contains('dark')) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        } else {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }
    toggleBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        updateThemeIcon();
    });
    // Set initial icon
    updateThemeIcon();
    </script>

    <?php include __DIR__ . '/components/footer.php'; ?>
</body>

</html>