# Environment Configuration Guide

## Overview

Your CashTimeline app is now configured to work with both local development and production environments. All sensitive credentials are stored in the `.env` file, which is never committed to version control.

## How It Works

- **Local Development**: Use `.env` with `APP_ENV=local`
- **Production**: Use `.env` with `APP_ENV=production` and production credentials
- Configuration files automatically read from environment variables

## Setup Instructions

### Local Development (Current Setup)

Your local `.env` file is already configured. Just fill in your local credentials:

1. Open `.env`
2. Add your local email credentials (if testing email):
   - `SMTP_USERNAME=your-email@gmail.com`
   - `SMTP_PASSWORD=your-app-password`
3. Add your Twilio credentials (if testing SMS):
   - `TWILIO_ACCOUNT_SID=...`
   - `TWILIO_AUTH_TOKEN=...`

### Production Deployment

When deploying to production:

1. **Copy the production template**:
   ```bash
   cp .env.production.example .env
   ```

2. **Update production values** in `.env`:
   - Set `APP_ENV=production`
   - Set `APP_URL=https://yourdomain.com`
   - Update database credentials with production values
   - Add production SMTP credentials
   - Add production Twilio credentials

3. **Secure the file**:
   ```bash
   chmod 600 .env  # Make file readable only by owner
   ```

4. **Never commit** `.env` to Git (already in `.gitignore`)

## Configuration Variables

### Database
- `DB_HOST` - Database host (localhost or remote)
- `DB_NAME` - Database name
- `DB_USER` - Database username
- `DB_PASS` - Database password

### Email (SMTP)
- `SMTP_HOST` - SMTP server hostname
- `SMTP_PORT` - Port (587 for TLS, 465 for SSL)
- `SMTP_USERNAME` - Your email address
- `SMTP_PASSWORD` - Email password or app password
- `SMTP_FROM_EMAIL` - From address for emails
- `EMAIL_DEBUG_MODE` - Set to `true` to see SMTP logs

**Production Email Recommendations**:
- [SendGrid](https://sendgrid.com) - Free tier: 100 emails/day
- [Mailgun](https://mailgun.com) - Free tier: 5,000 emails/month
- [Amazon SES](https://aws.amazon.com/ses/) - Very cheap, high limits
- [Postmark](https://postmarkapp.com) - Great for transactional emails

### SMS (Twilio)
- `TWILIO_ACCOUNT_SID` - Your Twilio account SID
- `TWILIO_AUTH_TOKEN` - Your Twilio auth token
- `TWILIO_PHONE_NUMBER` - Your Twilio phone number
- `SMS_DEBUG_MODE` - Set to `true` to log SMS instead of sending

## Working Between Environments

### Option 1: Two Separate .env Files (Recommended)

Keep two files locally:
- `.env.local` - Your local credentials
- `.env.production` - Your production credentials

When you deploy:
```bash
# On production server
cp .env.production .env

# Back on local
cp .env.local .env
```

### Option 2: Git Branches

- Keep production credentials in `main` branch's `.env` (on server only)
- Keep local credentials in your development branch's `.env`

### Option 3: Server Environment Variables

Some hosting providers (Heroku, DigitalOcean App Platform, etc.) let you set environment variables through their dashboard. You can use those instead of a `.env` file.

## Security Best Practices

✅ **DO**:
- Keep `.env` in `.gitignore` (already done)
- Use strong passwords for production
- Use app-specific passwords for Gmail
- Restrict file permissions on production: `chmod 600 .env`
- Use different credentials for local and production
- Backup your production `.env` securely (encrypted)

❌ **DON'T**:
- Never commit `.env` to Git
- Never share credentials in Slack/email
- Never use production credentials locally
- Never store passwords in code files

## Testing Your Configuration

### Test Database Connection
```bash
php -r "require 'config/database.php'; echo 'Connected successfully';"
```

### Test Email Configuration
Check that your SMTP credentials are loaded:
```bash
php -r "print_r(require 'config/email.php');"
```

### Test SMS Configuration
```bash
php -r "print_r(require 'config/sms.php');"
```

## Troubleshooting

**Problem**: Configuration not loading
- Make sure `.env` file exists in project root
- Check file permissions
- Verify `loadEnv()` function in `config/database.php`

**Problem**: Email not sending
- Check `SMTP_USERNAME` and `SMTP_PASSWORD` are correct
- For Gmail, use an app-specific password
- Set `EMAIL_DEBUG_MODE=true` to see SMTP logs

**Problem**: SMS not sending
- Verify Twilio credentials are correct
- Check Twilio account has credits
- Set `SMS_DEBUG_MODE=true` to see what would be sent

## Quick Reference

| Environment | APP_ENV | Database | Email | SMS |
|-------------|---------|----------|-------|-----|
| Local | `local` | Local MySQL | Test SMTP or Gmail | Test Twilio |
| Production | `production` | Production DB | Production SMTP | Production Twilio |

---

**Need Help?** Check the comments in:
- `config/database.php`
- `config/email.php`
- `config/sms.php`
