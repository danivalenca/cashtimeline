# 🚀 Hostinger Deployment Guide

Complete guide for deploying CashTimeline to Hostinger hosting.

## 📋 Prerequisites

Before you start, gather these from your Hostinger panel:

1. **SFTP Credentials** (from Hostinger Dashboard → Files → FTP Accounts)
   - Host: `your-server.hostinger.com`
   - Username: `u123456789` (your account username)
   - Password: Your SFTP password
   - Port: 22

2. **MySQL Database** (from Hostinger Dashboard → Databases → MySQL)
   - Database name
   - Database username  
   - Database password
   - Database host (usually `localhost`)

## ⚙️ Step 1: Configure SFTP

1. Open `.vscode/sftp.json`
2. Update **both** configurations with your Hostinger credentials:
   ```json
   "host": "your-actual-server.hostinger.com",
   "username": "u123456789",
   "password": "your-sftp-password"
   ```

3. Update the remote paths if different:
   - Root files: `/home/u123456789/cashtimeline`
   - Public files: `/home/u123456789/public_html`

## 📤 Step 2: Upload Files to Hostinger

### Option A: Using VS Code SFTP Extension

1. **Install SFTP extension** (if not already installed):
   - Search for "SFTP" by Natizyskunk in VS Code extensions

2. **Upload root files** (config, src, composer.json, etc.):
   - Press `Cmd+Shift+P` (Mac) or `Ctrl+Shift+P` (Windows)
   - Type "SFTP: Upload Folder"
   - Select "Hostinger - Root Files"
   - Choose folders: `config`, `src`, and files: `composer.json`, `composer.lock`, `.env.example`, etc.

3. **Upload public folder** to public_html:
   - Press `Cmd+Shift+P` / `Ctrl+Shift+P`
   - Type "SFTP: Upload Folder"
   - Select "Hostinger - Public (public_html)"
   - Upload the entire `public` folder content

### Option B: Using FileZilla or Another FTP Client

1. Connect with your SFTP credentials
2. Upload root files to `/home/u123456789/cashtimeline/`:
   - `config/`
   - `src/`
   - `composer.json`
   - `composer.lock`
   - `.env.example`
   - `.env.production.example`
   
3. Upload public folder contents to `/home/u123456789/public_html/`:
   - All files from `public/` folder

## 🗄️ Step 3: Set Up Database

1. **Create MySQL Database** in Hostinger panel:
   - Go to Hostinger Dashboard → Databases → MySQL Databases
   - Create new database (e.g., `u123456789_cashtimeline`)
   - Create database user and set strong password
   - Note down: database name, username, password

2. **Import database schema**:
   - You'll need to create/upload your SQL schema file
   - Use phpMyAdmin in Hostinger panel to import tables

## 🔧 Step 4: Configure Production Environment

1. **Connect via SSH** or use Hostinger File Manager:

2. **Navigate to project root**:
   ```bash
   cd ~/cashtimeline
   ```

3. **Create production .env file**:
   ```bash
   cp .env.production.example .env
   nano .env
   ```

4. **Update .env with your production values**:
   ```env
   APP_ENV=production
   APP_URL=https://yourdomain.com
   
   # Your Hostinger MySQL credentials
   DB_HOST=localhost
   DB_NAME=u123456789_cashtimeline
   DB_USER=u123456789_dbuser
   DB_PASS=your-strong-db-password
   
   # Production email settings
   SMTP_HOST=smtp.hostinger.com
   SMTP_PORT=587
   SMTP_USERNAME=noreply@yourdomain.com
   SMTP_PASSWORD=your-email-password
   SMTP_FROM_EMAIL=noreply@yourdomain.com
   
   # Production Twilio settings (if using SMS)
   TWILIO_ACCOUNT_SID=your-production-sid
   TWILIO_AUTH_TOKEN=your-production-token
   TWILIO_PHONE_NUMBER=+1234567890
   ```

5. **Install Composer dependencies**:
   ```bash
   cd ~/cashtimeline
   composer install --no-dev --optimize-autoloader
   ```
   
   If `composer` is not available, download it:
   ```bash
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   php composer.phar install --no-dev --optimize-autoloader
   ```

