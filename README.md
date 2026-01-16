# 1100erp

Enterprise Resource Planning System

## Quick Start

1. **Install Requirements:**
   - PHP 7.4+
   - MySQL 5.7+
   - Apache/Nginx web server

2. **Setup Database:**
   ```bash
   # Import database schema
   mysql -u root -p < database/schema.sql
   
   # Run migrations
   php run-bank-migration.php
   ```

3. **Configure:**
   ```bash
   # Edit config.php with your details
   # Update company information in Settings
   ```

4. **Access:**
   ```
   http://localhost/1100erp/
   Default login: admin / admin123
   ```

## Features

- ✅ Quote Management
- ✅ Invoice Generation
- ✅ Receipt Tracking
- ✅ Customer Management
- ✅ Product Inventory
- ✅ Dynamic Bank Accounts
- ✅ Audit Logging
- ✅ Mobile Responsive
- ✅ PDF Export
- ✅ Email Integration

## Documentation

- [CHANGELOG.md](CHANGELOG.md) - Version history
- [CONFIGURATION.md](CONFIGURATION.md) - Setup guide
- [GIT-PUSH-INSTRUCTIONS.md](GIT-PUSH-INSTRUCTIONS.md) - Deployment

## Version

**Current Version:** 2.0.0

## License

Proprietary - All rights reserved

## Support

For issues and support, please open an issue on GitHub.

---

**1100-ERP** - Enterprise Resource Planning Made Simple
