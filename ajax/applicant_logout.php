<?php
session_start();

// Clear all applicant session variables
unset($_SESSION['applicant_logged_in']);
unset($_SESSION['applicant_id']);
unset($_SESSION['applicant_name']);
unset($_SESSION['applicant_email']);
unset($_SESSION['applicant_status']);

// Redirect to landing page
header('Location: ../pages/landing.php');
exit;
