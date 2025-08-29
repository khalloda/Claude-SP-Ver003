# Spare Parts Management System - Installation Guide

This guide will help you install and configure the Spare Parts Management System on your GoDaddy Plesk hosting environment.

## Prerequisites

- GoDaddy Shared Hosting with Plesk Panel
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Access to Plesk File Manager and phpMyAdmin

## Step 1: Create Database

1. Log into your Plesk control panel
2. Go to **Databases** → **Add Database**
3. Create a new database:
   - Database name: `sp_main`
   - Username: `sp_user` 
   - Password: Generate a strong password and save it

## Step 2: Upload Files

1. In Plesk, go to **File Manager**
2. Navigate to your domain's folder
3. Upload all project files to the root directory
4. **IMPORTANT**: Set your document root to point to the `/public` folder
   - In Plesk: **Hosting Settings** → **Document root** → Set to `public`

## Step 3: Configure Environment

1. Copy `.env.example` to `.env`
2. Edit the `.env` file with your database credentials:
   ```env
   DB_HOST=localhost
   DB_DATABASE=sp_main
   DB_USERNAME=sp_user
   DB_PASSWORD=your_generated_password
   ```
3. Generate a secure APP_KEY:
   - Use an online base64 generator to create a 32-character random string
   - Add `base64:` prefix to the generated string

## Step 4: Import Database Schema

1. Go to **phpMyAdmin** in your Plesk panel
2. Select your `sp_main` database
3. Click **Import** tab
4. Upload and execute `sql/schema.sql`
5. Upload and execute `sql/seed.sql`

## Step 5: Set File Permissions

Ensure these directories are writable:
```bash
chmod 755 public/
chmod 644 .env
chmod -R 755 app/
```

## Step 6: Test Installation

1. Visit your domain
2. You should see the login page
3. Default admin credentials:
   - Email: `admin@example.com`
   - Password: `Admin@123`

## Step 7: Security Configuration

1. **Change Default Password**: Immediately change the admin password
2. **SSL Certificate**: Enable SSL in Plesk (Let's Encrypt is free)
3. **File Permissions**: Ensure `.env` file is not publicly accessible
4. **Remove Test Files**: Delete any test files if present

## Troubleshooting

### Blank Page (White Screen)
- Check PHP error logs in Plesk
- Ensure document root points to `/public`
- Verify file permissions

### Database Connection Error
- Verify database credentials in `.env`
- Ensure database user has proper privileges
- Check database server status

### 500 Internal Server Error
- Check `.htaccess` file in public directory
- Verify PHP version compatibility
- Check error logs for specific error details

### Pretty URLs Not Working
- Ensure `web.config` is in the `/public` directory
- Check IIS URL Rewrite module is enabled
- Verify document root configuration

### Permission Denied Errors
- Check file/directory permissions
- Ensure web server can read all application files
- Verify `.env` file permissions

## Support

For technical support:
1. Check error logs first (Plesk → Logs)
2. Verify all installation steps were completed
3. Ensure system requirements are met

## Default Features Available

- Multi-language support (English/Arabic)
- Quote → Sales Order → Invoice workflow
- Inventory management with low-stock alerts
- Multi-currency support
- Payment tracking
- User role management
- Responsive design

---

🔒 **Security Note**: Always keep your system updated and regularly backup your database and files.