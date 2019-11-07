
<div class="crm-section">
  <p>To find this login to Mailgun, go to the webhooks page for your domain and it should be in the top right corner under "HTTP webhook signing key"</p>
  <div class="label">{$form.mailgun_api_key.label}</div>
  <div class="content">{$form.mailgun_api_key.html}</div>
  <div class="clear"></div>
</div>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<h2>Your webhook URL</h2>

<p>You need to copy and paste this to set up your webhooks on Mailgun's website. You should set up webhooks for:</p>
<ul>
  <li>Permanent Fail</li>
  <li>Temporary Fail</li>
</ul>

<p><a href="{crmURL
  p='civicrm/mailgunny/webhook'
  a=TRUE
  h=TRUE
  fe=TRUE
}" >{crmURL
  p='civicrm/mailgunny/webhook'
  a=TRUE
  h=TRUE
  fe=TRUE
}</a></p>

<p>Note: this URL is NOT a web page. If you click that link you should just see something like <code>{literal}{"error":"Expected JSON but didn't get it."}{/literal}</code> - that's correct. If you click this link and you get a normal looking web page, something is wrong.</p>
