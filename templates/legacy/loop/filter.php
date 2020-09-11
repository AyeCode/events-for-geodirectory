<?php
/**
 * Select Sort
 *
 * @ver 1.0.0
 *
 * @var array   $event_types The event types array.
 * @var string  $event_type The current event type.
 * @var string  $current_url The current URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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