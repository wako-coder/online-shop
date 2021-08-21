<?php
/**
 * Online Shop Notice Handler
 *
 * @author  AcmeThemes
 * @package Online Shop
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle notices and Advanced Demo Import
 *
 * Class Online_Shop_Notice_Handler
 */
class Online_Shop_Notice_Handler {

	/**
	 * Empty Constructor
	 */
	public function __construct() {	}

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return object
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new self();
		}

		// Always return the instance
		return $instance;
	}

	/**
	 * Initialize the class
     *
     * 3 Different Process
	 */
	public function run() {
		$this->advanced_demo_import();
		$this->get_started_notice();
		$this->review_notice();
	}

	/**
	 * Advance Demo import process
	 * Active callback of advanced_import_demo_lists
	 * return array
	 */
	public function advanced_demo_import(){
		add_action( 'advanced_import_replace_post_ids', array( $this, 'replace_post_ids' ), 20 );
		add_action( 'advanced_import_replace_term_ids', array( $this, 'replace_term_ids' ), 20 );
	}

	/**
	 * Advance Demo import process
	 * Active callback of advanced_import_replace_post_ids
	 * return array
	 */
	public function replace_post_ids( $replace_post_ids ){

		/*Post IDS*/
		$post_ids = array(
            'first_page_id',
            'second_page_id',
            'online-shop-feature-post-one',
            'online-shop-feature-post-two',
		);

		return array_merge( $replace_post_ids,$post_ids );

    }

	/**
	 * Advance Demo import process
	 * Active callback of advanced_import_replace_term_ids
	 * return array
	 */
	public function replace_term_ids( $replace_term_ids ){

		/*Terms IDS*/
		$term_ids = array(
            'online_shop_post_cat',
            'online_shop_post_tag',
            'online_shop_featured_cats',
            'online_shop_wc_product_cat',
            'online_shop_wc_product_tag',
            'online-shop-feature-cat',
            'online-shop-feature-side-from-category',
		);

		return array_merge( $replace_term_ids, $term_ids );

	}

	/**
	 * Get Started Notice
	 *
     * Handle Getting Started Functions
	 * return void
	 */
	private function get_started_notice(){

		add_action( 'wp_loaded', array( $this, 'admin_notice' ), 20 );
		add_action( 'wp_loaded', array( $this, 'hide_notices' ), 15 );
		add_action( 'wp_ajax_at_getting_started', array( $this, 'install_advanced_import' ) );
	}

	/**
	 * Get Started Notice
	 * Active callback of wp_loaded
	 * return void
	 */
	public function admin_notice() {
		/*Check for notice nag*/
		$notice_nag = get_option( 'online_shop_admin_notice_welcome' );
		if ( ! $notice_nag ) {
			wp_enqueue_style( 'online-shop-notice', get_template_directory_uri() . '/acmethemes/admin-notice/admin-notice.css', array(), '3.0.0' );
			wp_enqueue_script( 'online-shop-adi-install', get_template_directory_uri()  . '/acmethemes/admin-notice/admin-notice.js', array( 'jquery' ), '', true );

			$translation = array(
                'btn_text' => esc_html__( 'Processing...', 'online-shop' ),
                'nonce'    => wp_create_nonce( 'online_shop_demo_import_nonce' ),
                'adminurl'    => admin_url()
            );
			wp_localize_script( 'online-shop-adi-install', 'online_shop_adi_install', $translation );
			
			/*admin notice hook*/
			add_action( 'admin_notices', array( $this, 'getting_started' ) );
		}

	}

	/**
	 * Get Started Notice
	 * Active callback of wp_loaded
	 * return void
	 */
	public static function hide_notices() {

		if ( isset( $_GET['at-gsm-hide-notice'] ) && isset( $_GET['at_gsm_admin_notice_nonce'] ) ) { // WPCS: input var ok.
			if ( ! wp_verify_nonce( wp_unslash( $_GET['at_gsm_admin_notice_nonce'] ), 'at_gsm_hide_notices_nonce' ) ) { // phpcs:ignore WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'online-shop' ) ); // WPCS: xss ok.
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'online-shop' ) ); // WPCS: xss ok.
			}

			$notice_type = sanitize_text_field( wp_unslash( $_GET['at-gsm-hide-notice'] ) );
			update_option( 'online_shop_admin_notice_' . $notice_type, 1 );

			/*Update to Hide.*/
			if ( 'welcome' === $_GET['at-gsm-hide-notice'] ) {
				update_option( 'online_shop_admin_notice_' . $notice_type, 1 );
			} else { // Show.
				delete_option( 'online_shop_admin_notice_' . $notice_type );
			}
		}

	}
	/**
	 * Get Started Notice
	 * Active callback of admin_notices
	 * return void
	 */
	public function getting_started() {
		?>
        <div id="at-gsm" class="updated notice-info at-gsm">
            <a class="at-gsm-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( remove_query_arg( array( 'activated' ), add_query_arg( 'at-gsm-hide-notice', 'welcome' ) ), 'at_gsm_hide_notices_nonce', 'at_gsm_admin_notice_nonce' ) ); ?>">
				<?php esc_html_e( 'Dismiss', 'online-shop' ); ?>
            </a>
            <div class="at-gsm-container">
                <img class="at-gsm-screenshot" src="<?php echo esc_url(get_template_directory_uri().'/screenshot.jpg' )?>" alt="<?php esc_attr_e( 'Online Shop', 'online-shop' ); ?>" />
                <div class="at-gsm-notice">
                    <h2>
						<?php
						printf(
						/* translators: 1: welcome page link starting html tag, 2: welcome page link ending html tag. */
							esc_html__( 'Welcome! Thank you for choosing %1$s! To fully take advantage of the best our theme can offer, please make sure you visit our %2$swelcome page%3$s.', 'online-shop' ), '<strong>'. wp_get_theme()->get('Name'). '</strong>','<a href="' . esc_url( admin_url( 'themes.php?page=online-shop-info' ) ) . '">','</a>' );
						?>
                    </h2>

                    <p class="plugin-install-notice"><?php esc_html_e( 'Clicking the button below will install and activate the Acme Demo Setup and Advanced Import plugins.', 'online-shop' ); ?></p>

                    <a class="at-gsm-btn button button-primary button-hero" href="#" data-name="" data-slug="" aria-label="<?php esc_attr_e( 'Get started with Online Shop', 'online-shop' ); ?>">
		                <?php esc_html_e( 'Get started with Online Shop', 'online-shop' );?>
                    </a>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Get Started Notice
	 * Active callback of wp_ajax
	 * return void
	 */
	public function install_advanced_import() {

		check_ajax_referer( 'online_shop_demo_import_nonce', 'security' );

        $slug   = $_POST['slug'];
        $plugin = $slug.'/'.$slug.'.php';
        $request = $_POST['request'];

		$status = array(
			'install' => 'plugin',
			'slug'    => sanitize_key( wp_unslash( $slug ) ),
		);
		$status['redirect'] = admin_url( '/themes.php?page=advanced-import&browse=all&at-gsm-hide-notice=welcome' );

		if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
			// Plugin is activated
			wp_send_json_success($status);
		}


		if ( ! current_user_can( 'install_plugins' ) ) {
			$status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.', 'online-shop' );
			wp_send_json_error( $status );
		}

        if( $request > 2){
            wp_send_json_error( );
        }

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Looks like a plugin is installed, but not active.
		if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
			$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$status['plugin']     = $plugin;
			$status['pluginName'] = $plugin_data['Name'];

			if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
				$result = activate_plugin( $plugin );

				if ( is_wp_error( $result ) ) {
					$status['errorCode']    = $result->get_error_code();
					$status['errorMessage'] = $result->get_error_message();
					wp_send_json_error( $status );
				}

				wp_send_json_success( $status );
			}
		}

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => sanitize_key( wp_unslash( $slug ) ),
				'fields' => array(
					'sections' => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			$status['errorMessage'] = $api->get_error_message();
			wp_send_json_error( $status );
		}

		$status['pluginName'] = $api->name;

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['errorMessage'] = $skin->get_error_messages();
			wp_send_json_error( $status );
		} elseif ( is_null( $result ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			WP_Filesystem();
			global $wp_filesystem;

			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'online-shop' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			wp_send_json_error( $status );
		}

		$install_status = install_plugin_install_status( $api );

		if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
			$result = activate_plugin( $install_status['file'] );

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();
				wp_send_json_error( $status );
			}
		}

		wp_send_json_success( $status );

	}

	/**
	 * Get Started Notice
	 *
	 * Handle Rating/Review Notice
	 * return void
	 */
	private function review_notice(){
		add_action( 'after_setup_theme', array( $this, 'setup_review_notice' ) );
		add_action( 'switch_theme', array( $this, 'remove_review_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'review_notice_enqueue' ) );
	}
	
	/**
	 * Set the Time
     * Aso call other necessary functions
     * 
	 * Active callback of after_setup_theme
	 * return void
	 */
	public function setup_review_notice() {

		// Set the installed time in `online_shop_theme_installed_time` option table.
		$option = get_option('online_shop_theme_installed_time' );

		if ( ! $option ) {
			update_option('online_shop_theme_installed_time', time() );
		}

		add_action( 'admin_notices', array( $this, 'display_review_notice' ), 0 );
		add_action( 'admin_init', array( $this, 'remove_theme_review_notice' ), 0 );
		add_action( 'admin_init', array( $this, 'remove_theme_review_notice_partially' ), 0 );

	}
	
	/**
	 * Display review notice
	 * Aso call other necessary functions
	 *
	 * Active callback of after_setup_theme
	 * return void
	 */
	public function display_review_notice() {

		global $current_user;
		$user_id                  = $current_user->ID;
		$ignored_notice           = get_user_meta( $user_id, 'remove_theme_review_notice', true );
		$ignored_notice_partially = get_user_meta( $user_id, 'nag_remove_theme_review_notice_partially', true );

		/**
		 * Return from notice display if:
		 *
		 * 1. The theme installed is less than 15 days ago.
		 * 2. If the user has ignored the message partially for 15 days.
		 * 3. Dismiss always if clicked on 'I Already Did' button.
		 */
		if ( ( get_option('online_shop_theme_installed_time' ) > strtotime( '-15 day' ) ) || ( $ignored_notice_partially > strtotime( '-15 day' ) ) || ( $ignored_notice ) ) {
			return;
		}
		?>
		<div class="notice updated at-review-notice">
			<p>
				<?php
				printf(
				/* Translators: %1$s current user display name. */
					esc_html__(
						'Howdy, %1$s! It seems that you have been using this theme for more than 15 days. We hope you are happy with everything that the theme has to offer. If you can spare a minute, please help us by leaving a 5-star review on WordPress.org.  By spreading the love, we can continue to develop new amazing features in the future, for free!', 'online-shop'
					),
					'<strong>' . esc_html( $current_user->display_name ) . '</strong>'
				);
				?>
			</p>

			<div class="links">
				<a href="https://wordpress.org/support/theme/online-shop/reviews/?filter=5#new-post" class="btn button-primary" target="_blank">
					<span class="dashicons dashicons-thumbs-up"></span>
					<span><?php esc_html_e( 'Sure', 'online-shop' ); ?></span>
				</a>

				<a href="?nag_remove_theme_review_notice_partially=0" class="btn at-danger-btn">
					<span class="dashicons dashicons-calendar"></span>
					<span><?php esc_html_e( 'Maybe later', 'online-shop' ); ?></span>
				</a>

				<a href="?nag_remove_theme_review_notice=0" class="btn at-success-btn">
					<span class="dashicons dashicons-smiley"></span>
					<span><?php esc_html_e( 'I already did', 'online-shop' ); ?></span>
				</a>

				<a href="<?php echo esc_url( 'https://wordpress.org/support/theme/online-shop/' ); ?>" class="btn at-default-btn" target="_blank">
					<span class="dashicons dashicons-edit"></span>
					<span><?php esc_html_e( 'Got theme support question?', 'online-shop' ); ?></span>
				</a>
			</div>

			<a class="notice-dismiss" style="text-decoration:none;" href="?nag_remove_theme_review_notice_partially=0"></a>
		</div>

		<?php
	}
	
	/**
	 * Remove notice permanently
	 *
	 * Active callback of after_setup_theme
	 * return void
	 */
	public function remove_theme_review_notice() {

		global $current_user;
		$user_id = $current_user->ID;

		/* If user clicks to ignore the notice, add info to user meta */
		if ( isset( $_GET['nag_remove_theme_review_notice'] ) && '0' == $_GET['nag_remove_theme_review_notice'] ) {
			add_user_meta( $user_id, 'remove_theme_review_notice', 'true', true );
		}
	}

	/**
	 * Remove notice partially
	 *
	 * Active callback of after_setup_theme
	 * return void
	 */
	public function remove_theme_review_notice_partially() {

		global $current_user;
		$user_id = $current_user->ID;

		/* If user clicks to ignore the notice, add that to their user meta */
		if ( isset( $_GET['nag_remove_theme_review_notice_partially'] ) && '0' == $_GET['nag_remove_theme_review_notice_partially'] ) {
			update_user_meta( $user_id, 'nag_remove_theme_review_notice_partially', time() );
		}

	}

	/**
	 * Remove the data set after the theme has been switched to other theme.
	 *
	 * Active callback of after_setup_theme
	 * return void
	 */
	public function remove_review_notice() {

		global $current_user;
		$user_id                  = $current_user->ID;
		$theme_installed_time     = get_option('online_shop_theme_installed_time' );
		$ignored_notice           = get_user_meta( $user_id, 'remove_theme_review_notice', true );
		$ignored_notice_partially = get_user_meta( $user_id, 'nag_remove_theme_review_notice_partially', true );

		// Delete options data.
		if ( $theme_installed_time ) {
			delete_option('online_shop_theme_installed_time' );
		}

		// Delete permanent notice remove data.
		if ( $ignored_notice ) {
			delete_user_meta( $user_id, 'remove_theme_review_notice' );
		}

		// Delete partial notice remove data.
		if ( $ignored_notice_partially ) {
			delete_user_meta( $user_id, 'nag_remove_theme_review_notice_partially' );
		}

	}

	/**
	 * Enqueue the required CSS file for theme review notice on admin page.
	 */
	public function review_notice_enqueue() {

		wp_enqueue_style( 'online-shop-review-notice', get_template_directory_uri()  . '/acmethemes/admin-notice/admin-notice.css' );

	}
}

/**
 * Begins execution of the hooks.
 *
 * @since    1.0.0
 */
function online_shop_notice_handler( ) {
	return Online_Shop_Notice_Handler::instance();
}
online_shop_notice_handler()->run();


/*For this theme only*/
if( !function_exists( 'online_shop_ai_update_image_size') ){
	function online_shop_ai_update_image_size(){
		/*Thumbnail Size*/
		update_option( 'thumbnail_size_w', 500 );
		update_option( 'thumbnail_size_h', 280 );
		update_option( 'thumbnail_crop', 1 );

		/*Medium Image Size*/
		update_option( 'medium_size_w', 690 );
		update_option( 'medium_size_h', 400 );

		/*Large Image Size*/
		update_option( 'large_size_w', 1080 );
		update_option( 'large_size_h', 530 );
	}
}
add_action( 'advanced_import_before_content_screen', 'online_shop_ai_update_image_size' );