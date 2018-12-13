<?php
use CRM_Mailgunny_ExtensionUtil as E;

class CRM_Mailgunny_Page_Webhook extends CRM_Core_Page {
  public function run() {
    try {
      $event = $this->validateInput(file_get_contents('php://input'));
      $this->processEvent($event);
      Civi::log()->info("Mailgun Webhook successfully processed");
    }
    catch (CRM_Mailgunny_WebhookRejectedException $e) {
      Civi::log()->notice("Mailgun Webhook ignored (returning 406)", ['message' => $e->getMessage()]);
      header("$_SERVER[SERVER_PROTOCOL] 406 " . $e->getMessage());
      echo json_encode(['error' => $e->getMessage()]);
    }
    catch (\Exception $e) {
      Civi::log()->notice("Mailgun Webhook fatal (returning 500)", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      header("$_SERVER[SERVER_PROTOCOL] 500");
    }
     CRM_Utils_System::civiExit();
  }
  /**
   * Check input and return the event data if all ok.
   *
   * @param string raw input from POST body (json)
   * @return StdClass
   */
  public function validateInput($input) {
    Civi::log()->info("Mailgun Webhook received. Raw input stored as context.", ['raw' => $input]);
    $input = json_decode($input);
    if (!$input) {
      throw new CRM_Mailgunny_WebhookRejectedException("Expected JSON but didn't get it.");
    }
    $sig = $input->signature->signature ?? 'MISSING SIGNATURE';

    $timestamp = ($input->signature->timestamp ?? 0);
    /*
    // Ensure webhooks recieved promptly - disable for testing.
    if (abs(time() - $timestamp) > 15) {
      throw new CRM_Mailgunny_WebhookRejectedException("Event too old.");
    }
    */
    $_ = $timestamp . ($input->signature->token ?? '');
    $secret = $this->getApiKey();
    if ($sig !==  hash_hmac('sha256', $_, $secret)) {
      throw new CRM_Mailgunny_WebhookRejectedException("Invalid signature");
    }
    if (empty($input->{'event-data'})) {
      throw new CRM_Mailgunny_WebhookRejectedException("Missing event-data");
    }
    // OK, looks valid.
    return $input->{'event-data'};
  }

  /**
   * Get API key from Mailgun account.
   *
   * @return string
   */
  public function getApiKey() {
    return Civi::settings()->get('mailgun_api_key');
  }

  /**
   * Actually process the data.
   */
  public function processEvent($event) {
    switch ($event->event){
    case 'failed':
      if ($event->severity === 'permanent') {
        $this->processPermanentBounce($event);
      }
      elseif ($event->severity === 'temporary') {
        $this->processTemporaryBounce($event);
      }
      echo '{"success": 1}';
      break;

    case 'accepted':
    case 'rejected':
    case 'delivered':
    case 'opened':
    case 'closed':
    case 'clicked':
    case 'unsubscribed':
    case 'complained':
    case 'stored':
      throw new CRM_Mailgunny_WebhookRejectedException("$event->event is not handled by this webhook.");

    default:
      throw new CRM_Mailgunny_WebhookRejectedException("Unrecognised webhook event type is not handled by this webhook.");
    }
  }

  public function processPermanentBounce($event) {
    $this->processCommonBounce($event, 'Invalid');
  }
  public function processTemporaryBounce($event) {
    $this->processCommonBounce($event, 'Syntax');
  }
  public function processCommonBounce($event, $type) {
    Civi::log()->info("Mailgun Webhook processing bounce: $type");
    // Ideally we would have access to 'X-CiviMail-Bounce' but I don't think we do.
    $bounce_params = $this->extractVerpData($event);
    if (!$bounce_params) {
      throw new CRM_Mailgunny_WebhookRejectedException("Cannot find VERP data necessary to process bounce.");
    }
    $bounce_params['bounce_type_id'] = $this->getCiviBounceTypeId($type);
    $bounce_params['bounce_reason'] = ($event->{'delivery-status'}->description ?? '')
      . " "
      . ($event->{'delivery-status'}->message ?? '')
      . " Mailgun Event Id: " . ($event->id ?? '');
    $bounced = CRM_Mailing_Event_BAO_Bounce::create($bounce_params);
  }
  /**
   * Extract data from verp data if we can.
   *
   * @param string $data e.g. 'b.22.23.1bc42342342@example.com'
   * @return array with keys: job_id, event_queue_id, hash
   */
  public function extractVerpData($event) {

    if (!empty($event->{'user-variables'}->{'civimail-bounce'})) {
      // Great, we found the header we added in our hook_civicrm_alterMailParams.
      $data = $event->{'user-variables'}->{'civimail-bounce'};
    }
    elseif (!empty($event->envelope->sender)) {
      // Hmmm. See if the envelope sender has anything useful in it.
      $data = $event->envelope->sender;
    }

    // Credit goes to https://github.com/mecachisenros for the verp parsing:
    $verp_separator = Civi::settings()->get('verpSeparator');
		$localpart = CRM_Core_BAO_MailSettings::defaultLocalpart();
    $parts = explode($verp_separator, substr(substr($data, 0, strpos($data, '@')), strlen($localpart) + 2));

    $verp_items = (count($parts) === 3)
      ? array_combine(['job_id', 'event_queue_id', 'hash'], $parts)
      : [];

    return $verp_items;
  }

  /**
   * Get CiviCRM bounce type.
   *
   * @param string Name of bounce type, e.g. Invalid|Syntax|Spam|Relay|Quota|Loop|Inactive|Host|Dns|Away|AOL
   * @return int Bounce type ID
   */
  protected function getCiviBounceTypeId($name) {
    $bounce_type = new CRM_Mailing_DAO_BounceType();
    $bounce_type->name = $name;
    $bounce_type->find(TRUE);
    return $bounce_type->id;
  }
}
