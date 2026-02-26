<?php

/**
 * Email Service
 * Handles sending email notifications using PHP's mail() function or SMTP
 */

class EmailService {
    private array $config;
    
    public function __construct() {
        $configFile = __DIR__ . '/../../config/email.php';
        $this->config = file_exists($configFile) ? require $configFile : $this->getDefaultConfig();
    }
    
    private function getDefaultConfig(): array {
        return [
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => '',
            'smtp_password' => '',
            'from_email' => 'noreply@cashtimeline.local',
            'from_name' => 'CashTimeline',
            'debug_mode' => false,
        ];
    }
    
    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool {
        // If SMTP is configured, use PHPMailer
        if ($this->isSmtpConfigured()) {
            return $this->sendViaSmtp($to, $subject, $htmlBody, $textBody);
        }
        
        // Otherwise, use PHP's mail() function
        return $this->sendViaMail($to, $subject, $htmlBody, $textBody);
    }
    
    private function isSmtpConfigured(): bool {
        return !empty($this->config['smtp_host']) 
            && !empty($this->config['smtp_username']) 
            && !empty($this->config['smtp_password']);
    }
    
    private function sendViaSmtp(string $to, string $subject, string $htmlBody, string $textBody): bool {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            error_log('PHPMailer not found. Install via: composer require phpmailer/phpmailer');
            return false;
        }
        
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = $this->config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['smtp_username'];
            $mail->Password   = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_encryption'];
            $mail->Port       = $this->config['smtp_port'];
            
            if ($this->config['debug_mode']) {
                $mail->SMTPDebug = 2;
            }
            
            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to);
            
            if (!empty($this->config['reply_to_email'])) {
                $mail->addReplyTo($this->config['reply_to_email'], $this->config['reply_to_name'] ?? '');
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);
            
            $mail->send();
            return true;
            
        } catch (\Exception $e) {
            error_log("Email send failed: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    private function sendViaMail(string $to, string $subject, string $htmlBody, string $textBody): bool {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>',
            'X-Mailer: PHP/' . phpversion(),
        ];
        
        $success = mail($to, $subject, $htmlBody, implode("\r\n", $headers));
        
        if (!$success) {
            error_log("Email send failed using mail() function");
        }
        
        return $success;
    }
    
    /**
     * Send notification email
     */
    public function sendNotification(string $to, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null): bool {
        $htmlBody = $this->buildNotificationHtml($title, $message, $actionUrl, $actionText);
        $textBody = $this->buildNotificationText($title, $message, $actionUrl);
        
        return $this->send($to, $title, $htmlBody, $textBody);
    }
    
    private function buildNotificationHtml(string $title, string $message, ?string $actionUrl, ?string $actionText): string {
        $actionButton = '';
        if ($actionUrl && $actionText) {
            $actionButton = '<p style="text-align:center;margin:30px 0;">
                <a href="' . htmlspecialchars($actionUrl) . '" 
                   style="background:#6366f1;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;">
                    ' . htmlspecialchars($actionText) . '
                </a>
            </p>';
        }
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin:0;padding:0;background:#f3f4f6;font-family:system-ui,-apple-system,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,0.08);">
                            <tr>
                                <td style="padding:40px;">
                                    <div style="text-align:center;margin-bottom:30px;">
                                        <div style="display:inline-block;background:#6366f1;color:#fff;width:48px;height:48px;line-height:48px;border-radius:12px;font-size:24px;">
                                            📊
                                        </div>
                                        <h1 style="margin:16px 0 0;font-size:24px;color:#111827;">CashTimeline</h1>
                                    </div>
                                    
                                    <h2 style="color:#111827;font-size:20px;margin:0 0 16px;">' . htmlspecialchars($title) . '</h2>
                                    <p style="color:#4b5563;font-size:15px;line-height:1.6;margin:0 0 24px;">' . nl2br(htmlspecialchars($message)) . '</p>
                                    
                                    ' . $actionButton . '
                                    
                                    <div style="margin-top:40px;padding-top:24px;border-top:1px solid #e5e7eb;">
                                        <p style="color:#9ca3af;font-size:12px;text-align:center;margin:0;">
                                            You received this email because you have notifications enabled in your CashTimeline account.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
    }
    
    private function buildNotificationText(string $title, string $message, ?string $actionUrl): string {
        $text = "CashTimeline Notification\n\n";
        $text .= $title . "\n\n";
        $text .= $message . "\n\n";
        
        if ($actionUrl) {
            $text .= "View in app: " . $actionUrl . "\n\n";
        }
        
        $text .= "---\nYou received this email because you have notifications enabled in your CashTimeline account.\n";
        
        return $text;
    }
}
