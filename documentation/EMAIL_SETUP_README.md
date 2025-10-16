# Email Notification System - Setup Guide

## Overview
The system automatically sends email notifications to applicants when their application status changes, and sends welcome emails with login credentials when hired.

**✨ NEW: Company information (name, address, phone, website) is now dynamically loaded from your database!** You can update these details from your admin panel without changing any code.

## Email Scenarios

### 1. **Application Under Review** (Status: `reviewing`)
- Sent when: HR/Admin changes status to "reviewing"
- Contains: Confirmation that application is being reviewed

### 2. **Interview Scheduled** (Status: `interview_scheduled`)
- Sent when: HR/Admin changes status to "interview_scheduled"
- Contains:
  - Interview date and time
  - Interview location
  - Additional notes (if provided)
  - Instructions to arrive 10 minutes early

### 3. **After Interview** (Status: `interviewed`)
- Sent when: HR/Admin changes status to "interviewed"
- Contains: Thank you message and next steps

### 4. **Accepted** (Status: `accepted`)
- Sent when: HR/Admin changes status to "accepted"
- Contains: Congratulations message

### 5. **Rejected** (Status: `rejected`)
- Sent when: HR/Admin changes status to "rejected"
- Contains: Professional rejection message

### 6. **Hired - Welcome Email** (Status: `hired`)
- Sent when: Accepted applicant is converted to employee
- Contains:
  - Welcome message
  - Login credentials (username & temporary password)
  - Instructions for first login
  - Security notice to change password

---

## Setup Instructions

### Option 1: Gmail (Recommended for Testing)

