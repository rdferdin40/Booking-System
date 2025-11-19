# Conference Room Booking System

## Version 1.0.0 - Phase 1

A complete, production-ready conference room booking system built with PHP 8.2 and MySQL, designed for deployment under XAMPP 8.2.

---

## Features

### Core Functionality
- ✅ Timeline-based booking interface (default view)
- ✅ Real-time room availability visualization
- ✅ Color-coded bookings by category
- ✅ PIN-based reservation management
- ✅ Email/phone lookup for reservations
- ✅ Multi-room support
- ✅ Configurable time increments (15/30/60 minutes)
- ✅ Adjustable max booking duration
- ✅ Conflict detection and prevention
- ✅ Admin override for conflicts
- ✅ Mobile-responsive design
- ✅ Touch-friendly interface for tablets

### Admin Features
- ✅ Complete admin panel
- ✅ Room management (CRUD, activate/deactivate, lock/unlock)
- ✅ Category management with color coding
- ✅ Reservation management (view, filter, cancel)
- ✅ System settings configuration
- ✅ Email/SMS notification setup
- ✅ User management
- ✅ Database backup
- ✅ Multi-language support (English/Spanish)

### Notifications
- ✅ Email notifications (configurable SMTP)
- ✅ SMS notifications (generic API integration)
- ✅ Customizable triggers (create/update/cancel)

---

## System Requirements

- **Web Server**: Apache 2.4+ (included in XAMPP)
- **PHP**: 8.2+ with PDO extension
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Operating System**: Windows, Linux, or macOS
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

---

## Installation Instructions

### Step 1: Install XAMPP

