<?php
/**
 * Uyond_CDN_Main class.
 *
 * @since 1.0.0
 * @package Uyond_CND\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Uyond_CND_Main class.
 */
class Uyond_CDN_Main {
	/**
	 * Singleton instance.
	 *
	 * @var Uyond_CDN_Main
	 */
	protected static $instance;

	/**
	 * Singletone instance.
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
		if ( ! is_admin() ) {
			if ( Uyond_Cdn_Ops::instance()->is_registered() ) {
				add_action( 'wp_head', array( $this, 'start_buffer' ), 0 );
				add_action( 'wp_footer', array( $this, 'end_buffer' ), 9999999 );
			}
		}

		require_once plugin_dir_path( __FILE__ ) . '/class-uyond-cdn-rewritter.php';

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );
	}

	/**
	 * Admin Enqueue Script.
	 */
	public function admin_enqueue_script() {
		wp_enqueue_style( 'uyond-main', UYOND_CDN_ASSETS_CSS_URL . 'main.css', null, UYOND_CDN_VERSION );
	}

	/**
	 * Buffer Callback.
	 *
	 * @param buffer $buffer Buffer.
	 */
	public function buffer_callback( $buffer ) {
		$rewriter = self::get_rewriter();
		$buffer   = $rewriter->rewrite( $buffer );
		return $buffer;
	}

	/**
	 * End Buffer.
	 */
	public function end_buffer() {
		ob_end_flush();
	}

	/**
	 * Start Buffer.
	 */
	public function start_buffer() {
		ob_start( array( $this, 'buffer_callback' ) );
	}

	/**
	 * Get Rewriter.
	 */
	public static function get_rewriter() {
		return new Uyond_CDN_Rewritter(
			get_option( 'home' ),
			'https://cdn-uyond.com/',
			'wp-content,wp-includes', // dirs.
			array( '.php' ), // excludes.
			0, // relative.
			1, // https.
			$domain_key = Uyond_Cdn_Ops::instance()->get_domain_key(), // keycdn_api_key.
		);
	}

	/**
	 * Rewrite the content.
	 *
	 * @param string $html Html.
	 */
	public static function rewrite_the_content( $html ) {
		$rewriter = self::get_rewriter();
		return $rewriter->rewrite( $html );
	}
}
