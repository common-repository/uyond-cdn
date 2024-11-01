<?php
/**
 * Uyond CDN Settings
 *
 * @since 1.0.0
 * @package Uyond_CND\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Uyond_Cdn_Setting_About
 */
class Uyond_Cdn_Setting_About {
	/**
	 * Singleton.
	 *
	 * @var Uyond_Cdn_Setting_About
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
	 * Display Page.
	 */
	public function display_page() {
		?>
		<div class="wrap uyond-settings-div">
		<?php Uyond_Cdn_Setting_General::instance()->display_tabs( 'About' ); ?>
		<p>Uyond CDN is a CDN service specifically made for WordPress only.  This is currently offered as a free service, and there is no plan to change this in the future.  Visit our <a href="https://www.uyond.com/cdn" target="_blank">website</a> to know more.</p>
			<p>Also checkout our profressional <a href="https://www.uyond.com" target="_blank">WordPress hosting service</a> in Uyond.  We offer outstanding performance hosting with very affortable price.</p>
		</div>
		<?php
	}
}
