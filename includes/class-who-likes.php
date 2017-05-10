<?php

/**
 * The main class
 */
class Who_Likes {

	// Key name for DB data saving
	const META_KEY = 'who_likes';
	// Type of the current component
	const WP_POST = 0;
	const WP_COMMENT = 1;
	const BP_ACTIVITY = 2;
	const BP_COMMENT = 3;
	const BBP_POST = 4;

	/** @var int Current component, one of the constants */
	private $component;

	public function __construct( $settings ) {
		// 'Like' button and 'Who Likes' block
		// For Wordpress
		if ( $settings->show_in_wp_post ) {
			add_filter( 'the_content', [ $this, 'add_likes_to_wp_posts' ] );
		}

		if ( $settings->show_in_wp_comment ) {
			add_filter( 'comment_reply_link_args', [ $this, 'add_likes_to_wp_comments' ], 10, 3 );
		}

		// For BuddyPress
		if ( $settings->show_in_bp_activity ) {
			add_action( 'bp_activity_entry_meta', [ $this, 'add_likes_to_bp_activities' ] );
			add_action( 'bp_activity_entry_content', [ $this, 'add_likes_to_bp_activities' ] );
		}

		if ( $settings->show_in_bp_comment ) {
			add_action( 'bp_activity_comment_options', [ $this, 'add_likes_to_bp_comments' ] );
		}

		// For BBPress
		if ( $settings->show_in_bbp_post ) {
			add_filter( 'bbp_topic_admin_links', [ $this, 'add_likes_to_bbp_posts' ], 10, 2 );
			add_filter( 'bbp_reply_admin_links', [ $this, 'add_likes_to_bbp_posts' ], 10, 2 );
			add_action( 'bbp_theme_after_reply_content', [ $this, 'add_likes_to_bbp_posts' ] );
		}

		// JS and AJAX
		add_action( 'wp_enqueue_scripts', [ $this, 'add_assets' ] );
		add_action( 'wp_ajax_like_and_who_likes', [ $this, 'ajax_action' ] );
	}

	public function add_likes_to_wp_posts( $content ) {
		if ( !is_single() ) {
			return $content;
		}

		$this->component = self::WP_POST;
		$id = get_the_ID();

		$liked_users = $this->get_likes( $id );

		$content .= $this->who_likes_list( $id, $liked_users );
		$content .= '<p>' . $this->like_button( $id, $liked_users ) . '</p>';

		return $content;
	}

	public function add_likes_to_wp_comments( $args, $comment, $post ) {
		$this->component = self::WP_COMMENT;
		$id = get_comment_ID();

		$liked_users = $this->get_likes( $id );

		$args['after'] = $this->like_button( $id, $liked_users ) . $args['after'];

		return $args;
	}

	public function add_likes_to_bp_activities() {
		if ( !bp_activity_can_comment() ) {
			return;
		}

		$this->component = self::BP_ACTIVITY;
		$id = bp_get_activity_id();

		$liked_users = $this->get_likes( $id );

		if ( current_filter() == 'bp_activity_entry_content' ) {
			echo $this->who_likes_list( $id, $liked_users );
		} else {
			echo $this->like_button( $id, $liked_users );
		}
	}

	public function add_likes_to_bp_comments() {
		$this->component = self::BP_COMMENT;
		$id = bp_get_activity_comment_id();

		$liked_users = $this->get_likes( $id );

		echo $this->like_button( $id, $liked_users );
	}

	public function add_likes_to_bbp_posts( $links = null, $reply_id = null ) {
		$this->component = self::BBP_POST;

		$id = bbp_get_reply_id();
		$liked_users = $this->get_likes( $id );

		if ( current_filter() == 'bbp_theme_after_reply_content' ) {
			echo $this->who_likes_list( $id, $liked_users );
		} else {
			$links['like'] = $this->like_button( $id, $liked_users );
			return $links;
		}
	}

	public function like_button( $id, $liked_users ) {
		if ( !is_user_logged_in() ) {
			return;
		}

		$classes = 'wl-like';

		if ( $this->component == self::WP_COMMENT ) {
			$classes .= ' comment-reply-link';
		}

		if ( $this->component == self::BP_ACTIVITY ) {
			$classes .= ' button bp-primary-action';
		}

		if ( $this->component == self::BP_COMMENT ) {
			$classes .= ' bp-primary-action';
		}

		$user_liked = in_array( get_current_user_id(), $liked_users );

		$classes .= $user_liked ? ' wl-unlike' : '';
		$button_text = $user_liked ? __( 'Unlike', 'like-and-who-likes' ) : __( 'Like', 'like-and-who-likes' );

		// Add the data attributes to get the element and its data by JS
		return "<a href='#' class='$classes' data-id='$id' data-component='$this->component'>$button_text<span>" . count( $liked_users ) . "</span></a>";
	}

