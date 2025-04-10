<?php
session_start();
include "db.php";

// Use the same session/cookie variables as your login page
$is_logged_in = $_SESSION['is_logged_in'] ?? $_COOKIE['is_logged_in'] ?? false;
$username = $_SESSION['username'] ?? $_COOKIE['username'] ?? null;
$user_role = $_SESSION['role'] ?? $_COOKIE['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? null;
$email = $_SESSION['email'] ?? null;

// Check if user is logged in
if (!$is_logged_in || !$username) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === "logout") {
        unset($_SESSION['username']);
        unset($_SESSION['is_logged_in']);
        unset($_SESSION['role']);
        unset($_SESSION['user_id']);
        unset($_SESSION['email']);
        session_destroy();

        setcookie("user_email", null, -1);
        setcookie("is_logged_in", null, -1);
        setcookie("role", null, -1);
        setcookie("user_id", null, -1);

        header("Location: index.php");
        exit();
    }
}

$errors = [];
$success = "";

if (!$user_id || !$email) {
    $stmt = $mysqli->prepare("SELECT id, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user_data = $result->fetch_assoc()) {
        if (!$user_id) {
            $user_id = $user_data['id'];
            $_SESSION['user_id'] = $user_id;
        }
        if (!$email) {
            $email = $user_data['email'];
            $_SESSION['email'] = $email;
        }
    } else {
        header("Location: login.php");
        exit();
    }
    $stmt->close();
}

// Get current resume info
$resume_file = "";
$resume_type = "";
$resume_uploaded = "";

$stmt = $mysqli->prepare("SELECT resume_file, resume_type, resume_uploaded FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($user_data = $result->fetch_assoc()) {
    $resume_file = $user_data['resume_file'];
    $resume_type = $user_data['resume_type'];
    $resume_uploaded = $user_data['resume_uploaded'];
}
$stmt->close();

if (isset($_POST['delete_account']) && $_POST['delete_account'] === "1") {
    $delete_password = $_POST['delete_password'] ?? '';

    $stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $errors[] = "User not found.";
    } elseif (!password_verify($delete_password, $user['password'])) {
        $errors[] = "Password is incorrect. Account not deleted.";
    } else {
        // Delete resume file if exists
        if (!empty($resume_file) && file_exists("uploads/resumes/" . $resume_file)) {
            unlink("uploads/resumes/" . $resume_file);
        }

        $delete_stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $user_id);

        if ($delete_stmt->execute()) {
            unset($_SESSION['username']);
            unset($_SESSION['is_logged_in']);
            unset($_SESSION['role']);
            unset($_SESSION['user_id']);
            unset($_SESSION['email']);
            session_destroy();

            setcookie("user_email", null, -1);
            setcookie("is_logged_in", null, -1);
            setcookie("role", null, -1);
            setcookie("user_id", null, -1);
            // Redirect to index with message
            header("Location: index.php?deleted=1");
            exit();
        } else {
            $errors[] = "Error deleting account: " . $mysqli->error;
        }
        $delete_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_account'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = $_POST['new_password'] ?? '';
    $current_password = $_POST['current_password'] ?? '';

    // Basic validation
    if (empty($new_username) || strlen($new_username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }

    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Handle resume file upload
    $resume_update = false;
    $new_resume_file = $resume_file;
    $new_resume_type = $resume_type;
    $new_resume_uploaded = $resume_uploaded;

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed_types = ['application/pdf' => 'pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx', 'application/msword' => 'doc'];
        $file_type = $_FILES['resume']['type'];

        if (!array_key_exists($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Please upload PDF, DOCX, or DOC files only.";
        } else {
            $file_ext = $allowed_types[$file_type];
            $file_name = uniqid('resume_') . '_' . $user_id . '.' . $file_ext;
            $upload_dir = 'uploads/resumes/';

            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . $file_name)) {
                // Delete old resume file if exists
                if (!empty($resume_file) && file_exists($upload_dir . $resume_file)) {
                    unlink($upload_dir . $resume_file);
                }

                $new_resume_file = $file_name;
                $new_resume_type = $file_ext;
                $new_resume_uploaded = date('Y-m-d H:i:s');
                $resume_update = true;
            } else {
                $errors[] = "Error uploading resume file.";
            }
        }
    } elseif (isset($_POST['delete_resume']) && $_POST['delete_resume'] == 1) {
        // Delete resume file
        if (!empty($resume_file) && file_exists("uploads/resumes/" . $resume_file)) {
            unlink("uploads/resumes/" . $resume_file);
        }
        $new_resume_file = null;
        $new_resume_type = null;
        $new_resume_uploaded = null;
        $resume_update = true;
    }

    if (empty($errors)) {
        // Get current user data from database to verify password
        $stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors[] = "User not found.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        } else {
            // Update user data based on what's changing
            if (!empty($new_password) && $resume_update) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ?, password = ?, 
                                               resume_file = ?, resume_type = ?, resume_uploaded = ? WHERE id = ?");
                $update_stmt->bind_param("ssssssi", $new_username, $new_email, $hashed_password,
                    $new_resume_file, $new_resume_type, $new_resume_uploaded, $user_id);
            } elseif (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $new_username, $new_email, $hashed_password, $user_id);
            } elseif ($resume_update) {
                $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ?, 
                                               resume_file = ?, resume_type = ?, resume_uploaded = ? WHERE id = ?");
                $update_stmt->bind_param("sssssi", $new_username, $new_email,
                    $new_resume_file, $new_resume_type, $new_resume_uploaded, $user_id);
            } else {
                $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
            }

            if ($update_stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $_SESSION['email'] = $new_email;
                $success = "Profile updated successfully.";

                // Update resume display variables
                $resume_file = $new_resume_file;
                $resume_type = $new_resume_type;
                $resume_uploaded = $new_resume_uploaded;

                if (isset($_COOKIE['username'])) {
                    setcookie("username", $new_username, time() + 120, "", "", true, true);
                }
            } else {
                $errors[] = "Error updating profile: " . $mysqli->error;
            }
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
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

