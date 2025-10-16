<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome - Employee Management System</title>
  <meta name="description" content="Professional Employee Management System - Streamline your HR operations" />

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

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../assets/vendor/css/core.css" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Public Sans', sans-serif;
      background: #FAFAFA;
      overflow-x: hidden;
    }

    /* Navigation */
    .navbar {
      background: #fff;
      padding: 1.5rem 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .navbar .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
    }

    .logo i {
      font-size: 2rem;
      color: #FF6B6B;
    }

    .logo span {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2C3E50;
    }

    .nav-menu {
      display: flex;
      align-items: center;
      gap: 2.5rem;
      list-style: none;
    }

    .nav-menu a {
      color: #6C757D;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .nav-menu a:hover,
    .nav-menu a.active {
      color: #FF6B6B;
    }

    .btn-nav {
      background: #2C3E50;
      color: #fff;
      padding: 0.75rem 2rem;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-nav:hover {
      background: #1a252f;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      color: #fff;
    }

    /* Hero Section */
    .hero {
      min-height: calc(100vh - 80px);
      display: flex;
      align-items: center;
      padding: 4rem 2rem;
      position: relative;
      background: #fff;
      overflow: hidden;
    }

    .hero-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      align-items: center;
    }

    .hero-content h1 {
      font-size: 4rem;
      font-weight: 800;
      color: #2C3E50;
      line-height: 1.2;
      margin-bottom: 1.5rem;
    }

    .hero-content p {
      font-size: 1.1rem;
      color: #6C757D;
      line-height: 1.8;
      margin-bottom: 2rem;
      max-width: 500px;
    }

    .hero-image {
      position: relative;
      text-align: center;
    }

    .hero-image svg {
      max-width: 100%;
      height: auto;
      filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.1));
    }

    /* Company Info */
    .company-info {
      background: #F8F9FA;
      border-radius: 15px;
      padding: 2rem;
      margin-top: 2rem;
    }

    .info-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem;
      margin-bottom: 0.5rem;
      transition: background 0.3s ease;
      border-radius: 10px;
    }

    .info-item:hover {
      background: #fff;
    }

    .info-item i {
      font-size: 1.5rem;
      color: #FF6B6B;
      width: 30px;
    }

    .info-item span,
    .info-item a {
      color: #2C3E50;
      font-size: 0.95rem;
      text-decoration: none;
    }

    .info-item a:hover {
      color: #FF6B6B;
    }

    /* Login Cards Section */
    .login-section {
      padding: 5rem 2rem;
      background: #F8F9FA;
    }

    .login-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .section-header {
      text-align: center;
      margin-bottom: 4rem;
    }

    .section-header h2 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2C3E50;
      margin-bottom: 1rem;
    }

    .section-header p {
      font-size: 1.1rem;
      color: #6C757D;
    }

    .cards-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 3rem;
    }

    .portal-card {
      background: #fff;
      border-radius: 20px;
      padding: 3rem;
      text-align: center;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      border: 2px solid transparent;
      text-decoration: none;
      display: block;
    }

    .portal-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      border-color: #FF6B6B;
    }

    .card-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #FF6B6B 0%, #FF8E8E 100%);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 2.5rem;
      color: #fff;
    }

    .portal-card:nth-child(2) .card-icon {
      background: linear-gradient(135deg, #4ECDC4 0%, #6FE7DD 100%);
    }

    .card-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: #2C3E50;
      margin-bottom: 1rem;
    }

    .card-desc {
      color: #6C757D;
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 2rem;
    }

    .card-btn {
      background: #2C3E50;
      color: #fff;
      padding: 1rem 2.5rem;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
      transition: all 0.3s ease;
    }

    .card-btn:hover {
      background: #1a252f;
      transform: scale(1.05);
      color: #fff;
    }

    /* Features Section */
    .features {
      padding: 5rem 2rem;
      background: #fff;
    }

    .features-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 3rem;
      margin-top: 3rem;
    }

    .feature-item {
      text-align: center;
    }

    .feature-icon {
      width: 70px;
      height: 70px;
      background: #F8F9FA;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 2rem;
      color: #FF6B6B;
    }

    .feature-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #2C3E50;
      margin-bottom: 0.75rem;
    }

    .feature-desc {
      color: #6C757D;
      line-height: 1.6;
    }

    /* Decorations */
    .leaf {
      position: absolute;
      opacity: 0.1;
      pointer-events: none;
    }

    .leaf-1 {
      top: 20%;
      right: 10%;
      width: 100px;
      height: 120px;
      background: #4ECDC4;
      border-radius: 0 100% 0 100%;
      transform: rotate(-20deg);
    }

    .leaf-2 {
      bottom: 20%;
      left: 5%;
      width: 80px;
      height: 100px;
      background: #FFE66D;
      border-radius: 100% 0 100% 0;
      transform: rotate(30deg);
    }

    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
      border-radius: 10px;
      display: inline-block;
    }

    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* Responsive */
    @media (max-width: 968px) {
      .hero-container,
      .cards-grid,
      .features-grid {
        grid-template-columns: 1fr;
      }

      .hero-content h1 {
        font-size: 2.5rem;
      }

      .nav-menu {
        display: none;
      }
    }
  </style>
