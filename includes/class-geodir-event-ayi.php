<?php
/**
 * Event AYI class
 *
 *
 * @link       https://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    GeoDir_Event_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Event_AYI class
 *
 * @class       GeoDir_Event_AYI
 * @version     2.0.0
 * @package     GeoDir_Event_Manager/Classes
 * @category    Class
 */
class GeoDir_Event_AYI {

    public function __construct() {
	}

	public static function init() {
		/*
		add_action( 'geodir_event_ayi_interested_updated', array( __CLASS__, 'update_interested_count' ), 10, 5 );
		add_action( 'wp_insert_post', array( __CLASS__, 'save_ayi_data' ), 10, 3 );
		add_action( 'before_delete_post', array( __CLASS__, 'remove_ayi_data' ) );
		add_action( 'wp_trash_post',      array( __CLASS__, 'remove_ayi_data' ) );
		*/
	}

	public static function update_interested_count( $post_id, $user_id, $type, $add_or_remove, $gde ) {
		global $wpdb, $plugin_prefix;

		$table = geodir_db_cpt_table( get_post_type( $post_id ) );

		$count = self::count_interested( $post_id, $gde );

		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $table . "'" ) == $table ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET rsvp_count = %d WHERE post_id = %d", array( $count['total'], $post_id ) ) );
		}
	}

	public static function count_interested( $post_id, $gde = false ) {
		$yes_users = get_post_meta( $post_id, 'event_rsvp_yes', true );
		$maybe_users = get_post_meta( $post_id, 'event_rsvp_maybe', true );

		if ( ! empty( $gde ) ) {
			$yes_users = isset( $yes_users[$gde] ) ? $yes_users[ $gde ] : false;
			$maybe_users = isset( $maybe_users[$gde] ) ? $maybe_users[ $gde ] : false;
		}

		$yes_count = 0;
		$maybe_count = 0;
		if ( $yes_users && is_array( $yes_users ) ) {
			$yes_count = count( $yes_users );
		}
		if ( $maybe_users && is_array( $maybe_users ) ) {
			$maybe_count = count( $maybe_users );
		}
		$total = $yes_count + $maybe_count;

		$count = array();
		$count['yes'] = $yes_count;
		$count['maybe'] = $maybe_count;
		$count['total'] = $total;

		return $count;
	}

	public static function geodir_ayi_widget_display( $args, $instance, $shortcode = false ) {
		global $post;

		extract( $args, EXTR_SKIP );

//		if ( ! get_current_user_id() ) {
//			return false;
//		}
		if ( !$args['is_preview'] && ! geodir_is_page( 'detail' ) ) {
			return false;
		}
		if ( !$args['is_preview'] && ! GeoDir_Post_types::supports( get_query_var( 'post_type' ), 'events' ) ) {
			return false;
		}

		$current_date = date_i18n( 'Y-m-d H:i:s', time() );
		$schedule = GeoDir_Event_Schedules::get_start_schedule( $post->ID );
		if ( !$args['is_preview'] && empty( $schedule ) ) {
			return false;
		}

		$gde = isset( $_GET['gde'] ) ? sanitize_text_field( strip_tags( $_GET['gde'] ) ) : false;

		if ( ! empty( $schedule->all_day ) ) {
			if ( ! empty( $gde ) ) {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $gde ) );
			} else {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $schedule->start_date ) );
			}
		} else {
			if ( ! empty( $gde ) ) {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $gde . ' ' . $schedule->start_time ) );
			} else {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $schedule->start_date . ' ' . $schedule->start_time ) );
			}
		}

		$buttons = false;
		if ( strtotime( $event_start_date ) > strtotime( $current_date ) ) {
			$buttons = true;
		}

		ob_start();
		?>
		<div class="ayi-html-wrap mb-3">
			<?php self::geodir_ayi_widget_html( $post, $buttons, $gde ); ?>
		</div>
		<?php
		$output = ob_get_clean();
	 
		return $output;
	}

	public static function geodir_ayi_widget_html( $post, $buttons = false, $gde = false ) {
		$cur_user_interested = self::geodir_ayi_event_is_current_user_interested( $post->ID, $gde );

		$no_of_users = apply_filters( 'geodir_event_rsvp_no_of_users_to_display', 10 );
		$count = GeoDir_Event_AYI::count_interested( $post->ID, $gde );
		$design_style = geodir_design_style();
		
		$template = $design_style ? $design_style."/widgets/ayi.php" : "legacy/widgets/ayi.php";
		$args = array(
			'cur_user_interested'    => $cur_user_interested,
			'buttons'    => $buttons,
			'gde'  => $gde,
			'count'    => $count,
			'no_of_users'  => $no_of_users,
			'post'      => $post
		);

		echo geodir_get_template_html( $template , $args, '', plugin_dir_path( GEODIR_EVENT_PLUGIN_FILE ). "templates/");

		self::geodir_are_you_interested_js();
	}

	public static function ajax_ayi_action() {
		check_ajax_referer('geodir-ayi-nonce', 'geodir_ayi_nonce');
		//set variables
		$action = strip_tags(esc_sql($_POST['btnaction']));
		$type = strip_tags(esc_sql($_POST['type']));
		$post_id = strip_tags(esc_sql($_POST['postid']));
		$gde = strip_tags(esc_sql($_POST['gde']));

		$rsvp_args = array();
		$rsvp_args['action'] = $action;
		$rsvp_args['type'] = $type;
		$rsvp_args['post_id'] = $post_id;
		$rsvp_args['gde'] = $gde;

		self::update_ayi_data($rsvp_args);
		$post = geodir_get_post_info($post_id);

		$current_date = date_i18n( 'Y-m-d H:i:s', time() );
		$gde = !empty($gde) ? sanitize_text_field( strip_tags( $gde ) ) : false;
		$schedule = GeoDir_Event_Schedules::get_start_schedule( $post->ID );

		if ( ! empty( $schedule->all_day ) ) {
			if ( ! empty( $gde ) ) {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $gde ) );
			} else {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $schedule->start_date ) );
			}
		} else {
			if ( ! empty( $gde ) ) {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $gde . ' ' . $schedule->start_time ) );
			} else {
				$event_start_date = date_i18n( 'Y-m-d H:i:s', strtotime( $schedule->start_date . ' ' . $schedule->start_time ) );
			}
		}

		$buttons = false;
		if (strtotime($event_start_date) > strtotime($current_date)) {
			$buttons = true;
		}

		self::geodir_ayi_widget_html($post, $buttons, $gde);
		wp_die();
	}

	public static function geodir_are_you_interested_js()
	{
		if (!get_current_user_id()) {
			return;
		}

		$ajax_nonce = wp_create_nonce("geodir-ayi-nonce");
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('a.geodir-ayi-btn-rsvp').on("click",function (e) {
					e.preventDefault();
					var container = jQuery('.ayi-html-wrap');
					var btnaction = jQuery(this).attr('data-action');
					var type = jQuery(this).attr('data-type');
					var postid = jQuery(this).attr('data-postid');
					var gde = jQuery(this).attr('data-gde');
					jQuery(this).addClass('disabled');
					var data = {
						'action': 'geodir_ayi_action',
						'geodir_ayi_nonce': '<?php echo $ajax_nonce; ?>',
						'btnaction': btnaction,
						'type': type,
						'postid': postid,
						'gde': gde
					};

					jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
						jQuery(this).removeClass('disabled');
						container.html(response);
						<?php
						if(geodir_design_style()){
							echo "aui_init();";
						}
						?>
					});
				})
			});
		</script>
		<?php
	}


	public static function geodir_ayi_event_is_current_user_interested( $post_id, $gde = false ) {
		if ( ! get_current_user_id() ) {
			return false;
		}
		$current_user = wp_get_current_user();

		$yes_users = get_post_meta( $post_id, 'event_rsvp_yes', true );
		$maybe_users = get_post_meta( $post_id, 'event_rsvp_maybe', true );

		if ( $gde ) {
			$yes_users = isset( $yes_users[ $gde] ) ? $yes_users[$gde] : false;
			$maybe_users = isset( $maybe_users[$gde] ) ? $maybe_users[$gde] : false;
		}

		$type = null;
		if ( $yes_users && is_array( $yes_users ) ) {
			foreach ( $yes_users as $uid ) {
				if ( $uid == $current_user->ID ) {
					$type = 'event_rsvp_yes';
					break;
				}
			}
		}

		if ( $maybe_users && is_array( $maybe_users ) ) {
			foreach ( $maybe_users as $uid ) {
				if ( $uid == $current_user->ID ) {
					$type = 'event_rsvp_maybe';
					break;
				}
			}
		}

		return $type;
	}




	public static function save_ayi_data( $post_id, $post, $update ) {
		if ( ! $update && GeoDir_Post_types::supports( $post->post_type, 'events' ) ) {
			$rsvp_args = array();
			$rsvp_args['action'] = 'add';
			$rsvp_args['type'] = 'event_rsvp_yes';
			$rsvp_args['post_id'] = $post_id;

			self::update_ayi_data( $rsvp_args );
		}
	}

	public static function remove_ayi_data( $post_id ) {
		if ( GeoDir_Post_types::supports( get_post_type( $post_id ), 'events' ) ) {
			$rsvp_args = array();
			$rsvp_args['action'] = 'remove';
			$rsvp_args['type'] = 'event_rsvp_yes';
			$rsvp_args['post_id'] = $post_id;

			self::update_ayi_data( $rsvp_args );
		}
	}

	public static function update_ayi_data($rsvp_args = array()) {
		if ( ! get_current_user_id() ) {
			return;
		}

		$current_user = wp_get_current_user();

		$users = get_post_meta( $rsvp_args['post_id'], $rsvp_args['type'], true );
		$gde = ! empty( $rsvp_args['gde'] ) ? sanitize_text_field( strip_tags( $rsvp_args['gde'] ) ) : false;

		if ( ! is_array( $users ) ) {
			$users = array();
		}

		if ( $rsvp_args['action'] == 'add' ) {
			$gde_users = is_array( $users ) ? $users : array();

			if ( ! empty( $gde ) ) {
				$users = isset( $users[$gde] ) ? $users[$gde] : false;
			}

			if ( is_array( $users ) ) {
				$users[ $current_user->ID ] = $current_user->ID;
			} else {
				$users = array( $current_user->ID => $current_user->ID );
			}

			if ( ! empty( $gde ) ) {
				$gde_users[$gde] = $users;
				$users = $gde_users;
			}

			update_post_meta( $rsvp_args['post_id'], $rsvp_args['type'], $users );

			$posts = get_user_meta( $current_user->ID, $rsvp_args['type'], true );
			$gde_posts = $posts;

			if ( ! empty( $gde ) ) {
				$pid = $rsvp_args['post_id'];
				$posts = isset( $posts[$pid] ) ? $posts[$pid] : false;

				if ( is_array( $posts ) ) {
					$posts[$gde] = $gde;
				} else {
					$posts = array( $gde => $gde );
				}

				$gde_posts[$pid] = $posts;
				$posts = $gde_posts;
			} else {
				if ( is_array( $posts ) ) {
					$posts[$rsvp_args['post_id']] = $rsvp_args['post_id'];
				} else {
					$posts = array( $rsvp_args['post_id'] => $rsvp_args['post_id'] );
				}
			}

			update_user_meta( $current_user->ID, $rsvp_args['type'], $posts );
		} else {
			if ( ! empty( $gde ) ) {
				if ( isset( $users[$gde][$current_user->ID] ) ) {
					unset( $users[$gde][$current_user->ID] );
				}
			} else {
				if ( is_array( $users ) ) {
					unset( $users[$current_user->ID] );
				}
			}

			update_post_meta( $rsvp_args['post_id'], $rsvp_args['type'], $users );

			$posts = get_user_meta( $current_user->ID, $rsvp_args['type'], true );

			if ( ! empty( $gde ) ) {
				if ( isset( $posts[$rsvp_args['post_id']][$gde] ) ) {
					unset( $posts[$rsvp_args['post_id']][$gde] );
				}
			} else {
				if ( is_array( $posts ) ) {
					unset( $posts[$rsvp_args['post_id']] );
				}
			}

			update_user_meta( $current_user->ID, $rsvp_args['type'], $posts );
		}
		
		if ( ! isset( $rsvp_args['gde'] ) ) {
			$rsvp_args['gde'] = '';
		}

		do_action( 'geodir_event_ayi_interested_updated', $rsvp_args['post_id'], $current_user->ID, $rsvp_args['type'], $rsvp_args['action'], $rsvp_args['gde'] );
	}

	public static function geodir_ayi_rsvp_users_for_a_post( $post_id, $type = "event_rsvp_yes", $limit = 10, $gde = '' ) {
		$ids = get_post_meta( $post_id, $type, true );

		if ( ! empty( $gde ) ) {
			$ids = isset( $ids[ $gde ] ) ? $ids[ $gde ] : false;
		}


		if ( ! is_array( $ids ) ) {
			return;
		}

		$count = 1;

		foreach ( $ids as $id ) {
			if ( $count > $limit ) {
				break;
			}

			$user = get_user_by( 'id', $id );

			if ( empty( $user ) ) {
				continue;
			}

			self::geodir_ayi_rsvp_user_avatar( $user );

			$count++;
		}
	}

	public static function geodir_ayi_rsvp_user_avatar($user, $class='') {
		$design_style = geodir_design_style();

		$template = $design_style ? $design_style."/widgets/ayi-user.php" : "legacy/widgets/ayi-user.php";
		$args = array(
			'class'    => $class,
			'user'    => $user,
		);

		echo geodir_get_template_html( $template , $args, '', plugin_dir_path( GEODIR_EVENT_PLUGIN_FILE ). "templates/");

	}

	public static function geodir_ayi_get_user_profile_link($user_id)
	{
		if (class_exists('BuddyPress')) {
			$user_link = bp_core_get_user_domain($user_id);
		} else {
			$user_link = get_author_posts_url($user_id);
		}
		return $user_link;
	}

	public static function geodir_ayi_get_current_user_name($current_user)
	{
		$uname = "";
		$name_stack = array(
			'display_name',
			'user_nicename',
			'user_login'
		);
		foreach ($name_stack as $source) {
			if (!empty($current_user->{$source})) {
				$uname = $current_user->{$source};
				break;
			}
		}
		return $uname;
	}

	public static function geodir_ayi_member_name($name)
	{
		$text = explode(' ', $name);
		if (count($text) > 1) {
			$first_char = strtoupper(substr($text[1], 0, 1));
			return $text[0] . ' ' . $first_char . '.';
		} else {
			return $name;
		}
	}

	public static function geodir_ayi_event_list_content_from_post($post) {
		$author_id = $post->post_author;
		$user = get_user_by('id', $author_id);
		?>
		<li>
			<div class="event-content-box">
				<div class="event-content-avatar">
					<div class="event-content-avatar-inner">
						<?php
						if ($fimage = geodir_get_featured_image($post->ID, '', true, $post->featured_image)) {
							?>
							<a href="<?php echo get_the_permalink($post->ID); ?>">
								<div class="geodir_thumbnail" style="background-image:url(<?php echo $fimage->src; ?>);"></div>
							</a>
						<?php } else {
							?>
							<a href="<?php echo get_the_permalink($post->ID); ?>">
								<div class="geodir_thumbnail" style="background-image:url(<?php echo plugin_dir_url( '' ) ?>geodir_ayi/assets/images/no_thumb.png"></div>
							</a>
							<?php
						} ?>
					</div>
				</div>
				<div class="event-content-body">
					<div class="event-content-body-top">
						<div class="event-title">
							<a href="<?php echo get_the_permalink($post->ID) ?>"><?php echo get_the_title($post->ID) ?></a>

							<div class="event-date">
								<?php echo self::geodir_ayi_get_event_date_from_post($post); ?>
							</div>
						</div>
						<div class="event-author">
							<div class="event-submitted-by">
								<?php echo __('Submitted by', 'geodirevents'); ?><br/>
								<a href="<?php echo self::geodir_ayi_get_user_profile_link($user->ID); ?>">
									<?php echo self::geodir_ayi_member_name(self::geodir_ayi_get_current_user_name($user)); ?>
								</a>
							</div>
							<div class="event-submitted-by-avatar">
								<a href="<?php echo self::geodir_ayi_get_user_profile_link($user->ID); ?>"><?php echo get_avatar($user->ID, 30); ?></a>
							</div>

						</div>

					</div>
					<div class="event-content-body-bottom">
						<div class="event-address">
							<?php
							echo self::geodir_ayi_get_address_html($post);
							?>
						</div>
						<div class="event-interested">
							<?php
							global $bp;
							$interested_url = add_query_arg(
								'ayi_interested',
								$post->ID,
								self::geodir_ayi_get_user_profile_link($bp->displayed_user->id)."events"
							);
							if ($post->rsvp_count > 0) {
								?>
								<a href="<?php echo esc_url( $interested_url ); ?>">
									<?php echo $post->rsvp_count; ?> <?php echo self::geodir_ayi_pluralize($post->rsvp_count, __('is interested', 'geodirevents'), __('are interested', 'geodirevents')); ?>
								</a>
								<?php
							} else {
								?>
								<?php echo $post->rsvp_count; ?> <?php echo self::geodir_ayi_pluralize($post->rsvp_count, __('is interested', 'geodirevents'), __('are interested', 'geodirevents')); ?>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</li>
		<?php
	}

	public static function geodir_ayi_get_event_date_from_post($post)
	{
		global $preview;

		$return = false;

		if ($preview) {
			$event_start_date = $post->event_start ? date('l, F j, Y', strtotime($post->event_start)) : '';
			$event_start_time = $post->starttime ? date('g:i a', strtotime($post->starttime)) : '';

			$event_end_date = $post->event_end ? date('l, F j, Y', strtotime($post->event_end)) : '';
			$event_end_time = $post->endtime ? date('g:i a', strtotime($post->endtime)) : '';
		} else {
			$event_details = maybe_unserialize($post->event_dates);

			$event_start_date = isset($event_details['event_start']) ? date('l, F j, Y', strtotime($event_details['event_start'])) : '';
			$event_start_time = isset($event_details['starttime']) ? date('g:i a', strtotime($event_details['starttime'])) : '';

			$event_end_date = isset($event_details['event_end']) ? date('l, F j, Y', strtotime($event_details['event_end'])) : '';
			$event_end_time = isset($event_details['endtime']) ? date('g:i a', strtotime($event_details['endtime'])) : '';

			if (isset($event_details['recurring']) && $event_details['repeat_type'] == 'week' && isset($event_details['repeat_days']) && $event_details['repeat_days']) {
				$days = array( __('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'));

				if (!empty($event_start_date) && empty($event_end_date)) {
					$return = '<span class="eve-start-date">' . $event_start_date . '</span><span class="eve-end-date">, ' . $event_start_time . ' - ' . $event_end_time . '</span>';
				} else {
					$event_start_date = $days[(int) $event_details['repeat_days'][0]];
					$event_end_date = $days[(int) end( $event_details['repeat_days'] )];
					$return = '<span class="eve-start-date">' . $event_start_date . ' to </span><span class="eve-end-date">' . $event_end_date . ', ' . $event_start_time . ' - ' . $event_end_time . '</span>';
				}


			} elseif (isset($event_details['recurring'])) {
				$gde = isset( $_GET['gde'] ) ? sanitize_text_field( strip_tags($_GET['gde']) ) : false;

				if ($gde) {
					$event_start_date = $event_details['event_start'] ? date('l, F j, Y', strtotime($gde)) : '';
				}
			}
		}


		if ( ! empty( $return ) ) {
			return $return;
		}
		return '<span class="eve-start-date">' . $event_start_date . ' ' . $event_start_time . ' - </span><span class="eve-end-date">' . $event_end_date . ' ' . $event_end_time . '</span>';
	}

	public static function geodir_ayi_get_address_html($post, $review_page = false) {
		$html = "";
		$street = isset($post->street) ? $post->street : geodir_get_post_meta($post->ID, 'street', true);
		$city = isset($post->city) ? $post->city : geodir_get_post_meta($post->ID, 'city', true);
		$region = isset($post->region) ? $post->region : geodir_get_post_meta($post->ID, 'region', true);
		$zip = isset($post->zip) ? $post->zip : geodir_get_post_meta($post->ID, 'zip', true);
		$country = isset($post->country) ? $post->country : geodir_get_post_meta($post->ID, 'country', true);
		$class = $review_page ? 'fsize12' : '';
		if ($street) {
			$html .= '<span class="'.$class.'">' . stripslashes($street) . '</span><br>';
		}
		if ($city) {
			$html .= '<span class="'.$class.'">' . $city . '</span>, ';
		}
		if ($region) {
			$html .= '<span class="'.$class.'">' . $region . '</span> ';
		}
		if ($zip) {
			$html .= '<span class="'.$class.'">' . $zip . '</span><br>';
		}
		if ($country && !$review_page) {
			$html .= '<span class="'.$class.'">' . $country . '</span><br>';
		}
		return apply_filters('geodir_ayi_get_address_html_filter', $html, $post, $class);
	}

	public static function geodir_ayi_pluralize($count, $singular, $plural = false) {
		return ($count == 1 ? $singular : $plural);
	}
}