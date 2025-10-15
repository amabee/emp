<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Applicant Sign Up - Employee Management System</title>
  <meta name="description" content="Register as a job applicant" />

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
      background: #fff;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .signup-container {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      max-width: 1200px;
      width: 100%;
      min-height: 100vh;
    }

    /* Left Side - Form */
    .form-side {
      padding: 3rem 4rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #fff;
    }

    .form-header {
      margin-bottom: 2rem;
    }

    .form-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2C3E50;
      margin-bottom: 0.5rem;
    }

    .form-header p {
      color: #6C757D;
      font-size: 0.95rem;
      line-height: 1.6;
    }

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

    .form-group input::placeholder {
      color: #B0B0B0;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #4A90E2;
    }

    .checkbox-group label {
      font-size: 0.9rem;
      color: #6C757D;
      cursor: pointer;
      margin: 0;
    }

    .btn-submit {
      width: 100%;
      padding: 1rem;
      background: #4A90E2;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-submit:hover {
      background: #357ABD;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
    }

    .btn-submit:disabled {
      background: #B0B0B0;
      cursor: not-allowed;
      transform: none;
    }

    .signin-link {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.9rem;
      color: #6C757D;
    }

    .signin-link a {
      color: #4A90E2;
      text-decoration: none;
      font-weight: 600;
    }

    .signin-link a:hover {
      text-decoration: underline;
    }

    .back-home {
      margin-top: 1rem;
      text-align: center;
    }

    .back-home a {
      color: #6C757D;
      text-decoration: none;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: color 0.3s ease;
    }

    .back-home a:hover {
      color: #4A90E2;
    }

    /* Right Side - Illustration */
    .illustration-side {
      background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem;
      position: relative;
      overflow: hidden;
    }

    .illustration-side::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    }

    .illustration-content {
      position: relative;
      z-index: 2;
      text-align: center;
    }

    .illustration-content img {
      max-width: 100%;
      height: auto;
      filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.15));
    }

    /* 3D Isometric Illustration using CSS */
    .isometric-illustration {
      width: 450px;
      height: 450px;
      position: relative;
      transform: rotateX(60deg) rotateZ(-45deg);
      transform-style: preserve-3d;
    }

    .iso-building {
      position: absolute;
      background: #4A90E2;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .iso-building-1 {
      width: 120px;
      height: 180px;
      bottom: 150px;
      left: 100px;
      transform: translateZ(0px);
    }

    .iso-building-2 {
      width: 100px;
      height: 150px;
      bottom: 120px;
      left: 250px;
      background: #64B5F6;
      transform: translateZ(20px);
    }

    .iso-building-3 {
      width: 80px;
      height: 120px;
      bottom: 100px;
      left: 50px;
      background: #90CAF9;
      transform: translateZ(-10px);
    }

    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }

    .alert-info {
      background: #E3F2FD;
      color: #1976D2;
      border: 1px solid #90CAF9;
    }

    .alert-danger {
      background: #FFEBEE;
      color: #C62828;
      border: 1px solid #EF9A9A;
    }

    .alert-success {
      background: #E8F5E9;
      color: #2E7D32;
      border: 1px solid #A5D6A7;
    }

    /* Responsive */
    @media (max-width: 968px) {
      .signup-container {
        grid-template-columns: 1fr;
      }

      .illustration-side {
        display: none;
      }

      .form-side {
        padding: 2rem;
      }

      .form-header h1 {
        font-size: 2rem;
      }
    }
  </style>
</head>

