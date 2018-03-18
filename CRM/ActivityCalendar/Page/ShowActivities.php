<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';

class CRM_ActivityCalendar_Page_ShowActivities extends CRM_Core_Page {

  function run() {
    CRM_Core_Resources::singleton()->addScriptFile('info.dmwilson.activitycalendar', 'js/fullcalendar.js');
    CRM_Core_Resources::singleton()->addStyleFile('info.dmwilson.activitycalendar', 'css/civicrm_activities.css');
    CRM_Core_Resources::singleton()->addStyleFile('info.dmwilson.activitycalendar', 'css/fullcalendar.css');

    $config = CRM_Core_Config::singleton();

    //get settings
    $settings = $this->_activityCalendar_getSettings();

    //set title from settings; allow empty value so we don't duplicate titles
    CRM_Utils_System::setTitle(ts($settings['calendar_title']));

    $whereCondition = '';
    $activityTypes = $settings['activity_types'];

    if(!empty($activityTypes)) {
      $activityTypesList = implode(',', array_keys($activityTypes));
      $whereCondition .= " AND civicrm_activity.activity_type_id in ({$activityTypesList})";
    }
    else {
      $whereCondition .= ' AND civicrm_activity.activity_type_id in (0)';
    }

    //Show/Hide Past Activities
    $currentDate = date("Y-m-d h:i:s", time());
    if (empty($settings['activity_past'])) {
      $whereCondition .= " AND civicrm_activity.activity_date_time > '" .$currentDate . "'";
    }

    // Show activities according to number of next months
    if(!empty($settings['activity_from_month'])) {
      $monthActivities = $settings['activity_from_month'];
      $monthActivitiesDate = date("Y-m-d h:i:s",
        strtotime(date("Y-m-d h:i:s", strtotime($currentDate))."+" . $monthActivities . " month"));
      $whereCondition .= " AND civicrm_activity.activity_date_time < '" .$monthActivitiesDate . "'";
    }

    $query = "
      SELECT `id`, IF(`subject` IS NULL, 'No title', IF(`subject` = 'NULL', 'No title', `subject`)) title, `activity_date_time` start, `activity_type_id` activity_type
      FROM `civicrm_activity`
      WHERE civicrm_activity.is_deleted = 0 AND civicrm_activity.activity_date_time > DATE_ADD(now(), interval -3 month)
    ";

    $query .= $whereCondition;
    $activities['activities'] = array();

    $dao = CRM_Core_DAO::executeQuery($query);
    $activityCalendarParams = array ('title' => 'title', 'start' => 'start', 'url' => 'url');

    if(!empty($settings['activity_end_date'])) {
      $activityCalendarParams['end'] = 'end';
    }

    while ($dao->fetch()) {
      $activityData = array();

      $dao->url = html_entity_decode(CRM_Utils_System::url('civicrm/activity/view', 'id='.$dao->id));
      foreach ($activityCalendarParams as $k) {
        $activityData[$k] = $dao->$k;

        if(!empty($activityTypes)) {
          $activityData['backgroundColor'] = "#{$activityTypes[$dao->activity_type]}";
        }
      }
      $activities['events'][] = $activityData;
	  
    }
	
    //Civi::log()->debug('ActivityCalendar run', array('activities' => $activities));

    $activities['header']['left'] = 'prev,next today';
    $activities['header']['center'] = 'title';
    $activities['header']['right'] = 'month,basicWeek,basicDay';

    //send Activities array to calendar.
    $this->assign('civicrm_activities', json_encode($activities));
    parent::run();
  }

  /*
   * retrieve and reconstruct extension settings
   */
  function _activityCalendar_getSettings() {
    $settings = array(
      'calendar_title' => Civi::settings()->get('activitycalendar_calendar_title'),
      'activity_past' => Civi::settings()->get('activitycalendar_activity_past'),
      'activity_end_date' => Civi::settings()->get('activitycalendar_activity_end_date'),
      'activity_month' => Civi::settings()->get('activitycalendar_activity_month'),
      'activity_from_month' => Civi::settings()->get('activitycalendar_activity_from_month'),
    );

    $activityTypes = Civi::settings()->get('activitycalendar_activity_types');
    $activityTypes = json_decode($activityTypes);
    foreach ($activityTypes as $activityType) {
      $settings['activity_types'][$activityType->id] = $activityType->color;
    }

    /*Civi::log()->debug('_activityCalendar_getSettings', array(
      'activityTypes' => $activityTypes,
      'settings' => $settings,
    ));*/

    return $settings;
  }
}
