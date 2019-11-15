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

### Step 1: configure your webhooks at Mailgun

Log in to mailgun's website and find the Webhooks page (*not* the "legacy webhooks").

For **Permanent Failure** and **Temporary Failure** events, enter the webhook
URL for your site, which will look like:

- Drupal 7: `https://example.com/civicrm/mailgunny/webhook`
- Wordpress: `https://example.com/wp-admin/admin.php?page=CiviCRM&q=civicrm/mailgunny/webhook`
- Joomla: `https://example.com/index.php?option=com_civicrm&task=civicrm/mailgunny/webhook`

### Step 2: enter your Mailgun "HTTP webhook signing key" in your CiviCRM Mailgunny settings page

Nb. the "HTTP webhook signing key" key is *not* your Mailgun password (nor your domain's SMTP
password). You can find it on the Webhooks page.

**CiviCRM 5.8+ users can** visit the settings page at:

- Drupal 7: `https://example.com/civicrm/mailgunny/settings`
- Wordpress: `https://example.com/wp-admin/admin.php?page=CiviCRM&q=civicrm/mailgunny/settings`
- Joomla: `https://example.com/index.php?option=com_civicrm&task=civicrm/mailgunny/settings`

Put it in the box and press Save.

**CiviCRM 5.0 - 5.7 users** must instead define a constant in their `civicrm.settings.php` file like this:

```php
define('MAILGUN_API_KEY', 'xxxxxxxxxxxxxxxxx');
```

Right, you're all set.


## Hey what's with the name?

Gunny is a strong coarse material. Such that you might make sacks out of. Like
post/mail sacks. And this is about mailings. So Mailgunny. And, what we're
**not** interested in at all is guns, so it's a deliberate subversion of
Mailgun's name. After all, who wants to be shot by email?
