<?php
function is_active($page)
{
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg bg-transparent navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand mx-2" href="index.php"><img src="./assets/images/logo.png" alt="logo" style="max-height: 70px;"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <a class="navbar-brand mx-2" href="index.php"><img src="./assets/images/dark-logo.png" alt="logo" style="max-height: 70px;"></a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 fw-medium px-3 py-3 mx-auto">
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= is_active('index.php') ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= is_active('jobs.php') ?>" href="jobs.php">Jobs</a>
                    </li>
                    <?php if ($is_logged_in && $username): ?>
                        <li class="nav-item dropdown mx-2">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <?= htmlspecialchars($username) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item pt-1">
                            <a class="btn btn-light rounded-3" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>
