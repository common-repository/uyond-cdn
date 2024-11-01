<?php
/**
 * Uyond CDN Settings
 *
 * @since 1.0.0
 * @package Uyond_CND\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Uyond_Cdn_Setup
 */
class Uyond_Cdn_Setting_Wizard {


	/**
	 * Singleton.
	 *
	 * @var Uyond_Cdn_Setup
	 */
	protected static $instance;

	/**
	 * Singleton instance.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'redirect_setup' ) );
		add_action( 'admin_post_uyond_cdn_register', array( $this, 'register_domain_post' ) );
	}

	/**
	 * Load Setup.
	 */
	public function redirect_setup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_option( Uyond_CDN::UYOND_CND_SETUP_META ) === 'start' ) {

			update_option( Uyond_CDN::UYOND_CND_SETUP_META, 'done' );

			if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
				return;
			}

      // phpcs:ignore
			$url = $_SERVER['REQUEST_URI'];

			if ( wp_parse_url( $url, PHP_URL_QUERY ) === 'page=uyond-cdn-setup' ) {
				return;
			}

			// don't route if it's registered.
			if ( Uyond_Cdn_Ops::instance()->is_registered() ) {
				return;
			}

			wp_safe_redirect( admin_url( 'admin.php?page=uyond-cdn-setup' ) );
			exit;
		}
	}

	/**
	 * Render register form.
	 */
	private function render_register_form() {
		$email  = get_option( 'admin_email' );
		$domain = Uyond_Cdn_Ops::instance()->get_domain();

		?>

	<table>
	<tr>
		<td><?php esc_html_e( 'Email:', 'uyond-cdn' ); ?></td>
		<td><span class="uyond-bold"><?php echo esc_html( $email ); ?></span></td>
	</tr>
	<tr>
		<td><?php esc_html_e( 'Site URL:', 'uyond-cdn' ); ?></td>
		<td><span style="font-weight: bold;"><?php echo esc_html( $domain ); ?></span></td>
	</tr>
	</table>
	<p class="uyond-notes">By using this service, you agree on our <a href="https://www.uyond.com/cdn-tc" target="_blank">terms and conditions</a> here.</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'uyond-register' ); ?>
			<input name="action" type="hidden" value="uyond_cdn_register" />
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Register">
		</form>
		<?php
	}

	/**
	 * Print HTML.
	 */
	public function display_page() {
		add_option( Uyond_CDN::UYOND_CND_SETUP_META, 'done', '', false );
		update_option( Uyond_CDN::UYOND_CND_SETUP_META, 'done' );

		$key = Uyond_Cdn_Ops::instance()->get_domain_key();

		?>
		<div class="uyond-setup-div wrap">
			<?php
		    	// phpcs:ignore
				echo '<img width="200" src="' . UYOND_CDN_ASSETS_IMG_URL . 'logo.png' . '" />';
			?>
			<hr />
			<p class="uyond-h1-title">Welcome to Uyond CDN</p>
			<p>Register your WordPress to start using our CDN service for free now.</p>

			<?php $this->render_register_form(); ?>

			<a href="http://uyond.com/cdn" target="_blank">Know More About Uyond CDN</a>
		</div>
		<?php
	}

	/**
	 * Register domain post
	 */
	public function register_domain_post() {
		check_admin_referer( 'uyond-register' );

		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		Uyond_Cdn_Ops::instance()->register_domain_remote();
		wp_safe_redirect( admin_url( 'admin.php?page=uyond-cdn' ) );
		exit;
	}
}
