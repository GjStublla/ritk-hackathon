<?php
session_start();
include "db.php";

$is_logged_in = $_SESSION['is_logged_in'] ?? $_COOKIE['is_logged_in'] ?? false;
$username = $_SESSION['username'] ?? $_COOKIE['username'] ?? null;
$user_role = $_SESSION['role'] ?? $_COOKIE['role'] ?? null;

// Redirect to index if already logged in
if ($is_logged_in && $username) {
    header("Location: index.php");
    exit;
}

function is_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

$errors = [];

if (isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        if (is_email($email)) {
            $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `email` = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();

                    if (password_verify($password, $row['password'])) {
                        $username = $row['username'];
                        $role = $row['role'];
                        $user_id = $row['id'];

                        $_SESSION['username'] = $username;
                        $_SESSION['is_logged_in'] = true;
                        $_SESSION['role'] = $role;
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['email'] = $email;

                        setcookie("username", $username, time() + 120, "", "", true, true);
                        setcookie("is_logged_in", $_SESSION['is_logged_in'], time() + 120, "", "", true, true);
                        setcookie("role", $_SESSION['role'], time() + 120, "", "", true, true);
                        setcookie("user_id", $user_id, time() + 120, "", "", true, true);

                        header("Location: index.php");
                    } else {
                        $errors[] = "Password is incorrect!";
                    }
                } else {
                    $errors[] = "User doesn't exist!";
                }

                $stmt->close();
            } else {
                $errors[] = "Login failed: database error.";
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
    <title>Login | </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
<section class="login d-flex align-items-center justify-content-center">
    <div class="container">
        <?php include './assets/components/navbar.php' ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg mx-auto rounded-4" style="max-width: 480px;">
                    <div class="card-body">
                        <div class="text-center">
                            <h1 class="card-title h3">Login</h1>
                            <p class="card-text text-muted">Log in below to access your account</p>
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

                        <div class="mt-2">
                            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                <div class="mb-4">
                                    <label for="email" class="form-label text-muted">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           placeholder="Email Address" required>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label text-muted">Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="login_btn" class="btn btn-dark">Sign in</button>
                                </div>
                                <p class="text-center text-muted mt-4">Don't have an account yet?
                                    <a href="register.php" class="text-decoration-none">Sign up</a>.
                                </p>
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