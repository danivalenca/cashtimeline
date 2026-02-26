<?php
/**
 * SMS Configuration
 * 
 * Reads SMS gateway settings from environment variables (.env file).
 * This allows you to have different credentials for local and production.
 */

// Load environment variables if not already loaded
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/database.php';
}

// Helper function to get environment variable with fallback
if (!function_exists('env')) {
    function env($key, $default = '') {
        return defined($key) ? constant($key) : $default;
    }
}

return [
    // Twilio Configuration
    'twilio_account_sid' => env('TWILIO_ACCOUNT_SID', ''),
    'twilio_auth_token' => env('TWILIO_AUTH_TOKEN', ''),
    'twilio_phone_number' => env('TWILIO_PHONE_NUMBER', ''),
    
    // SMS Settings
    'default_country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '+1'),
    
    // Development Mode
    'debug_mode' => filter_var(env('SMS_DEBUG_MODE', 'false'), FILTER_VALIDATE_BOOLEAN),
    
    /*
     * SETUP INSTRUCTIONS:
     * 
     * Configure these settings in your .env file:
     * - TWILIO_ACCOUNT_SID
     * - TWILIO_AUTH_TOKEN
     * - TWILIO_PHONE_NUMBER
     * 
     * For Twilio:
     * 1. Sign up at https://www.twilio.com
     * 2. Get a phone number from the console
     * 3. Find your Account SID and Auth Token in the console
     * 4. Twilio SDK is already installed via Composer
     * 
     * Alternative providers:
     * - Nexmo/Vonage: https://www.vonage.com
     * - MessageBird: https://www.messagebird.com
     * - AWS SNS: https://aws.amazon.com/sns/
     * 
     * Note: SMS services require payment/credits
     */
];
