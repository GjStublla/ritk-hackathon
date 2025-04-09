<?php 
session_start();
include "db.php";

function is_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

$is_logged_in = $_SESSION['is_logged_in'] ?? $_COOKIE['is_logged_in'] ?? false;
$username = $_SESSION['username'] ?? $_COOKIE['username'] ?? null;
$user_role = $_SESSION['role'] ?? $_COOKIE['role'] ?? null;

// Redirect to index if already logged in
if ($is_logged_in && $username) {
    header("Location: index.php");
    exit;
}

$errors = [];

if (isset($_POST['register_btn'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = isset($_POST['role']) ? intval($_POST['role']) : 0; 

    if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if (is_email($email)) {
            if ($password === $confirm_password) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $check_stmt = $mysqli->prepare("SELECT id FROM `users` WHERE `email` = ?");
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows > 0) {
                    $errors[] = "Email is already registered!";
                } else {
                    $stmt = $mysqli->prepare("INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $username, $email, $hashed_password, $role);

                    if ($stmt->execute()) {
                        $_SESSION['username'] = $username;
                        $_SESSION['is_logged_in'] = true;
                        $_SESSION['role'] = $role;

            
                        setcookie("username", $username, time() + 120, "", "", true, true);
                        setcookie("is_logged_in", $_SESSION['is_logged_in'], time() + 120, "", "", true, true);
                        setcookie("role", $_SESSION['role'], time() + 120, "", "", true, true);

                        header("Location: index.php");
                        exit;
                    } else {
                        $errors[] = "Registration failed!";
                    }

                    $stmt->close();
                }

                $check_stmt->close();
            } else {
                $errors[] = "Password and Confirm password don't match!";
            }
        } else {
            $errors[] = "Email is not valid!";
        }
    } else {
        $errors[] = "All fields are required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
<section class="login d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <?php include './assets/components/navbar.php' ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg mx-auto rounded-4" style="max-width: 480px;">
                    <div class="card-body">
                        <div class="text-center">
                            <h1 class="card-title h3">Register</h1>
                            <p class="card-text text-muted">Create an account to get started</p>
                        </div>

                        <?php if (isset($errors) && count($errors)): ?>
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <label for="username" class="form-label text-muted">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <label for="email" class="form-label text-muted">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label for="password" class="form-label text-muted">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label text-muted">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                        </div>
                                    </div>
                                </div>


                                <div class="mb-4">
                                    <label for="role" class="form-label text-muted">Select Role</label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option value="0">User</option>
                                        <option value="1">Admin</option>
                                    </select>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" name="register_btn" class="btn btn-dark">Register</button>
                                </div>

                                <div class="text-center">
                                    <p class="text-muted">Already have an account? <a href="login.php" class="text-decoration-none">Login</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
</script>

</body>

</html>