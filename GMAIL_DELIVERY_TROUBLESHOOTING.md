# 🚨 Gmail Email Delivery Troubleshooting Guide

## Problem Description

**Symptom:** SMTP2Go dashboard shows emails as "successfully sent", but emails are **not appearing** in Gmail inbox or spam folder.

**Environment:**
- Hosted on: DigitalOcean App Platform
- Email Service: SMTP2Go
- Sender Email: `helmandashelle.dacuma@sccpag.edu.ph`
- Recipient: Gmail accounts

## Root Causes & Solutions

### 1. SPF/DKIM/DMARC Misconfiguration (MOST LIKELY)

**Problem:** The domain `sccpag.edu.ph` may not have proper DNS records allowing SMTP2Go to send emails on its behalf. Gmail silently drops emails that fail SPF/DKIM checks.

**Solution:**

#### Step 1: Verify Sender Email in SMTP2Go
1. Log into SMTP2Go: https://app.smtp2go.com/
2. Go to **Settings** → **Verified Senders**
3. Verify that `helmandashelle.dacuma@sccpag.edu.ph` is listed and verified ✅

#### Step 2: Check SPF Record for sccpag.edu.ph
1. Use an SPF checker tool: https://mxtoolbox.com/spf.aspx
2. Enter domain: `sccpag.edu.ph`
3. Look for SPF record that includes SMTP2Go's sending servers

**Expected SPF Record:**
```
v=spf1 include:spf.smtp2go.com ~all
```

**If SPF record is missing or incorrect:**
- Contact the domain administrator for `sccpag.edu.ph`
- Request them to add SMTP2Go to the SPF record
- This requires DNS access to the domain

#### Step 3: Check DKIM Configuration
1. In SMTP2Go dashboard, go to **Settings** → **Domain Authentication**
2. Check if DKIM is configured for `sccpag.edu.ph`
3. If not configured, SMTP2Go may be signing with their own domain (less trusted by Gmail)

**Note:** For educational domains like `.edu.ph`, you may not have DNS access. In this case, consider using a custom domain or a Gmail address as the sender.

### 2. Domain Reputation Issues

**Problem:** Educational domains (`.edu.ph`) can have reputation issues, especially if:
- The domain has been used for spam before
- The domain is new or has low email volume
- Gmail has flagged the domain

**Solution:**
- **Option A:** Use a custom domain (e.g., `noreply@dmarsians.com`) with proper SPF/DKIM setup
- **Option B:** Use a Gmail address as sender (requires SMTP2Go to support Gmail OAuth)
- **Option C:** Warm up the domain by sending small batches over time

### 3. Bulk Sending Pattern (Triggering Gmail Filters)

**Problem:** Sending many emails at once can trigger Gmail's spam filters, causing silent drops.

**Solution:** 
- ✅ **IMPLEMENTED:** Rate limiting has been added to bulk sends (see `send_due_reminder.php`)
- Emails are now sent with delays between batches
- Consider reducing batch size if issues persist

### 4. SMTP2Go Suppression List

**Problem:** Recipient emails may be on SMTP2Go's suppression list (bounced, complained, unsubscribed).

**Solution:**
1. Log into SMTP2Go dashboard
2. Go to **Reports** → **Suppressions**
3. Check if recipient emails are listed
4. Remove from suppression list if needed

### 5. Gmail Silent Filtering

**Problem:** Gmail may be silently dropping emails before they reach spam folder due to:
- Content filtering
- Sender reputation
- Missing authentication headers

**Solution:**
- ✅ **IMPLEMENTED:** Improved email headers for better deliverability
- Use Gmail Postmaster Tools to check domain reputation: https://postmaster.google.com/
- Request recipients to check "All Mail" folder (not just Inbox/Spam)

## Immediate Action Steps

### Step 1: Test with Different Sender Email

**Quick Test:** Try sending from a Gmail address to isolate the issue.