1. **Open `shared/email_config.php`** and update:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_ENCRYPTION', 'tls');
   define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail address
   define('SMTP_PASSWORD', 'your-app-password'); // See below
   define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
   ```

2. **Generate Gmail App Password:**
   - Go to [Google Account Settings](https://myaccount.google.com/)
   - Security → 2-Step Verification (enable if not already)
   - App Passwords → Select "Mail" and device
   - Copy the 16-character password
   - Use this password in `SMTP_PASSWORD`

3. **Update Company Information in Database:**
   - Go to your admin panel → Company Settings
   - Update:
     - Company Name (e.g., "Wonderpets EMS")
     - Company Address
     - Phone Number
     - Website URL
   - These values are automatically used in all email templates!
   
   **Note:** Company information is no longer hardcoded in `email_config.php`. It's now dynamically fetched from the `company_info` table in your database.

### Option 2: Microsoft 365 / Outlook

```php
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USERNAME', 'your-email@yourcompany.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'hr@yourcompany.com');
```

**Company information** is managed from your admin panel, not in the config file!

### Option 3: Other SMTP Providers

Common SMTP settings:
- **SendGrid:** `smtp.sendgrid.net` (Port 587)
- **Mailgun:** `smtp.mailgun.org` (Port 587)
- **AWS SES:** `email-smtp.us-east-1.amazonaws.com` (Port 587)

---

## Testing the Email System

### Test 1: Status Change Notifications

1. Log in as Admin or HR
2. Go to **Job Applications** page
3. Click on an applicant
4. Click **"Update Status"**
5. Change status to "Reviewing" → Submit
6. Check applicant's email inbox

### Test 2: Interview Scheduled Email

1. Go to **Job Applications**
2. Select an applicant
3. Click **"Update Status"**
4. Change status to **"Interview Scheduled"**
5. Enter:
   - Interview Date & Time
   - Interview Location (e.g., "Main Office - Conference Room A")
   - Notes (optional)
6. Submit
7. Check applicant's email for interview details

### Test 3: Welcome Email with Credentials

1. Go to **Job Applications**
2. Find an **Accepted** applicant
3. Go to **Employee Management**
4. Click **"Add New Employee"**
5. Select the accepted applicant from dropdown
6. Form will auto-fill
7. Submit the form
8. System will:
   - Create employee record
   - Generate username and password
   - Create user account
   - Send welcome email
9. Check email for login credentials

---

## Email Template Customization

Email templates are in `shared/EmailService.php`:

### Customize Email Subjects
Edit the `getStatusEmailSubject()` method:
```php
private static function getStatusEmailSubject($status) {
    $subjects = [
        'reviewing' => 'Your Application is Under Review - Company Name',
        // ... customize other subjects
    ];
    return $subjects[$status] ?? 'Application Status Update';
}
```

### Customize Email Content
Edit the `getStatusEmailTemplate()` method to modify:
- Email header color (line: `background: linear-gradient(...)`)
- Icons for each status
- Message text
- Footer information

### Customize Welcome Email
Edit `getEmployeeAccountTemplate()` method to change:
- Welcome message
- Getting started instructions
- Security warnings
- Company branding

---

## Troubleshooting

### Emails Not Sending

1. **Check PHP mail() configuration:**
   ```bash
   php -i | grep -i mail
   ```

2. **Enable error logging:**
   - Check PHP error log
   - Check `error_log()` messages in browser console (F12)

3. **Verify SMTP credentials:**
   - Test login with email client
   - Ensure 2FA is enabled for Gmail
   - Use App Password, not regular password

4. **Check firewall/port access:**
   - Ensure port 587 (TLS) or 465 (SSL) is open
   - Test with telnet: `telnet smtp.gmail.com 587`

### Gmail Blocking Emails

- Enable "Less secure app access" (if available)
- Use App Passwords instead
- Check Google Account activity for blocked sign-in attempts

### Emails Going to Spam

1. Add SPF record to your domain:
   ```
   v=spf1 include:_spf.google.com ~all
   ```

2. Add DKIM signature (requires domain configuration)

3. Use a verified sender email address

---

## Advanced: PHPMailer Integration (Optional)

For production environments, consider using PHPMailer:

1. **Install via Composer:**
   ```bash
   composer require phpmailer/phpmailer
   ```

2. **Update EmailService.php:**
   ```php
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;

   public static function sendEmail($to, $subject, $htmlBody) {
       $mail = new PHPMailer(true);
       try {
           $mail->isSMTP();
           $mail->Host = SMTP_HOST;
           $mail->SMTPAuth = true;
           $mail->Username = SMTP_USERNAME;
           $mail->Password = SMTP_PASSWORD;
           $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
           $mail->Port = SMTP_PORT;

           $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
           $mail->addAddress($to);
           $mail->isHTML(true);
           $mail->Subject = $subject;
           $mail->Body = $htmlBody;

           $mail->send();
           return ['success' => true, 'message' => 'Email sent'];
       } catch (Exception $e) {
           return ['success' => false, 'message' => $mail->ErrorInfo];
       }
   }
   ```

---

## Security Best Practices

1. **Never commit email credentials to Git:**
   - Add `email_config.php` to `.gitignore`
   - Use environment variables in production

2. **Use App Passwords:**
   - Never use your main email password
   - Generate app-specific passwords

3. **Encrypt sensitive data:**
   - Use HTTPS for all pages
   - Store passwords hashed (already implemented)

4. **Rate limiting:**
   - Monitor email sending to prevent abuse
   - Implement cooldown between status changes

---

## Files Modified

- `shared/email_config.php` - Email configuration
- `shared/EmailService.php` - Email service with templates
- `ajax/update_applicant_status.php` - Status update with email
- `ajax/add_employee.php` - Employee creation with welcome email
- `pages/job-applications.php` - Interview date/location fields
- `pages/employee-management.php` - Applicant ID tracking
- `pages/modals/add-employee-modal.php` - Hidden field for applicant
- `controllers/ApplicantController.php` - Credential generation

---

## Support

For issues or questions:
1. Check error logs: `error_log` in PHP
2. Test SMTP connection separately
3. Verify email configuration values
4. Check applicant email addresses are valid

---

**Last Updated:** <?php echo date('Y-m-d'); ?>
