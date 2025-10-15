<?php
session_start();

// Check if applicant is logged in
if (!isset($_SESSION['applicant_logged_in']) || !$_SESSION['applicant_logged_in']) {
  // Show login form instead
  $showLogin = true;
} else {
  $showLogin = false;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $showLogin ? 'Applicant Login' : 'My Application'; ?> - Employee Management System</title>
  <meta name="description" content="Applicant Portal" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Public Sans', sans-serif;
      background: #F5F7FA;
      min-height: 100vh;
    }

    /* Navigation Bar */
    .navbar {
      background: #fff;
      padding: 1rem 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 1.25rem;
      font-weight: 700;
      color: #2C3E50;
      text-decoration: none;
    }

    .navbar-brand i {
      font-size: 1.5rem;
      color: #4A90E2;
    }

    .navbar-user {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 600;
    }

    .btn-logout {
      padding: 0.5rem 1.25rem;
      background: #fff;
      border: 1px solid #E0E0E0;
      border-radius: 6px;
      color: #6C757D;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .btn-logout:hover {
      background: #F8F9FA;
      border-color: #CCC;
    }

    /* Login Container */
    .login-container {
      max-width: 450px;
      margin: 4rem auto;
      background: #fff;
      padding: 3rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .login-header h1 {
      font-size: 2rem;
      color: #2C3E50;
      margin-bottom: 0.5rem;
    }

    .login-header p {
      color: #6C757D;
      font-size: 0.95rem;
    }

    /* Dashboard Container */
    .dashboard-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
    }

    .page-header {
      margin-bottom: 2rem;
    }

    .page-header h1 {
      font-size: 2rem;
      color: #2C3E50;
      margin-bottom: 0.5rem;
    }

    .page-header p {
      color: #6C757D;
    }

    /* Status Card */
    .status-card {
      background: #fff;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
    }

    .status-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-badge.pending { background: #FFF3CD; color: #856404; }
    .status-badge.reviewing { background: #D1ECF1; color: #0C5460; }
    .status-badge.interview_scheduled { background: #D4EDDA; color: #155724; }
    .status-badge.interviewed { background: #CCE5FF; color: #004085; }
    .status-badge.accepted { background: #D4EDDA; color: #155724; }
    .status-badge.hired { background: #28A745; color: #fff; }
    .status-badge.rejected { background: #F8D7DA; color: #721C24; }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
    }

    .info-item {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .info-label {
      font-size: 0.85rem;
      color: #6C757D;
      font-weight: 500;
    }

    .info-value {
      font-size: 1rem;
      color: #2C3E50;
      font-weight: 600;
    }

    /* Form Styles */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      font-size: 0.9rem;
      font-weight: 500;
      color: #2C3E50;
      margin-bottom: 0.5rem;
    }

    .form-group input {
      width: 100%;
      padding: 0.85rem 1rem;
      border: 1px solid #E0E0E0;
      border-radius: 8px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      font-family: 'Public Sans', sans-serif;
    }

    .form-group input:focus {
      outline: none;
      border-color: #4A90E2;
      box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    .btn-primary {
      padding: 0.85rem 2rem;
      background: #4A90E2;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background: #357ABD;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
    }

    .btn-primary:disabled {
      background: #B0B0B0;
      cursor: not-allowed;
      transform: none;
    }

    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .alert-info { background: #E3F2FD; color: #1976D2; border: 1px solid #90CAF9; }
    .alert-danger { background: #FFEBEE; color: #C62828; border: 1px solid #EF9A9A; }
    .alert-success { background: #E8F5E9; color: #2E7D32; border: 1px solid #A5D6A7; }
    .alert-warning { background: #FFF3CD; color: #856404; border: 1px solid #FFEAA7; }

    .link-text {
      text-align: center;
      margin-top: 1.5rem;
      color: #6C757D;
      font-size: 0.9rem;
    }

    .link-text a {
      color: #4A90E2;
      text-decoration: none;
      font-weight: 600;
    }

    .link-text a:hover {
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .dashboard-container {
        padding: 0 1rem;
      }

      .navbar {
        padding: 1rem;
      }

      .status-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
    }
  </style>
</head>

<body>
  <?php if ($showLogin): ?>
    <!-- Login View -->
    <div class="navbar">
      <a href="./landing.php" class="navbar-brand">
        <i class='bx bx-briefcase'></i>
        <span id="companyName">Employee Management System</span>
      </a>
    </div>

    <div class="login-container">
      <div class="login-header">
        <h1>Applicant Login</h1>
        <p>Sign in to track your job application</p>
      </div>

      <div id="alertContainer"></div>

      <form id="loginForm">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="your.email@example.com" required autofocus>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-primary" id="loginBtn" style="width: 100%;">Sign In</button>
      </form>

      <div class="link-text">
        Don't have an account? <a href="./applicant-login.php">Register here</a>
      </div>

      <div class="link-text" style="margin-top: 1rem;">
        <a href="./landing.php"><i class='bx bx-left-arrow-alt'></i> Back to Home</a>
      </div>
    </div>

  <?php else: ?>
    <!-- Dashboard View -->
    <div class="navbar">
      <a href="./applicant-portal.php" class="navbar-brand">
        <i class='bx bx-briefcase'></i>
        <span id="companyName">Employee Management System</span>
      </a>
      <div class="navbar-user">
        <div class="user-info">
          <div class="user-avatar" id="userAvatar">
            <?php echo strtoupper(substr($_SESSION['applicant_name'], 0, 1)); ?>
          </div>
          <span id="userName"><?php echo htmlspecialchars($_SESSION['applicant_name']); ?></span>
        </div>
        <a href="../ajax/applicant_logout.php" class="btn-logout">
          <i class='bx bx-log-out'></i> Logout
        </a>
      </div>
    </div>

    <div class="dashboard-container">
      <div class="page-header">
        <h1>My Application</h1>
        <p>Track your job application status and manage your profile</p>
      </div>

      <div id="alertContainer"></div>

      <!-- Application Status Card -->
      <div class="status-card">
        <div class="status-header">
          <h2 style="margin: 0; font-size: 1.5rem; color: #2C3E50;">Application Status</h2>
          <span class="status-badge" id="statusBadge">Loading...</span>
        </div>

        <div class="info-grid" id="applicationInfo">
          <div class="info-item">
            <span class="info-label">Application Date</span>
            <span class="info-value" id="applicationDate">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Position Applied</span>
            <span class="info-value" id="positionApplied">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Branch</span>
            <span class="info-value" id="branchApplied">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Email</span>
            <span class="info-value" id="applicantEmail">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Phone</span>
            <span class="info-value" id="applicantPhone">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Interview Date</span>
            <span class="info-value" id="interviewDate">Not scheduled</span>
          </div>
        </div>
      </div>

      <!-- Profile Information -->
      <div class="status-card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; color: #2C3E50;">Profile Information</h2>
        <div class="info-grid" id="profileInfo">
          <div class="info-item">
            <span class="info-label">Full Name</span>
            <span class="info-value" id="fullName">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Date of Birth</span>
            <span class="info-value" id="dateOfBirth">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Gender</span>
            <span class="info-value" id="gender">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Address</span>
            <span class="info-value" id="address">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Skills</span>
            <span class="info-value" id="skills">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Experience</span>
            <span class="info-value" id="experience">-</span>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <script>
    // Load company name
    fetch('../ajax/get_public_company_info.php')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.company) {
          const companyNameElements = document.querySelectorAll('#companyName');
          companyNameElements.forEach(el => {
            el.textContent = data.company.company_name;
          });
        }
      })
      .catch(error => console.error('Error loading company info:', error));

    <?php if ($showLogin): ?>
      // Login form handler
      document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const loginBtn = document.getElementById('loginBtn');
        loginBtn.disabled = true;
        loginBtn.textContent = 'Signing in...';
        
        try {
          const formData = new FormData(this);
          const response = await fetch('../ajax/applicant_login.php', {
            method: 'POST',
            body: formData
          });
          
          const result = await response.json();
          
          if (result.success) {
            showAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          } else {
            showAlert(result.message || 'Login failed. Please try again.', 'danger');
            loginBtn.disabled = false;
            loginBtn.textContent = 'Sign In';
          }
        } catch (error) {
          console.error('Login error:', error);
          showAlert('An error occurred. Please try again later.', 'danger');
          loginBtn.disabled = false;
          loginBtn.textContent = 'Sign In';
        }
      });
    <?php else: ?>
      // Load applicant profile
      async function loadProfile() {
        try {
          const response = await fetch('../ajax/get_applicant_profile.php');
          const result = await response.json();
          
          if (result.success && result.applicant) {
            const app = result.applicant;
            
            // Update status badge
            const statusBadge = document.getElementById('statusBadge');
            statusBadge.textContent = app.status.replace('_', ' ').toUpperCase();
            statusBadge.className = 'status-badge ' + app.status;
            
            // Update application info
            document.getElementById('applicationDate').textContent = formatDate(app.application_date);
            document.getElementById('positionApplied').textContent = app.position_name || 'Not specified';
            document.getElementById('branchApplied').textContent = app.branch_name || 'Not specified';
            document.getElementById('applicantEmail').textContent = app.email;
            document.getElementById('applicantPhone').textContent = app.phone || 'Not provided';
            document.getElementById('interviewDate').textContent = app.interview_date ? formatDate(app.interview_date) : 'Not scheduled';
            
            // Update profile info
            document.getElementById('fullName').textContent = `${app.first_name} ${app.middle_name || ''} ${app.last_name}`.trim();
            document.getElementById('dateOfBirth').textContent = app.date_of_birth ? formatDate(app.date_of_birth) : 'Not provided';
            document.getElementById('gender').textContent = app.gender || 'Not specified';
            document.getElementById('address').textContent = app.address || 'Not provided';
            document.getElementById('skills').textContent = app.skills || 'Not specified';
            document.getElementById('experience').textContent = app.experience_years ? `${app.experience_years} years` : 'Not specified';
          } else {
            showAlert('Failed to load profile information', 'danger');
          }
        } catch (error) {
          console.error('Error loading profile:', error);
          showAlert('Error loading profile information', 'danger');
        }
      }
      
      // Load profile on page load
      loadProfile();
    <?php endif; ?>
    
    function formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }
    
    function showAlert(message, type) {
      const alertContainer = document.getElementById('alertContainer');
      const alertClass = type === 'success' ? 'alert-success' : (type === 'danger' ? 'alert-danger' : 'alert-info');
      
      alertContainer.innerHTML = `
        <div class="alert ${alertClass}">
          <i class='bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'}'></i> 
          ${message}
        </div>
      `;
      
      if (type !== 'success') {
        setTimeout(() => {
          alertContainer.innerHTML = '';
        }, 5000);
      }
    }
  </script>
</body>

</html>