1. In SMTP2Go, verify a Gmail address (e.g., `helmandacuma5@gmail.com`)
2. Temporarily change `SMTP2GO_SENDER_EMAIL` to the Gmail address
3. Send a test reminder
4. If Gmail address works → **Domain/DNS issue confirmed**
5. If Gmail address also fails → **SMTP2Go configuration issue**

### Step 2: Check SMTP2Go Activity Logs

1. Log into SMTP2Go: https://app.smtp2go.com/
2. Go to **Reports** → **Activity**
3. Find the sent emails
4. Check the status:
   - ✅ **Delivered** → Email reached recipient server (check spam)
   - ⚠️ **Bounced** → Recipient server rejected (check bounce reason)
   - ❌ **Failed** → SMTP2Go couldn't send (check error)

### Step 3: Use Gmail Postmaster Tools

1. Go to: https://postmaster.google.com/
2. Add domain: `sccpag.edu.ph` (requires domain verification)
3. Check:
   - **Spam Rate** (should be < 0.1%)
   - **IP Reputation**
   - **Domain Reputation**
   - **Authentication** (SPF/DKIM/DMARC)

### Step 4: Check Email Headers

Ask a recipient (if they receive the email) to:
1. Open the email in Gmail
2. Click the three dots → **Show original**
3. Check for:
   - `SPF: PASS`
   - `DKIM: PASS`
   - `DMARC: PASS`

If any show `FAIL` or `NONE`, that's the issue.

## Long-Term Solutions

### Option 1: Use Custom Domain (RECOMMENDED)

1. Register a domain (e.g., `dmarsians.com`)
2. Configure DNS:
   - Add SPF record: `v=spf1 include:spf.smtp2go.com ~all`
   - Configure DKIM in SMTP2Go
   - Add DMARC record: `v=DMARC1; p=none; rua=mailto:admin@dmarsians.com`
3. Verify domain in SMTP2Go
4. Update `SMTP2GO_SENDER_EMAIL` to use custom domain

### Option 2: Use SMTP2Go's Shared Domain

1. In SMTP2Go, use their shared sending domain
2. Sender will be something like: `noreply@mail.smtp2go.com`
3. Less professional but more reliable for delivery

### Option 3: Use Transactional Email Service

Consider switching to:
- **SendGrid** (better Gmail deliverability)
- **Mailgun** (excellent reputation)
- **Amazon SES** (requires AWS setup)

## Diagnostic Tools

### 1. SPF Checker
- https://mxtoolbox.com/spf.aspx
- Enter: `sccpag.edu.ph`

### 2. DKIM Checker
- https://mxtoolbox.com/dkim.aspx
- Enter domain and selector

### 3. DMARC Checker
- https://mxtoolbox.com/dmarc.aspx
- Enter: `sccpag.edu.ph`

### 4. Email Header Analyzer
- https://mxtoolbox.com/emailhealth/
- Paste email headers to analyze

### 5. Gmail Postmaster Tools
- https://postmaster.google.com/
- Requires domain verification

## Code Improvements Made

1. ✅ **Rate Limiting:** Added delays between bulk email sends
2. ✅ **Better Headers:** Improved email headers for deliverability
3. ✅ **Error Logging:** Enhanced logging for debugging
4. ✅ **Diagnostic Tool:** Created `diagnose_gmail_delivery.php` (see below)

## Next Steps

1. **Immediate:** Test with Gmail sender address to isolate issue
2. **Short-term:** Check SPF/DKIM/DMARC records for `sccpag.edu.ph`
3. **Long-term:** Consider using custom domain with proper DNS setup

## Contact Information

If you need to contact domain administrators:
- **Domain:** sccpag.edu.ph
- **Who to contact:** IT department of the educational institution
- **What to request:** Add SMTP2Go to SPF record and configure DKIM

---

**Last Updated:** January 25, 2026
**Status:** Active troubleshooting