<?php include './assets/components/navbar.php' ?>

<section class="login profile-section py-5" style="padding-top: 100px!important;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="card shadow-lg mx-auto rounded-4">
                    <div class="card-body p-md-4 p-3">
                        <div class="text-center mb-4">
                            <h1 class="card-title h3">Edit Profile</h1>
                            <p class="card-text text-muted">Update your account information below</p>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success mt-3"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" enctype="multipart/form-data">
                                <!-- Username and Email in one row on larger screens -->
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="username" class="form-label text-muted">Username</label>
                                        <input type="text" class="form-control" id="username" name="username"
                                               value="<?= htmlspecialchars($username) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label text-muted">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?= htmlspecialchars($email) ?>" required>
                                    </div>
                                </div>

                                <!-- Resume Upload Section -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label for="resume" class="form-label text-muted">Resume (PDF, DOCX, DOC)</label>
                                        <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.docx,.doc">

                                        <?php if (!empty($resume_file)): ?>
                                            <div class="mt-2 p-2 bg-light rounded border">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8 col-sm-12 mb-2 mb-md-0">
                                                        <small class="text-muted">Current resume:
                                                            <span class="text-dark"><?= htmlspecialchars($resume_file) ?></span>
                                                        </small>
                                                        <br>
                                                        <small class="text-muted">Uploaded:
                                                            <span class="text-dark"><?= date('M j, Y', strtotime($resume_uploaded)) ?></span>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4 col-sm-12 text-md-end">
                                                        <div class="d-flex gap-2 justify-content-md-end justify-content-start">
                                                            <a href="uploads/resumes/<?= htmlspecialchars($resume_file) ?>"
                                                               class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                                            <button type="submit" name="delete_resume" value="1"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete your resume?')">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-1">
                                                <small class="text-muted">No resume uploaded yet. Upload your resume to apply for jobs.</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Password fields side by side -->
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="new_password" class="form-label text-muted">New Password</label>
                                        <input type="password" class="form-control" id="new_password"
                                               name="new_password" placeholder="optional">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="current_password" class="form-label text-muted">Current Password
                                            <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="current_password"
                                               name="current_password" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 d-flex align-items-center justify-content-center py-2">
                                        <button type="submit" class="btn btn-dark px-4 py-2 w-50">Save Changes</button>
                                        <button type="button" class="btn btn-outline-danger px-4 py-2 mx-3 w-50" data-bs-toggle="modal"
                                                data-bs-target="#deleteAccountModal">
                                            Delete My Account
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">Warning: This action cannot be undone. All your data will be permanently
                        deleted.</p>
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Enter your password to confirm:</label>
                        <input type="password" class="form-control" id="delete_password" name="delete_password"
                               required>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" name="delete_account" value="1">Permanently
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include './assets/components/footer.php' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
</script>

<script>
    window.addEventListener('scroll', function() {
        var scrollPosition = document.documentElement.scrollTop || document.body.scrollTop;
        var navbar = document.querySelector('nav');

        if (scrollPosition > 50) {
            navbar.classList.add('scrolled');
            var navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function(link) {
                link.classList.add('scrolled');
            });
        } else {
            navbar.classList.remove('scrolled');
            var navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function(link) {
                link.classList.remove('scrolled');
            });
        }
    });
</script>
</body>
</html>