<?php
// Test email sending functionality
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/EmailService.php';

echo "<h1>Email Test Script</h1>";
echo "<p>Testing email configuration...</p>";

// Test email address (change this to your email)
$testEmail = 'dancethenightaway.kr@gmail.com'; // CHANGE THIS!

echo "<h3>Configuration:</h3>";
echo "<ul>";
echo "<li>SMTP Host: " . SMTP_HOST . "</li>";
echo "<li>SMTP Port: " . SMTP_PORT . "</li>";
echo "<li>SMTP Username: " . SMTP_USERNAME . "</li>";
echo "<li>From Email: " . SMTP_FROM_EMAIL . "</li>";
echo "</ul>";

// Test company info
echo "<h3>Company Information (from database):</h3>";
try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM company_info ORDER BY company_id DESC LIMIT 1");
    $stmt->execute();
    $company = $stmt->fetch();
    
    if ($company) {
        echo "<ul>";
        echo "<li>Company Name: " . htmlspecialchars($company['name']) . "</li>";
        echo "<li>Address: " . htmlspecialchars($company['address']) . "</li>";
        echo "<li>Phone: " . htmlspecialchars($company['contact_number']) . "</li>";
        echo "<li>Website: " . htmlspecialchars($company['website']) . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>⚠️ No company info found in database!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Send test email
echo "<h3>Sending Test Email...</h3>";

$subject = "Test Email from Employee Management System";
$htmlBody = "
<html>
<body>
    <h2>Test Email</h2>
    <p>This is a test email from your Employee Management System.</p>
    <p>If you receive this, your email configuration is working correctly!</p>
    <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
</body>
</html>
";

$result = EmailService::sendEmail($testEmail, $subject, $htmlBody);

if ($result['success']) {
    echo "<p style='color: green; font-weight: bold;'>✅ Email sent successfully!</p>";
    echo "<p>Check your inbox at: " . htmlspecialchars($testEmail) . "</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Email failed to send</p>";
    echo "<p>Error: " . htmlspecialchars($result['message']) . "</p>";
}

echo "<hr>";
echo "<h3>Troubleshooting Tips:</h3>";
echo "<ol>";
echo "<li>Make sure your Gmail App Password is correct (16 characters, no spaces)</li>";
echo "<li>Enable 2-Step Verification in your Google Account</li>";
echo "<li>Generate a new App Password from: <a href='https://myaccount.google.com/apppasswords' target='_blank'>Google App Passwords</a></li>";
echo "<li>Check if 'Less secure app access' is enabled (if using regular password)</li>";
echo "<li>Verify SMTP_FROM_EMAIL matches SMTP_USERNAME or is allowed to send from that account</li>";
echo "</ol>";

echo "<h3>Check PHP Error Log:</h3>";
echo "<p>Location: <code>c:\\laragon\\www\\emp\\error_log</code></p>";
?>
