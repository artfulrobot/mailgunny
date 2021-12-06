# Mailgunny - Mailgun bounce processing.

Mailgun is one of many emailing services (e.g. SMTP relays). While many services
offer to send all your bounced email to a particular email address (e.g. so
CiviCRM can process bounces), Mailgun does not.

However, Mailgun can be configured to send bounce information directly to
CiviCRM using webhooks, enabling the normal CiviMail mailing reports.

This extension provides this functionality.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM 5.x (tested with 5.8.1)

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl mailgunny@https://github.com/artfulrobot/mailgunny/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/artfulrobot/mailgunny.git
cv en mailgunny
```

## Usage

### Step 0: Log in to Civi and Mailgun in separate tabs/browsers

In CiviCRM, visit the settings page at: **Administer » CiviMail » Mailgunny Settings** (path `civicrm/mailgunny/settings`).

### Step 1: configure your webhooks at Mailgun

Log in to Mailgun's website and find the Webhooks page (*not* the "legacy webhooks"). As of Nov 2021, this is under **Sending » Webhooks** in the side panel.

For **Permanent Failure** and **Temporary Failure** events, enter the webhook URL for your site, which **you can copy from the Mailgunny Settings page** and will look like:

- Drupal 7: `https://example.com/civicrm/mailgunny/webhook`
- Wordpress: `https://example.com/?page=CiviCRM&q=civicrm/mailgunny/webhook`
- Joomla: `https://example.com/index.php?option=com_civicrm&task=civicrm/mailgunny/webhook`

### Step 2: enter your Mailgun "HTTP webhook signing key" in your CiviCRM Mailgunny settings page

Nb. the "HTTP webhook signing key" key is *not* your Mailgun password (nor your domain's SMTP password). You can find it on the Webhooks page.

Put it in the box and press Save.

If you don't want your API key exposed here, you can put it in a constant, e.g. in `civicrm.settings.php`:

```php
define('MAILGUN_API_KEY', 'xxxxxxxxxxxxxxxxx');
```
## Hey what's with the name?

Gunny is a strong coarse material. Such that you might make sacks out of. Like
post/mail sacks. And this is about mailings. So Mailgunny. And, what we're
**not** interested in at all is guns, so it's a deliberate subversion of
Mailgun's name. After all, who wants to be shot by email?

## DKIM, SPF and sub domains

Mailgun would have you set up a subdomain, e.g. email.example.org and use that for your mailings. This is OK but it does result in emails showing up as "via: email.example.org" on some mail clients, which is confusing cruft.

After a long support thread Mailgunny now offers a feature so that you can achieve the following holy grail:

- `Return-Path:` header using the subdomain (e.g. email.example.org).

   - ✔ Good: delayed bounces will be handled by Mailgun (and fed back to Civi)

   - ✔ Good: your dedicated subdomain SPF record handles SPF authentication for this subdomain.

- `From:` header using a real email address in your main domain, e.g. wilma@example.org

   - ✔ Good: your email comes from a normal address

- `DKIM-Signature:` header uses the main domain (`example.org`) for the signature, and there's no `Sender:` header.

   - ✔ Good: gets rid of 'via...' and looks like a proper, normal email.

You can achieve this by:

1. 'Verifying' your root domain (example.org) with Mailgun.

   - Add a DKIM public key
   - Add `include:mailgun.org` into your domain's SPF. (Yes you apparently have to do this even though we won't be using SPF on the root domain)
   - **Do NOT** change the MX records to Mailgun for your root domain. That would be very bad (as in Mailgun would receive *all* your inbound email...)
   - You do not need to add 'tracking' records.

2. Next, verify your **subdomain** (e.g. may be _email_.example.org)

   - Add a DKIM public key for the subdomain.
   - Add an SPF record for the subdomain
   - Add the MX records to Mailgun for your subdomain.
   - You do not need to add 'tracking' records.

3. In CiviCRM, visit **Administer » CiviMail » Mailgunny Settings** and tick the box about the "Mailgun native send header"

**Note:** If you did (2) before (1), you'll need to contact Mailgun or [use their API](https://documentation.mailgun.com/en/latest/api-domains.html#domains) and explain what you want. (By the way, this took me 13 emails...).

Testing: Unfortunately, you can't properly test this by sending a test email from the CiviCRM SMTP settings page, as that does not call the hook that this extension uses. The easiest way is to install the [Test-Send Message Templates](https://lab.civicrm.org/extensions/msgtpltester) extension, and send yourself an email that way. Or maybe just send an email by adding an Email activity.

In the headers, if successful, you should see the above headers, plus one called `x-mailgun-native-send: true`.

