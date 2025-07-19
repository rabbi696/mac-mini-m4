# CyberPanel Deployment Guide

This guide will help you deploy your PHP application to CyberPanel with the optimized database configuration.

## 🚀 Quick Setup

### 1. Upload Your Files
Upload your project files to your CyberPanel domain directory:
```
/home/[username]/public_html/[domain]/
```

### 2. Create Database in CyberPanel
1. Log into CyberPanel
2. Go to "Databases" → "Create Database"
3. Create database (format: `username_dbname`)
4. Note down the database credentials

### 3. Configure Environment
1. Copy the environment template:
   ```bash
   cp .env.cyberpanel.example .env
   ```

2. Edit `.env` with your actual database credentials:
   ```
   DB_SERVER=localhost
   DB_USERNAME=yoursite_appdb
   DB_PASSWORD=your_secure_password
   DB_NAME=yoursite_appdb
   DB_PORT=3306
   ```

### 4. Set File Permissions
```bash
chmod 644 .env
chmod 755 config/
chmod 644 config/*.php
```

## 🔧 Configuration Features

### Automatic Environment Detection
The configuration automatically detects:
- ✅ CyberPanel environment
- ✅ Local development environment
- ✅ Appropriate connection method (TCP vs Socket)

### Connection Methods
- **Development**: Uses socket connection (`/tmp/mysql_3306.sock`)
- **CyberPanel**: Uses TCP connection for reliability
- **Fallback**: Automatically detects socket paths if needed

### Socket Path Detection
The system checks these common socket locations:
- `/var/lib/mysql/mysql.sock`
- `/var/run/mysqld/mysqld.sock`
- `/tmp/mysql.sock`
- `/run/mysqld/mysqld.sock`

## 🛠️ Troubleshooting

### Connection Issues
1. **Check database credentials** in CyberPanel
2. **Verify database exists** and user has permissions
3. **Check error logs**: `/usr/local/lsws/logs/error.log`

### Permission Issues
```bash
# Fix file permissions
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

### Debug Mode
To enable debug mode, add to your `.env`:
```
PHP_DISPLAY_ERRORS=On
```

## 📋 CyberPanel Database Naming
CyberPanel typically uses this format:
- **Database**: `username_dbname`
- **User**: `username_dbname`
- **Password**: Your chosen password

Example:
- If your site is `mywebsite` and you want database `app`
- Database name: `mywebsite_app`
- Username: `mywebsite_app`

## 🔒 Security Notes
- Never commit `.env` files to version control
- Use strong passwords for database users
- Keep your CyberPanel installation updated
- Regular database backups through CyberPanel

## 🚨 Production Checklist
- [ ] Database created in CyberPanel
- [ ] `.env` file configured with correct credentials
- [ ] File permissions set correctly
- [ ] Error logging enabled
- [ ] Database connection tested
- [ ] Application functionality verified

## 📞 Support
If you encounter issues:
1. Check CyberPanel logs: `/usr/local/lsws/logs/`
2. Verify PHP version compatibility (PHP 8.2.28)
3. Test database connection manually
4. Check firewall settings if using remote database
