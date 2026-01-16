# Bluedots Technologies Quote Management System
## Phase 0 - MVP

A simple, professional quote management system for creating quotes with Nigerian VAT calculation (7.5%), saving to MySQL database, and generating print-ready documents.

---

## 🎯 Features (Phase 0)

✅ **Create Quotes**
- Dynamic line items (add/remove rows)
- Automatic VAT calculation (7.5% Nigerian VAT - optional per line)
- Real-time totals update
- Save as draft or finalized

✅ **View Quotes**
- List all saved quotes
- Filter by status (draft/finalized)
- Professional display with Bluedots branding
- Print-friendly layout

✅ **Professional Branding**
- Bluedots logo and color scheme
- Company contact information
- Bank account details for payments
- Clean, modern Tailwind CSS design

---

## 📁 Project Structure

```
bluedotserp/
│
├── index.php                  # Landing page
├── config.php                 # Database configuration
│
├── pages/
│   ├── create-quote.php      # Quote creation form
│   ├── view-quotes.php       # List all quotes
│   └── view-quote.php        # Single quote detail & print
│
├── includes/
│   ├── header.php            # Common header (logo, nav)
│   └── footer.php            # Common footer
│
├── api/
│   └── save-quote.php        # Handle quote submission
│
├── assets/
│   ├── css/
│   │   └── style.css         # Custom styles
│   └── js/
│       └── quote-form.js     # Line items + VAT calculation
│
├── database/
│   └── schema.sql            # Database schema
│
└── README.md                 # This file
```

---

## 🚀 Installation Instructions

### Step 1: Install XAMPP (if not already installed)

1. Download XAMPP from: https://www.apachefriends.org/
2. Install and start **Apache** and **MySQL** services

### Step 2: Setup Project Files

1. Navigate to your XAMPP htdocs folder:
   - **Windows**: `C:\xampp\htdocs\`
   - **Mac**: `/Applications/MAMP/htdocs/`

2. Your project is already in: `bluedotserp/`

### Step 3: Create Database

1. Open your browser and go to: http://localhost/phpmyadmin

2. Click **"New"** in the left sidebar

3. Create a new database:
   - **Database name**: `bluedots_quotes`
   - **Collation**: `utf8mb4_unicode_ci`
   - Click **"Create"**

4. Click on the `bluedots_quotes` database (left sidebar)

5. Click the **"SQL"** tab at the top

6. Open the file: `database/schema.sql` in a text editor

7. Copy the **entire contents** and paste into the SQL tab

8. Click **"Go"** to execute

   ✅ You should see: "3 rows inserted" - this creates sample data

### Step 4: Configure Database Connection

The default configuration should work for local development. If you need to change it:

1. Open `config.php`

2. Update these lines if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'bluedots_quotes');
   define('DB_USER', 'root');     // Default XAMPP user
   define('DB_PASS', '');         // Default XAMPP password (blank)
   ```

### Step 5: Access the Application

1. Open your browser

2. Go to: http://localhost/bluedotserp/

3. You should see the Bluedots Technologies landing page

4. Try the links:
   - **Create Quote**: http://localhost/bluedotserp/pages/create-quote.php
   - **View Quotes**: http://localhost/bluedotserp/pages/view-quotes.php

---

## 🧪 Testing the System

### Test 1: View Sample Quote

1. Go to: http://localhost/bluedotserp/pages/view-quotes.php
2. You should see one quote: **QT-0001** (from sample data)
3. Click **"View →"** to see the full quote
4. Click **"Print Quote"** to test print layout

### Test 2: Create New Quote

1. Go to: http://localhost/bluedotserp/pages/create-quote.php

