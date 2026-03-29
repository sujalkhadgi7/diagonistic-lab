<?php
$currentPage = isset($currentPage) ? (string) $currentPage : '';
$showNav = isset($showNav) ? (bool) $showNav : true;
$headerLogoExtra = $headerLogoExtra ?? '';
$isLoggedIn = isset($_SESSION['loggedIn']) ? (bool) $_SESSION['loggedIn'] : false;
$ariaCurrentAttr = 'aria-current="page"';

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
                <h1>OM Diagnostic Lab</h1>
                <p class="brand-meta">Accurate diagnostics, compassionate care</p>
            </div>
            <?php if (!empty($headerLogoExtra)): ?>
                <div class="welcome-chip">
                    <?php echo $headerLogoExtra; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($showNav): ?>
            <nav class="main-nav" aria-label="Main navigation">
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
