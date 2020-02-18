jQuery(function($) {

});

function geodir_event_get_calendar($container, params) {
    var $calendar,data;
    $calendar = jQuery('.geodir_event_calendar', $container);
    $calendar.addClass('geodir-calendar-loading');
    data = 'action=geodir_ajax_calendar' + params + geodir_event_params.calendar_params;
    jQuery.ajax({
        type: "GET",
        url: geodir_params.ajax_url,
        data: data,
        success: function(html) {
            $calendar.removeClass('geodir-calendar-loading').html(html);
        }
    });
}