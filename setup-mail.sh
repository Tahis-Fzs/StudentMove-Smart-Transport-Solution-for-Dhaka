#!/bin/bash

echo "🚀 Setting up automatic email (Mailpit)..."

# Check if Homebrew is installed
if ! command -v brew &> /dev/null; then
    echo "❌ Homebrew not found. Installing Homebrew..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
fi

# Install Mailpit if not installed
if ! command -v mailpit &> /dev/null; then
    echo "📦 Installing Mailpit..."
    brew install axllent/mailpit/mailpit
else
    echo "✅ Mailpit already installed"
fi

# Check if Mailpit is running
if curl -s http://127.0.0.1:8025 > /dev/null 2>&1; then
    echo "✅ Mailpit is already running"
else
    echo "🚀 Starting Mailpit..."
    # Start Mailpit in background
    mailpit > /dev/null 2>&1 &
    sleep 2
    
    if curl -s http://127.0.0.1:8025 > /dev/null 2>&1; then
        echo "✅ Mailpit started successfully!"
    else
        echo "⚠️  Mailpit may need to be started manually: mailpit"
    fi
fi

# Update .env file
echo "📝 Updating .env file..."
cd "$(dirname "$0")"

# Backup .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# Update mail settings
sed -i '' 's/^MAIL_MAILER=.*/MAIL_MAILER=smtp/' .env 2>/dev/null || sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=smtp/' .env
sed -i '' 's/^MAIL_HOST=.*/MAIL_HOST=127.0.0.1/' .env 2>/dev/null || sed -i 's/^MAIL_HOST=.*/MAIL_HOST=127.0.0.1/' .env
sed -i '' 's/^MAIL_PORT=.*/MAIL_PORT=1025/' .env 2>/dev/null || sed -i 's/^MAIL_PORT=.*/MAIL_PORT=1025/' .env
sed -i '' 's/^MAIL_USERNAME=.*/MAIL_USERNAME=/' .env 2>/dev/null || sed -i 's/^MAIL_USERNAME=.*/MAIL_USERNAME=/' .env
sed -i '' 's/^MAIL_PASSWORD=.*/MAIL_PASSWORD=/' .env 2>/dev/null || sed -i 's/^MAIL_PASSWORD=.*/MAIL_PASSWORD=/' .env
sed -i '' 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=/' .env 2>/dev/null || sed -i 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=/' .env

echo "✅ Configuration updated!"

# Clear Laravel config cache
echo "🔄 Clearing Laravel config cache..."
php artisan config:clear

echo ""
echo "✅ Email setup complete!"
echo ""
echo "📧 Mailpit Web Interface: http://127.0.0.1:8025"
echo "📬 SMTP Server: 127.0.0.1:1025"
echo ""
echo "Now when you request a password reset, emails will be sent to Mailpit!"
echo "View them at: http://127.0.0.1:8025"


