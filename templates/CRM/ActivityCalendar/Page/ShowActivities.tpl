<div id="calendar"></div>
{crmScript ext=info.dmwilson.activitycalendar file=js/fullcalendar.js}
{crmStyle  ext=info.dmwilson.activitycalendar file=css/fullcalendar.css}
{crmStyle  ext=info.dmwilson.activitycalendar file=css/civicrm_activities.css}

{literal}
<script type="text/javascript">
 if (typeof(jQuery) != 'function')
     var jQuery = cj; 
 cj( function( ) {
    buildCalendar( );
  });
 function buildCalendar( ) {
  var activities_data = {/literal}{$civicrm_activities}{literal};
  var jsonStr = JSON.stringify(activities_data);
  jQuery("#calendar").fullCalendar(activities_data);		
 }
</script>
{/literal}