1. Download XAMPP 8.2 from https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp` (Windows) or `/Applications/XAMPP` (macOS)
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Deploy Application

1. Copy the entire `booking` folder to `C:\xampp\htdocs\`
2. The final path should be: `C:\xampp\htdocs\booking\`

### Step 3: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `booking_system`
3. Select the database
4. Click "Import" tab
5. Choose file: `C:\xampp\htdocs\booking\migrations\schema.sql`
6. Click "Go" to import

### Step 4: Configure Database Connection

1. Open `booking/config/database.php`
2. Verify the settings (default XAMPP settings):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'booking_system');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Save the file if any changes were made

### Step 5: Configure Base URL

1. Open `booking/config/config.php`
2. Update the `BASE_URL` constant:
   ```php
   define('BASE_URL', 'http://localhost/booking');
   ```
3. For production, use your domain:
   ```php
   define('BASE_URL', 'https://yourdomain.com/booking');
   ```

### Step 6: Set Permissions (Linux/macOS only)

```bash
chmod -R 755 booking/
chmod -R 777 booking/backups/
chmod -R 777 booking/logs/
```

### Step 7: Access the Application

- **Public Booking Interface**: http://localhost/booking/public/index.php
- **Admin Panel**: http://localhost/booking/admin/login.php

### Step 8: Default Admin Login

```
Username: admin
Password: admin123
```

**⚠️ IMPORTANT**: Change the admin password immediately after first login!

---

## Directory Structure

```
booking/
├── admin/                  # Admin panel
│   ├── index.php          # Dashboard
│   ├── login.php          # Admin login
│   ├── logout.php         # Logout
│   ├── rooms.php          # Room management
│   ├── categories.php     # Category management
│   ├── reservations.php   # Reservation management
│   ├── settings.php       # System settings
│   ├── notifications.php  # Notification config
│   ├── system.php         # System management
│   └── partials/          # Header & footer
├── public/                # Public booking interface
│   ├── index.php          # Timeline view (main page)
│   ├── create.php         # Create booking
│   ├── edit.php           # Find/edit booking
│   ├── view.php           # View booking details
│   └── partials/          # Header & footer
├── config/                # Configuration files
│   ├── config.php         # Main config
│   ├── database.php       # DB credentials
│   ├── mail.php           # Email config
│   ├── sms.php            # SMS config
│   └── settings.php       # Settings loader
├── core/                  # Core PHP classes
│   ├── Database.php       # DB connection
│   ├── Validator.php      # Input validation
│   ├── TimeHelpers.php    # Time utilities
│   ├── ReservationService.php
│   ├── RoomService.php
│   ├── CategoryService.php
│   ├── NotificationService.php
│   └── AdminAuth.php      # Authentication
├── assets/                # Static assets
│   ├── css/
│   │   ├── public.css     # Public interface styles
│   │   └── admin.css      # Admin panel styles
│   └── js/
│       ├── public.js      # Public interface scripts
│       └── admin.js       # Admin panel scripts
├── migrations/            # Database schema
│   └── schema.sql         # Complete DB schema
├── backups/               # Database backups (auto-created)
├── logs/                  # Application logs (auto-created)
└── README.md              # This file
```

---

## Configuration

### System Settings (Admin Panel)

Access: `Admin Panel > Settings`

- **Max Duration**: Maximum booking length (default: 240 minutes / 4 hours)
- **Time Increment**: Slot intervals (15, 30, or 60 minutes)
- **Auto Release Mode**: Instant or grace period
- **Grace Period**: Minutes before auto-release (0-60)
- **Show Titles**: Display booking titles on public view
- **Public View Mode**: Timeline, Calendar, or List
- **Screensaver**: Enable/disable with timeout
- **Language**: English or Spanish

### Email Notifications

Access: `Admin Panel > Notifications`

Supports any SMTP server:

**Gmail Example:**
- SMTP Host: `smtp.gmail.com`
- Port: `587`
- Encryption: `TLS`
- Username: Your Gmail address
- Password: App password (not regular password)

**Office365 Example:**
- SMTP Host: `smtp.office365.com`
- Port: `587`
- Encryption: `STARTTLS`

### SMS Notifications

Access: `Admin Panel > Notifications`

Supports any HTTP-based SMS API:

**Required Settings:**
- API URL: Full endpoint URL
- API Key: Authentication key (optional)
- Method: POST or GET
- Phone Field: Parameter name for phone number
- Message Field: Parameter name for message text

**Popular Providers:**
- Twilio
- Nexmo/Vonage
- Plivo
- MessageBird
- Clickatell
- Sinch

---

## Usage Guide

### For End Users

#### Making a Booking:

1. Visit the public booking page
2. Select room and date
3. Click on an available (green) time slot
4. Fill in booking details:
   - Full name
   - Department
   - Category
   - Meeting title
   - Email/phone (optional but recommended)
5. Click "Book Now"
6. **Save your PIN code!** You'll need it to manage your booking

#### Finding/Managing Your Booking:

1. Visit "Find Booking" page
2. Option 1: Enter your 6-digit PIN
3. Option 2: Enter your email or phone to see all your bookings
4. View booking details
5. Cancel if needed (future bookings only)

### For Administrators

#### Managing Rooms:

1. Go to `Admin Panel > Rooms`
2. Add new room with name and description
3. Activate/deactivate rooms
4. Lock rooms to prevent new bookings
5. Set per-room view mode

#### Managing Categories:

1. Go to `Admin Panel > Categories`
2. Add category with name and color
3. Edit or delete categories
4. Categories are used for color-coding

#### Managing Reservations:

1. Go to `Admin Panel > Reservations`
2. Filter by room, category, or search
3. View all booking details
4. Cancel any reservation
5. Admin can override conflicts

#### Creating Backups:

1. Go to `Admin Panel > System`
2. Click "Create Backup Now"
3. Backups saved to `/backups/` folder
4. Download backups regularly

---

## Security Considerations

### Immediately After Installation:

1. **Change admin password**: Admin Panel > System
2. **Secure database**: Use strong password for MySQL root user
3. **Enable HTTPS**: Install SSL certificate (Let's Encrypt recommended)
4. **Restrict access**: Use .htaccess to restrict admin panel
5. **Regular backups**: Schedule daily database backups

### Production Deployment:

1. Set error reporting to 0 in config.php:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. Use environment variables for sensitive data

3. Enable PHP security settings in php.ini:
   ```ini
   expose_php = Off
   session.cookie_httponly = 1
   session.cookie_secure = 1
   ```

4. Implement rate limiting for login attempts

---

## Troubleshooting

### Database Connection Error

**Problem**: "Database connection failed"

**Solution**:
1. Verify MySQL is running in XAMPP
2. Check database credentials in `config/database.php`
3. Ensure database `booking_system` exists
4. Check if schema.sql was imported successfully

### Page Not Found (404)

**Problem**: "Not Found" error when accessing pages

**Solution**:
1. Verify files are in correct location: `C:\xampp\htdocs\booking\`
2. Check BASE_URL in `config/config.php` matches your setup
3. Ensure Apache is running in XAMPP

### Styles Not Loading

**Problem**: Page displays but without CSS

**Solution**:
1. Check browser console for 404 errors
2. Verify `assets/css/` folder exists and contains CSS files
3. Check ASSETS_URL constant in config.php
4. Clear browser cache

### Email Not Sending

**Problem**: Emails not being sent

**Solution**:
1. Check SMTP settings in Admin Panel > Notifications
2. Verify email is enabled in Settings
3. Test with "Send Test Email" button
4. Check PHP error logs
5. For Gmail, use App Passwords, not regular password

### Permission Denied Errors (Linux/macOS)

**Problem**: "Permission denied" when creating backups/logs

**Solution**:
```bash
sudo chmod -R 777 booking/backups/
sudo chmod -R 777 booking/logs/
```

---

## Maintenance

### Regular Tasks:

- **Daily**: Check for failed bookings or conflicts
- **Weekly**: Review and clean up old reservations
- **Monthly**: Create database backup
- **Quarterly**: Review and update categories
- **As needed**: Add/remove rooms, update settings

### Database Cleanup:

Old reservations can be cleaned automatically:

```php
// Add to a scheduled task
require_once 'core/ReservationService.php';
$service = new ReservationService();
$service->cleanupOld(90); // Delete reservations older than 90 days
```

---

## Customization

### Changing Colors:

Edit `assets/css/public.css`:
- Available slots: `.slot-available { background: #2ecc71; }`
- Booked slots: Colors come from category settings
- Past slots: `.slot-past { background: #95a5a6; }`

### Changing Business Hours:

Edit `config/config.php`:
```php
define('BUSINESS_START_HOUR', 8);   // 8:00 AM
define('BUSINESS_END_HOUR', 20);    // 8:00 PM
```

### Adding Custom Fields:

1. Add column to `reservations` table
2. Update ReservationService validation
3. Add field to booking forms
4. Update email/SMS templates

---

## Support & Documentation

### Need Help?

1. Check this README first
2. Review inline code comments
3. Check PHP error logs: `xampp/apache/logs/error.log`
4. Check application logs: `booking/logs/app.log`

### Useful Resources:

- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- XAMPP Documentation: https://www.apachefriends.org/docs/

---

## License

This software is provided as-is for use in your organization.

## Credits

**Conference Room Booking System v1.0.0**
Built with PHP 8.2 + MySQL
Modern, responsive, production-ready

---

## Changelog

### Version 1.0.0 (Phase 1)
- Initial release
- Complete booking system
- Timeline view interface
- Admin panel
- Email/SMS notifications
- Multi-room support
- Category management
- PIN-based access
- Mobile responsive design

---

**Congratulations! Your conference room booking system is now ready to use.**

For additional configuration or customization, please refer to the inline documentation in the PHP files.
