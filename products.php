<?php
session_start();
include 'db.php';

//SESSIONS
$is_logged_in = $_SESSION['is_logged_in'] ?? $_COOKIE['is_logged_in'] ?? false;
$username = $_SESSION['username'] ?? $_COOKIE['username'] ?? null;
$user_role = $_SESSION['role'] ?? $_COOKIE['role'] ?? null;
//TO CHECK FOR ADMIN PRIVILEGES
$is_admin = ($user_role == 1);

//CRUD OPERATIONS FOR PRODUCTS IF ADMIN
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $name = htmlspecialchars($_POST['name'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
            $category = htmlspecialchars($_POST['category'] ?? '');
            $image_url = htmlspecialchars($_POST['image_url'] ?? '');

            $stmt = $mysqli->prepare("INSERT INTO products (name, description, price, image_url, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdss", $name, $description, $price, $image_url, $category);
            $stmt->execute();
            $stmt->close();

            $success_message = "Product added successfully!";

        } elseif ($action === 'edit') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $name = htmlspecialchars($_POST['name'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
            $category = htmlspecialchars($_POST['category'] ?? '');
            $image_url = htmlspecialchars($_POST['image_url'] ?? '');

            $stmt = $mysqli->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, category = ? WHERE id = ?");
            $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $category, $id);
            $stmt->execute();
            $stmt->close();

            $success_message = "Product updated successfully!";

        } elseif ($action === 'delete' && isset($_POST['id'])) {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $success_message = "Product deleted successfully!";
        }
    }
}

//FETCH CATEGORIES
$categories = [];
$result = $mysqli->query("SELECT DISTINCT category FROM products ORDER BY category");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

//FILTER INPUTS
$category_filter = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? filter_var($_GET['min_price'], FILTER_VALIDATE_FLOAT) : '';
$max_price = isset($_GET['max_price']) ? filter_var($_GET['max_price'], FILTER_VALIDATE_FLOAT) : '';
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = '';

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

if (!empty($min_price)) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}

if (!empty($max_price)) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

$sql .= " ORDER BY name ASC";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

//EDIT PRODUCT FOR FORM MODAL
$edit_product = null;
if ($is_admin && isset($_GET['edit_id'])) {
    $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
}

//CART FUNCTIONS
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    //CHECKING IF YOURE LOGGED IN TO ADD TO CART
    if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
        header('Location: login.php');
        exit;
    }

    //PRODUCT ID
    $product_id = (int)$_POST['product_id'];

    //FUNCTION TO CHECK FOR PRODUCTS
    $product_exists = false;
    foreach ($products as $product) {
        if ($product['id'] == $product_id) {
            $product_exists = true;
            break;
        }
    }

    // Add or update product quantity in cart
    if ($product_exists) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
    }

    header("Location: products.php#products-section");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'remove_from_cart' && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Remove the product from the cart session
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    // Redirect back to the page to see the changes
    header("Location: products.php");
    exit;
}




