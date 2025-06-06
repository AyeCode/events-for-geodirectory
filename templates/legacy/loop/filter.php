<?php
/**
 * Select Sort
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/legacy/loop/filter.php.
 *
 * @var array   $event_types The event types array.
 * @var string  $event_type The current event type.
 * @var string  $current_url The current URL.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDir_Event_Manager
 * @version    2.1.0.0
 */

defined( 'ABSPATH' ) || exit;

$options = '';
foreach ( $event_types as $value => $label ) {
	$url = add_query_arg( array( 'etype' => $value ), $current_url );
	$url = apply_filters( 'geodir_event_type_filter_url', $url, $value );
	$options .= '<option ' . selected( $value, $event_type, false ) . ' value="' . esc_url( $url ) . '">' . $label . '</option>';
}
?>
<div class="geodir-event-filter">
<select name="etype" id="etype" class="geodir-select" onchange="javascript:window.location=this.value;"><?php echo $options;?></select>
</div>