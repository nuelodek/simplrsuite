<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function mail_ready(): bool {
    $transport = config('MAIL_TRANSPORT','smtp');
    if ($transport === 'log') return config('APP_ENV') === 'local';
    if ($transport !== 'smtp') return false;
    $port = filter_var(config('SMTP_PORT','587'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>65535]]);
    $host = config('SMTP_HOST');
    $validHost = filter_var($host,FILTER_VALIDATE_IP)
        || (bool)filter_var($host,FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME);
    $appUrl = config('APP_URL');
    return $validHost
        && $port !== false
        && config('SMTP_USERNAME') !== ''
        && config('SMTP_PASSWORD') !== ''
        && in_array(config('SMTP_ENCRYPTION','tls'),['tls','ssl'],true)
        && (bool)filter_var(config('MAIL_FROM_ADDRESS'),FILTER_VALIDATE_EMAIL)
        && (bool)filter_var($appUrl,FILTER_VALIDATE_URL)
        && !str_contains($appUrl,'.example');
}
function safe_mail_error(string $message, string $recipient): string {
    foreach ([$recipient,config('SMTP_USERNAME'),config('SMTP_PASSWORD'),config('MAIL_FROM_ADDRESS')] as $sensitive) {
        if ($sensitive !== '') $message = str_ireplace($sensitive,'[redacted]',$message);
    }
    $message = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i','[email]',$message) ?? '';
    $message = preg_replace('/[\r\n\t]+/',' ',$message) ?? '';
    return mb_substr(trim($message),0,500);
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
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
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
    } catch (PHPMailer\PHPMailer\Exception $error) {
        $detail = safe_mail_error($mail->ErrorInfo ?: $error->getMessage(),$recipient);
        error_log('Simplr: account email delivery failed (' . get_class($error) . '): ' . $detail);
        return false;
    }
}
function account_link(string $path, string $token): string {
    require_once __DIR__ . '/audiences.php';
    $origin = configured_audience_url(account_audience($path)) ?: rtrim(config('APP_URL'), '/');
    return $origin . $path . '?token=' . rawurlencode($token);
}
