<?php

/**
 * SMS Service
 * Handles sending SMS notifications using Twilio or other providers
 */

class SmsService {
    private array $config;
    
    public function __construct() {
        $configFile = __DIR__ . '/../../config/sms.php';
        $this->config = file_exists($configFile) ? require $configFile : $this->getDefaultConfig();
    }
    
    private function getDefaultConfig(): array {
        return [
            'twilio_account_sid' => '',
            'twilio_auth_token' => '',
            'twilio_phone_number' => '',
            'default_country_code' => '+1',
            'debug_mode' => false,
        ];
    }
    
    public function send(string $to, string $message): bool {
        // Normalize phone number
        $to = $this->normalizePhoneNumber($to);
        
        if (empty($to)) {
            error_log('Invalid phone number for SMS');
            return false;
        }
        
        // Debug mode: just log the SMS
        if ($this->config['debug_mode']) {
            error_log("SMS (DEBUG MODE) to {$to}: {$message}");
            return true;
        }
        
        // Check if Twilio is configured
        if ($this->isTwilioConfigured()) {
            return $this->sendViaTwilio($to, $message);
        }
        
        error_log('SMS service not configured. Please configure Twilio in config/sms.php');
        return false;
    }
    
    private function isTwilioConfigured(): bool {
        return !empty($this->config['twilio_account_sid'])
            && !empty($this->config['twilio_auth_token'])
            && !empty($this->config['twilio_phone_number']);
    }
    
    private function sendViaTwilio(string $to, string $message): bool {
        // Check if Twilio SDK is available
        if (!class_exists('Twilio\\Rest\\Client')) {
            error_log('Twilio SDK not found. Install via: composer require twilio/sdk');
            return false;
        }
        
        try {
            $client = new \Twilio\Rest\Client(
                $this->config['twilio_account_sid'],
                $this->config['twilio_auth_token']
            );
            
            $result = $client->messages->create(
                $to,
                [
                    'from' => $this->config['twilio_phone_number'],
                    'body' => $message
                ]
            );
            
            return $result->status !== 'failed';
            
        } catch (\Exception $e) {
            error_log("SMS send failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function normalizePhoneNumber(string $phone): string {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If no country code, add default
        if (!str_starts_with($phone, '+')) {
            $phone = $this->config['default_country_code'] . $phone;
        }
        
        // Basic validation: must have + and at least 10 digits
        if (strlen($phone) < 11 || !str_starts_with($phone, '+')) {
            return '';
        }
        
        return $phone;
    }
    
    /**
     * Send notification SMS (shortens text appropriately)
     */
    public function sendNotification(string $to, string $title, string $message): bool {
        // SMS messages should be concise (160 chars for single SMS, 153 for multi-part)
        $smsBody = $title;
        
        // Add message if there's room
        if (strlen($smsBody) + strlen($message) + 2 <= 150) {
            $smsBody .= ": " . $message;
        }
        
        return $this->send($to, $smsBody);
    }
}
