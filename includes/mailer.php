<?php

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to_email, $to_name, $subject, $html_body)
{
    // Set to false to force real email sending via SMTP, even on localhost.
    // Set to true to log emails locally without sending (for dev environment).
    $is_dev_mode = false;

    if ($is_dev_mode) {
        if (!file_exists(EMAIL_LOG_DIR)) {
            mkdir(EMAIL_LOG_DIR, 0777, true);
        }

        $filename = EMAIL_LOG_DIR . '/' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
        $log_content = "To: $to_name <$to_email>\nSubject: $subject\n\n" . $html_body;
        file_put_contents($filename, $log_content);

        $log_entry = "[" . date('Y-m-d H:i:s') . "] Email to: $to_email\nSubject: $subject\nMessage: " .
            strip_tags($html_body) . "\n\n";
        file_put_contents(EMAIL_LOG_FILE, $log_entry, FILE_APPEND);

        return true;
    }

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;

        // Debug output level
        $mail->SMTPDebug = SMTP_DEBUG;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_body;
        $mail->AltBody = strip_tags($html_body);

        $mail->send();

        // Log successful email
        file_put_contents(
            EMAIL_LOG_FILE,
            "[" . date('Y-m-d H:i:s') . "] Email SENT to: $to_email\nSubject: $subject\n\n",
            FILE_APPEND
        );

        return true;
    } catch (Exception $e) {
        // Log error
        $error_msg = "[" . date('Y-m-d H:i:s') . "] Email FAILED to: $to_email\nError: " . $e->getMessage() . "\n\n";
        file_put_contents(EMAIL_ERROR_LOG, $error_msg, FILE_APPEND);

        return false;
    }
}