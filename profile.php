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

//    if (!empty($new_password) && strlen($new_password) < 8) {
//        $errors[] = "New password must be at least 8 characters long.";
//    }

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
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $new_username, $new_email, $hashed_password, $user_id);
            } else {
                $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
            }

            if ($update_stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $_SESSION['email'] = $new_email;
                $success = "Profile updated successfully.";


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
    <title>Edit Profile | </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>

<?php include './assets/components/navbar.php' ?>

<section class="login d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-sm-12">
                <div class="card shadow-lg mx-auto rounded-4" style="max-width: 480px;">
                    <div class="card-body">
                        <div class="text-center">
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
                            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="username" class="form-label text-muted">Username</label>
                                        <input type="text" class="form-control" id="username" name="username"
                                               value="<?= htmlspecialchars($username) ?>" required>
                                    </div>
                                </div>
                                <div class="row py-2">
                                    <div class="col-12">
                                        <label for="email" class="form-label text-muted">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?= htmlspecialchars($email) ?>" required>
                                    </div>
                                </div>
                                <div class="row py-2">
                                    <div class="col-6">
                                        <label for="new_password" class="form-label text-muted">New Password
                                            </label>
                                        <input type="password" class="form-control" id="new_password"
                                               name="new_password" placeholder="optional">
                                    </div>
                                    <div class="col-6">
                                        <label for="current_password" class="form-label text-muted">Current Password
                                            <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="current_password"
                                               name="current_password" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 d-flex align-items-center justify-content-center py-3">
                                        <button type="submit" class="btn btn-dark">Save Changes</button>
                                    </div>
                                </div>
                            </form>


                            <hr class="my-1">

                            <!-- Delete Account Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="text-center mb-3 mt-2">
                                        <h5 class="text-danger">Delete Account</h5>
                                        <p class="text-muted small">This action cannot be undone.</p>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteAccountModal">
                                            Delete My Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Outside Card -->
</section>

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
</script>
</body>
</html>