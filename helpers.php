<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

if (!function_exists('send_email')) {
    // Use google SMTP to send email
    function send_email($to, $subject, $body)
    {
        $mail = new PHPMailer(true);
        //        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');

        $mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
        $mail->addAddress($to);

        $mail->IsHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    }
}

if (!function_exists('encrypt')) {
    // encrypt data to secure
    function encrypt($data, $encryptionKey)
    {
        $iv        = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-gcm'));
        $encrypted = openssl_encrypt($data, 'aes-256-gcm', $encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $encrypted);
    }
}

if (!function_exists('decrypt')) {
    // decrypt data to secure
    function decrypt($data, $encryptionKey)
    {
        $c              = base64_decode($data);
        $ivlen          = openssl_cipher_iv_length($cipher = "AES-256-GCM");
        $iv             = substr($c, 0, $ivlen);
        $tag            = substr($c, $ivlen, $taglen = 16);
        $ciphertext_raw = substr($c, $ivlen + $taglen);
        return openssl_decrypt($ciphertext_raw, 'aes-256-gcm', $encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
    }
}