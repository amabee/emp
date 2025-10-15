﻿<?php
include 'shared/config.php';
include 'controllers/auth/login.php';
include 'controllers/SystemController.php';

if (isset($_SESSION['user_id'])) {
  redirect('index.php');
}

$systemController = new SystemController();
$system_details = $systemController->getSystemDetails();

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = sanitize($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $error_message = 'Please enter both username/email and password.';
  } else {
    try {
      $loginController = new Login();
      if ($loginController->authenticate($username, $password)) {
        // Redirect based on user type
        $userType = $_SESSION['user_type'];
        if ($userType === 'admin') {
          redirect('admin/dashboard.php', 'Login successful!', 'success');
        } else if ($userType === 'supervisor') {
          redirect('supervisor/dashboard.php', 'Login successful!', 'success');
        } else if ($userType === 'hr') {
          redirect('hr/dashboard.php', 'Login successful!', 'success');
        } else {
          redirect('employee/dashboard.php', 'Login successful!', 'success');
        }
      } else {
        $error_message = 'Invalid username/email or password.';
      }
    } catch (Exception $e) {
      $error_message = "Login failed. Please try again later. {$e->getMessage()}";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Login - <?= htmlspecialchars($system_details['name'] ?? 'EMP System') ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />
</head>

<body>
  <!-- Content -->
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Login Card -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4">
              <span class="app-brand-text demo text-primary fw-bold">
                <?= htmlspecialchars($system_details['name'] ?? 'EMP System') ?>
              </span>
            </div>
            <!-- /Logo -->
            <h4 class="mb-2 text-center">Welcome Back! 👋</h4>
            <p class="mb-4 text-center">Please sign-in to your account</p>

            <?php if ($error_message): ?>
              <div class="alert alert-danger alert-dismissible" role="alert">
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form id="formAuthentication" class="mb-3" method="POST">
              <div class="mb-3">
                <label for="email" class="form-label">Username or Email</label>
                <input type="text" class="form-control" id="email" name="email"
                  placeholder="Enter your username or email" value="<?= htmlspecialchars($username ?? '') ?>" autofocus
                  required />
              </div>
              <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                  <label class="form-label" for="password">Password</label>
                  <a href="#">
                    <small>Forgot Password?</small>
                  </a>
                </div>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                  <span class="input-group-text cursor-pointer"><i class="bi bi-eye"></i></span>
                </div>
              </div>
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember-me" />
                  <label class="form-check-label" for="remember-me"> Remember Me </label>
                </div>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
              </div>
            </form>

            <div class="divider my-4">
              <div class="divider-text">or</div>
            </div>

            <p class="text-center">
              <a href="./pages/landing.php" class="btn btn-outline-secondary">
                <i class='bx bx-left-arrow-circle'></i> Back to Home
              </a>
            </p>

            <p class="text-center mt-4">
              <span>Job applicant?</span>
              <a href="./pages/applicant-login.php">
                <span>Applicant Portal</span>
              </a>
            </p>

            <!-- <p class="text-center">
              <span>New here?</span>
              <a href="#">
                <span>Create an account</span>
              </a>
            </p> -->
          </div>
        </div>
        <!-- /Login Card -->
      </div>
    </div>
  </div>

  <!-- Core JS -->
  <script src="assets/vendor/libs/jquery/jquery.js"></script>
  <script src="assets/vendor/libs/popper/popper.js"></script>
  <script src="assets/vendor/js/bootstrap.js"></script>
  <script src="assets/vendor/js/menu.js"></script>

  <!-- Page JS -->
  <script>
    // Password show/hide functionality
    document.querySelectorAll('.form-password-toggle i').forEach(icon => {
      icon.addEventListener('click', e => {
        const input = e.target.closest('.input-group-merge').querySelector('input');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('bi-eye');
          icon.classList.add('bi-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('bi-eye-slash');
          icon.classList.add('bi-eye');
        }
      });
    });
  </script>
</body>

</html>
