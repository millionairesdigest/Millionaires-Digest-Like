<?php

/**
 * Class for rendering, saving and getting the plugin settings
 */
class Who_Likes_Settings {

	const SETTINGS_KEY = 'who_likes_settings';
	const SETTINGS_PAGE = 'like-and-who-likes';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'settings_init' ] );

		// Get options and their defaults
		$options = $this->get_options();
		$defaults = array_combine( array_column( $options, 'name' ), array_column( $options, 'default' ) );
		$settings = get_option( self::SETTINGS_KEY, $defaults );

		// Populate the class properties with the approppriate settings
		foreach ( $options as $option ) {
			// This is a simple checkbox check
			$this->{$option['name']} = isset( $settings[$option['name']] );
		}
	}

	/** Sets up array of options and their default values */
	private function get_options() {
		$options = [
			[
				'name' => 'show_in_wp_post',
				'default' => true,
				'title' => 'Wordpress',
				'label' => __( 'Display in Wordpress posts', 'like-and-who-likes' ),
			],
			[
				'name' => 'show_in_wp_comment',
				'default' => true,
				'title' => '',
				'label' => __( 'Display in Wordpress comments', 'like-and-who-likes' ),
			],
			[
				'name' => 'show_in_bp_activity',
				'default' => true,
				'title' => 'BuddyPress',
				'label' => __( 'Display in BuddyPress activities', 'like-and-who-likes' ),
			],
			[
				'name' => 'show_in_bp_comment',
				'default' => true,
				'title' => '',
				'label' => __( 'Display in BuddyPress activity comments', 'like-and-who-likes' ),
			],
			[
				'name' => 'show_in_bbp_post',
				'default' => true,
				'title' => 'BBPress',
				'label' => __( 'Display in BBPress posts', 'like-and-who-likes' ),
			],
		];

		return $options;
	}

	public function admin_menu() {
		add_options_page( 'Like And Who Likes', 'Like And Who Likes', 'manage_options', self::SETTINGS_PAGE, [ $this, 'settings_page' ] );
	}

	public function settings_init() {
		register_setting( self::SETTINGS_PAGE, self::SETTINGS_KEY );

		add_settings_section( 'display', __( 'Display settings', 'like-and-who-likes' ), null, self::SETTINGS_PAGE );

		foreach ( $this->get_options() as $option ) {
			add_settings_field( $option['name'], $option['title'], [ $this, 'input_checkbox' ], self::SETTINGS_PAGE, 'display', $option );
		}
	}

	public function settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form action="options.php" method="post">
				<?php
				settings_fields( self::SETTINGS_PAGE );
				do_settings_sections( self::SETTINGS_PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function input_checkbox( $option ) {
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::SETTINGS_KEY . "[$option[name]]" ); ?>" <?php checked( $this->{$option['name']}, true ); ?>>
			<?php echo esc_html( $option['label'] ); ?>
		</label>
		<?php
	}

	public static function uninstall() {
		delete_option( self::SETTINGS_KEY );
	}

}
