<?php
/**
 * GeoDirectory Events dummy data for recurring events.
 *
 * @since 2.0.0
 * @package GeoDirectory_Event_Manager
 */

require_once __DIR__ . '/standard_events.php';

// Recurring options
$recurring_options = array();
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+30 days")),
    'end_date' => '',
    'start_time' => '10:00',
    'end_time' => '12:00',
    'all_day' => '',
    'duration_x' => '1',
    'repeat_type' => 'month',
    'repeat_x' => '1',
    'repeat_end_type' => '1',
    'max_repeat' => '',
    'repeat_end' => date_i18n('Y-m-d', strtotime("+4 months")),
    'recurring_dates' => '',
    'different_times' => '',
    'start_times' => '',
    'end_times' => '',
    'repeat_days' => array('6'),
    'repeat_weeks' => array('1', '3')
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+7 days")),
    'end_date' => '',
    'start_time' => '08:00',
    'end_time' => '21:00',
    'all_day' => '',
    'duration_x' => '1',
    'repeat_type' => 'year',
    'repeat_x' => '1',
    'repeat_end_type' => '0',
    'max_repeat' => '3',
    'repeat_end' => '',
    'recurring_dates' => '',
    'different_times' => '',
    'start_times' => '',
    'end_times' => '',
    'repeat_days' => '',
    'repeat_weeks' => ''
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+50 days")),
    'end_date' => '',
    'start_time' => '11:00',
    'end_time' => '15:00',
    'all_day' => '',
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+5 days")),
    'end_date' => '',
    'start_time' => '',
    'end_time' => '',
    'all_day' => '1',
    'duration_x' => '1',
    'repeat_type' => 'week',
    'repeat_x' => '1',
    'repeat_end_type' => '0',
    'max_repeat' => '7',
    'repeat_end' => '',
    'recurring_dates' => '',
    'different_times' => '',
    'start_times' => '',
    'end_times' => '',
    'repeat_days' => array('0'),
    'repeat_weeks' => ''
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+5 days")),
    'end_date' => '',
    'start_time' => '11:00',
    'end_time' => '15:00',
    'all_day' => '',
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+28 days")),
    'end_date' => '',
    'start_time' => '10:30',
    'end_time' => '12:30',
    'all_day' => '',
    'duration_x' => '1',
    'repeat_type' => 'day',
    'repeat_x' => '2',
    'repeat_end_type' => '0',
    'max_repeat' => '5',
    'repeat_end' => '',
    'recurring_dates' => '',
    'different_times' => '',
    'start_times' => '',
    'end_times' => '',
    'repeat_days' => '',
    'repeat_weeks' => ''
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+10 days")),
    'end_date' => '',
    'start_time' => '11:30',
    'end_time' => '15:00',
    'all_day' => '',
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+10 days")),
    'end_date' => '',
    'start_time' => '10:15',
    'end_time' => '12:15',
    'all_day' => '',
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+8 days")),
    'end_date' => '',
    'start_time' => '10:10',
    'end_time' => '12:10',
    'all_day' => '',
);
$recurring_options[] = array(
    'start_date' => date_i18n('Y-m-d', strtotime("+14 days")),
    'end_date' => '',
    'start_time' => '09:00',
    'end_time' => '16:00',
    'all_day' => '',
    'duration_x' => '1',
    'repeat_type' => 'custom',
    'repeat_x' => '',
    'repeat_end_type' => '0',
    'max_repeat' => '',
    'repeat_end' => '',
    'recurring_dates' => array(date_i18n('Y-m-d', strtotime("+6 days")), date_i18n('Y-m-d', strtotime("+9 days")), date_i18n('Y-m-d', strtotime("+13 days"))),
    'different_times' => '1',
    'start_times' => array('09:30', '10:00', '10:30'),
    'end_times' => array('16:30', '17:00', '17:30'),
    'repeat_days' => '',
    'repeat_weeks' => ''
);


if (!empty($dummy_posts)) {
    foreach ($dummy_posts as $key => $dummy_post) {
        $randomKey = array_rand($recurring_options);
        $dummy_post['event_dates'] = $recurring_options[$randomKey];
        if (isset($recurring_options[$randomKey]['repeat_weeks'])) {
            $dummy_post['recurring'] = 1;
        }
        $dummy_posts[$key] = $dummy_post;
    }
}


//print_r($dummy_posts);exit;
function geodir_event_extra_custom_fields_recurring_events( $fields, $post_type, $package_id ) {
    if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
        return $fields;
    }

    return $fields;
}