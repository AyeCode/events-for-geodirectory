<?php
/**
 * Are you interested?
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/legacy/widgets/ayi.php.
 *
 * @var string  $cur_user_interested If the current user is interested.
 * @var bool    $buttons If the buttons should be show.
 * @var string  $gde
 * @var int     $count The interested count.
 * @var int     $no_of_users The number of users to show (default 10).
 * @var object  $post The post object.
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

if ( $buttons ) {
	?>
	<div class="geodir-ayi-buttons">
		<?php
		if ( $cur_user_interested ) {
			if ( $cur_user_interested == 'event_rsvp_yes' ) {
				?>
				<p class="geodir-ayi-cur-user-interested">
					<?php echo __('You Replied:', 'geodirevents'); ?>
					<strong><?php echo __("I'm in!", 'geodirevents'); ?></strong>
					<a href="#" data-action="remove" data-type="event_rsvp_yes" data-gde="<?php echo esc_attr( $gde ); ?>" data-postid="<?php echo absint( $post->ID ); ?>" class="geodir-ayi-btn-rsvp"><?php echo __('Cancel', 'geodirevents'); ?></a>
				</p>
				<?php
			} elseif ( $cur_user_interested == 'event_rsvp_maybe' ) {
				?>
				<p class="event-cur-user-interested">
					<?php echo __('You Replied:', 'geodirevents'); ?>
					<strong><?php echo __('Sounds Cool', 'geodirevents'); ?></strong>
					<a href="#" data-action="remove" data-type="event_rsvp_maybe" data-gde="<?php echo esc_attr( $gde ); ?>" data-postid="<?php echo absint( $post->ID ); ?>" class="geodir-ayi-btn-rsvp"><?php echo __('Cancel', 'geodirevents'); ?></a>
				</p>
				<?php
			}
		} else { ?>
			<ul class="geodir-ayi-inline-layout">
				<li>
					<a href="#" data-action="add" data-type="event_rsvp_yes" data-gde="<?php echo esc_attr( $gde ); ?>" data-postid="<?php echo absint( $post->ID ); ?>" class="geodir-ayi-btn geodir-ayi-btn-small geodir-ayi-btn-full geodir-ayi-btn-rsvp geodir-ayi-btn-rsvp-yes button button-primary"><?php echo __("I'm in!", 'geodirevents'); ?></a>
				</li>
				<li>
					<a href="#" data-action="add" data-type="event_rsvp_maybe" data-gde="<?php echo esc_attr( $gde ); ?>" data-postid="<?php echo absint( $post->ID ); ?>" class="geodir-ayi-btn geodir-ayi-btn-small geodir-ayi-btn-full geodir-ayi-btn-rsvp geodir-ayi-btn-rsvp-maybe button button-secondary"><?php echo __("Sounds Cool", 'geodirevents'); ?></a>
				</li>
			</ul>
		<?php } ?>
	</div>
<?php } ?>
<div class="geodir-ayi-wid geodir-ayi-whois-in">
	<h3 class="geodir-ayi-section-title"><?php echo __('Who\'s in?', 'geodirevents'); ?>
		<span><?php echo wp_sprintf( _n( '1 response', '%s responses', $count['yes'], 'geodirevents'), $count['yes'] ); ?></span></h3>
	<?php if ($count['yes'] > 0) { ?>
		<ul class="geodir-ayi-item-list geodir-ayi-list-users">
			<?php GeoDir_Event_AYI::geodir_ayi_rsvp_users_for_a_post( $post->ID, "event_rsvp_yes", $no_of_users, $gde ); ?>
		</ul>
	<?php } else { ?>
		<p class="geodir-ayi-noone">
			<?php echo __('No one is in yet.', 'geodirevents'); ?>
		</p>
	<?php } ?>
</div>
<div class="geodir-ayi-wid">
	<h3 class="geodir-ayi-section-title"><?php echo __('Sounds Cool', 'geodirevents'); ?>
		<span><?php echo wp_sprintf(_n('1 response', '%s responses', $count['maybe'], 'geodirevents'), $count['maybe'] ); ?></span></h3>
	<?php if ($count['maybe'] > 0) { ?>
		<ul class="geodir-ayi-item-list geodir-ayi-list-users">
			<?php GeoDir_Event_AYI::geodir_ayi_rsvp_users_for_a_post( $post->ID, "event_rsvp_maybe", $no_of_users, $gde ); ?>
		</ul>
	<?php } else { ?>
		<p class="geodir-ayi-noone">
			<?php echo __('No one is in yet.', 'geodirevents'); ?>
		</p>
	<?php } ?>
</div>