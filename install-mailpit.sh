#!/bin/bash

echo "🚀 Installing Mailpit for automatic email testing..."

# Create local bin directory if it doesn't exist
mkdir -p ~/.local/bin
export PATH="$HOME/.local/bin:$PATH"

# Detect OS architecture
ARCH=$(uname -m)
OS=$(uname -s)

if [ "$OS" = "Darwin" ]; then
    if [ "$ARCH" = "arm64" ]; then
        BINARY="mailpit-darwin-arm64"
    else
        BINARY="mailpit-darwin-amd64"
    fi
else
    echo "❌ This script is for macOS. Please install Mailpit manually."
    exit 1
fi

# Download Mailpit
echo "📥 Downloading Mailpit..."
LATEST_VERSION=$(curl -s https://api.github.com/repos/axllent/mailpit/releases/latest | grep tag_name | cut -d '"' -f 4)
DOWNLOAD_URL="https://github.com/axllent/mailpit/releases/download/${LATEST_VERSION}/${BINARY}"

curl -L -o ~/.local/bin/mailpit "$DOWNLOAD_URL"
chmod +x ~/.local/bin/mailpit

if [ -f ~/.local/bin/mailpit ]; then
    echo "✅ Mailpit installed successfully!"
    echo ""
    echo "🚀 Starting Mailpit..."
    ~/.local/bin/mailpit > /dev/null 2>&1 &
    sleep 2
    
    if curl -s http://127.0.0.1:8025 > /dev/null 2>&1; then
        echo "✅ Mailpit is running!"
        echo ""
        echo "📧 Mailpit Web Interface: http://127.0.0.1:8025"
        echo "📬 SMTP Server: 127.0.0.1:1025"
        echo ""
        echo "✅ Email is now configured automatically!"
        echo "Password reset emails will be sent to Mailpit."
    else
        echo "⚠️  Mailpit installed but not running. Start it with: ~/.local/bin/mailpit"
    fi
else
    echo "❌ Installation failed. Please install manually from: https://github.com/axllent/mailpit"
fi