2. Fill in the form:
   - **Quote Title**: `IT Support Package`
   - **Customer Name**: `XYZ Company`
   - **Salesperson**: `Your Name`
   - **Date**: (today's date - pre-filled)

3. Add line items (click "Add Line Item"):

   **Line 1:**
   - Qty: `1`
   - Description: `Monthly IT Support`
   - Unit Price: `50000`
   - VAT: ✓ (checked)
   - Expected Line Total: ₦53,750.00

   **Line 2:**
   - Qty: `2`
   - Description: `Laptop Repair`
   - Unit Price: `15000`
   - VAT: ✗ (unchecked)
   - Expected Line Total: ₦30,000.00

4. Verify totals:
   - **Subtotal**: ₦80,000.00
   - **VAT (7.5%)**: ₦3,750.00
   - **Grand Total**: ₦83,750.00

5. Click **"Save & Finalize"**

6. You should be redirected to the quote view with success message

### Test 3: VAT Calculation

Test these scenarios to verify VAT calculation:

**Scenario A: All VAT Enabled**
- 2 items @ ₦50,000 each with VAT
- Expected: Subtotal ₦100,000, VAT ₦7,500, Total ₦107,500

**Scenario B: No VAT**
- 3 items @ ₦10,000 each without VAT
- Expected: Subtotal ₦30,000, VAT ₦0.00, Total ₦30,000

**Scenario C: Mixed VAT** (as in Test 2)
- Item 1: ₦100,000 with VAT → ₦107,500
- Item 2: ₦5,000 no VAT → ₦5,000
- Expected: Subtotal ₦105,000, VAT ₦7,500, Total ₦112,500

---

## 💰 VAT Calculation Logic

Nigeria VAT Rate: **7.5%**

### Formula:
```javascript
// For EACH line item:
baseAmount = quantity × unitPrice
lineVAT = vatEnabled ? (baseAmount × 0.075) : 0
lineTotal = baseAmount + lineVAT

// For entire document:
subtotal = sum of all baseAmounts
totalVAT = sum of all lineVAT amounts
grandTotal = subtotal + totalVAT
```

### Important Rules:
- VAT is **OPTIONAL** per line item (checkbox controlled)
- VAT is calculated **per line** before summing
- VAT amount is added **ON TOP** of the base price
- Only calculate VAT if checkbox is enabled

---

## 🎨 Bluedots Branding

### Colors:
- **Primary Blue**: #0076BE
- **Secondary Green**: #34A853
- **Background**: #F3F4F6
- **Text**: #111827

### Logo Design:
4 circles in increasing size:
1. Small (w-3 h-3, bg-sky-500)
2. Medium (w-5 h-5, bg-sky-600, border green)
3. Large (w-8 h-8, bg-sky-700)
4. Outline (w-10 h-10, border green, hollow)

### Company Details:
- **Name**: Bluedots Technologies
- **Address**: No. 9 Ugbor Village Road, Ugbor GRA, Benin City, Edo State
- **Phone**: 07031635955
- **Email**: bluedotsng@gmail.com
- **Website**: www.bluedots.com.ng

### Bank Accounts:
- **Access Bank**: 0107309773
- **UBA**: 1023821430

---

## 🗄️ Database Schema

### `documents` Table
Stores quote header information.

| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| document_number | VARCHAR(50) | Quote number (QT-0001) |
| quote_title | VARCHAR(255) | Quote title/project name |
| customer_name | VARCHAR(255) | Customer/client name |
| salesperson | VARCHAR(255) | Salesperson name |
| quote_date | DATE | Quote date |
| subtotal | DECIMAL(15,2) | Total before VAT |
| total_vat | DECIMAL(15,2) | Total VAT amount |
| grand_total | DECIMAL(15,2) | Final total |
| payment_terms | VARCHAR(255) | Payment terms (default: 80% Initial Deposit) |
| status | ENUM | 'draft' or 'finalized' |
| notes | TEXT | Optional notes |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

### `line_items` Table
Stores individual quote line items.

| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| document_id | INT (FK) | References documents.id |
| item_number | INT | Line item number (1, 2, 3...) |
| quantity | DECIMAL(10,2) | Quantity |
| description | TEXT | Item description |
| unit_price | DECIMAL(15,2) | Price per unit |
| vat_applicable | TINYINT(1) | 0 = No VAT, 1 = VAT enabled |
| vat_amount | DECIMAL(15,2) | Calculated VAT for this line |
| line_total | DECIMAL(15,2) | Total for this line (inc. VAT) |
| created_at | TIMESTAMP | Creation timestamp |

---

## 🔧 Troubleshooting

### Database Connection Error

**Error**: "Database connection failed"

**Solution**:
1. Make sure MySQL is running in XAMPP Control Panel
2. Check database name is `bluedots_quotes`
3. Verify credentials in `config.php`
4. Run the schema.sql file again

### Quote Number Not Auto-Generating

**Error**: Quote shows blank or wrong number

**Solution**:
1. Check if `documents` table exists
2. Verify the `generateQuoteNumber()` function in `config.php`
3. Test by creating a quote - it should start at QT-0001

### VAT Calculation Not Working

**Error**: Totals not updating or incorrect

**Solution**:
1. Check browser console for JavaScript errors (F12)
2. Verify `quote-form.js` is loading correctly
3. Clear browser cache and reload
4. Check that line item inputs have correct `onchange` events

### Print Layout Issues

**Error**: Print layout looks wrong

**Solution**:
1. Use Chrome or Edge for best print results
2. In Print Preview, set margins to "Default"
3. Check "Background graphics" is enabled
4. Use landscape orientation for wider quotes

---

## 📝 Usage Guide

### Creating a Quote

1. Navigate to **Create Quote**
2. Fill in required fields (marked with *)
3. Click **"Add Line Item"** for each product/service
4. For each line:
   - Enter quantity
   - Enter description
   - Enter unit price
   - Check "VAT?" box if VAT applies
   - Watch line total update automatically
5. Review totals at bottom
6. Choose:
   - **Save as Draft**: Save for later editing (Phase 1+)
   - **Save & Finalize**: Complete and ready to print

### Viewing Quotes

1. Navigate to **View Quotes**
2. Browse all quotes in table format
3. Click **"View →"** on any quote to see details
4. From detail page:
   - Click **"Print Quote"** to print
   - Click **"Back to Quotes"** to return to list

### Printing a Quote

1. Open the quote detail page
2. Click **"Print Quote"** button
3. Print dialog will open
4. Settings:
   - **Destination**: Your printer or "Save as PDF"
   - **Layout**: Portrait
   - **Margins**: Default
   - **Options**: Enable "Background graphics"
5. Click **"Print"**

---

## 🚀 Deployment to Production (Smartweb Hosting)

### Step 1: Prepare Files

1. Zip the entire `bluedotserp/` folder
2. Or use FTP client (FileZilla recommended)

### Step 2: Upload via FTP

1. Connect to Smartweb hosting:
   - **Host**: ftp.yourdomain.com (check Smartweb email)
   - **Username**: [provided by Smartweb]
   - **Password**: [provided by Smartweb]
   - **Port**: 21

2. Upload to `public_html/` folder

### Step 3: Create Production Database

1. Login to cPanel (provided by Smartweb)
2. Find **MySQL Databases**
3. Create new database:
   - Name: `[cpanel_user]_bluedots`
   - Create user
   - Assign all privileges
4. Go to **phpMyAdmin**
5. Select your database
6. Import `schema.sql` file

### Step 4: Update Configuration

1. Edit `config.php` on server via FTP or cPanel File Manager
2. Update:
   ```php
   define('DB_HOST', 'localhost'); // Usually localhost
   define('DB_NAME', '[your_db_name]'); // From Step 3
   define('DB_USER', '[your_db_user]'); // From Step 3
   define('DB_PASS', '[your_db_password]'); // From Step 3
   ```

### Step 5: Test Live Site

1. Visit: `https://yourdomain.com/bluedotserp/`
2. Create a test quote
3. Verify it saves to database
4. Test print functionality

### Step 6: Security (Important!)

1. Change default database password
2. Consider moving `config.php` outside `public_html/` (advanced)
3. Add `.htaccess` if needed for custom URLs

---

## 🚫 Phase 0 Limitations

This is the **MVP (Minimum Viable Product)**. The following features are NOT included yet:

❌ User authentication/login
❌ User roles (Admin, Manager, etc.)
❌ Customer database
❌ Product catalog
❌ Invoice generation
❌ PDF export
❌ Email sending
❌ Edit/delete quotes
❌ Search/filters
❌ Analytics dashboard

These will be added in **Phase 1+**

---

## ✅ Phase 0 Success Criteria

- [x] Quote creation form loads
- [x] Dynamic line items (add/remove)
- [x] VAT calculation (7.5%) works correctly
- [x] Real-time total updates
- [x] Naira symbol (₦) displays
- [x] Number formatting with commas
- [x] Quote saves to database
- [x] Auto-generate quote numbers
- [x] View quotes list
- [x] View single quote details
- [x] Print functionality works
- [x] Bluedots branding applied
- [x] Mobile responsive (basic)

---

## 📧 Support

For issues or questions:
- **Email**: bluedotsng@gmail.com
- **Phone**: 07031635955

---

## 📄 License

© 2026 Bluedots Technologies. All rights reserved.

---

**Built with ❤️ by Bluedots Technologies**

Phase 0 - MVP | Nigerian VAT-Compliant Quote Management System
