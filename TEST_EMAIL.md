# Test Email Configuration

## Current Status

Your `.env` file currently has **test credentials** that won't work with Gmail:
- Username: `test@example.com` 
- Password: `testpassword123456`

These are placeholder values and will cause authentication errors when trying to send emails.

## What You Need To Do

### Step 1: Get Real Gmail App Password

1. Go to: **https://myaccount.google.com/apppasswords**
2. Enable 2-Step Verification if not already enabled: **https://myaccount.google.com/security**
3. Generate App Password:
   - Select "Mail" as app
   - Select "Other (Custom name)" as device
   - Enter "StudentMove"
   - Click "Generate"
   - Copy the 16-character password (remove spaces!)

### Step 2: Configure with Real Credentials

Run this command with your REAL Gmail and App Password:

```bash
cd "/Users/md.shadmantahsin/Desktop/TV Series/StudentMove-Smart-Transport-Solution-for-Dhaka"
php artisan email:configure-gmail YOUR-REAL-EMAIL@gmail.com YOUR-REAL-APP-PASSWORD
```

**Example:**
```bash
php artisan email:configure-gmail shadmantahsimmd@gmail.com abcdefghijklmnop
```

### Step 3: Verify Configuration

```bash
php artisan tinker --execute="echo 'Host: ' . config('mail.mailers.smtp.host') . PHP_EOL; echo 'Username: ' . config('mail.mailers.smtp.username');"
```

Should show:
```
Host: smtp.gmail.com
Username: YOUR-REAL-EMAIL@gmail.com
```

### Step 4: Test Registration

1. Go to: **http://127.0.0.1:8000/register**
2. Fill out the form
3. Submit registration
4. Check your Gmail inbox (and spam folder)

## If You See "Gmail Authentication Error"

This means your App Password is incorrect or expired. To fix:

1. Generate a NEW App Password: **https://myaccount.google.com/apppasswords**
2. Run the configure command again with the new password
3. Request a new verification email from your profile page

## Debugging

Check logs if emails aren't working:
```bash
# Check debug logs
tail -50 .cursor/debug.log | grep -E "REG[0-9]|CG[0-9]"

# Check Laravel logs
tail -50 storage/logs/laravel.log | grep -i "error\|mail\|email"
```