</head>

<body>
  <!-- Navigation -->
  <nav class="navbar">
    <div class="container">
      <a href="#" class="logo">
        <i class='bx bx-buildings'></i>
        <span id="navCompanyName">Employee System</span>
      </a>
      <ul class="nav-menu">
        <li><a href="#" class="active">Home</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
      <a href="#login" class="btn-nav">Get Started</a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="leaf leaf-1"></div>
    <div class="leaf leaf-2"></div>
    
    <div class="hero-container">
      <div class="hero-content">
        <h1 id="heroTitle">
          <span class="skeleton" style="width: 400px; height: 4rem;">Loading...</span>
        </h1>
        <p id="heroDesc">
          <span class="skeleton" style="width: 100%; height: 1.5rem; margin-bottom: 0.5rem;">Loading...</span>
        </p>

        <!-- Company Info -->
        <div class="company-info" id="companyInfoBox" style="display: none;">
          <div class="info-item" id="addressItem" style="display: none;">
            <i class='bx bxs-map'></i>
            <span id="companyAddress"></span>
          </div>
          <div class="info-item" id="phoneItem" style="display: none;">
            <i class='bx bxs-phone'></i>
            <span id="companyPhone"></span>
          </div>
          <div class="info-item" id="emailItem" style="display: none;">
            <i class='bx bxs-envelope'></i>
            <span id="companyEmail"></span>
          </div>
          <div class="info-item" id="websiteItem" style="display: none;">
            <i class='bx bxs-globe'></i>
            <a href="#" id="companyWebsite" target="_blank"></a>
          </div>
        </div>
      </div>

      <div class="hero-image">
        <!-- Simple illustration using SVG -->
        <svg viewBox="0 0 500 400" xmlns="http://www.w3.org/2000/svg">
          <!-- Background shape -->
          <ellipse cx="250" cy="350" rx="200" ry="30" fill="#F0F0F0" opacity="0.5"/>
          
          <!-- Person -->
          <circle cx="200" cy="150" r="40" fill="#FF6B6B"/>
          <path d="M 200 190 Q 180 250 160 300 Q 150 330 170 350" stroke="#2C3E50" stroke-width="12" fill="none" stroke-linecap="round"/>
          <path d="M 200 190 Q 220 250 240 300 Q 250 330 230 350" stroke="#2C3E50" stroke-width="12" fill="none" stroke-linecap="round"/>
          <path d="M 200 190 Q 180 220 150 240" stroke="#FF8E53" stroke-width="10" fill="none" stroke-linecap="round"/>
          <path d="M 200 190 Q 220 220 250 240" stroke="#FF8E53" stroke-width="10" fill="none" stroke-linecap="round"/>
          
          <!-- Document/Board -->
          <rect x="320" y="120" width="120" height="180" rx="10" fill="#4ECDC4" opacity="0.3"/>
          <rect x="330" y="140" width="100" height="15" rx="5" fill="#2C3E50" opacity="0.2"/>
          <rect x="330" y="170" width="80" height="10" rx="5" fill="#2C3E50" opacity="0.2"/>
          <rect x="330" y="195" width="90" height="10" rx="5" fill="#2C3E50" opacity="0.2"/>
          <rect x="330" y="220" width="70" height="10" rx="5" fill="#2C3E50" opacity="0.2"/>
          
          <!-- Decorative elements -->
          <circle cx="80" cy="80" r="25" fill="#FFE66D" opacity="0.3"/>
          <circle cx="420" cy="300" r="30" fill="#95E1D3" opacity="0.3"/>
          <path d="M 350 60 Q 360 50 370 60 Q 375 70 365 75 Q 355 70 350 60" fill="#4ECDC4" opacity="0.4"/>
          <path d="M 100 300 Q 110 290 120 300 Q 125 310 115 315 Q 105 310 100 300" fill="#FFB6C1" opacity="0.4"/>
        </svg>
      </div>
    </div>
  </section>

  <!-- Login Cards Section -->
  <section class="login-section" id="login">
    <div class="login-container">
      <div class="section-header">
        <h2>Choose Your Portal</h2>
        <p>Select the appropriate portal to access your account</p>
      </div>

      <div class="cards-grid">
        <!-- Employee Portal -->
        <a href="../login.php" class="portal-card">
          <div class="card-icon">
            <i class='bx bxs-user-check'></i>
          </div>
          <h3 class="card-title">Employee Portal</h3>
          <p class="card-desc">
            Access your dashboard, view schedules, track time, request leave, and manage your employee profile
          </p>
          <span class="card-btn">Login Now →</span>
        </a>

        <!-- Applicant Portal -->
        <a href="./applicant-login.php" class="portal-card">
          <div class="card-icon">
            <i class='bx bxs-user-plus'></i>
          </div>
          <h3 class="card-title">Job Applicant</h3>
          <p class="card-desc">
            Apply for open positions, track your application status, and connect with our HR recruitment team
          </p>
          <span class="card-btn">Apply Now →</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features" id="services">
    <div class="features-container">
      <div class="section-header">
        <h2>Why Choose Us</h2>
        <p>We provide the best employee management experience</p>
      </div>

      <div class="features-grid">
        <div class="feature-item">
          <div class="feature-icon">
            <i class='bx bxs-shield-alt-2'></i>
          </div>
          <h3 class="feature-title">Secure & Safe</h3>
          <p class="feature-desc">Your information is protected with enterprise-grade security and encryption</p>
        </div>

        <div class="feature-item">
          <div class="feature-icon">
            <i class='bx bxs-time-five'></i>
          </div>
          <h3 class="feature-title">24/7 Access</h3>
          <p class="feature-desc">Access your portal anytime, anywhere from any device with internet connection</p>
        </div>

        <div class="feature-item">
          <div class="feature-icon">
            <i class='bx bxs-heart'></i>
          </div>
          <h3 class="feature-title">We Care</h3>
          <p class="feature-desc">Dedicated support team ready to assist you with any questions or concerns</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Scripts -->
  <script src="../assets/vendor/libs/jquery/jquery.js"></script>
  
  <script>
    $(document).ready(function() {
      loadCompanyInfo();
    });

    function loadCompanyInfo() {
      $.ajax({
        url: '../ajax/get_public_company_info.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success && response.company) {
            const company = response.company;
            
            // Update navigation
            $('#navCompanyName').text(company.company_name || 'Employee System');
            
            // Update hero title with full company name
            $('#heroTitle').html('Welcome to <br>' + (company.company_name || 'Employee Management System'));
            
            // Update hero description
            $('#heroDesc').html(company.company_description || 'Manage your workforce efficiently with our comprehensive employee management system');
            
            // Company info
            let hasInfo = false;
            
            if (company.company_address) {
              $('#companyAddress').text(company.company_address);
              $('#addressItem').show();
              hasInfo = true;
            }
            
            if (company.company_phone) {
              $('#companyPhone').text(company.company_phone);
              $('#phoneItem').show();
              hasInfo = true;
            }
            
            if (company.company_email) {
              $('#companyEmail').text(company.company_email);
              $('#emailItem').show();
              hasInfo = true;
            }
            
            if (company.company_website) {
              $('#companyWebsite').text(company.company_website).attr('href', company.company_website);
              $('#websiteItem').show();
              hasInfo = true;
            }
            
            if (hasInfo) {
              $('#companyInfoBox').show();
            }
          } else {
            $('#navCompanyName').text('Employee System');
            $('#heroTitle').html('Welcome to <br>Employee Management System');
            $('#heroDesc').html('Manage your workforce efficiently with our comprehensive employee management system');
          }
        },
        error: function() {
          $('#navCompanyName').text('Employee System');
          $('#heroTitle').html('Welcome to <br>Employee Management System');
          $('#heroDesc').html('Manage your workforce efficiently with our comprehensive employee management system');
        }
      });
    }
  </script>
</body>

</html>
