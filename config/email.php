<?php
/**
 * Email Configuration
 * 
 * Reads SMTP settings from environment variables (.env file).
 * This allows you to have different credentials for local and production.
 */

// Load environment variables if not already loaded
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/database.php';
}

// Helper function to get environment variable with fallback
function env($key, $default = '') {
    return defined($key) ? constant($key) : $default;
}

return [
    // SMTP Configuration
    'smtp_host' => env('SMTP_HOST', 'smtp.gmail.com'),
    'smtp_port' => (int)env('SMTP_PORT', '587'),
    'smtp_encryption' => env('SMTP_ENCRYPTION', 'tls'),
    'smtp_username' => env('SMTP_USERNAME', ''),
    'smtp_password' => env('SMTP_PASSWORD', ''),
    'from_email' => env('SMTP_FROM_EMAIL', ''),
    'from_name' => env('SMTP_FROM_NAME', 'CashTimeline'),
    
    // Email Settings
    'reply_to_email' => env('SMTP_REPLY_TO_EMAIL', ''),
    'reply_to_name' => env('SMTP_REPLY_TO_NAME', 'CashTimeline Support'),
    
    // Development Mode
    'debug_mode' => filter_var(env('EMAIL_DEBUG_MODE', 'false'), FILTER_VALIDATE_BOOLEAN),
    
    /*
     * SETUP INSTRUCTIONS:
     * 
     * Configure these settings in your .env file:
     * - SMTP_HOST, SMTP_PORT, SMTP_ENCRYPTION
     * - SMTP_USERNAME, SMTP_PASSWORD
     * - SMTP_FROM_EMAIL, SMTP_FROM_NAME
     * 
     * For Gmail:
     * 1. Enable 2-factor authentication on your Google account
     * 2. Generate an App Password: https://myaccount.google.com/apppasswords
     * 3. Use the app password in SMTP_PASSWORD
     * 
     * For production:
     * - Consider using dedicated SMTP services: SendGrid, Mailgun, Amazon SES, Postmark
     */
];
