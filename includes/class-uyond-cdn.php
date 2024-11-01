<?php
/**
 * Main class of Uyond_CND.
 *
 * @since 1.0.0
 * @package Uyond_CND\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Uyond_CDN Class
 */
class Uyond_CDN {
	const MINIMUM_PHP_VERSION = '5.6.0';
	const MINIMUM_WP_VERSION  = '4.4';

	const PLUGIN_NAME          = 'Uyond CDN';
	const UYOND_CND_SETUP_META = 'uyond_cdn_setup';


	/**
	 * Variable $notices
	 *
	 * @var Array
	 */
	protected $notices = array();

	/**
	 * Singleton instance of Uyond_CND.
	 *
	 * @var Uyond_CDN
	 */
	protected static $instance;

	/**
	 * Init - hook into events.
	 */
	protected function __construct() {
		register_activation_hook( UYOND_CDN_FILE, array( $this, 'activate_plugin' ) );
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		add_action( 'admin_init', array( $this, 'add_plugin_notices' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		add_action( 'init', array( $this, 'load_textdomain' ) );

		$plugin = plugin_basename( UYOND_CDN_FILE );
		add_filter( "plugin_action_links_$plugin", array( $this, 'my_plugin_settings_link' ) );
	}

	/**
	 * Init Plugin
	 */
	public function init_plugin() {
		// load the main plugin class.
		require_once dirname( __FILE__ ) . '/class-uyond-cdn-main.php';
		require_once dirname( __FILE__ ) . '/class-uyond-cdn-setting-general.php';
		require_once dirname( __FILE__ ) . '/class-uyond-cdn-setting-wizard.php';
		require_once dirname( __FILE__ ) . '/class-uyond-cdn-setting-status.php';
		require_once dirname( __FILE__ ) . '/class-uyond-cdn-setting-about.php';
		require_once dirname( __FILE__ ) . '/class-uyond-cdn-ops.php';

		Uyond_CDN_Main::instance();
		Uyond_Cdn_Setting_General::instance();
		Uyond_CDN_Setting_Wizard::instance();
		Uyond_CDN_Setting_Status::instance();
		Uyond_Cdn_Setting_About::instance();
	}

	/**
	 * Activate plugin
	 */
	public function activate_plugin() {
		if ( ! $this->is_environment_compatible() ) {
			$this->deactivate_plugin();
			wp_die( esc_html( self::PLUGIN_NAME . ' could not be activated. ' . $this->get_environment_message() ) );
			return;
		}

		add_option( self::UYOND_CND_SETUP_META, 'start', '', false );
	}

	/**
	 * Is environment compatible
	 */
	protected function is_environment_compatible() {
		return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Add plugin notices
	 */
	public function add_plugin_notices() {
	}

	/**
	 * Add admin notice
	 *
	 * @param string $slug Slug.
	 * @param string $class Class.
	 * @param string $message Message.
	 */
	protected function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}

	/**
	 * Admin notices
	 */
	public function admin_notices() {
		foreach ( (array) $this->notices as $notice_key => $notice ) :
			?>
			<div class="<?php echo esc_attr( $notice['class'] ); ?>">
				<p><?php echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) ); ?></p>
			</div>
			<?php
		endforeach;
	}

	/**
	 * My plugin settings link
	 *
	 * @param array $actions array.
	 */
	public function my_plugin_settings_link( $actions ) {
		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=uyond-cdn' ), 'Settings' ) );
		return $actions;
	}

	/**
	 * Load ttextdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'uyond-cdn', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * Singleton instance of Uyond_CDN
	 *
	 * @return Uyond_CDN
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
