<?php
namespace App\Core;

class Mailer {

    public static function send(string $to, string $subject, string $body, bool $isHtml = false): bool 
    {
        $host = getenv('MAILTRAP_HOST') ?: 'sandbox.smtp.mailtrap.io';
        $port = getenv('MAILTRAP_PORT') ?: 2525;
        $username = getenv('MAILTRAP_USER');
        $password = getenv('MAILTRAP_PASS');

        if (!$username || !$password) {
            error_log("Mailer Error: Missing SMTP credentials.");
            return false;
        }

        $headers = "From: SayoNet <no-reply@sayonet.com>\r\n";
        $headers .= "Reply-To: no-reply@sayonet.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if ($isHtml) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }

        // Use PHP's built-in mail() with SMTP override
        ini_set("SMTP", $host);
        ini_set("smtp_port", $port);
        ini_set("sendmail_from", "no-reply@sayonet.com");
        ini_set("auth_username", $username);
        ini_set("auth_password", $password);

        return mail($to, $subject, $body, $headers);
    }
}
