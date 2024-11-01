<?php
/**
 * Uyond CDN Settings
 *
 * @since 1.0.0
 * @package Uyond_CND\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Uyond_Cdn_Setting_General
 *
 * SQL:
 * select * from wp0w_options where option_name = 'uyond_domain_key';
 * delete from wp0w_options where option_name = 'uyond_domain_key';
 */
class Uyond_Cdn_Setting_General {
	/**
	 * Singleton.
	 *
	 * @var Uyond_Cdn_Setting_General
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
		add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
		add_action( 'admin_post_uyond_cdn_unregister', array( Uyond_Cdn_Ops::instance(), 'unregister_domain_post' ) );
		add_action( 'admin_post_uyond_cdn_purge_cache', array( $this, 'purge_cache_post' ) );
	}

	/**
	 * Add plugin menu.
	 */
	public function add_plugin_menu() {
		add_menu_page(
			__( 'Uyond CDN', 'uyond-cdn' ),
			__( 'Uyond CDN', 'uyond-cdn' ),
			'manage_options',
			'uyond-cdn',
			array( $this, 'display_page' ),
			'data:image/svg+xml;base64,PHN2ZyBpZD0iYWZiMTE4OWQtZGIyYy00NGYwLWExNWMtMDUyMjQ4MjBkZTdmIiBkYXRhLW5hbWU9IkxheWVyIDEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDgwNC45NSA4MDMuNCI+PGRlZnM+PHN0eWxlPi5iYjYwMzkxZS01N2IwLTRiOTctODhkNy1jNTFlNDBlYmRhOWZ7ZmlsbDojYjg1YzJmO30uYTdjM2Y0Y2UtOWMzMS00ODM4LTlhOTktMjg3NTFmMjY1NzNhe2ZpbGw6I2ZmODA0MDt9PC9zdHlsZT48L2RlZnM+PHBhdGggY2xhc3M9ImJiNjAzOTFlLTU3YjAtNGI5Ny04OGQ3LWM1MWU0MGViZGE5ZiIgZD0iTTUyOS40MywxMjAuNDksNTAyLjE5LDI3NS43OGwwLC4yNEw0NzIuNTEsNDQ0LjkzcS03LjMzLDM5Ljg1LTMwLjA5LDYzdC02MS44LDIzLjE4cS0zMS43MiwwLTQ2LjM1LTE1Ljg2YTUwLjc3LDUwLjc3LDAsMCwxLTQuNTQtNS43MUMzNDQuMjQsNTc4LjQ3LDM5Mi45Miw2MjksNDU0Ljg4LDYzNy40NGExMzAuNDcsMTMwLjQ3LDAsMCwwLDE5LDEuMjNxNC43OCwwLDkuNTQtLjI4bC41MywwYy45NC0uMDYsMS44OC0uMTQsMi44MS0uMjNsLjU0LDBjLjg3LS4wOCwxLjczLS4xOCwyLjU5LS4yN2wuNzUtLjA4YzEtLjEzLDIuMDYtLjI2LDMuMDgtLjQxbC4yNiwwYzEuMS0uMTYsMi4yLS4zMywzLjMtLjUyaDBjMS4wNS0uMTgsMi4wOS0uMzcsMy4xNC0uNTdsLjE4LDBjMS4wNy0uMjEsMi4xMy0uNDMsMy4xOS0uNjZsLjExLDBxNC44OC0xLjA3LDkuNjktMi40OGwuMTUsMGMxLjA3LS4zMSwyLjEzLS42NCwzLjE5LTFsLjA2LDBhMTU3LjY5LDE1Ny42OSwwLDAsMCw1Ny4xOC0zMy4zOGMxLjM0LTEuMzYsMTguNDMtMTMsNDUuMTktNTQuODksNy4xNC0xMS4xOCwxMS42LTI0LjA1LDE2LjM0LTM2LjkzYTM2NCwzNjQsMCwwLDAsMTYuNTEtNjEuODRsNTYuOTEtMzI0LjQ0WiIvPjxwYXRoIGNsYXNzPSJiYjYwMzkxZS01N2IwLTRiOTctODhkNy1jNTFlNDBlYmRhOWYiIGQ9Ik00NDkuMjUsNjUzLjE5QzM3Nyw2NDMuMzIsMzIzLjUyLDU3OS41MywzMTMuNzEsNTAwLjQ2Yy0uMTYtMS4yNS0uMy0yLjUxLS40NC0zLjc3LS4wNi0uNjMtLjEzLTEuMjctLjE5LTEuOTFzLS4xMi0xLjI5LS4xOC0xLjkzYy0uMTItMS4zMS0uMjItMi42Mi0uMzEtMy45NC0uMTQtMi4wOS0uMjYtNC4xOS0uMzQtNi4zbDAtMi4yNGMwLS42NCwwLTEuMjksMC0xLjk0LDAtMS41MiwwLTMsLjA2LTQuNTJhMTQ2LjE5LDE0Ni4xOSwwLDAsMSwyLjQyLTIyLjgzbDcuNDYtNDIuNTYuMTYtLjg4LDUwLjM4LTI4Ny4xNUgxODkuNjJsLTU4LDMzMC41OWEzNDYuOCwzNDYuOCwwLDAsMC01LjgsNjNxMCw5Ni4xNCw1Ny41OCwxNDcuNDd0MTU2LjE5LDUxLjM4cTc0LjU3LDAsMTQwLTI5LjgyYTI4NS4yMiwyODUuMjIsMCwwLDAsNjcuOTQtNDMuMzlBMjk3LjA2LDI5Ny4wNiwwLDAsMCw1NzUuNzEsNjEyYy0yOS43OCwyNi45MS02Ny4wNiw0Mi42OS0xMDYuMjcsNDIuNDVBMTQyLDE0MiwwLDAsMSw0NDkuMjUsNjUzLjE5WiIvPjxwb2x5Z29uIGNsYXNzPSJiYjYwMzkxZS01N2IwLTRiOTctODhkNy1jNTFlNDBlYmRhOWYiIHBvaW50cz0iNTc1LjcyIDYxMi4wNCA1NzUuNjcgNjExLjk5IDU3NS43MSA2MTIuMDQgNTc1LjcyIDYxMi4wNCA1NzUuNzIgNjEyLjA0Ii8+PHBhdGggY2xhc3M9ImE3YzNmNGNlLTljMzEtNDgzOC05YTk5LTI4NzUxZjI2NTczYSIgZD0iTTU3NS43MSw2MTJhMjk3LjA2LDI5Ny4wNiwwLDAsMS0yOC4xNSwyNy42NiwyODUuMjIsMjg1LjIyLDAsMCwxLTY3Ljk0LDQzLjM5cS02NS40NiwyOS44Mi0xNDAsMjkuODItOTguNjEsMC0xNTYuMTktNTEuMzhUMTI1LjgyLDUxNC4wNmEzNDYuOCwzNDYuOCwwLDAsMSw1LjgtNjNsNTgtMzMwLjU5SDM3Mi43M0wzMjIuMzUsNDA3LjY0bC0uMTYuODgtNy40Niw0Mi41NmExNDYuMTksMTQ2LjE5LDAsMCwwLTIuNDIsMjIuODNsMCw2LjQ2LDAsMi4yNGMuMDgsMi4xMS4yLDQuMjEuMzQsNi4zLjA5LDEuMzIuMTksMi42My4zMSwzLjk0LjA2LjY0LjExLDEuMjkuMTgsMS45M3MuMTMsMS4yOC4xOSwxLjkxYy4xNCwxLjI2LjI4LDIuNTIuNDQsMy43Nyw5LjgxLDc5LjA3LDYzLjMzLDE0Mi44NiwxMzUuNTQsMTUyLjczYTE0MiwxNDIsMCwwLDAsMjAuMTksMS4zQzUwOC42NSw2NTQuNzMsNTQ1LjkzLDYzOSw1NzUuNzEsNjEyWiIvPjwvc3ZnPg=='
		);

		add_submenu_page(
			'uyond-cdn',
			__( 'Uyond CDN General', 'uyond-cdn' ),
			__( 'General', 'uyond-cdn' ),
			'manage_options',
			'uyond-cdn',
			array( $this, 'display_page' )
		);

		add_submenu_page(
			'uyond-cdn',
			__( 'Uyond CDN Status', 'uyond-cdn' ),
			__( 'Status', 'uyond-cdn' ),
			'manage_options',
			'uyond-cdn-status',
			array( Uyond_Cdn_Setting_Status::instance(), 'display_page' )
		);

		if ( ! Uyond_Cdn_Ops::instance()->is_registered() ) {
			add_submenu_page(
				'uyond-cdn',
				__( 'Uyond CDN Setup Wizard', 'uyond-cdn' ),
				__( 'Setup Wizard', 'uyond-cdn' ),
				'manage_options',
				'uyond-cdn-setup',
				array( Uyond_Cdn_Setting_Wizard::instance(), 'display_page' )
			);
		}

		add_submenu_page(
			'uyond-cdn',
			__( 'Uyond CDN About', 'uyond-cdn' ),
			__( 'About', 'uyond-cdn' ),
			'manage_options',
			'uyond-cdn-about',
			array( Uyond_Cdn_Setting_About::instance(), 'display_page' )
		);
	}