6. **Secure the .env file**:
   ```bash
   chmod 600 .env
   ```

## 🔗 Step 5: Configure public_html

Your `public_html` folder should now contain your public files. You need to update paths:

1. **Update paths in public_html files** to reference the root project:

   Edit files like `index.php`, `dashboard.php`, etc. in `public_html`:
   
   Change:
   ```php
   require_once __DIR__ . '/../config/database.php';
   ```
   
   To:
   ```php
   require_once '/home/u123456789/cashtimeline/config/database.php';
   ```

   Or better, create a `bootstrap.php` in your cashtimeline folder:
   ```php
   <?php
   // ~/cashtimeline/bootstrap.php
   define('APP_ROOT', __DIR__);
   require_once APP_ROOT . '/vendor/autoload.php';
   require_once APP_ROOT . '/config/database.php';
   ```
   
   Then in public files:
   ```php
   <?php
   require_once '/home/u123456789/cashtimeline/bootstrap.php';
   ```

## 🔒 Step 6: Security & Permissions

1. **Set proper permissions**:
   ```bash
   # Project root
   chmod 755 ~/cashtimeline
   chmod 755 ~/cashtimeline/config
   chmod 600 ~/cashtimeline/.env
   
   # Public folder
   chmod 755 ~/public_html
   chmod 755 ~/public_html/assets
   chmod 755 ~/public_html/uploads
   ```

2. **Create .htaccess** in public_html (if not exists):
   ```apache
   # ~/public_html/.htaccess
   RewriteEngine On
   
   # Redirect to HTTPS
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   
   # Security headers
   Header always set X-Frame-Options "SAMEORIGIN"
   Header always set X-Content-Type-Options "nosniff"
   Header always set X-XSS-Protection "1; mode=block"
   ```

3. **Protect sensitive files** - Create `.htaccess` in root:
   ```apache
   # ~/cashtimeline/.htaccess
   Order deny,allow
   Deny from all
   ```

## ✅ Step 7: Test Your Deployment

1. Visit `https://yourdomain.com`
2. Test registration/login
3. Test creating transactions
4. Test email notifications (if configured)
5. Check error logs if issues occur:
   ```bash
   tail -f ~/public_html/error_log
   ```

## 🔄 Updating Your Production Site

When you make changes locally and want to deploy:

1. **Commit changes to Git** (push to GitHub)
2. **Upload changed files via SFTP**:
   - Use VS Code SFTP: Right-click file → "Upload"
   - Or upload via FileZilla

3. **If dependencies changed**:
   ```bash
   cd ~/cashtimeline
   composer install --no-dev --optimize-autoloader
   ```

4. **If database schema changed**:
   - Export your SQL changes
   - Import via phpMyAdmin in Hostinger panel

## 🆘 Troubleshooting

### Issue: "Class not found" errors
**Solution**: Run `composer install` in the cashtimeline directory

### Issue: "Permission denied" errors  
**Solution**: Check file permissions (755 for directories, 644 for files)

### Issue: White screen / 500 error
**Solution**: Check error logs in `~/public_html/error_log`

### Issue: Email not sending
**Solution**: 
- Verify SMTP credentials in `.env`
- Hostinger SMTP: `smtp.hostinger.com`, port 587
- Make sure your domain is verified with Hostinger email

### Issue: Database connection failed
**Solution**: 
- Verify database credentials in `.env`
- Check database exists in Hostinger panel
- Ensure database user has proper permissions

## 📞 Hostinger Support

If you encounter hosting-specific issues:
- Live Chat: Available 24/7 in Hostinger Dashboard
- Knowledge Base: https://support.hostinger.com

## ✨ Alternative: Using Git for Deployment

For easier updates, you can set up Git deployment:

1. **SSH into your Hostinger account**
2. **Clone from GitHub**:
   ```bash
   cd ~
   git clone https://github.com/yourusername/cashtimeline.git
   ```

3. **Update anytime**:
   ```bash
   cd ~/cashtimeline
   git pull origin main
   composer install --no-dev
   ```

This way you only need to use SFTP for the initial `.env` file!

---

**Need help?** Check the main [DEPLOYMENT.md](DEPLOYMENT.md) for general deployment tips.
