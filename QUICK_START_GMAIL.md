# Quick Start: Configure Gmail in 2 Minutes

## Step 1: Get Your Gmail App Password

1. Go to: **https://myaccount.google.com/apppasswords**
2. If you see "App passwords aren't available for your account":
   - Go to: **https://myaccount.google.com/security**
   - Enable **"2-Step Verification"** first
   - Then go back to App Passwords
3. Click **"Select app"** → Choose **"Mail"**
4. Click **"Select device"** → Choose **"Other (Custom name)"**
5. Type: **"StudentMove"**
6. Click **"Generate"**
7. **Copy the 16-character password** (it looks like: `abcd efgh ijkl mnop`)
   - **Remove all spaces** when copying!

## Step 2: Configure Gmail

Open Terminal and run:

```bash
cd "/Users/md.shadmantahsin/Desktop/TV Series/StudentMove-Smart-Transport-Solution-for-Dhaka"
php artisan email:configure-gmail YOUR-EMAIL@gmail.com YOUR-16-CHAR-PASSWORD
```

**Example:**
```bash
php artisan email:configure-gmail shadmantahsimmd@gmail.com abcdefghijklmnop
```

**Important:** 
- Replace `YOUR-EMAIL@gmail.com` with your actual Gmail address
- Replace `YOUR-16-CHAR-PASSWORD` with the App Password you just generated (no spaces!)

## Step 3: Verify It Worked

Run this to check:
```bash
php artisan tinker --execute="echo config('mail.mailers.smtp.host');"
```

Should show: `smtp.gmail.com`

## Step 4: Test It

1. Go to: **http://127.0.0.1:8000/register**
2. Fill out the registration form
3. Click **"Create Account"**
4. Check your **Gmail inbox** (and spam folder)
5. Click the verification link in the email

## Troubleshooting

### "Configuration verification failed"
- Make sure you removed all spaces from the App Password
- Make sure the App Password is exactly 16 characters
- Try generating a new App Password

### "Gmail Authentication Error" (after registration)
- Your App Password may be incorrect or expired
- Generate a new App Password and reconfigure:
  ```bash
  php artisan email:configure-gmail your-email@gmail.com new-app-password
  ```
- Then request a new verification email from your profile page

### Emails not arriving
- Check your spam folder
- Make sure 2-Step Verification is enabled
- Verify the App Password is correct
- Check debug logs: `tail -50 .cursor/debug.log | grep REG`


