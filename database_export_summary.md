# Mac Software Database Export Summary

**Export Date:** July 19, 2025 21:27:39  
**Database Name:** mac_software  
**MySQL Version:** 8.0.40  
**Export Tool:** mysqldump 10.13

## Database Structure

### Tables Exported:

#### 1. `admin_users`
- **Purpose:** Admin authentication and user management
- **Columns:**
  - `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
  - `username` (VARCHAR(50), NOT NULL, UNIQUE)
  - `password` (VARCHAR(255), NOT NULL) - bcrypt hashed
  - `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
  - `updated_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

- **Current Data:** 1 admin user (username: 'admin')

#### 2. `contact_messages`
- **Purpose:** Store contact form submissions from website visitors
- **Columns:** (Structure exported, currently no data)
  - Likely includes: id, name, email, message, date

#### 3. `software_requests`
- **Purpose:** Store software requests from users
- **Columns:**
  - Contains software requests with details like name, version, email, etc.
  - **Current Data:** 1 request (Adobe v3.21 from golamrabby40@gmail.com)

## Export Files Generated:

1. **database_backup.sql** (4,413 bytes)
   - Standard database export
   - Contains all tables, structure, and data

2. **mac_software_complete_backup_YYYYMMDD_HHMMSS.sql**
   - Enhanced export with database creation statements
   - Includes DROP DATABASE statements for clean imports

## Security Notes:
- Admin passwords are properly bcrypt hashed
- Export files contain sensitive data and should be stored securely
- Remove or secure backup files after use

## Restoration Instructions:

To restore this database on a new server:

```bash
# Create database (if using complete backup, this is included)
mysql -u [username] -p -e "CREATE DATABASE mac_software;"

# Import the backup
mysql -u [username] -p mac_software < database_backup.sql

# Or use the complete backup (includes database creation)
mysql -u [username] -p < mac_software_complete_backup_YYYYMMDD_HHMMSS.sql
```

## Notes:
- Backup completed successfully with minor tablespace warnings (normal)
- All essential data and structure preserved
- Ready for migration or backup purposes
