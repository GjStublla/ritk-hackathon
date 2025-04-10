<?php
session_start();
include 'db.php';

//SESSIONS
$is_logged_in = $_SESSION['is_logged_in'] ?? $_COOKIE['is_logged_in'] ?? false;
$username = $_SESSION['username'] ?? $_COOKIE['username'] ?? null;
$user_role = $_SESSION['role'] ?? $_COOKIE['role'] ?? null;
//TO CHECK FOR ADMIN PRIVILEGES
$is_admin = ($user_role == 1);

//CRUD OPERATIONS FOR JOBS IF ADMIN
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $title = htmlspecialchars($_POST['title'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $salary = filter_var($_POST['salary'] ?? 0, FILTER_VALIDATE_FLOAT);
            $category = htmlspecialchars($_POST['category'] ?? '');
            $location = htmlspecialchars($_POST['location'] ?? '');
            $company = htmlspecialchars($_POST['company'] ?? '');

            $stmt = $mysqli->prepare("INSERT INTO jobs (title, description, salary, location, category, company) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsss", $title, $description, $salary, $location, $category, $company);
            $stmt->execute();
            $stmt->close();

            $success_message = "Job added successfully!";

        } elseif ($action === 'edit') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $title = htmlspecialchars($_POST['title'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $salary = filter_var($_POST['salary'] ?? 0, FILTER_VALIDATE_FLOAT);
            $category = htmlspecialchars($_POST['category'] ?? '');
            $location = htmlspecialchars($_POST['location'] ?? '');
            $company = htmlspecialchars($_POST['company'] ?? '');

            $stmt = $mysqli->prepare("UPDATE jobs SET title = ?, description = ?, salary = ?, location = ?, category = ?, company = ? WHERE id = ?");
            $stmt->bind_param("ssdsssi", $title, $description, $salary, $location, $category, $company, $id);
            $stmt->execute();
            $stmt->close();

            $success_message = "Job updated successfully!";

        } elseif ($action === 'delete' && isset($_POST['id'])) {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $stmt = $mysqli->prepare("DELETE FROM jobs WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $success_message = "Job deleted successfully!";
        }
    }
}

//FETCH CATEGORIES
$categories = [];
$result = $mysqli->query("SELECT DISTINCT category FROM jobs ORDER BY category");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

//FILTER INPUTS
$category_filter = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$min_salary = isset($_GET['min_salary']) ? filter_var($_GET['min_salary'], FILTER_VALIDATE_FLOAT) : '';
$max_salary = isset($_GET['max_salary']) ? filter_var($_GET['max_salary'], FILTER_VALIDATE_FLOAT) : '';
$location_filter = isset($_GET['location']) ? htmlspecialchars($_GET['location']) : '';
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

$sql = "SELECT * FROM jobs WHERE 1=1";
$params = [];
$types = '';

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if (!empty($location_filter)) {
    $sql .= " AND location = ?";
    $params[] = $location_filter;
    $types .= 's';
}

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR company LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

if (!empty($min_salary)) {
    $sql .= " AND salary >= ?";
    $params[] = $min_salary;
    $types .= 'd';
}

if (!empty($max_salary)) {
    $sql .= " AND salary <= ?";
    $params[] = $max_salary;
    $types .= 'd';
}

$sql .= " ORDER BY title ASC";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);

//FETCH LOCATIONS FOR FILTER
$locations = [];
$location_result = $mysqli->query("SELECT DISTINCT location FROM jobs ORDER BY location");
if ($location_result) {
    while ($row = $location_result->fetch_assoc()) {
        $locations[] = $row['location'];
    }
}

//EDIT JOB FOR FORM MODAL
$edit_job = null;
if ($is_admin && isset($_GET['edit_id'])) {
    $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);
    $stmt = $mysqli->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_job = $result->fetch_assoc();
}

