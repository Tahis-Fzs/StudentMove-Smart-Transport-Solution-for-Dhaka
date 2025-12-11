# Email Setup Instructions for Password Reset

## Quick Setup (Gmail)

To receive password reset emails in your inbox, follow these steps:

### 1. Enable 2-Step Verification on Gmail
- Go to: https://myaccount.google.com/security
- Enable "2-Step Verification" if not already enabled

### 2. Generate App Password
- Go to: https://myaccount.google.com/apppasswords
- Select "Mail" as the app
- Select "Other (Custom name)" as the device
- Enter "StudentMove" as the name
- Click "Generate"
- Copy the 16-character password (it will look like: `abcd efgh ijkl mnop`)

### 3. Update .env File
Open your `.env` file and update these lines:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="StudentMove"
```

**Important:** 
- Replace `your-email@gmail.com` with your actual Gmail address
- Replace `abcdefghijklmnop` with the app password you generated (remove spaces)
- Keep the quotes around "StudentMove" in MAIL_FROM_NAME

### 4. Clear Config Cache
Run this command:
```bash
php artisan config:clear
```

### 5. Test Password Reset
1. Go to the "Forgot Password" page
2. Enter your email address
3. Click "Send Reset Link"
4. Check your email inbox (and spam folder)
5. Click the reset link in the email
6. Set your new password

## Alternative: Mailtrap (Testing Service)

If you want to test emails without sending real emails:

1. Sign up at: https://mailtrap.io (free account)
2. Get your SMTP credentials from the inbox
3. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@studentmove.com"
MAIL_FROM_NAME="StudentMove"
```

## Troubleshooting

- **Emails not arriving?** Check spam folder first
- **Connection error?** Verify your Gmail app password is correct
- **Still using log driver?** Make sure `MAIL_MAILER=smtp` in `.env` and run `php artisan config:clear`


