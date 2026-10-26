<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function mail_ready(): bool {
    if (config('APP_ENV') === 'local' && config('MAIL_TRANSPORT') === 'log') return true;
    return config('MAIL_TRANSPORT', 'smtp') === 'smtp' && config('SMTP_HOST') !== '' && filter_var(config('MAIL_FROM_ADDRESS'), FILTER_VALIDATE_EMAIL) && filter_var(config('APP_URL'), FILTER_VALIDATE_URL) && !str_contains(config('APP_URL'), '.example');
}
function send_account_mail(string $recipient, string $subject, string $text): bool {
    if (!mail_ready()) return false;
    if (config('APP_ENV') === 'local' && config('MAIL_TRANSPORT') === 'log') {
        $dir = SIMPLR_ROOT . '/storage/mail';
        if (!is_dir($dir)) mkdir($dir, 0700, true);
        return file_put_contents($dir . '/' . bin2hex(random_bytes(12)) . '.json', json_encode(['to' => $recipient, 'subject' => $subject, 'text' => $text], JSON_PRETTY_PRINT), LOCK_EX) !== false;
    }
    require_once SIMPLR_ROOT . '/PHPMailer-master/src/Exception.php';
    require_once SIMPLR_ROOT . '/PHPMailer-master/src/PHPMailer.php';
    require_once SIMPLR_ROOT . '/PHPMailer-master/src/SMTP.php';
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = config('SMTP_HOST');
        $mail->Port = (int)config('SMTP_PORT', '587');
        $mail->SMTPAuth = config('SMTP_USERNAME') !== '';
        $mail->Username = config('SMTP_USERNAME');
        $mail->Password = config('SMTP_PASSWORD');
        $mail->SMTPSecure = config('SMTP_ENCRYPTION', 'tls');
        $mail->Timeout = 10;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(config('MAIL_FROM_ADDRESS'), config('MAIL_FROM_NAME', 'Simplr Suite'));
        $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->Body = $text;
        return $mail->send();
    } catch (Throwable $error) {
        error_log('Simplr: account email delivery failed (' . get_class($error) . ')');
        return false;
    }
}
function account_link(string $path, string $token): string {
    require_once __DIR__ . '/audiences.php';
    $origin = configured_audience_url(account_audience($path)) ?: rtrim(config('APP_URL'), '/');
    return $origin . $path . '?token=' . rawurlencode($token);
}
