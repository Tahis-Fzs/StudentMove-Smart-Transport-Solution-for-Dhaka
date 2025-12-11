#!/bin/bash

echo "🔍 Checking current Gmail configuration..."
echo ""

# Check current config
HOST=$(php artisan tinker --execute="echo config('mail.mailers.smtp.host');" 2>/dev/null | tail -1)
USERNAME=$(php artisan tinker --execute="echo config('mail.mailers.smtp.username');" 2>/dev/null | tail -1)

echo "Current configuration:"
echo "  Host: $HOST"
echo "  Username: $USERNAME"
echo ""

if [ "$USERNAME" = "test@example.com" ]; then
    echo "❌ PROBLEM FOUND: System is using test credentials!"
    echo ""
    echo "You need to configure Gmail with your REAL credentials."
    echo ""
    echo "Run this command (replace with your real Gmail and App Password):"
    echo ""
    echo "  php artisan email:configure-gmail YOUR-EMAIL@gmail.com YOUR-APP-PASSWORD"
    echo ""
    echo "To get your App Password:"
    echo "  1. Go to: https://myaccount.google.com/apppasswords"
    echo "  2. Generate a new App Password for 'Mail'"
    echo "  3. Copy the 16-character password (remove spaces!)"
    echo ""
    exit 1
else
    echo "✅ Gmail is configured with: $USERNAME"
    echo ""
    echo "Testing email sending..."
    php artisan email:test "$USERNAME" 2>&1
fi


