<?php
/**
 * Are you interested?
 *
 * @ver 1.0.0
 *
 * @var string  $cur_user_interested If the current user is interested.
 * @var bool    $buttons If the buttons should be show.
 * @var string  $gde
 * @var int     $count The interested count.
 * @var int     $no_of_users The number of users to show (default 10).
 * @var object  $post The post object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $buttons ) {
	?>
	<div class="geodir-ayi-buttons">
		<?php
		if ( $cur_user_interested ) {
			if ( $cur_user_interested == 'event_rsvp_yes' ) {
				echo aui()->alert(array(
						'type'=> 'success',
						'content'=> sprintf(__('You Replied: <b>Going!</b> %s', 'geodirevents'),'<a href="#" data-action="remove" data-type="event_rsvp_yes" data-gde="'.$gde.'" data-postid="'.$post->ID.'" class="geodir-ayi-btn-rsvp">'. __('Cancel', 'geodirevents').'</a>')
					)
				);

			} elseif ( $cur_user_interested == 'event_rsvp_maybe' ) {
				echo aui()->alert(array(
						'type'=> 'warning',
						'content'=> sprintf(__('You Replied: <b>Interested!</b> %s', 'geodirevents'),'<a href="#" data-action="remove" data-type="event_rsvp_maybe" data-gde="'.$gde.'" data-postid="'.$post->ID.'" class="geodir-ayi-btn-rsvp">'. __('Cancel', 'geodirevents').'</a>')
					)
				);
			}
		} else {

			echo aui()->button(
				array(
					'type'       => 'a',
					'class'      =>  "geodir-ayi-btn-rsvp geodir-ayi-btn-rsvp-yes btn btn-sm btn-primary mb-2",
					'content'    => __("Going", 'geodirevents'),
					'icon'      => !empty($action['icon']) ? $action['icon'] : '',
					'href'       => '#going',
					'extra_attributes'  =>  array(
						'data-action'   =>  'add',
						'data-type'   =>  'event_rsvp_yes',
						'data-gde'   =>  esc_attr($gde),
						'data-postid'   =>  absint($post->ID),
					)
				)
			);
			echo aui()->button(
				array(
					'type'       => 'a',
					'class'      =>  "geodir-ayi-btn-rsvp geodir-ayi-btn-rsvp-maybe btn btn-sm btn-outline-primary mb-2",
					'content'    => __("Interested", 'geodirevents'),
					'icon'      => !empty($action['icon']) ? $action['icon'] : '',
					'href'       => '#interested',
					'extra_attributes'  =>  array(
						'data-action'   =>  'add',
						'data-type'   =>  'event_rsvp_maybe',
						'data-gde'   =>  esc_attr($gde),
						'data-postid'   =>  absint($post->ID),
					)
				)
			);

			  } ?>
	</div>
<?php } ?>
<div class="geodir-ayi-wid geodir-ayi-whois-in">
	<div class="d-flex w-100 justify-content-between">
		<h5 class="mb-1 h5"><?php echo __('Going', 'geodirevents'); ?></h5>
		<small class="text-muted"><?php echo wp_sprintf( _n( '1 response', '%s responses', $count['yes'], 'geodirevents'), $count['yes'] ); ?></small>
	</div>
	<?php if ($count['yes'] > 0) { ?>
		<div class="geodir-ayi-item-list geodir-ayi-list-users row p-0 m-0">
			<?php GeoDir_Event_AYI::geodir_ayi_rsvp_users_for_a_post( $post->ID, "event_rsvp_yes", $no_of_users, $gde ); ?>
		</div>
	<?php } else { ?>
		<p class="geodir-ayi-noone  text-muted mb-2">
			<?php echo __('No responses yet.', 'geodirevents'); ?>
		</p>
	<?php } ?>
</div>
<div class="geodir-ayi-wid">
	<div class="d-flex w-100 justify-content-between">
		<h5 class="mb-1 h5"><?php echo __('Interested', 'geodirevents'); ?></h5>
		<small class="text-muted"><?php echo wp_sprintf(_n('1 response', '%s responses', $count['maybe'], 'geodirevents'), $count['maybe'] ); ?></small>
	</div>
	<?php if ($count['maybe'] > 0) { ?>
		<div class="geodir-ayi-item-list geodir-ayi-list-users row p-0 m-0">
			<?php GeoDir_Event_AYI::geodir_ayi_rsvp_users_for_a_post( $post->ID, "event_rsvp_maybe", $no_of_users, $gde ); ?>
		</div>
	<?php } else { ?>
		<p class="geodir-ayi-noone text-muted ">
			<?php echo __('No responses yet..', 'geodirevents'); ?>
		</p>
	<?php } ?>
</div>