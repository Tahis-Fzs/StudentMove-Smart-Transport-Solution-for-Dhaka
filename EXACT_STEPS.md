# EXACT STEPS TO FIX EMAIL (Copy & Paste)

## The Problem
Your system is using test credentials (`test@example.com`) which Gmail rejects. That's why you're not getting emails.

## The Solution (5 Minutes)

### Step 1: Get Gmail App Password

1. Open: **https://myaccount.google.com/apppasswords**
2. If you see "App passwords aren't available":
   - Go to: **https://myaccount.google.com/security**
   - Click **"2-Step Verification"** → Enable it
   - Go back to App Passwords
3. Click **"Select app"** → Choose **"Mail"**
4. Click **"Select device"** → Choose **"Other (Custom name)"**
5. Type: **StudentMove**
6. Click **"Generate"**
7. **Copy the password** (looks like: `abcd efgh ijkl mnop`)
8. **IMPORTANT:** Remove ALL spaces! It should be 16 characters with no spaces.

### Step 2: Configure Gmail

Open Terminal and run these commands (copy and paste):

```bash
cd "/Users/md.shadmantahsin/Desktop/TV Series/StudentMove-Smart-Transport-Solution-for-Dhaka"
php artisan email:configure-gmail shadmantahsinmmd@gmail.com YOUR-APP-PASSWORD-HERE
```

**Replace `YOUR-APP-PASSWORD-HERE` with the 16-character password you copied (no spaces!)**

Example:
```bash
php artisan email:configure-gmail shadmantahsinmmd@gmail.com abcdefghijklmnop
```

### Step 3: Test It

```bash
php artisan email:test shadmantahsinmmd@gmail.com
```

**If you see "✅ Test email sent successfully!"** → Check your Gmail inbox!

**If you see an error** → Your App Password might be wrong. Generate a new one and try again.

### Step 4: Resend Verification Email

1. Log in to your account
2. Go to the email verification page
3. Click "Resend Verification Email"
4. Check your Gmail inbox (and spam folder)

## Still Not Working?

Run this to check:
```bash
./fix-gmail.sh
```

This will show you exactly what's wrong.


