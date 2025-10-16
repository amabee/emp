# Dynamic Company Information for Emails

## Overview
Email templates now automatically fetch company information from your database instead of using hardcoded values. This means you can update your company details from the admin panel, and all emails will reflect the changes immediately!

## What's Dynamic?

The following information is fetched from the `company_info` table:

- ✅ **Company Name** - Used in email headers and footers
- ✅ **From Name** - Email sender shows as "Company Name HR Department"
- ✅ **Company Address** - Shown in email footer
- ✅ **Company Phone** - Shown in email footer
- ✅ **Company Website** - Shown in email footer and welcome email

## How It Works

### Database Table: `company_info`

The system queries this table to get your company information:

```sql
SELECT * FROM company_info ORDER BY company_id DESC LIMIT 1
```

### Fields Used:
- `name` → Company Name
- `address` → Company Address
- `contact_number` → Company Phone
- `website` → Company Website
- `email` → Company Email (optional)

### Caching
Company information is cached in memory during each request to avoid multiple database queries. The cache is cleared on each new page load.

## How to Update Company Information

### Method 1: Admin Panel (Recommended)
1. Log in as Admin
2. Go to **Settings** → **Company Settings**
3. Update:
   - Company Name
   - Address
   - Phone Number
   - Website
4. Save changes

All emails sent after this will use the new information!

### Method 2: Direct Database Update

```sql
UPDATE company_info 
SET 
  name = 'Wonderpets EMS',
  address = '123 Main Street, Quezon City, Philippines',
  contact_number = '+63 912 345 6789',
  website = 'https://wonderpets-ems.com'
WHERE company_id = 1;
```

## Email Templates Affected

All email templates automatically use the dynamic company information:

### 1. Status Update Emails
- Header: Shows company name
- Footer: Shows company name, address, phone, website

### 2. Interview Scheduled Emails
- Subject: "Interview Scheduled - [Company Name]"
- Header: Company name
- Footer: Full company contact info

### 3. Welcome Emails (New Employees)
- Subject: "Welcome to [Company Name] - Your Employee Account"
- Body: "Congratulations and welcome to [Company Name]!"
- Portal Link: Shows company website
- Footer: Full company contact info

## Fallback Values

If no company information is found in the database, the system uses these defaults:

- Company Name: "Employee Management System"
- Address: "Not Set"
- Phone: "Not Set"
- Website: "Not Set"
- Email: Value from `SMTP_FROM_EMAIL` config

## Configuration Files

### `shared/email_config.php`
Only contains SMTP settings (no company info):
- `SMTP_HOST`
- `SMTP_PORT`
- `SMTP_ENCRYPTION`
- `SMTP_USERNAME`
- `SMTP_PASSWORD`
- `SMTP_FROM_EMAIL`

### `shared/EmailService.php`
Contains:
- `getCompanyInfo()` - Fetches company data from database
- Caching mechanism
- Dynamic email templates

## Benefits

✅ **No Code Changes** - Update company info without touching code
✅ **Consistent Branding** - All emails use the same company information
✅ **Easy Maintenance** - One place to update company details
✅ **Multi-tenant Ready** - Can support multiple companies in the future
✅ **Database Driven** - Backup/restore includes company info

## Troubleshooting

### Email Shows "Not Set" Values
**Problem:** Email footer shows "Not Set" for address, phone, or website

**Solution:**
1. Check if `company_info` table has data:
   ```sql
   SELECT * FROM company_info;
   ```
2. If empty, insert company information:
   ```sql
   INSERT INTO company_info (name, address, contact_number, website, created_at, updated_at)
   VALUES ('Your Company', '123 Main St', '+1234567890', 'https://example.com', NOW(), NOW());
   ```

### Email Shows Wrong Company Name
**Problem:** Old company name appears in emails

**Solution:**
1. Update the database:
   ```sql
   UPDATE company_info SET name = 'New Company Name' WHERE company_id = 1;
   ```
2. Clear any application cache (if applicable)
3. Send a test email to verify

### Database Connection Errors
**Problem:** Emails fail to send due to database errors

**Solution:**
1. Check `error_log` for details
2. Verify `getDBConnection()` is working
3. The system will use fallback values if database fails

## Technical Implementation

### Code Example (EmailService.php)

```php
private static function getCompanyInfo()
{
    if (self::$companyInfo !== null) {
        return self::$companyInfo; // Return cached data
    }

    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM company_info ORDER BY company_id DESC LIMIT 1");
        $stmt->execute();
        $info = $stmt->fetch();

        if ($info) {
            self::$companyInfo = [
                'name' => $info['name'] ?? 'Employee Management System',
                'address' => $info['address'] ?? '',
                'phone' => $info['contact_number'] ?? '',
                'website' => $info['website'] ?? '',
                'email' => $info['email'] ?? SMTP_FROM_EMAIL
            ];
        }

        return self::$companyInfo;
    } catch (Exception $e) {
        // Return fallback values on error
        return [
            'name' => 'Employee Management System',
            'address' => 'Not Set',
            'phone' => 'Not Set',
            'website' => 'Not Set',
            'email' => SMTP_FROM_EMAIL
        ];
    }
}
```

### Email Header Generation

```php
$companyInfo = self::getCompanyInfo();
$headers[] = 'From: ' . $companyInfo['name'] . ' HR Department <' . SMTP_FROM_EMAIL . '>';
```

**Result:** From: "Wonderpets EMS HR Department <no-reply-ems@gmail.com>"

## Migration Notes

If you're upgrading from the old hardcoded system:

1. ✅ Remove old constants from `email_config.php`:
   - ~~SMTP_FROM_NAME~~ (now dynamic)
   - ~~COMPANY_NAME~~ (from database)
   - ~~COMPANY_ADDRESS~~ (from database)
   - ~~COMPANY_PHONE~~ (from database)
   - ~~COMPANY_WEBSITE~~ (from database)

2. ✅ Ensure `company_info` table has your data

3. ✅ Test emails to verify company info appears correctly

---

**Last Updated:** October 16, 2025