// Logout handler
if (isset($_GET['action']) && $_GET['action'] === "logout") {
    unset($_SESSION['username'], $_SESSION['is_logged_in'], $_SESSION['role']);
    session_destroy();

    setcookie("username", null, -1);
    setcookie("is_logged_in", null, -1);
    setcookie("role", null, -1);

    header("Location: index.php");
    exit;
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hackathon Template</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
<section id="hero">
    <?php include './assets/components/navbar.php' ?>
    <div class="position-relative overflow-hidden hero d-flex align-items-center justify-content-center mb-5">
        <div class="container">
            <div class="row d-flex py-5 text-center align-items-center">
                <div class="col-md-12 text-white">
                    <h1 class="display-3 fw-bold">View Our Products</h1>
                    <p class="lead">Fair prices and excellent quality</p>
                    <form action="#products-section" method="GET" class="d-flex justify-content-center mt-3">
                        <input type="text" class="form-control rounded-4 w-50" id="search" name="search"
                               value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search products...">
                        <button type="submit" class="btn btn-light rounded-4 ms-2 search-btn">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="product-page">
    <!-- Admin Form Modal -->
    <?php if ($is_admin): ?>
        <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">
                            <?= $edit_product ? 'Edit Product' : 'Add New Product' ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="productForm" method="POST" action="products.php">
                            <input type="hidden" name="action" value="<?= $edit_product ? 'edit' : 'add' ?>">
                            <?php if ($edit_product): ?>
                                <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description"
                                          rows="3"><?= $edit_product ? htmlspecialchars($edit_product['description']) : '' ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price ($)</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required
                                       value="<?= $edit_product ? $edit_product['price'] : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" required
                                       value="<?= $edit_product ? htmlspecialchars($edit_product['category']) : '' ?>"
                                       list="category-list">
                                <datalist id="category-list">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>">
                                        <?php endforeach; ?>
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <label for="image_url" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" required
                                       value="<?= $edit_product ? htmlspecialchars($edit_product['image_url']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary"><?= $edit_product ? 'Save Changes' : 'Add Product' ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section id="products-section" class="mb-5">
        <div class="container">
            <h2 class="text-center mb-4">Our Products</h2>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <div class="admin-controls mb-4">
                    <h5>Admin Controls</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                        Add New Product
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar">
                        <h5>Filters</h5>
                        <button class="btn btn-dark w-100 d-md-none mobile-filter-toggle" type="button"
                                data-bs-toggle="collapse" data-bs-target="#filterContent">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                 class="bi bi-funnel" viewBox="0 0 16 16">
                                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
                            </svg>
                            Show/Hide Filters
                        </button>

                        <div class="filter-content d-md-block" id="filterContent">
                            <form action="products.php" method="GET">
                                <!-- Search -->
                                <div class="mb-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="<?= htmlspecialchars($search) ?>" placeholder="Search products...">
                                </div>

                                <!-- Category Filter -->
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter == $cat ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Price Range -->
                                <div class="mb-3">
                                    <label class="form-label">Price Range</label>
                                    <div class="price-range">
                                        <input type="number" class="form-control" name="min_price"
                                               value="<?= htmlspecialchars($min_price) ?>" placeholder="Min">
                                        <span>to</span>
                                        <input type="number" class="form-control" name="max_price"
                                               value="<?= htmlspecialchars($max_price) ?>" placeholder="Max">
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-dark">Apply Filters</button>
                                    <a href="products.php" class="btn btn-outline-secondary">Reset Filters</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Display -->
                <div class="col-md-9">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <div class="col">
                                    <div class="card product-card h-100">
                                        <div class="product-img-container">
                                            <img src="<?= htmlspecialchars($product['image_url']) ?>"
                                                 class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                            <p class="card-text text-muted small"><?= htmlspecialchars($product['category']) ?></p>
                                            <p class="card-text">
                                                $<?= htmlspecialchars(number_format($product['price'], 2)) ?></p>
                                            <?php if (!empty($product['description'])): ?>
                                                <p class="card-text small text-muted"><?= htmlspecialchars($product['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-white border-top-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <form method="POST" action="products.php" class="d-inline">
                                                    <input type="hidden" name="action" value="add_to_cart">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-dark">Add to Cart</button>
                                                </form>
                                                <?php if ($is_admin): ?>
                                                    <div class="btn-group" role="group">
                                                        <a href="products.php?edit_id=<?= $product['id'] ?>#products-section"
                                                           class="btn btn-sm btn-outline-secondary rounded-3 mx-2"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                 height="16" fill="currentColor" class="bi bi-pencil"
                                                                 viewBox="0 0 16 16">
                                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                                            </svg>
                                                        </a>
                                                        <form method="POST" action="products.php" class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id"
                                                                   value="<?= $product['id'] ?>">
                                                            <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger rounded-3">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                     height="16" fill="currentColor"
                                                                     class="bi bi-trash3" viewBox="0 0 16 16">
                                                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No products found. Try different filter criteria.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

<?php include './assets/components/footer.php' ?>

<!-- Back to Top Button -->
<a id="backtotop-button" style="text-decoration: none">↑</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
</script>

<script>
    // Back to top button
    const btn = document.querySelector("#backtotop-button");

    window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
            btn.classList.add("show");
        } else {
            btn.classList.remove("show");
        }
    });

    btn.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function () {
        var scrollPosition = document.documentElement.scrollTop || document.body.scrollTop;

        if (scrollPosition > 100) {
            document.querySelector('nav').classList.add('scrolled');
            var navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function (link) {
                link.classList.add('scrolled');
            });
        } else {
            document.querySelector('nav').classList.remove('scrolled');
            var navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function (link) {
                link.classList.remove('scrolled');
            });
        }
    });

    // Populate form when editing
    <?php if ($is_admin && $edit_product): ?>
    document.addEventListener('DOMContentLoaded', function () {
        // Open modal automatically if we're editing
        var productModal = new bootstrap.Modal(document.getElementById('productModal'))
        productModal.show();
    });
    <?php endif; ?>
</script>
</body>
</html>