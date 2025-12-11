#!/bin/bash

# Gmail SMTP Setup Script for StudentMove
# This script helps configure Gmail to send emails to your actual inbox

echo "=========================================="
echo "Gmail SMTP Setup for StudentMove"
echo "=========================================="
echo ""
echo "To send emails to your Gmail inbox, you need:"
echo "1. A Gmail App Password (not your regular password)"
echo "2. Updated .env file with Gmail SMTP settings"
echo ""
echo "STEP 1: Get Gmail App Password"
echo "----------------------------------------"
echo "1. Go to: https://myaccount.google.com/security"
echo "2. Enable '2-Step Verification' if not already enabled"
echo "3. Go to: https://myaccount.google.com/apppasswords"
echo "4. Select 'Mail' as the app"
echo "5. Select 'Other (Custom name)' as the device"
echo "6. Enter 'StudentMove' as the name"
echo "7. Click 'Generate'"
echo "8. Copy the 16-character password (it will look like: abcd efgh ijkl mnop)"
echo ""
read -p "Press Enter when you have your Gmail App Password ready..."
echo ""
read -p "Enter your Gmail address (e.g., yourname@gmail.com): " GMAIL_EMAIL
read -p "Enter your Gmail App Password (16 characters, no spaces): " GMAIL_PASSWORD
echo ""

# Update .env file
ENV_FILE=".env"

if [ ! -f "$ENV_FILE" ]; then
    echo "Error: .env file not found!"
    exit 1
fi

# Backup .env
cp "$ENV_FILE" "$ENV_FILE.backup"
echo "✓ Created backup: $ENV_FILE.backup"

# Update mail configuration
sed -i '' "s|^MAIL_HOST=.*|MAIL_HOST=smtp.gmail.com|" "$ENV_FILE"
sed -i '' "s|^MAIL_PORT=.*|MAIL_PORT=587|" "$ENV_FILE"
sed -i '' "s|^MAIL_USERNAME=.*|MAIL_USERNAME=$GMAIL_EMAIL|" "$ENV_FILE"
sed -i '' "s|^MAIL_PASSWORD=.*|MAIL_PASSWORD=$GMAIL_PASSWORD|" "$ENV_FILE"
sed -i '' "s|^MAIL_ENCRYPTION=.*|MAIL_ENCRYPTION=tls|" "$ENV_FILE"
sed -i '' "s|^MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=\"$GMAIL_EMAIL\"|" "$ENV_FILE"
sed -i '' "s|^MAIL_FROM_NAME=.*|MAIL_FROM_NAME=\"StudentMove\"|" "$ENV_FILE"

echo "✓ Updated .env file with Gmail SMTP settings"
echo ""
echo "STEP 2: Clear Configuration Cache"
echo "----------------------------------------"
php artisan config:clear
echo "✓ Configuration cache cleared"
echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Your emails will now be sent to: $GMAIL_EMAIL"
echo ""
echo "To test:"
echo "1. Register a new account"
echo "2. Check your Gmail inbox (and spam folder)"
echo "3. Click the verification link"
echo ""
echo "Note: If emails don't arrive, check:"
echo "- Spam folder"
echo "- Gmail App Password is correct"
echo "- 2-Step Verification is enabled"
echo ""


