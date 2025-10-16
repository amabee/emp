<?php
require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
  private static $companyInfo = null;

  /**
   * Get company information from database (cached)
   */
  private static function getCompanyInfo()
  {
    if (self::$companyInfo !== null) {
      return self::$companyInfo;
    }

    try {
      $db = getDBConnection();
      if (!$db) {
        throw new Exception("Database connection failed");
      }

      $stmt = $db->prepare("SELECT * FROM company_info ORDER BY company_id DESC LIMIT 1");
      $stmt->execute();
      $info = $stmt->fetch();

      if ($info) {
        self::$companyInfo = [
          'name' => $info['name'] ?? 'Employee Management System',
          'address' => $info['address'] ?? '',
          'phone' => $info['contact_number'] ?? '',
          'website' => $info['website'] ?? '',
          'email' => $info['email'] ?? SMTP_FROM_EMAIL
        ];
      } else {
        // Fallback if no company info in database
        self::$companyInfo = [
          'name' => 'Employee Management System',
          'address' => 'Not Set',
          'phone' => 'Not Set',
          'website' => 'Not Set',
          'email' => SMTP_FROM_EMAIL
        ];
      }

      return self::$companyInfo;
    } catch (Exception $e) {
      error_log("Failed to load company info: " . $e->getMessage());
      // Return fallback values
      return [
        'name' => 'Employee Management System',
        'address' => 'Not Set',
        'phone' => 'Not Set',
        'website' => 'Not Set',
        'email' => SMTP_FROM_EMAIL
      ];
    }
  }

  public static function sendEmail($to, $subject, $htmlBody, $plainTextBody = '')
  {
    try {
      $companyInfo = self::getCompanyInfo();

      // If plain text not provided, strip HTML tags
      if (empty($plainTextBody)) {
        $plainTextBody = strip_tags($htmlBody);
      }

      // Create PHPMailer instance
      $mail = new PHPMailer(true);

      try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, $companyInfo['name'] . ' HR Department');
        $mail->addAddress($to);
        $mail->addReplyTo(SMTP_FROM_EMAIL, $companyInfo['name'] . ' HR Department');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainTextBody;

        $mail->send();
        error_log("Email sent successfully to: $to");
        return ['success' => true, 'message' => 'Email sent successfully'];

      } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
      }

    } catch (Exception $e) {
      error_log("Email error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Email error: ' . $e->getMessage()];
    }
  }

  /**
   * Send application status update email
   */
  public static function sendStatusUpdateEmail($applicant, $newStatus, $notes = '', $interviewDate = null, $interviewLocation = '')
  {
    $subject = self::getStatusEmailSubject($newStatus);
    $htmlBody = self::getStatusEmailTemplate($applicant, $newStatus, $notes, $interviewDate, $interviewLocation);

    return self::sendEmail($applicant['email'], $subject, $htmlBody);
  }

  /**
   * Send employee account creation email
   */
  public static function sendEmployeeAccountEmail($employee, $username, $temporaryPassword)
  {
    $companyInfo = self::getCompanyInfo();
    $subject = 'Welcome to ' . $companyInfo['name'] . ' - Your Employee Account';
    $htmlBody = self::getEmployeeAccountTemplate($employee, $username, $temporaryPassword);

    return self::sendEmail($employee['email'], $subject, $htmlBody);
  }

  /**
   * Get email subject based on status
   */
  private static function getStatusEmailSubject($status)
  {
    $companyInfo = self::getCompanyInfo();
    $subjects = [
      'reviewing' => 'Your Application is Under Review',
      'interview_scheduled' => 'Interview Scheduled - ' . $companyInfo['name'],
      'interviewed' => 'Thank You for Your Interview',
      'accepted' => 'Congratulations! You\'ve Been Accepted',
      'rejected' => 'Application Status Update',
      'hired' => 'Welcome to ' . $companyInfo['name'] . '!'
    ];

    return $subjects[$status] ?? 'Application Status Update - ' . $companyInfo['name'];
  }

  /**
   * Get email template for status updates
   */
  private static function getStatusEmailTemplate($applicant, $status, $notes, $interviewDate, $interviewLocation)
  {
    $companyInfo = self::getCompanyInfo();
    $fullName = trim($applicant['first_name'] . ' ' . ($applicant['middle_name'] ?? '') . ' ' . $applicant['last_name']);

    $statusMessages = [
      'reviewing' => [
        'title' => 'Your Application is Under Review',
        'message' => 'We have received your application and our HR team is currently reviewing your qualifications. We will contact you soon regarding the next steps.',
        'icon' => '📋'
      ],
      'interview_scheduled' => [
        'title' => 'Interview Scheduled',
        'message' => 'Congratulations! We would like to invite you for an interview.',
        'icon' => '📅'
      ],
      'interviewed' => [
        'title' => 'Thank You for Your Interview',
        'message' => 'Thank you for taking the time to interview with us. We are currently reviewing all candidates and will get back to you soon.',
        'icon' => '🤝'
      ],
      'accepted' => [
        'title' => 'Congratulations!',
        'message' => 'We are pleased to inform you that you have been accepted for the position. We will be in touch soon with next steps.',
        'icon' => '🎉'
      ],
      'rejected' => [
        'title' => 'Application Status Update',
        'message' => 'Thank you for your interest in joining our team. After careful consideration, we have decided to move forward with other candidates at this time. We encourage you to apply for future openings that match your qualifications.',
        'icon' => '💼'
      ],
      'hired' => [
        'title' => 'Welcome to the Team!',
        'message' => 'Congratulations! We are excited to welcome you to our team. Your employee account has been created and you will receive login credentials shortly.',
        'icon' => '🎊'
      ]
    ];

    $statusInfo = $statusMessages[$status] ?? $statusMessages['reviewing'];

    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .icon { font-size: 48px; text-align: center; margin-bottom: 20px; }
        .message-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .info-box { background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .info-box strong { color: #1976d2; display: block; margin-bottom: 5px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e0e0e0; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .notes { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .notes strong { color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . htmlspecialchars($companyInfo['name']) . '</h1>
        </div>
        <div class="content">
            <div class="icon">' . $statusInfo['icon'] . '</div>
            <h2 style="color: #667eea; text-align: center;">' . $statusInfo['title'] . '</h2>
            
            <p>Dear ' . htmlspecialchars($fullName) . ',</p>
            
            <div class="message-box">
                <p>' . $statusInfo['message'] . '</p>
            </div>';

    // Add interview details if status is interview_scheduled
    if ($status === 'interview_scheduled' && $interviewDate) {
      $formattedDate = date('l, F j, Y \a\t g:i A', strtotime($interviewDate));
      $html .= '
            <div class="info-box">
                <strong>📅 Interview Details:</strong>
                <p style="margin: 5px 0;"><strong>Date & Time:</strong> ' . htmlspecialchars($formattedDate) . '</p>';

      if (!empty($interviewLocation)) {
        $html .= '<p style="margin: 5px 0;"><strong>Location:</strong> ' . htmlspecialchars($interviewLocation) . '</p>';
      }

      $html .= '
                <p style="margin: 10px 0 0 0;"><em>Please arrive 10 minutes early. Bring a copy of your resume and a valid ID.</em></p>
            </div>';
    }

    // Add notes if provided
    if (!empty($notes)) {
      $html .= '
            <div class="notes">
                <strong>📝 Additional Information:</strong>
                <p>' . nl2br(htmlspecialchars($notes)) . '</p>
            </div>';
    }

    $html .= '
            <p>If you have any questions, please don\'t hesitate to contact us.</p>
            
            <p style="margin-top: 30px;">Best regards,<br>
            <strong>' . htmlspecialchars($companyInfo['name']) . ' HR Team</strong></p>
        </div>
        <div class="footer">
            <p><strong>' . htmlspecialchars($companyInfo['name']) . '</strong></p>
            <p>' . htmlspecialchars($companyInfo['address']) . '</p>
            <p>Phone: ' . htmlspecialchars($companyInfo['phone']) . ' | Website: ' . htmlspecialchars($companyInfo['website']) . '</p>
            <p style="margin-top: 15px; color: #999;">This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>';

    return $html;
  }

  /**
   * Get email template for employee account creation
   */
  private static function getEmployeeAccountTemplate($employee, $username, $temporaryPassword)
  {
    $companyInfo = self::getCompanyInfo();
    $fullName = trim($employee['fname'] . ' ' . ($employee['mname'] ?? '') . ' ' . $employee['lname']);

    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .icon { font-size: 48px; text-align: center; margin-bottom: 20px; }
        .credentials-box { background: #f8f9fa; border: 2px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .credential-item { background: white; padding: 12px; margin: 10px 0; border-radius: 4px; border: 1px solid #e0e0e0; }
        .credential-item label { font-weight: bold; color: #667eea; display: block; margin-bottom: 5px; font-size: 12px; text-transform: uppercase; }
        .credential-item .value { font-family: monospace; font-size: 16px; color: #333; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .warning strong { color: #856404; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e0e0e0; }
        .steps { background: #e3f2fd; padding: 20px; border-radius: 4px; margin: 20px 0; }
        .steps ol { margin: 10px 0; padding-left: 20px; }
        .steps li { margin: 8px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . htmlspecialchars($companyInfo['name']) . '</h1>
        </div>
        <div class="content">
            <div class="icon">🎉</div>
            <h2 style="color: #667eea; text-align: center;">Welcome to the Team!</h2>
            
            <p>Dear ' . htmlspecialchars($fullName) . ',</p>
            
            <p>Congratulations and welcome to <strong>' . htmlspecialchars($companyInfo['name']) . '</strong>! We are excited to have you join our team.</p>
            
            <p>Your employee account has been created. Below are your login credentials to access the Employee Management System:</p>
            
            <div class="credentials-box">
                <h3 style="margin-top: 0; color: #667eea;">🔐 Your Login Credentials</h3>
                <div class="credential-item">
                    <label>Username</label>
                    <div class="value">' . htmlspecialchars($username) . '</div>
                </div>
                <div class="credential-item">
                    <label>Temporary Password</label>
                    <div class="value">' . htmlspecialchars($temporaryPassword) . '</div>
                </div>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important Security Notice:</strong>
                <p style="margin: 10px 0 0 0;">For security reasons, please change your password immediately after your first login. This temporary password will expire after your initial login.</p>
            </div>
            
            <div class="steps">
                <h4 style="margin-top: 0; color: #1976d2;">📝 Getting Started:</h4>
                <ol>
                    <li>Visit the employee portal at: <strong>' . htmlspecialchars($companyInfo['website']) . '</strong></li>
                    <li>Click on "Employee Login"</li>
                    <li>Enter your username and temporary password</li>
                    <li>You will be prompted to create a new password</li>
                    <li>Complete your profile information</li>
                </ol>
            </div>
            
            <p>If you have any questions or need assistance logging in, please contact the HR department or IT support.</p>
            
            <p style="margin-top: 30px;">We look forward to working with you!</p>
            
            <p>Best regards,<br>
            <strong>' . htmlspecialchars($companyInfo['name']) . ' HR Team</strong></p>
        </div>
        <div class="footer">
            <p><strong>' . htmlspecialchars($companyInfo['name']) . '</strong></p>
            <p>' . htmlspecialchars($companyInfo['address']) . '</p>
            <p>Phone: ' . htmlspecialchars($companyInfo['phone']) . ' | Website: ' . htmlspecialchars($companyInfo['website']) . '</p>
            <p style="margin-top: 15px; color: #999;">This email contains sensitive information. Please keep your credentials secure.</p>
        </div>
    </div>
</body>
</html>';

    return $html;
  }
}
