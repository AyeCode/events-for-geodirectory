<?php
/**
 * Are you interested user.
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/widgets/ayi-user.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="col-auto p-0 m-0 mr-1 me-1 mb-1 <?php echo $class; ?>">
	<div class="item-avatar">
		<a href="<?php echo GeoDir_Event_AYI::geodir_ayi_get_user_profile_link($user->ID); ?>" title="<?php echo esc_attr( GeoDir_Event_AYI::geodir_ayi_member_name(GeoDir_Event_AYI::geodir_ayi_get_current_user_name($user))); ?>" data-toggle="tooltip"><?php echo get_avatar($user->ID, 40,'','',array('class'=>'rounded-circle')); ?></a>
	</div>
</div>