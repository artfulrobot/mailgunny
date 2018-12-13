<?php

use CRM_Mailgunny_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Mailgunny_Form_Settings extends CRM_Admin_Form_Setting {
  protected $_settings = [
    'mailgun_api_key' =>  CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
  ];
  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->assign('elementNames', array_keys($this->_settings));
  }
}
