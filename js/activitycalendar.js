cj(function() {
  cj('input[id^=activity_]').each(function(){
    var activity_id = cj(this).prop('id').replace('activity_', '');
    showhidecolorbox(activity_id);
  });

  /*if(!cj("#activitycalendar_activity_month").is( ':checked')) {
    cj('.crm-activity-extension-show_activity_from_month').hide();
  }
  cj('.crm-activity-extension-activities_activity_month').bind('click', function() {
    if(cj("#activities_activity_month").is( ':checked')) {
      cj('.crm-activity-extension-show_activity_from_month').show();
      cj('#show_activity_from_month').val('');
    } else {
      cj('.crm-activity-extension-show_activity_from_month').hide();
    }
  });*/
});

function updatecolor(label, color) {
  cj('input[name="'+label+'"]').val( color );
}

function showhidecolorbox(activity_id) {
  var n = "activitycolorid_" + activity_id;
  var m = "activity_" + activity_id;
  if(!cj("#"+m).is( ':checked')) {
    cj("#"+n).parents('.crm-section').hide();
  }
  else {
    cj("#"+n).parents('.crm-section').show();
  }
}
