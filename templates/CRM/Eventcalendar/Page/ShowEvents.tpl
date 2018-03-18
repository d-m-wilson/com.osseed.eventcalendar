<div id="calendar"></div>
{crmScript ext=com.osseed.eventcalendar file=js/fullcalendar.js}
{crmStyle ext=com.osseed.eventcalendar file=css/fullcalendar.css}
{crmStyle ext=com.osseed.eventcalendar file=css/civicrm_events.css}

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
