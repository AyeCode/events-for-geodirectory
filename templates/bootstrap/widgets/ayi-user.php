<?php
/**
 * Are you interested user.
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

?>
<div class="col-auto p-0 m-0 mr-1 mb-1 <?php echo $class; ?>">
	<div class="item-avatar">
		<a href="<?php echo GeoDir_Event_AYI::geodir_ayi_get_user_profile_link($user->ID); ?>" title="<?php echo esc_attr( GeoDir_Event_AYI::geodir_ayi_member_name(GeoDir_Event_AYI::geodir_ayi_get_current_user_name($user))); ?>" data-toggle="tooltip"><?php echo get_avatar($user->ID, 40,'','',array('class'=>'rounded-circle')); ?></a>
	</div>
</div>