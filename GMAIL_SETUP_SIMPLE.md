# Simple Gmail Setup - Choose Your Method

## Method 1: Terminal Command (EASIEST - Recommended)

1. Get your Gmail App Password:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" → "Other (Custom name)" → Enter "StudentMove"
   - Click "Generate" and copy the 16-character password (remove spaces)

2. Run this command in Terminal:
```bash
cd "/Users/md.shadmantahsin/Desktop/TV Series/StudentMove-Smart-Transport-Solution-for-Dhaka"
php artisan email:configure-gmail your-email@gmail.com your-16-char-app-password
```

Replace:
- `your-email@gmail.com` with your actual Gmail address
- `your-16-char-app-password` with your App Password (no spaces)

Example:
```bash
php artisan email:configure-gmail shadmantahsimmd@gmail.com abcdefghijklmnop
```

3. Done! Now register and emails will go to your Gmail inbox.

---

## Method 2: Manual .env Edit

1. Get your Gmail App Password (same as Method 1)

2. Open `.env` file in your project root

3. Find these lines and update them:
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="StudentMove"
```

4. Save the file

5. Run: `php artisan config:clear`

---

## Method 3: Web Form

1. Go to: http://127.0.0.1:8000/email-setup
2. Enter your Gmail and App Password
3. Click "Configure Gmail Automatically"

---

## Verify It Worked

Run this to check:
```bash
php artisan tinker --execute="echo 'Host: ' . config('mail.mailers.smtp.host') . PHP_EOL; echo 'Username: ' . config('mail.mailers.smtp.username') . PHP_EOL;"
```

Should show:
```
Host: smtp.gmail.com
Username: your-email@gmail.com
```

---

## Test It

1. Register a new account
2. Check your Gmail inbox (and spam folder)
3. Click the verification link


