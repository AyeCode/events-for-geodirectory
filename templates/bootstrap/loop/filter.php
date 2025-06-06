<?php
/**
 * Select Sort
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/loop/filter.php.
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
 * @version    2.3.20
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = '';
$button_label = __( "Upcoming", "geodirevents" );
foreach ( $event_types as $value => $label ) {
	$url = add_query_arg( array( 'etype' => $value ), $current_url );
	$url = apply_filters( 'geodir_event_type_filter_url', $url, $value );
	$active = $event_type == $value ? 'active': '';
	if( $event_type == $value ){
		$button_label = $label;
	}
	$options .= '<a href="' . esc_url( $url ) . '" data-etype="' . esc_attr( $value ) . '" class="dropdown-item ' . esc_attr( $active ) . '" rel="nofollow">' . esc_attr( $label ) . '</a>'; 
}
?>
<div class="btn-group btn-group-sm geodir-event-filter geodir-loop-event-filter" role="group" aria-label="<?php esc_attr_e("Filter Events","geodirevents");?>">
	<div class="btn-group btn-group-sm" role="group">
		<button id="geodir-event-filter" type="button" class="btn btn-outline-primary rounded-right rounded-end" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo esc_html( $button_label );?> <i class="fas fa-sort"></i></button>
		<div class="dropdown-menu dropdown-caret-0 scrollbars-ios overflow-auto p-0 m-0 my-3" aria-labelledby="gd-list-view-select-grid" style="max-height: 50vh">
			<h6 class="dropdown-header"><?php echo esc_html__( "Filter Events", "geodirevents" ); ?></h6>
			<?php echo $options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
			<div class="dropdown-divider"></div>
			<a class="dropdown-item" href="<?php echo esc_url( remove_query_arg( 'etype' ) );?>" rel="nofollow"><?php echo esc_html__( "Default", "geodirectory" ); ?></a>
		</div>
	</div>
</div>