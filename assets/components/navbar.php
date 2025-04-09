<?php
function is_active($page)
{
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$current_page = basename($_SERVER['PHP_SELF']);

?>
<nav class="navbar navbar-expand-lg bg-transparent navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand mx-5" href="#">template.</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">template.</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 fw-medium px-3 py-3 mx-auto">
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= is_active('index.php') ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= is_active('products.php') ?>" href="products.php">Products</a>
                    </li>
                    <?php if ($is_logged_in && $username): ?>
                        <li class="nav-item dropdown mx-2">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <?= htmlspecialchars($username) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <?php if ($user_role == 1): ?>
                                    <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <?php endif; ?>
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
                    <li class="nav-item dropdown mx-2 position-relative">
                        <!-- Only show the cart if we're on the products page and the user is logged in -->
                        <?php if ($current_page === 'products.php' && $is_logged_in): ?>
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="cartDropdown">
                                <!-- Cart Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart4" viewBox="0 0 16 16">
                                    <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0"/>
                                </svg>

                                <!-- Show the badge if the cart has items -->
                                <?php if ($cart_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" style="font-size: 10px;">
                                        <?= $cart_count ?>
                                    </span>

                                <?php endif; ?>
                            </a>

                            <!-- Cart dropdown menu -->
                            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 550px;" aria-labelledby="cartDropdown">
                                <h6 class="dropdown-header">Your Cart</h6>
                                <?php if ($cart_count > 0): ?>
                                    <?php foreach ($_SESSION['cart'] as $product_id => $quantity): ?>
                                        <?php
                                        // Fetch product details from session (assuming $products is populated)
                                        $product = array_filter($products, fn($p) => $p['id'] == $product_id);
                                        $product = reset($product);
                                        ?>
                                        <div class="card rounded-3 mb-4">
                                            <div class="card-body p-4">
                                                <div class="row d-flex justify-content-between align-items-center">
                                                    <!-- Product Image -->
                                                    <div class="col-md-2 col-lg-2 col-xl-2">
                                                        <img src="<?= $product['image_url'] ?>" class="img-fluid rounded-3" alt="<?= htmlspecialchars($product['name']) ?>">
                                                    </div>

                                                    <!-- Product Name -->
                                                    <div class="col-md-3 col-lg-3 col-xl-3">
                                                        <p class="fw-normal mb-2"><?= htmlspecialchars($product['name']) ?></p>
                                                    </div>

                                                    <!-- Product Price -->
                                                    <div class="col-md-3 col-lg-2 col-xl-2 offset-lg-1">
                                                        <p class="mb-0">$<?= number_format($product['price'], 2) ?></p>
                                                    </div>

                                                    <!-- Remove Item Icon -->
                                                    <div class="col-md-1 col-lg-1 col-xl-1 text-end">
                                                        <a href="?action=remove_from_cart&product_id=<?= $product['id'] ?>" class="text-danger">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                 height="16" fill="currentColor"
                                                                 class="bi bi-trash3" viewBox="0 0 16 16">
                                                                <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <li><hr class="dropdown-divider"></li>
                                <?php else: ?>
                                    <li class="dropdown-item text-muted small">Your cart is empty.</li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </li>



                </ul>
            </div>
        </div>
    </div>
</nav>
