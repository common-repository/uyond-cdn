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
class Uyond_Cdn_Ops {
	const OPTION_DOMAIN_KEY = 'uyond_domain_key';
	const OPTION_SITE_URL   = 'uyond_cdn_site_url';
	const OPTION_SECRET_KEY = 'uyond_cdn_secret_key';
	const UYOND_API_URL     = 'https://api.uyond.com';

	/**
	 * Singleton instance.
	 *
	 * @var Uyond_Cdn_Ops
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
		add_action( 'admin_init', array( $this, 'upgrade_secret_key' ) );
	}

	/**
	 * Get domain key.
	 */
	public function get_domain_key() {
		return get_option( self::OPTION_DOMAIN_KEY );
	}

	/**
	 * Get Secret Key.
	 */
	public function get_secret_key() {
		return get_option( self::OPTION_SECRET_KEY );
	}

	/**
	 * Get Registered Site URL.
	 */
	public function get_registered_site_url() {
		return get_option( self::OPTION_SITE_URL );
	}

	/**
	 * Get domain.
	 */
	public function get_domain() {
		return get_site_url();
	}

	/**
	 * Is Registered.
	 */
	public function is_registered() {
		return $this->get_domain_key() !== false;
	}

	/**
	 * Register domain remote.
	 */
	public function register_domain_remote() {
		$email  = get_option( 'admin_email' );
		$domain = $this->get_domain();

		$args = array(
			'body' => array(
				'email'  => $email,
				'domain' => $domain,
			),
		);

		$res = wp_remote_post( self::UYOND_API_URL . '/domain/create', $args );

		if ( 200 !== $res['response']['code'] ) {
			return false;
		}

		$obj        = json_decode( $res['http_response']->get_data(), true );
		$domain_key = $obj['domainKey'];
		$secret_key = $obj['secretKey'];

		add_option( self::OPTION_DOMAIN_KEY, $domain_key, '', false );
		add_option( self::OPTION_SECRET_KEY, $secret_key, '', false );
		add_option( self::OPTION_SITE_URL, get_site_url(), '', false );

		return true;
	}

	/**
	 * Get domain key remote.
	 */
	private function get_domain_key_remote() {
		$url = sprintf( self::UYOND_API_URL . '/domain/getKey?domain=%s', get_site_url() );
		$res = wp_remote_get( $url );

		if ( 200 !== $res['response']['code'] ) {
			return false;
		}

		$obj = json_decode( $res['http_response']->get_data(), true );

		$domain_key = $obj['domainKey'];

		if ( empty( $domain_key ) ) {
			return false;
		}

		return $domain_key;
	}

	/**
	 * Unregister Domain Post.
	 */
	public function unregister_domain_post() {
		check_admin_referer( 'uyond-unregister' );

		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		delete_option( self::OPTION_DOMAIN_KEY );
		delete_option( self::OPTION_SECRET_KEY );
		delete_option( self::OPTION_SITE_URL );
		wp_safe_redirect( admin_url( 'admin.php?page=uyond-cdn' ) );
	}

	/**
	 * Add Site URL Backward Compatable.
	 *
	 * To Version 1.0.6, this option will be added during the registration process.
	 * So, for older version, we need to add them back in admin_init stage.
	 */
	public function upgrade_secret_key() {
		// if no secret key set.
		if ( get_option( self::OPTION_SECRET_KEY ) ) {
			return;
		}

		if ( ! $this->is_registered() ) {
			return;
		}

		if ( get_transient( 'uyond_upgrade_secret' ) ) {
			return;
		}

		set_transient( 'uyond_upgrade_secret', 'start', 120 ); // 2 minutes.

		$args = array(
			'body' => array(
				'domainKey' => $this->get_domain_key(),
				'siteUrl'   => get_site_url(),
				'version'   => UYOND_CDN_VERSION,
			),
		);

		$res = wp_remote_post( self::UYOND_API_URL . '/domain/upgrade-secret-key', $args );

		if ( 200 === $res['response']['code'] ) {

			$obj        = json_decode( $res['http_response']->get_data(), true );
			$secret_key = $obj['secretKey'];

			add_option( self::OPTION_SECRET_KEY, $secret_key, '', false );
			add_option( self::OPTION_SITE_URL, get_site_url(), '', false );
		}

		delete_transient( 'uyond_upgrade_secret' );
	}
}