//JOB APPLICATION FUNCTIONS
if (!isset($_SESSION['applications'])) {
    $_SESSION['applications'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply_for_job') {
    //CHECKING IF YOURE LOGGED IN TO APPLY
    if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
        header('Location: login.php');
        exit;
    }

    //JOB ID
    $job_id = (int)$_POST['job_id'];

    //FUNCTION TO CHECK FOR JOBS
    $job_exists = false;
    foreach ($jobs as $job) {
        if ($job['id'] == $job_id) {
            $job_exists = true;
            break;
        }
    }

    // Add job to applications
    if ($job_exists) {
        if (!in_array($job_id, $_SESSION['applications'])) {
            $_SESSION['applications'][] = $job_id;
        }
    }

    header("Location: jobs.php#jobs-section");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'withdraw_application' && isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];

    // Remove the job from applications session
    if (($key = array_search($job_id, $_SESSION['applications'])) !== false) {
        unset($_SESSION['applications'][$key]);
    }

    // Redirect back to the page to see the changes
    header("Location: jobs.php");
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
    <title>Job Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        #hero {
            @media (max-width: 725px) {
                h1 {
                    padding-top: 0px!important;
                }
            }
        }
    </style>
</head>

<body>
<section id="hero">
    <?php include './assets/components/navbar.php' ?>
    <div class="position-relative overflow-hidden hero d-flex align-items-center justify-content-center mb-5">
        <div class="container">
            <div class="row d-flex py-5 text-center align-items-center">
                <div class="col-md-12 text-white">
                    <h1 class="display-3 fw-bold">Job Listings</h1>
                    <p class="lead">Find your dream job today</p>
                    <form action="#jobs-section" method="GET" class="d-flex justify-content-center mt-4">
                        <input type="text" class="form-control rounded-4 w-50 outline-dark" id="search" name="search"
                               value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search jobs...">
                        <button type="submit" class="btn btn-light rounded-4 ms-2 search-btn">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="job-page">
    <!-- Admin Form Modal -->
    <?php if ($is_admin): ?>
        <div class="modal fade" id="jobModal" tabindex="-1" aria-labelledby="jobModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="jobModalLabel">
                            <?= $edit_job ? 'Edit Job' : 'Add New Job' ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="jobForm" method="POST" action="jobs.php">
                            <input type="hidden" name="action" value="<?= $edit_job ? 'edit' : 'add' ?>">
                            <?php if ($edit_job): ?>
                                <input type="hidden" name="id" value="<?= $edit_job['id'] ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="title" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?= $edit_job ? htmlspecialchars($edit_job['title']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="company" class="form-label">Company</label>
                                <input type="text" class="form-control" id="company" name="company" required
                                       value="<?= $edit_job ? htmlspecialchars($edit_job['company']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" required
                                       value="<?= $edit_job ? htmlspecialchars($edit_job['location']) : '' ?>"
                                       list="location-list">
                                <datalist id="location-list">
                                    <?php foreach ($locations

                                    as $loc): ?>
                                    <option value="<?= htmlspecialchars($loc) ?>">
                                        <?php endforeach; ?>
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description"
                                          rows="3"><?= $edit_job ? htmlspecialchars($edit_job['description']) : '' ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="salary" class="form-label">Salary ($)</label>
                                <input type="number" step="0.01" class="form-control" id="salary" name="salary" required
                                       value="<?= $edit_job ? $edit_job['salary'] : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" required
                                       value="<?= $edit_job ? htmlspecialchars($edit_job['category']) : '' ?>"
                                       list="category-list">
                                <datalist id="category-list">
                                    <?php foreach ($categories

                                    as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>">
                                        <?php endforeach; ?>
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <button type="submit"
                                        class="btn btn-primary"><?= $edit_job ? 'Save Changes' : 'Add Job' ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section id="jobs-section" class="mb-5">
        <div class="container">
            <h2 class="text-center mb-4">Available Jobs</h2>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <div class="admin-controls mb-4">
                    <h5>Admin Controls</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#jobModal">
                        Add New Job
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
                            <form action="jobs.php" method="GET">
                                <!-- Search -->
                                <div class="mb-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="<?= htmlspecialchars($search) ?>" placeholder="Search jobs...">
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

                                <!-- Location Filter -->
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <select class="form-select" id="location" name="location">
                                        <option value="">All Locations</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?= htmlspecialchars($loc) ?>" <?= $location_filter == $loc ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($loc) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Salary Range -->
                                <div class="mb-3">
                                    <label class="form-label">Salary Range</label>
                                    <div class="salary-range">
                                        <input type="number" class="form-control" name="min_salary"
                                               value="<?= htmlspecialchars($min_salary) ?>" placeholder="Min">
                                        <span>to</span>
                                        <input type="number" class="form-control" name="max_salary"
                                               value="<?= htmlspecialchars($max_salary) ?>" placeholder="Max">
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-dark">Apply Filters</button>
                                    <a href="jobs.php" class="btn btn-outline-secondary">Reset Filters</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Jobs Display -->
                <div class="col-md-9">
                    <div class="row">
                        <?php if (count($jobs) > 0): ?>
                            <?php foreach ($jobs as $job): ?>
                                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                    <div class="card job-card h-100 rounded-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="card-title"><?= htmlspecialchars($job['title']) ?></h5>
                                                    <h6 class="card-subtitle mb-2 text-muted">
                                                        <span class="ms-2 text-warning d-inline-flex align-items-center" style="line-height: 1;">
                                                            4.5
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                                 class="bi bi-star ms-1" viewBox="0 0 16 16">
                                                                <path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256
                                                                         4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73
                                                                         3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513
                                                                         0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523
                                                                         3.356-.83 4.73zm4.905-2.767-3.686 1.894.694-3.957a.56.56
                                                                         0 0 0-.163-.505L1.71 6.745l4.052-.576a.53.53 0 0 0
                                                                         .393-.288L8 2.223l1.847 3.658a.53.53 0 0 0 .393.288l4.052.575-2.906
                                                                         2.77a.56.56 0 0 0-.163.506l.694 3.957-3.686-1.894a.5.5
                                                                         0 0 0-.461 0z"/>
                                                            </svg>
                                                        </span>

                                                        <?= htmlspecialchars($job['company']) ?>
                                                    </h6>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary text-truncate"
                                                          style="max-width: 100px;"><?= htmlspecialchars($job['category']) ?></span>
                                                    <p class="card-text mt-2">
                                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($job['location']) ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <p class="card-text mt-3">
                                                Salary: €<?= htmlspecialchars(number_format($job['salary'], 2)) ?> per
                                                month
                                            </p>

                                            <?php if (!empty($job['description'])): ?>
                                                <p class="card-text"><?= htmlspecialchars($job['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-white border-top-0 rounded-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <form method="POST" action="jobs.php" class="d-inline">
                                                    <input type="hidden" name="action" value="apply_for_job">
                                                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                                    <?php if (in_array($job['id'], $_SESSION['applications'] ?? [])): ?>
                                                        <a href="jobs.php?action=withdraw_application&job_id=<?= $job['id'] ?>"
                                                           class="btn btn-sm btn-outline-secondary">Withdraw
                                                            Application</a>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-sm btn-dark">Apply Now
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                                <?php if ($is_admin): ?>
                                                    <div class="btn-group" role="group">
                                                        <a href="jobs.php?edit_id=<?= $job['id'] ?>#jobs-section"
                                                           class="btn btn-sm btn-outline-secondary rounded-3 mx-2"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                 height="16" fill="currentColor" class="bi bi-pencil"
                                                                 viewBox="0 0 16 16">
                                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                                            </svg>
                                                        </a>
                                                        <form method="POST" action="jobs.php" class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this job?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id"
                                                                   value="<?= $job['id'] ?>">
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
                                    No jobs found. Try different filter criteria.
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
    <?php if ($is_admin && $edit_job): ?>
    document.addEventListener('DOMContentLoaded', function () {
        // Open modal automatically if we're editing
        var jobModal = new bootstrap.Modal(document.getElementById('jobModal'))
        jobModal.show();
    });
    <?php endif; ?>
</script>
</body>
</html>