	/**
	 * Display Tabs.
	 *
	 * @param string $active_tab_name Active Tab Name.
	 */
	public function display_tabs( $active_tab_name ) {
		echo '<img class="uyond-setting-banner" src="' . esc_url( UYOND_CDN_ASSETS_IMG_URL ) . 'banner.png" />';

		$tabs = array(
			'General' => array(
				'link'  => admin_url( 'admin.php?page=uyond-cdn' ),
				'class' => '',
			),
			'Status'  => array(
				'link'  => admin_url( 'admin.php?page=uyond-cdn-status' ),
				'class' => '',
			),
			'About'   => array(
				'link'  => admin_url( 'admin.php?page=uyond-cdn-about' ),
				'class' => '',
			),
		);

		$tabs[ $active_tab_name ]['class'] = 'tab-active';

		?>
	<div class="uyond-setting-page-title">
		<?php
		foreach ( $tabs as $key => $value ) {
			echo sprintf( '<a href="%s" class="%s">%s</a>', esc_attr( $value['link'] ), esc_attr( $value['class'] ), esc_attr( $key ) );
		}
		?>
	</div>
		<?php
	}

	/**
	 * Print page.
	 */
	public function display_page() {
		$key      = Uyond_Cdn_Ops::instance()->get_domain_key();
		$site_url = Uyond_Cdn_Ops::instance()->get_registered_site_url();

		?>
		<div class="wrap uyond-settings-div">
		<?php $this->display_tabs( 'General' ); ?>
			<p>This plugin automatically offload your assets files to Uyond CDN.  This helps to increase to page loading time.</p>
			<?php
			if ( $key ) {

				?>
			<!-- <p class="uyond-title">You are now registered to Uyond CDN Service.</p> -->
			<p>Current Status: <span class="uyond-status-start">Start</span></p>

			<div class="reg">
			<h4>Registration Info</h4>
				<p>Registration Key: <span class="uyond-bold"><?php echo esc_html( $key ); ?></span><br />
				Site URL: <span class="uyond-bold"><?php echo esc_html( $site_url ); ?></span></p>
			</div>
		</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'uyond-unregister' ); ?>
				<input name="action" type="hidden" value="uyond_cdn_unregister" />
				<input type="submit" class="button button-primary" value="Remove Setup" />
			</form>
				<?php
			} else {
				echo sprintf( '<a href="%s" class="button button-primary">Setup Wizard</a>', esc_url( admin_url( 'admin.php?page=uyond-cdn-setup' ) ) );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Purge Cache Post.
	 */
	public function purge_cache_post() {
		check_admin_referer( 'uyond-cdn-purge-cache' );

		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		wp_remote_post(
			Uyond_Cdn_Ops::UYOND_API_URL . '/domain/purge-by-key',
			array(
				'body' => array(
					'domainKey' => Uyond_Cdn_Ops::instance()->get_domain_key(),
					'secretKey' => Uyond_Cdn_Ops::instance()->get_secret_key(),
				),
			)
		);

		wp_safe_redirect( admin_url( 'admin.php?page=uyond-cdn-status&purged=true' ) );
		exit;
	}
}
