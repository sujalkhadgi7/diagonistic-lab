<?php
// Include PHPMailer files
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// Use PHPMailer namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email
function sendAppointmentEmail($patientEmail, $subject, $message) {
    $mail = new PHPMailer(true); // Create instance of PHPMailer

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'sujalkhadgi29@gmail.com'; // Your Gmail address
        $mail->Password = 'jnid yvdx yvem gtfd'; // Your Gmail password or app password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Sender & Recipient
        $mail->setFrom('sujalkhadgi29@gmail.com'); // Sender's email
        $mail->addAddress($patientEmail); // Recipient's email

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Send email
        $mail->send();
        echo "<script>alert('Sent successfully');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Failed to send email: {$mail->ErrorInfo}');</script>";
    }
}
?>
