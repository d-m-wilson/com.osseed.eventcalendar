<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Eventcalendar_Form_EventCalendarSettings extends CRM_Core_Form {
  private $_settingFilter = array('group' => 'activitycalendar');
  private $_submittedValues = array();
  private $_settings = array();

  function buildQuickForm() {
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/jscolor.js');
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/eventcalendar.js');

    $settings = $this->getFormSettings();
    $descriptions = array();
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        $add = 'add' . $setting['quick_form_type'];

        if ($name != 'activitycalendar_activity_types') {
          if ($add == 'addElement') {
            $this->$add($setting['html_type'], $name, ts($setting['title']),
              CRM_Utils_Array::value('html_attributes', $setting, array()));
          }
          else {
            $this->$add($name, ts($setting['title']));
          }
          $descriptions[$name] = $setting['description'];
        }
        else {
          //special handling for activity types; we construct these dynamically
          //and store as json
          $activityTypes = CRM_Core_PseudoConstant::activityType();
          foreach ($activityTypes as $id => $type) {
            $this->addElement('checkbox', "activitytype_{$id}", $type, NULL,
              array('onclick' => "showhidecolorbox('{$id}')", 'id' => "activity_{$id}"));
            $this->addElement('text', "activitycolor_{$id}", "Color",
              array(
                'onchange' => "updatecolor('activitycolor_{$id}', this.value);",
                'class' => 'color',
                'id' => "activitycolorid_{$id}",
                //'value'=> 'EXISTING VALUE?',
              ));
          }

          $this->assign('activityTypes', $activityTypes);
        }
      }
    }
    $this->assign('descriptions', $descriptions);

    $this->addButtons(array(
      array (
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      )
    ));
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
    }

    //Civi::log()->debug('getFormSettings', array('settings' => $settings));
    return $settings['values'];
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function saveSettings() {
    $settings = $this->getFormSettings();
    //Civi::log()->debug('saveSettings', array('_submitValues' => $this->_submitValues));

    //we extract activitytype_ and activitycolor_ settings and store as json
    $activityTypes = array();
    foreach ($this->_submittedValues as $f => $v) {
      if (strpos($f, 'activitytype_') !== FALSE) {
        $id = str_replace('activitytype_', '', $f);
        $activityTypes[] = array(
          'id' => $id,
          'color' => $this->_submittedValues["activitycolor_{$id}"],
        );
      }
    }
    $this->_submittedValues['activitycalendar_activity_types'] = json_encode($activityTypes);

    foreach ($settings as $settingName => $settingDate) {
      if ($settingDate['html_type'] === 'checkbox' &&
        empty($this->_submittedValues[$settingName])
      ) {
        $this->_submittedValues[$settingName] = 0;
      }
    }

    $values = array_intersect_key($this->_submittedValues, $settings);
    //Civi::log()->debug('saveSettings', array('values' => $values));
    civicrm_api3('setting', 'create', $values);
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
      if ($name == 'activitycalendar_activity_types') {
        // set activity type color
        foreach(json_decode($value, true) as $activityType) {
          $defaults['activitytype_'.$activityType['id']] = 1;
          $defaults['activitycolor_'.$activityType['id']] = $activityType['color'];
        }
      }
    }
    return $defaults;
  }
}
