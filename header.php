<?php
$currentPage = isset($currentPage) ? (string) $currentPage : '';
$showNav = isset($showNav) ? (bool) $showNav : true;
$headerLogoExtra = $headerLogoExtra ?? '';
$isLoggedIn = isset($_SESSION['loggedIn']) ? (bool) $_SESSION['loggedIn'] : false;
?>
<header>
    <div class="logo">
        <h1>OM Diagnostic Lab</h1>
        <?php if (!empty($headerLogoExtra)): ?>
            <?php echo $headerLogoExtra; ?>
        <?php endif; ?>
    </div>

    <?php if ($showNav): ?>
        <nav>
            <ul>
                <li><a href="." <?php echo $currentPage === 'home' ? 'aria-current="page"' : ''; ?>>Home</a></li>
                <li><a href="about.php" <?php echo $currentPage === 'about' ? 'aria-current="page"' : ''; ?>>About Us</a></li>
                <li><a href="health-package.php" <?php echo $currentPage === 'health-package' ? 'aria-current="page"' : ''; ?>>Health Packages</a></li>
                <li><a href="contact.php" <?php echo $currentPage === 'contact' ? 'aria-current="page"' : ''; ?>>Contact</a>
                </li>
                <li><a href="test-results.php" <?php echo $currentPage === 'test-results' ? 'aria-current="page"' : ''; ?>>Test Results</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</header>