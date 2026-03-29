<?php
// Include PHPMailer files
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// Use PHPMailer namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email
function sendAppointmentEmail($patientEmail, $subject, $message, &$error = null) {
    $mail = new PHPMailer(true); // Create instance of PHPMailer

    $smtpUsername = getenv('LAB_SMTP_USERNAME') ?: 'sujalkhadgi29@gmail.com';
    $smtpSecret = getenv('LAB_SMTP_PASSWORD') ?: '';

    if ($smtpSecret === '') {
        $error = 'SMTP password is not configured. Set LAB_SMTP_PASSWORD in environment.';
        return false;
    }

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpSecret;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Sender & Recipient
        $mail->setFrom($smtpUsername); // Sender's email
        $mail->addAddress($patientEmail); // Recipient's email

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        $error = $mail->ErrorInfo ?: $e->getMessage();
        return false;
    }
}
