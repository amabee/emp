# Landing Page Documentation

## Overview
A professional landing page for the Employee Management System that serves as the entry point for the application.

## Features

### 🎨 Design Features
- **Gradient Background**: Beautiful purple gradient (from #667eea to #764ba2)
- **Animated Elements**: Floating animation for login card and wave animation for background
- **Responsive Design**: Mobile-friendly layout that adapts to all screen sizes
- **Modern UI**: Clean, professional design with glassmorphism effects

### 📋 Content Sections

#### 1. Company Information (Left Column)
- **Company Logo**: Dynamically loaded from backend
- **Company Name**: Fetched from database
- **Company Description**: Custom description text
- **Contact Information**: 
  - Address (with map icon)
  - Phone number (with phone icon)
  - Email (with envelope icon)
  - Website (with globe icon, clickable link)

#### 2. Features Section
- **Secure & Reliable**: Enterprise-grade security
- **24/7 Access**: Anytime, anywhere access
- **Support Ready**: Support team availability

#### 3. Login Portal (Right Column)
Two main access points:

**Employee Portal**
- Icon: User-check icon
- Description: Access dashboard, schedules, leave requests, profile
- Link: `./login.php`

**Job Applicant**
- Icon: User-plus icon
- Description: Apply for positions, track applications
- Link: `./applicant-login.php`

## Files Created

### 1. `/pages/landing.php`
Main landing page with:
- Full-height hero section
- Company info fetching via AJAX
- Skeleton loading states
- Responsive layout
- Animated elements

### 2. `/ajax/get_public_company_info.php`
Public API endpoint that:
- Doesn't require authentication
- Returns safe company information
- Provides fallback defaults
- Sanitizes data for public consumption

Returns:
```json
{
  "success": true,
  "company": {
    "company_name": "...",
    "company_address": "...",
    "company_email": "...",
    "company_phone": "...",
    "company_website": "...",
    "company_logo": "...",
    "company_description": "..."
  }
}
```

### 3. `/pages/applicant-login.php`
Placeholder page for applicant portal:
- Coming soon message
- Login form (disabled)
- Back to home button
- Link to employee login

## Modified Files

### 1. `/dashboard_router.php`
Changed redirect from `login.php` to `pages/landing.php`

### 2. `/login.php`
Added:
- "Back to Home" button
- Link to applicant portal
- Boxicons CSS for icons

## Usage

### Accessing the Landing Page
1. Navigate to your app's root URL (e.g., `http://localhost/emp/`)
2. You'll be automatically redirected to the landing page

### Direct Access
- Landing page: `http://localhost/emp/pages/landing.php`
- Employee login: `http://localhost/emp/login.php`
- Applicant portal: `http://localhost/emp/pages/applicant-login.php`

## Customization

### Update Company Information
Company details are pulled from the database via `SystemSettingsController->getCompanyInfo()`. 
To update:
1. Go to system settings in admin panel
2. Update company information fields
3. Changes will reflect immediately on landing page

### Styling
Main styles are in `<style>` block in `landing.php`. Key classes:
- `.landing-hero`: Main hero section
- `.login-card`: Login options card
- `.login-option`: Individual login option cards
- `.company-info`: Company information section
- `.feature-item`: Feature showcase items

### Colors
Primary gradient: `#667eea` to `#764ba2`
Accent color: `#ffd700` (gold)

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Performance
- AJAX loading for company info
- Skeleton loaders for better UX
- Optimized animations
- Minimal dependencies

## Security
- Public endpoint doesn't expose sensitive data
- Only safe company information is returned
- No authentication required for landing page
- Proper sanitization of all data

## Future Enhancements
- [ ] Implement full applicant portal
- [ ] Add registration functionality
- [ ] Include testimonials section
- [ ] Add image gallery/carousel
- [ ] Integration with job posting system
- [ ] Newsletter signup
- [ ] Social media links
- [ ] Multi-language support

## Notes
- The applicant portal is currently a placeholder
- All company data is loaded dynamically from the database
- Fallback defaults are provided if no data exists
- The page is fully responsive and mobile-friendly
