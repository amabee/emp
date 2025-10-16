# Email Not Sending - Troubleshooting Guide

## Issue
Emails are not being sent when changing applicant status.

## ✅ Solution Implemented

I've just updated your system to use **PHPMailer** instead of PHP's built-in `mail()` function. PHPMailer provides proper SMTP authentication with Gmail.

### What Changed:
1. ✅ Installed PHPMailer via Composer
2. ✅ Updated `EmailService.php` to use PHPMailer with SMTP authentication
3. ✅ Added detailed error logging
4. ✅ Created test script to verify email configuration

---

## 🧪 Step 1: Test Email Configuration

1. **Open your browser and go to:**
   ```
   http://localhost/emp/test_email.php
   ```

2. **Before running the test:**
   - Open `test_email.php`
   - Change this line to your real email:
     ```php
     $testEmail = 'your-test-email@example.com'; // CHANGE THIS!
     ```
   - Save the file

3. **Run the test**
   - Refresh the page
   - Check if you receive the test email
   - Look for any error messages on the page

---

## 🔧 Step 2: Verify Gmail App Password

Your current configuration uses:
- **Username:** `sinoydarlingmae155@gmail.com`
- **App Password:** `qgiv mydf hqhr blil`

### ⚠️ Important: App Password Format
The password in `email_config.php` should have **NO SPACES**:

**Open:** `c:\laragon\www\emp\shared\email_config.php`

**Change from:**
```php
define('SMTP_PASSWORD', 'qgiv mydf hqhr blil'); // WRONG - has spaces!
```

**Change to:**
```php
define('SMTP_PASSWORD', 'qgivmydfhqhrblil'); // CORRECT - no spaces!
```

### How to Generate Gmail App Password:

1. Go to: https://myaccount.google.com/
2. Click **Security** (left sidebar)
3. Enable **2-Step Verification** (if not already enabled)
4. Scroll down to **App Passwords**
5. Click **App Passwords**
6. Select:
   - App: **Mail**
   - Device: **Windows Computer** (or Other)
7. Click **Generate**
8. Copy the 16-character password (it will look like: `abcd efgh ijkl mnop`)
9. Remove spaces and paste in `email_config.php`:
   ```php
   define('SMTP_PASSWORD', 'abcdefghijklmnop');
   ```

---

## 📝 Step 3: Check Error Logs

### Option A: Check Browser Console
1. Open Job Applications page
2. Press **F12** to open Developer Tools
3. Go to **Console** tab
4. Change an applicant status
5. Look for any error messages

### Option B: Check PHP Error Log
Location: `c:\laragon\www\emp\error_log`

Look for messages like:
- ✅ `Email sent successfully to: applicant@email.com`
- ❌ `Failed to send status update email: [error message]`
- ⚠️ `PHPMailer Error: [detailed error]`

### Option C: Check Laragon Logs
- Open Laragon
- Click **Menu** → **www** → **error.log**

---

## 🎯 Step 4: Test Status Change Email

1. Go to **Job Applications** page
2. Click on any applicant
3. Click **"Update Status"**
4. Change status to **"Reviewing"**
5. Click Submit
6. Check:
   - ✅ Browser console for messages
   - ✅ PHP error log for email status
   - ✅ Applicant's email inbox

---

## 🔍 Common Issues & Solutions

### Issue 1: "SMTP Error: Could not authenticate"
**Solution:**
- Verify App Password has no spaces
- Make sure 2-Step Verification is enabled in Google Account
- Generate a new App Password

### Issue 2: "SMTP connect() failed"
**Solution:**
- Check if port 587 is open
- Try changing to port 465 with SSL:
  ```php
  define('SMTP_PORT', 465);
  define('SMTP_ENCRYPTION', 'ssl');
  ```

### Issue 3: "Could not instantiate mail function"
**Solution:**
- Make sure PHPMailer is installed (already done)
- Check if `vendor/autoload.php` exists
- Run: `composer install` in the emp directory

### Issue 4: Email goes to spam
**Solution:**
- Use a verified sender email
- Match SMTP_FROM_EMAIL with SMTP_USERNAME:
  ```php
  define('SMTP_FROM_EMAIL', 'sinoydarlingmae155@gmail.com');
  ```

### Issue 5: "From address mismatch"
**Solution:**
Gmail may reject if `FROM` email doesn't match authenticated account.

**Update email_config.php:**
```php
define('SMTP_FROM_EMAIL', 'sinoydarlingmae155@gmail.com');
```

---

## 📊 Verify Configuration

**Current Settings in `email_config.php`:**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); 
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USERNAME', 'sinoydarlingmae155@gmail.com');
define('SMTP_PASSWORD', 'qgivmydfhqhrblil'); // REMOVE SPACES!
define('SMTP_FROM_EMAIL', 'sinoydarlingmae155@gmail.com'); // MATCH USERNAME
```

---

## 🎬 Quick Fix Checklist

- [ ] Remove spaces from App Password in `email_config.php`
- [ ] Make `SMTP_FROM_EMAIL` match `SMTP_USERNAME`
- [ ] Verify 2-Step Verification is enabled
- [ ] Generate new App Password if needed
- [ ] Test with `test_email.php`
- [ ] Check error logs after testing
- [ ] Try changing applicant status
- [ ] Check applicant's email inbox

---

## 📧 What Happens When Status Changes

The system now sends emails for these statuses:
1. ✉️ **reviewing** → "Application Under Review"
2. ✉️ **interview_scheduled** → "Interview Scheduled" (with date/location)
3. ✉️ **interviewed** → "Thank You for Interview"
4. ✉️ **accepted** → "Congratulations!"
5. ✉️ **rejected** → "Application Status Update"
6. ✉️ **hired** → "Welcome!" (sent when converted to employee)

---

## 🆘 Still Not Working?

1. **Check applicant has valid email:**
   - Go to Job Applications
   - View applicant details
   - Verify email address is correct

2. **Enable PHPMailer debugging:**
   Open `shared/EmailService.php`, find line ~78, add:
   ```php
   $mail->SMTPDebug = 2; // Detailed debug output
   $mail->Debugoutput = 'error_log'; // Send debug to error log
   ```

3. **Test with a different email service:**
   Try using a different SMTP provider (like Mailtrap for testing)

4. **Contact me with error logs:**
   Copy the error messages from:
   - Browser console (F12)
   - PHP error log
   - Laragon error log

---

**Next Steps:**
1. Fix the App Password (remove spaces)
2. Run `test_email.php`
3. Try changing a status
4. Check logs and inbox

Good luck! 🚀
