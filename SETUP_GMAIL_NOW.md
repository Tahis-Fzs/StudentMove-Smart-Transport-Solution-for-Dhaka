# ⚠️ IMPORTANT: Configure Gmail with REAL Credentials

## Current Problem

Your `.env` file has **test credentials** that won't work:
- Username: `test@example.com` ❌
- Password: `testpassword123456` ❌

These are placeholder values. You **MUST** replace them with your real Gmail credentials.

## Quick Fix (3 Steps)

### Step 1: Get Gmail App Password

1. **Enable 2-Step Verification** (if not already):
   - Go to: https://myaccount.google.com/security
   - Click "2-Step Verification" and follow the steps

2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Click "Select app" → Choose **"Mail"**
   - Click "Select device" → Choose **"Other (Custom name)"**
   - Type: **"StudentMove"**
   - Click **"Generate"**
   - **Copy the 16-character password** (looks like: `abcd efgh ijkl mnop`)
   - **IMPORTANT:** Remove all spaces when copying!

### Step 2: Configure Gmail

Open Terminal and run:

```bash
cd "/Users/md.shadmantahsin/Desktop/TV Series/StudentMove-Smart-Transport-Solution-for-Dhaka"
php artisan email:configure-gmail YOUR-REAL-EMAIL@gmail.com YOUR-16-CHAR-APP-PASSWORD
```

**Replace:**
- `YOUR-REAL-EMAIL@gmail.com` with your actual Gmail address
- `YOUR-16-CHAR-APP-PASSWORD` with the App Password (no spaces!)

**Example:**
```bash
php artisan email:configure-gmail shadmantahsimmd@gmail.com abcdefghijklmnop
```

### Step 3: Test It

Run this to verify it works:

```bash
php artisan email:test YOUR-REAL-EMAIL@gmail.com
```

**If successful:** You'll see "✅ Test email sent successfully!" - check your Gmail inbox.

**If failed:** You'll see an error message with instructions to fix your App Password.

## After Configuration

Once the test succeeds:

1. **Register a new account** at: http://127.0.0.1:8000/register
2. **Check your Gmail inbox** (and spam folder) for the verification email
3. **Click the verification link** to verify your email

## Troubleshooting

### "Gmail Authentication Error" after registration
- Your App Password may be incorrect or expired
- Generate a NEW App Password and reconfigure:
  ```bash
  php artisan email:configure-gmail your-email@gmail.com new-app-password
  ```
- Then request a new verification email from your profile page

### Test email fails
- Make sure you removed all spaces from the App Password
- Make sure the App Password is exactly 16 characters
- Try generating a new App Password
- Make sure 2-Step Verification is enabled

### Still not working?
Check the logs:
```bash
# Check debug logs
tail -50 .cursor/debug.log | grep -E "REG[0-9]|CG[0-9]"

# Check Laravel logs
tail -50 storage/logs/laravel.log | grep -i "error\|mail"
```