	public function who_likes_list( $id, $liked_users ) {
		$who_likes_number = 3; // How many users to show in the 'Who Likes' block. Use 0 to hide

		$output = "<p class='wl-list' data-id='$id' data-component='$this->component'>";

		$user_liked = in_array( get_current_user_id(), $liked_users );

		if ( $liked_users && $who_likes_number ) {
			$shown_user_number = 0;

			if ( $user_liked ) {
				// Remove the current user from the array and reset the array keys
				$liked_users = array_values( array_diff( $liked_users, [ get_current_user_id() ] ) );
				$output .= __( 'You', 'like-and-who-likes' );
				$shown_user_number++;
			}

			while ( $liked_users && $shown_user_number < $who_likes_number ) {
				$link = bp_core_get_userlink( array_pop( $liked_users ) );
				$more_count = count( $liked_users );

				$delimeter = $shown_user_number ? ($more_count ? ', ' : ' ' . __( 'and', 'like-and-who-likes' ) . ' ') : '';
				$output .= $delimeter . $link;
				$output .= $shown_user_number == $who_likes_number - 1 && $more_count ? sprintf( ' ' . _n( 'and %s more person', 'and %s more people', $more_count, 'like-and-who-likes' ), $more_count ) : '';

				$shown_user_number++;
			}

			$output .= ' ' . ($shown_user_number == 1 && !$user_liked ? __( 'likes this', 'like-and-who-likes' ) : __( 'like this', 'like-and-who-likes' ));
		}

		$output .= '</p>';

		return $output;
	}

	public function add_assets() {
		wp_enqueue_script( 'like-and-who-likes', plugin_dir_url( __DIR__ ) . '/js/like-and-who-likes.js', [ 'jquery' ] );
		wp_localize_script( 'like-and-who-likes', 'who_likes', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
		wp_enqueue_style( 'like-and-who-likes', plugin_dir_url( __DIR__ ) . '/css/like-and-who-likes.css' );
	}

	public function ajax_action() {
		$component = filter_input( INPUT_POST, 'component', FILTER_VALIDATE_INT );
		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		if ( $id && is_user_logged_in() && in_array( $component, [ self::WP_POST, self::WP_COMMENT, self::BP_ACTIVITY, self::BP_COMMENT, self::BBP_POST ] ) ) {
			$this->component = $component;
		} else {
			exit;
		}

		// If there is no such Wordpress post or comment, BuddyPress activity or comment, BBPress post
		if ( !$this->is_item_exist( $id ) ) {
			$this->delete_likes( $id );
			exit;
		}

		// Like or Unlike
		if ( $_POST['type'] == 'like' ) {
			$this->add_user_like( $id );
		}

		if ( $_POST['type'] == 'unlike' ) {
			$this->remove_user_like( $id );
		}

		exit;
	}

	private function is_item_exist( $id ) {
		switch ( $this->component ) {
			case self::WP_POST:
				$item = get_post( $id );
				break;
			case self::WP_COMMENT:
				$item = get_comment( $id );
				break;
			case self::BP_ACTIVITY:
			case self::BP_COMMENT:
				$item = (new BP_Activity_Activity( $id ))->id;
				break;
			case self::BBP_POST:
				$item = bbp_get_reply( $id ) ?: bbp_get_topic( $id );
				break;
		}

		return (bool) $item;
	}

	private function add_user_like( $id ) {
		$userId = get_current_user_id();

		/* Add to the total likes for this activity. */
		$liked_users = $this->get_likes( $id );
		$liked_users[] = $userId;
		$this->update_likes( $id, array_unique( $liked_users ) );

		$this->ajax_response( [
			'button' => $this->like_button( $id, $liked_users ),
			'list' => $this->who_likes_list( $id, $liked_users ),
		] );
	}

	private function remove_user_like( $id ) {
		$userId = get_current_user_id();

		/* Get likes except user's */
		$liked_users = array_diff( $this->get_likes( $id ), [ $userId ] );

		/* If nobody likes the activity, delete the meta for it to save space, otherwise, update the meta */
		if ( !$liked_users ) {
			$this->delete_likes( $id );
		} else {
			$this->update_likes( $id, $liked_users );
		}

		$this->ajax_response( [
			'button' => $this->like_button( $id, $liked_users ),
			'list' => $this->who_likes_list( $id, $liked_users ),
		] );
	}

	/**
	 * @param int $id BuddyPress Activity or BBPress Post ID
	 */
	private function get_likes( $id ) {
		switch ( $this->component ) {
			case self::WP_POST:
			case self::BBP_POST:
				$likes = get_post_meta( $id, self::META_KEY, true );
				break;
			case self::WP_COMMENT:
				$likes = get_comment_meta( $id, self::META_KEY, true );
				break;
			case self::BP_ACTIVITY:
			case self::BP_COMMENT:
				$likes = bp_activity_get_meta( $id, self::META_KEY, true );
				break;
		}

		return $likes ?: [];
	}

	private function update_likes( $id, $liked_users ) {
		switch ( $this->component ) {
			case self::WP_POST:
			case self::BBP_POST:
				update_post_meta( $id, self::META_KEY, $liked_users );
				break;
			case self::WP_COMMENT:
				update_comment_meta( $id, self::META_KEY, $liked_users );
				break;
			case self::BP_ACTIVITY:
			case self::BP_COMMENT:
				bp_activity_update_meta( $id, self::META_KEY, $liked_users );
				break;
		}
	}

	private function delete_likes( $id ) {
		switch ( $this->component ) {
			case self::WP_POST:
			case self::BBP_POST:
				delete_post_meta( $id, self::META_KEY );
				break;
			case self::WP_COMMENT:
				delete_comment_meta( $id, self::META_KEY );
				break;
			case self::BP_ACTIVITY:
			case self::BP_COMMENT:
				bp_activity_delete_meta( $id, self::META_KEY );
				break;
		}
	}

	private function ajax_response( $data ) {
		exit( json_encode( $data ) );
	}

	public static function uninstall() {
		if ( function_exists( 'bp_activity_delete_meta' ) ) {
			bp_activity_delete_meta( 0, self::META_KEY, '', true );
		}

		delete_metadata( 'post', 0, self::META_KEY, '', true );
		delete_metadata( 'comment', 0, self::META_KEY, '', true );
	}

}