<body>
  <div class="signup-container">
    <!-- Left Side - Form -->
    <div class="form-side">
      <div class="form-header">
        <h1>SIGN UP</h1>
        <p>Welcome to the Smart Site System for Oil Depot.<br>Register as a member to experience.</p>
      </div>

      <div class="alert alert-info">
        <i class='bx bx-info-circle'></i> 
        <strong>Coming Soon!</strong> The applicant registration portal is currently under development.
        Please check back later or contact HR for assistance.
      </div>

      <form id="signupForm" onsubmit="return false;">
        <div class="form-group">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" placeholder="yourname4051@gmail.com" disabled>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="••••••••••••" disabled>
        </div>

        <div class="checkbox-group">
          <input type="checkbox" id="terms" name="terms" disabled>
          <label for="terms">I agree to the terms of service</label>
        </div>

        <button type="submit" class="btn-submit" disabled>Create Account</button>
      </form>

      <div class="signin-link">
        Already a member? <a href="./login.php">Sign in</a>
      </div>

      <div class="back-home">
        <a href="./landing.php">
          <i class='bx bx-left-arrow-alt'></i> Back to Home
        </a>
      </div>
    </div>

    <!-- Right Side - Illustration -->
    <div class="illustration-side">
      <div class="illustration-content">
        <!-- Simple 3D-style illustration using SVG -->
        <svg width="500" height="500" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
          <!-- Base/Ground -->
          <ellipse cx="250" cy="420" rx="180" ry="40" fill="#B3E5FC" opacity="0.3"/>
          
          <!-- Building 1 (Back Left) -->
          <g transform="translate(100, 150)">
            <!-- Front face -->
            <polygon points="0,100 80,60 80,200 0,240" fill="#1E88E5"/>
            <!-- Top face -->
            <polygon points="0,100 80,60 160,100 80,140" fill="#42A5F5"/>
            <!-- Right face -->
            <polygon points="80,60 160,100 160,240 80,200" fill="#1565C0"/>
            
            <!-- Windows -->
            <rect x="15" y="130" width="15" height="20" fill="#90CAF9" opacity="0.7"/>
            <rect x="15" y="170" width="15" height="20" fill="#90CAF9" opacity="0.7"/>
            <rect x="45" y="130" width="15" height="20" fill="#90CAF9" opacity="0.7"/>
            <rect x="45" y="170" width="15" height="20" fill="#90CAF9" opacity="0.7"/>
          </g>
          
          <!-- Building 2 (Front Center) -->
          <g transform="translate(200, 200)">
            <!-- Front face -->
            <polygon points="0,80 70,50 70,180 0,210" fill="#1976D2"/>
            <!-- Top face -->
            <polygon points="0,80 70,50 140,80 70,110" fill="#2196F3"/>
            <!-- Right face -->
            <polygon points="70,50 140,80 140,210 70,180" fill="#0D47A1"/>
            
            <!-- Windows -->
            <rect x="10" y="110" width="12" height="15" fill="#BBDEFB" opacity="0.8"/>
            <rect x="10" y="140" width="12" height="15" fill="#BBDEFB" opacity="0.8"/>
            <rect x="40" y="110" width="12" height="15" fill="#BBDEFB" opacity="0.8"/>
            <rect x="40" y="140" width="12" height="15" fill="#BBDEFB" opacity="0.8"/>
          </g>
          
          <!-- Building 3 (Right) -->
          <g transform="translate(320, 180)">
            <!-- Front face -->
            <polygon points="0,90 60,65 60,220 0,245" fill="#1E88E5"/>
            <!-- Top face -->
            <polygon points="0,90 60,65 120,90 60,115" fill="#42A5F5"/>
            <!-- Right face -->
            <polygon points="60,65 120,90 120,245 60,220" fill="#1565C0"/>
            
            <!-- Windows -->
            <rect x="12" y="125" width="10" height="15" fill="#90CAF9" opacity="0.7"/>
            <rect x="12" y="155" width="10" height="15" fill="#90CAF9" opacity="0.7"/>
            <rect x="35" y="125" width="10" height="15" fill="#90CAF9" opacity="0.7"/>
            <rect x="35" y="155" width="10" height="15" fill="#90CAF9" opacity="0.7"/>
          </g>
          
          <!-- Screen/Monitor (Front Left) -->
          <g transform="translate(50, 280)">
            <rect x="0" y="0" width="80" height="60" rx="5" fill="#263238"/>
            <rect x="5" y="5" width="70" height="50" fill="#4FC3F7"/>
            <line x1="10" y1="15" x2="50" y2="15" stroke="#fff" stroke-width="2" opacity="0.7"/>
            <line x1="10" y1="25" x2="60" y2="25" stroke="#fff" stroke-width="2" opacity="0.7"/>
            <line x1="10" y1="35" x2="45" y2="35" stroke="#fff" stroke-width="2" opacity="0.7"/>
            <rect x="30" y="60" width="20" height="8" fill="#455A64"/>
            <rect x="20" y="68" width="40" height="3" fill="#455A64"/>
          </g>
          
          <!-- Decorative Elements -->
          <circle cx="380" cy="120" r="25" fill="#FFD54F" opacity="0.4"/>
          <circle cx="100" cy="100" r="20" fill="#81C784" opacity="0.3"/>
          <polygon points="420,350 435,340 450,350 435,360" fill="#FF8A65" opacity="0.3"/>
        </svg>
      </div>
    </div>
  </div>
</body>

</html>
