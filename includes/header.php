<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$currentPage = isset($currentPage) ? (string) $currentPage : '';
$showNav = isset($showNav) ? (bool) $showNav : true;
$headerLogoExtra = $headerLogoExtra ?? '';
$isLoggedIn = !empty($_SESSION['loggedIn']) || isset($_SESSION['user_id']) || isset($_SESSION['user_name']);
$ariaCurrentAttr = 'aria-current="page"';
$mainNavId = 'mainSiteNav';

$navItems = [
    ['slug' => 'home', 'href' => '.', 'label' => 'Home'],
    ['slug' => 'about', 'href' => 'about.php', 'label' => 'About Us'],
    ['slug' => 'health-package', 'href' => 'health-package.php', 'label' => 'Health Packages'],
    ['slug' => 'contact', 'href' => 'contact.php', 'label' => 'Contact'],
    ['slug' => 'test-results', 'href' => 'test-results.php', 'label' => 'Test Results'],
];

// Generate welcome message if user is logged in
if (isset($_SESSION['user_name'])) {
    $safeUserName = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');
    $headerLogoExtra = '<span>Welcome, ' . $safeUserName . '</span>';
} else {
    $headerLogoExtra = '';
}
?>
<header class="site-header">
    <div class="header-inner">
        <div class="header-top">
            <div class="logo">
                <img src="assets/image/omcenterlogo.jpeg" alt="OM Diagnostic Lab logo" class="brand-logo" loading="lazy">
                <div class="brand-copy">
                    <h1>OM Diagnostic Lab</h1>
                    <p class="brand-meta">Accurate diagnostics, compassionate care</p>
                </div>
            </div>
            <div class="header-actions">
                <?php if (!empty($headerLogoExtra)): ?>
                    <div class="welcome-chip">
                        <?php echo $headerLogoExtra; ?>
                    </div>
                <?php endif; ?>
                <?php if ($showNav): ?>
                    <button class="nav-toggle" type="button" aria-controls="<?php echo $mainNavId; ?>" aria-expanded="false" aria-label="Toggle navigation menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($showNav): ?>
            <nav class="main-nav" id="<?php echo $mainNavId; ?>" aria-label="Main navigation">
                <ul>
                    <?php foreach ($navItems as $item): ?>
                        <li>
                            <a href="<?php echo $item['href']; ?>" <?php echo $currentPage === $item['slug'] ? $ariaCurrentAttr : ''; ?>>
                                <?php echo $item['label']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php if ($isLoggedIn): ?>
                        <li><a href="logout.php" class="auth-link">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="auth-link">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</header>

<?php if ($showNav): ?>
    <script>
        (function() {
            var header = document.querySelector('.site-header');
            if (!header) {
                return;
            }

            var toggle = header.querySelector('.nav-toggle');
            var nav = header.querySelector('.main-nav');
            if (!toggle || !nav) {
                return;
            }

            header.classList.add('nav-enhanced');

            var setOpen = function(isOpen) {
                nav.classList.toggle('is-open', isOpen);
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            };

            toggle.addEventListener('click', function() {
                setOpen(!nav.classList.contains('is-open'));
            });

            nav.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 860) {
                        setOpen(false);
                    }
                });
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    setOpen(false);
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 860) {
                    setOpen(false);
                }
            });

            document.addEventListener('click', function(event) {
                if (window.innerWidth > 860 || !nav.classList.contains('is-open')) {
                    return;
                }

                if (!header.contains(event.target)) {
                    setOpen(false);
                }
            });
        })();
    </script>
<?php endif; ?>
