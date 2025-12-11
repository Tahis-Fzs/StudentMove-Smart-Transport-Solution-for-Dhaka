# Quick Email Setup - Automatic Mode

## ✅ Current Status

Your system is **already configured** for automatic email! The `.env` file is set up to use Mailpit (local email testing server).

## 🚀 To Enable Real Email Sending (One Command)

Run this command in your terminal:

```bash
# Option 1: Install via Homebrew (if you have it)
brew install axllent/mailpit/mailpit && mailpit

# Option 2: Download directly
curl -L https://github.com/axllent/mailpit/releases/latest/download/mailpit-darwin-arm64 -o ~/mailpit && chmod +x ~/mailpit && ~/mailpit
```

Then **keep Mailpit running** in that terminal window.

## 📧 How It Works

1. **Mailpit is running** → Emails are sent automatically to Mailpit web interface
2. **Mailpit is NOT running** → Reset link appears directly on the page (automatic fallback)

## 🎯 View Your Emails

Once Mailpit is running, view emails at: **http://127.0.0.1:8025**

## ✨ Current Behavior

- ✅ **Automatic**: Reset links appear on page if Mailpit isn't running
- ✅ **Automatic**: Emails sent to Mailpit if it IS running  
- ✅ **No configuration needed**: Everything works out of the box!

## 🔄 To Start Mailpit Automatically

Add this to your `~/.zshrc` or `~/.bash_profile`:

```bash
# Auto-start Mailpit if not running
if ! curl -s http://127.0.0.1:8025 > /dev/null 2>&1; then
    mailpit > /dev/null 2>&1 &
fi
```

Then restart your terminal or run `source ~/.zshrc`


