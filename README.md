# 💰 CashTimeline

A modern personal finance application for tracking transactions, managing accounts, and visualizing your financial timeline with intelligent notifications.

## ✨ Features

- **📊 Transaction Management** - Track income and expenses with detailed categorization
- **💳 Multiple Accounts** - Manage multiple bank accounts, credit cards, and wallets
- **🔄 Recurring Transactions** - Set up automatic recurring bills and income
- **📅 Financial Timeline** - Visualize your financial history and future projections
- **🔔 Smart Notifications** - Get email and SMS alerts for transactions and due dates
- **📱 Progressive Web App** - Install on mobile devices for app-like experience
- **🌙 Modern UI** - Clean, responsive interface that works on all devices

## 🚀 Quick Start

### Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/cashtimeline.git
   cd cashtimeline
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and add your database credentials and other settings.

4. **Import database**
   ```bash
   mysql -u root -p < database.sql
   ```
   *(Create the database schema - you'll need to create this file)*

5. **Set permissions**
   ```bash
   chmod 755 public/
   chmod 666 public/uploads/
   ```

6. **Access the application**
   - Navigate to `http://localhost/cashtimeline/public`
   - Register a new account
   - Start tracking your finances!

## ⚙️ Configuration

### Database Setup

Update your `.env` file with database credentials:

```env
DB_HOST=localhost
DB_NAME=cashtimeline
DB_USER=root
DB_PASS=your-password
```

### Email Notifications (Optional)

To enable email notifications, configure SMTP settings in `.env`:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
```

**For Gmail:**
1. Enable 2-factor authentication
2. Generate an [App Password](https://myaccount.google.com/apppasswords)
3. Use the app password in `SMTP_PASSWORD`

### SMS Notifications (Optional)

To enable SMS notifications via Twilio:

```env
TWILIO_ACCOUNT_SID=your-account-sid
TWILIO_AUTH_TOKEN=your-auth-token
TWILIO_PHONE_NUMBER=+1234567890
```

Sign up at [Twilio](https://www.twilio.com) to get your credentials.

## 📁 Project Structure

```
cashtimeline/
├── config/              # Configuration files
│   ├── database.php     # Database connection
│   ├── email.php        # Email configuration
│   └── sms.php          # SMS configuration
├── public/              # Public web root
│   ├── assets/          # CSS, JS, icons
│   ├── partials/        # UI components
│   └── *.php            # Main pages
├── src/
│   ├── controllers/     # Business logic
│   ├── models/          # Data models
│   ├── services/        # Email/SMS services
│   └── jobs/            # Background jobs
├── vendor/              # Composer dependencies (not in repo)
├── .env.example         # Environment template
├── composer.json        # PHP dependencies
└── README.md           # This file
```

## 🔒 Security

- **Never commit** `.env` file to version control
- Use strong passwords for production databases
- Enable HTTPS in production
- Regularly update dependencies: `composer update`
- Secure file permissions on production server

## 🌐 Production Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed production deployment instructions.

**Deploying to Hostinger?** See [HOSTINGER-DEPLOYMENT.md](HOSTINGER-DEPLOYMENT.md) for a complete step-by-step guide.

### Quick Production Setup

1. Clone repository on production server
2. Run `composer install --no-dev --optimize-autoloader`
3. Copy `.env.production.example` to `.env`
4. Update `.env` with production credentials
5. Set `APP_ENV=production` in `.env`
6. Secure: `chmod 600 .env`
7. Point web server to `public/` directory

### SFTP Deployment

If using SFTP (via Hostinger or other hosting):

1. Copy `sftp.json.example` to `.vscode/sftp.json`
2. Update with your FTP credentials
3. Use VS Code SFTP extension to upload files

## 🛠️ Development

### Local Development with XAMPP

1. Place project in `htdocs/cashtimeline/`
2. Configure `.env` with local settings
3. Start Apache and MySQL
4. Access at `http://localhost/cashtimeline/public`

### Adding Dependencies

```bash
composer require vendor/package
```

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📧 Support

For support, questions, or feature requests:
- Open an issue on GitHub
- Check existing issues for solutions

## 🙏 Acknowledgments

- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Email sending
- [Twilio SDK](https://www.twilio.com/docs/libraries/php) - SMS notifications

---

**Built with ❤️ for better personal finance management**
