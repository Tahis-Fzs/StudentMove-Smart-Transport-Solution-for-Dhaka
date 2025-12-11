# Install Mailpit for Secure Email Testing

## Why Mailpit?

For **security and privacy**, password reset links are **only sent via email** - they are never displayed on the page. Mailpit allows you to test emails locally without configuring Gmail.

## Quick Install (macOS)

### Option 1: Using Homebrew (Recommended)

```bash
brew tap axllent/mailpit
brew install mailpit
mailpit
```

### Option 2: Direct Download

1. Go to: https://github.com/axllent/mailpit/releases/latest
2. Download: `mailpit-darwin-arm64` (for Apple Silicon) or `mailpit-darwin-amd64` (for Intel)
3. Make it executable:
   ```bash
   chmod +x ~/Downloads/mailpit-darwin-arm64
   mv ~/Downloads/mailpit-darwin-arm64 ~/mailpit
   ```
4. Start Mailpit:
   ```bash
   ~/mailpit
   ```

## Start Mailpit

After installation, start Mailpit in a terminal:

```bash
mailpit
```

Keep this terminal window open while testing.

## View Emails

Open in your browser: **http://127.0.0.1:8025**

## Your .env is Already Configured!

Your `.env` file is already set up for Mailpit:
- `MAIL_HOST=127.0.0.1`
- `MAIL_PORT=1025`

Just start Mailpit and password reset emails will work automatically!

## Security Note

✅ **Secure**: Reset links are only sent via email  
✅ **Private**: Email addresses are never exposed  
✅ **Safe**: Links are never shown on the page


