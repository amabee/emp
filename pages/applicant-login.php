<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Job Application - Employee Management System</title>
  <meta name="description" content="Apply for a position" />

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

    .signup-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: 100vh;
    }

    /* Left Side - Form */
    .form-side {
      padding: 2rem 3rem;
      background: #fff;
      overflow-y: auto;
      max-height: 100vh;
    }

    .form-header {
      margin-bottom: 2rem;
    }

    .form-header h1 {
      font-size: 2rem;
      font-weight: 700;
      color: #2C3E50;
      margin-bottom: 0.5rem;
    }

    .form-header p {
      color: #6C757D;
      font-size: 0.95rem;
      line-height: 1.6;
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #2C3E50;
      margin: 1.5rem 0 1rem 0;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #E3F2FD;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    .form-group label {
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
      color: #2C3E50;
      margin-bottom: 0.4rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #E0E0E0;
      border-radius: 6px;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      font-family: 'Public Sans', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #4A90E2;
      box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
      color: #B0B0B0;
    }

    .form-group small {
      display: block;
      margin-top: 0.25rem;
      font-size: 0.8rem;
      color: #6C757D;
    }

    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .form-grid-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 1rem;
    }

    /* File Upload Styling */
    .file-upload-container {
      margin-bottom: 1.2rem;
    }

    .file-upload-box {
      border: 2px dashed #4A90E2;
      border-radius: 8px;
      padding: 1.5rem;
      text-align: center;
      background: #F8FBFF;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .file-upload-box:hover {
      background: #E3F2FD;
      border-color: #357ABD;
    }

    .file-upload-box i {
      font-size: 2rem;
      color: #4A90E2;
      margin-bottom: 0.5rem;
    }

    .file-upload-box p {
      color: #6C757D;
      font-size: 0.9rem;
      margin: 0.5rem 0;
    }

    .file-upload-box small {
      color: #999;
      font-size: 0.8rem;
    }

    .file-input {
      display: none;
    }

    .file-preview {
      margin-top: 1rem;
      padding: 0.75rem;
      background: #F5F7FA;
      border-radius: 6px;
      display: none;
    }

    .file-preview.active {
      display: block;
    }

    .file-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.5rem;
      background: #fff;
      border: 1px solid #E0E0E0;
      border-radius: 4px;
      margin-bottom: 0.5rem;
    }

    .file-item:last-child {
      margin-bottom: 0;
    }

    .file-info {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .file-info i {
      color: #4A90E2;
      font-size: 1.2rem;
    }

    .file-name {
      font-size: 0.875rem;
      color: #2C3E50;
      font-weight: 500;
    }

    .file-size {
      font-size: 0.75rem;
      color: #999;
    }

    .remove-file {
      background: none;
      border: none;
      color: #E74C3C;
      cursor: pointer;
      font-size: 1.2rem;
      padding: 0.25rem 0.5rem;
    }

    .remove-file:hover {
      color: #C0392B;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 1.5rem 0;
    }

    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #4A90E2;
    }

    .checkbox-group label {
      font-size: 0.875rem;
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
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-bottom: 1rem;
    }

    .btn-submit:hover {
      background: #357ABD;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
    }

    .btn-submit:disabled {
      background: #B0B0B0;
      cursor: not-allowed;
      transform: none;
    }

    .back-home {
      text-align: center;
      margin: 1rem 0;
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

    /* Right Side - Illustration (FIXED) */
    .illustration-side {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow: hidden;
    }

    .illustration-content {
      width: 100%;
      height: 100%;
    }

    .illustration-content img {
      width: 100%;
      height: 100%;
      object-fit: cover;
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
        padding: 2rem 1.5rem;
        max-height: none;
      }

      .form-header h1 {
        font-size: 1.75rem;
      }

      .form-grid-2,
      .form-grid-3 {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      .form-side {
        padding: 1.5rem 1rem;
      }

      .form-header h1 {
        font-size: 1.5rem;
      }

      .section-title {
        font-size: 1rem;
      }

      .btn-submit {
        padding: 0.875rem;
        font-size: 0.95rem;
      }
    }
  </style>
</head>

<body>
  <div class="signup-container">
    <!-- Left Side - Form -->
    <div class="form-side">
      <div class="form-header">
        <h1>Job Application</h1>
        <p id="systemName">Submit your application and we'll contact you via email.</p>
      </div>

      <div id="alertContainer"></div>

      <form id="signupForm" enctype="multipart/form-data">
        <!-- Personal Information -->
        <h3 class="section-title">Personal Information</h3>

        <div class="form-grid-2">
          <div class="form-group">
            <label for="first_name">First Name <span style="color: red;">*</span></label>
            <input type="text" id="first_name" name="first_name" placeholder="Juan" required>
          </div>

          <div class="form-group">
            <label for="middle_name">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name" placeholder="Santos">
          </div>
        </div>

        <div class="form-group">
          <label for="last_name">Last Name <span style="color: red;">*</span></label>
          <input type="text" id="last_name" name="last_name" placeholder="Dela Cruz" required>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth">
          </div>

          <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <!-- Contact Information -->
        <h3 class="section-title">Contact Information</h3>

        <div class="form-group">
          <label for="email">E-mail <span style="color: red;">*</span></label>
          <input type="email" id="email" name="email" placeholder="yourname@gmail.com" required>
          <small>We'll send interview invitations to this email</small>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label for="phone">Phone Number <span style="color: red;">*</span></label>
            <input type="tel" id="phone" name="phone" placeholder="09171234567" required>
          </div>

          <div class="form-group">
            <label for="alternative_phone">Alternative Phone</label>
            <input type="tel" id="alternative_phone" name="alternative_phone" placeholder="09181234567">
          </div>
        </div>

        <div class="form-group">
          <label for="address">Street Address</label>
          <input type="text" id="address" name="address" placeholder="123 Main Street, Barangay">
        </div>

        <div class="form-grid-3">
          <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" placeholder="Manila">
          </div>

          <div class="form-group">
            <label for="state">Province/State</label>
            <input type="text" id="state" name="state" placeholder="Metro Manila">
          </div>

          <div class="form-group">
            <label for="zip_code">Zip Code</label>
            <input type="text" id="zip_code" name="zip_code" placeholder="1000">
          </div>
        </div>

        <!-- Position Applied -->
        <h3 class="section-title">Application Details</h3>

        <div class="form-group">
          <label for="position_applied">Position Applied For <span style="color: red;">*</span></label>
          <select id="position_applied" name="position_applied" required>
            <option value="">Select Position</option>
          </select>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label for="branch_applied">Preferred Branch</label>
            <select id="branch_applied" name="branch_applied">
              <option value="">Select Branch</option>
            </select>
          </div>

          <div class="form-group">
            <label for="department_applied">Department</label>
            <select id="department_applied" name="department_applied">
              <option value="">Select Department</option>
            </select>
          </div>
        </div>

        <!-- Qualifications -->
        <h3 class="section-title">Qualifications</h3>

        <div class="form-group">
          <label for="skills">Skills/Qualifications</label>
          <textarea id="skills" name="skills"
            placeholder="e.g., PHP, JavaScript, Project Management, 5 years in software development..."
            rows="3"></textarea>
        </div>

        <div class="form-grid-3">
          <div class="form-group">
            <label for="experience_years">Years of Experience</label>
            <input type="number" id="experience_years" name="experience_years" placeholder="5.0" step="0.5" min="0">
          </div>

          <div class="form-group">
            <label for="expected_salary">Expected Salary</label>
            <input type="number" id="expected_salary" name="expected_salary" placeholder="25000" step="1000" min="0">
          </div>

          <div class="form-group">
            <label for="available_start_date">Available Start Date</label>
            <input type="date" id="available_start_date" name="available_start_date">
          </div>
        </div>

        <!-- Document Uploads -->
        <h3 class="section-title">Documents</h3>

        <div class="file-upload-container">
          <label>Resume/CV <span style="color: red;">*</span></label>
          <div class="file-upload-box" onclick="document.getElementById('resume').click()">
            <i class='bx bx-cloud-upload'></i>
            <p>Click to upload your Resume/CV</p>
            <small>PDF, DOC, DOCX (Max 5MB)</small>
          </div>
          <input type="file" id="resume" name="resume" class="file-input" accept=".pdf,.doc,.docx">
          <div id="resumePreview" class="file-preview"></div>
        </div>

        <div class="file-upload-container">
          <label>Cover Letter (Optional)</label>
          <div class="file-upload-box" onclick="document.getElementById('coverLetter').click()">
            <i class='bx bx-file'></i>
            <p>Click to upload your Cover Letter</p>
            <small>PDF, DOC, DOCX (Max 5MB)</small>
          </div>
          <input type="file" id="coverLetter" name="cover_letter_file" class="file-input" accept=".pdf,.doc,.docx">
          <div id="coverLetterPreview" class="file-preview"></div>
        </div>

        <div class="file-upload-container">
          <label>Additional Documents (Optional)</label>
          <div class="file-upload-box" onclick="document.getElementById('additionalDocs').click()">
            <i class='bx bx-folder-plus'></i>
            <p>Click to upload certificates, portfolio, etc.</p>
            <small>PDF, DOC, DOCX, JPG, PNG (Max 5MB each, up to 5 files)</small>
          </div>
          <input type="file" id="additionalDocs" name="additional_documents[]" class="file-input"
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
          <div id="additionalDocsPreview" class="file-preview"></div>
        </div>

        <!-- Reference Information -->
        <h3 class="section-title">Reference (Optional)</h3>

        <div class="form-group">
          <label for="reference_name">Reference Name</label>
          <input type="text" id="reference_name" name="reference_name" placeholder="John Doe">
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label for="reference_contact">Reference Contact</label>
            <input type="text" id="reference_contact" name="reference_contact"
              placeholder="09171234567 or email@example.com">
          </div>

          <div class="form-group">
            <label for="reference_relationship">Relationship</label>
            <input type="text" id="reference_relationship" name="reference_relationship"
              placeholder="Former Supervisor, Colleague, etc.">
          </div>
        </div>

        <!-- Cover Letter Text (Optional) -->
        <div class="form-group">
          <label for="cover_letter">Cover Letter Message (Optional)</label>
          <textarea id="cover_letter" name="cover_letter"
            placeholder="Tell us why you're interested in this position and what makes you a great fit..."
            rows="4"></textarea>
        </div>

        <div class="checkbox-group">
          <input type="checkbox" id="terms" name="terms" required>
          <label for="terms">I agree that the information provided is accurate</label>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <i class='bx bx-send'></i> Submit Application
        </button>
      </form>

      <div class="back-home">
        <a href="./landing.php">
          <i class='bx bx-left-arrow-alt'></i> Back to Home
        </a>
      </div>
    </div>

    <!-- Right Side - Illustration (FIXED POSITION) -->
    <div class="illustration-side">
      <div class="illustration-content">
        <img src="../assets/img/backgrounds/pets.jpg" alt="Job Application Illustration">
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>

  <script>
    // Define removeFile globally so it's accessible from onclick
    function removeFile(inputId, index) {
      const input = document.getElementById(inputId);
      const preview = document.getElementById(inputId + 'Preview');

      // For single file inputs
      if (!input.multiple) {
        input.value = '';
        preview.classList.remove('active');
        preview.innerHTML = '';
        return;
      }

      // For multiple file inputs
      const dt = new DataTransfer();
      const files = input.files;

      for (let i = 0; i < files.length; i++) {
        if (i !== index) {
          dt.items.add(files[i]);
        }
      }

      input.files = dt.files;

      // Trigger change event to update preview
      const event = new Event('change');
      input.dispatchEvent(event);
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

      // Scroll to top to show alert
      document.querySelector('.form-side').scrollTop = 0;

      // Auto-hide after 5 seconds for non-success messages
      if (type !== 'success') {
        setTimeout(() => {
          alertContainer.innerHTML = '';
        }, 5000);
      }
    }

    $(document).ready(function () {
      // Load company info using AJAX
      $.ajax({
        url: '../ajax/get_public_company_info.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          if (data.success && data.company) {
            $('#systemName').html(
              `Submit your application to ${data.company.company_name} and we'll contact you via email.`
            );
          }
        },
        error: function (xhr, status, error) {
          console.error('Error loading company info:', error);
        }
      });

      // Load positions dropdown (using public endpoint)
      $.ajax({
        url: '../ajax/get_public_positions.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          if (data.success && data.positions) {
            const positionSelect = $('#position_applied');
            $.each(data.positions, function (index, position) {
              positionSelect.append(
                $('<option></option>')
                  .val(position.position_id)
                  .text(position.position_name)
              );
            });
          }
        },
        error: function (xhr, status, error) {
          console.error('Error loading positions:', error);
        }
      });

      // Load branches dropdown (using public endpoint)
      $.ajax({
        url: '../ajax/get_public_branches.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          if (data.success && data.branches) {
            const branchSelect = $('#branch_applied');
            $.each(data.branches, function (index, branch) {
              branchSelect.append(
                $('<option></option>')
                  .val(branch.branch_id)
                  .text(branch.branch_name)
              );
            });
          }
        },
        error: function (xhr, status, error) {
          console.error('Error loading branches:', error);
        }
      });

      // Load departments dropdown (using public endpoint)
      $.ajax({
        url: '../ajax/get_public_departments.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          if (data.success && data.departments) {
            const deptSelect = $('#department_applied');
            $.each(data.departments, function (index, dept) {
              deptSelect.append(
                $('<option></option>')
                  .val(dept.department_id)
                  .text(dept.department_name)
              );
            });
          }
        },
        error: function (xhr, status, error) {
          console.error('Error loading departments:', error);
        }
      });

      // File upload handlers
      function handleFileSelect(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        input.addEventListener('change', function () {
          const files = this.files;
          if (files.length === 0) {
            preview.classList.remove('active');
            preview.innerHTML = '';
            return;
          }

          let html = '';
          for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const sizeKB = (file.size / 1024).toFixed(2);
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            const sizeText = file.size > 1024 * 1024 ? sizeMB + ' MB' : sizeKB + ' KB';

            html += `
            <div class="file-item" data-index="${i}">
              <div class="file-info">
                <i class='bx bx-file'></i>
                <div>
                  <div class="file-name">${file.name}</div>
                  <div class="file-size">${sizeText}</div>
                </div>
              </div>
              <button type="button" class="remove-file" onclick="removeFile('${inputId}', ${i})">
                <i class='bx bx-x'></i>
              </button>
            </div>
          `;
          }

          preview.innerHTML = html;
          preview.classList.add('active');
        });
      }

      // Initialize file upload handlers
      handleFileSelect('resume', 'resumePreview');
      handleFileSelect('coverLetter', 'coverLetterPreview');
      handleFileSelect('additionalDocs', 'additionalDocsPreview');

      // Handle registration form submission
      $('#signupForm').on('submit', function (e) {
        e.preventDefault();

        // Validate file size
        const maxSize = 5 * 1024 * 1024; // 5MB
        const fileInputs = ['resume', 'coverLetter', 'additionalDocs'];

        for (const inputId of fileInputs) {
          const input = document.getElementById(inputId);
          if (input.files.length > 0) {
            for (const file of input.files) {
              if (file.size > maxSize) {
                showAlert(`File "${file.name}" exceeds 5MB limit`, 'danger');
                return;
              }
            }
          }
        }

        // Check if resume is uploaded
        const resumeInput = document.getElementById('resume');
        if (resumeInput.files.length === 0) {
          showAlert('Please upload your Resume/CV', 'danger');
          return;
        }

        // Disable submit button
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="bx bx-loader bx-spin"></i> Submitting Application...');

        const formData = new FormData(this);

        $.ajax({
          url: '../ajax/applicant_register.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function (result) {
            if (result.success) {
              showAlert('Application submitted successfully! We will contact you via email.', 'success');
              // Clear form
              $('#signupForm')[0].reset();
              // Clear file previews
              $('#resumePreview').removeClass('active');
              $('#coverLetterPreview').removeClass('active');
              $('#additionalDocsPreview').removeClass('active');

              setTimeout(() => {
                window.location.href = './landing.php';
              }, 3000);
            } else {
              showAlert(result.message || 'Submission failed. Please try again.', 'danger');
              submitBtn.prop('disabled', false);
              submitBtn.html('<i class="bx bx-send"></i> Submit Application');
            }
          },
          error: function (xhr, status, error) {
            console.error('Submission error:', error);
            showAlert('An error occurred. Please try again later.', 'danger');
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="bx bx-send"></i> Submit Application');
          }
        });
      });

    }); // End document.ready
  </script>
</body>

</html>
