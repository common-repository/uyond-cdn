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
class Uyond_Cdn_Setting_Status {

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
		add_action( 'admin_notices', array( $this, 'purge_notice' ) );
	}

	/**
	 * Print Page.
	 */
	public function display_page() {
		echo '<div class="wrap uyond-status-div">';
		$this->display_status_table();
		echo '</div>';
	}

	/**
	 * Display statust table.
	 */
	private function display_status_table() {
		$status = $this->get_loader_status();

		Uyond_Cdn_Setting_General::instance()->display_tabs( 'Status' );

		$this->render_purge_cache_button();
		if ( false === $status ) {
			echo '<p>Error fetching status data.</p>';
			return;
		}

		echo sprintf( "<p>%s <span class='uyond-bold'>%s</span></p>", esc_html__( 'Number of fetched objects:', 'uyond-cdn' ), count( $status ) );

		if ( count( $status ) === 0 ) {
			echo sprintf( '<p>%s</p>', esc_html__( 'If this is your first installation, it takes time to see resources being fetched.  Data should be showing within 10 minutes.', 'uyond-cdn' ) );
		} else {
			?>
			<table class="uyond-status-table">
				<tr>
					<th><?php esc_html_e( 'URL', 'uyond-cdn' ); ?></th>
					<th><?php esc_html_e( 'Size', 'uyond-cdn' ); ?></th>
					<th><?php esc_html_e( 'Reduced', 'uyond-cdn' ); ?></th>
					<th><?php esc_html_e( 'Status', 'uyond-cdn' ); ?></th>
				</tr>

			<?php
			$count = count( $status );
			for ( $i = 0; $i < $count; $i++ ) {
				?>
				<tr>
					<td><?php echo esc_html( $status[ $i ]['key'] ); ?></td>
					<td class="uyond-status-size"><?php echo esc_html( $this->format_bytes( $status[ $i ]['size'] ) ); ?></td>
					<td class="uyond-status-diff"><?php $this->display_size_diff( $status[ $i ] ); ?></td>
					<td class="uyond-status-fetched"><?php esc_html_e( 'Fetched', 'uyond-cdn' ); ?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
	}

	/**
	 * Get loader status.
	 */
	private function get_loader_status() {
		$url = sprintf(
			Uyond_Cdn_Ops::UYOND_API_URL . '/domain/status-by-key?domainKey=%s&secretKey=%s',
			Uyond_Cdn_Ops::instance()->get_domain_key(),
			Uyond_Cdn_Ops::instance()->get_secret_key()
		);

		$res = wp_remote_get( $url );

		if ( 200 !== $res['response']['code'] ) {
			return false;
		}

		return json_decode( $res['http_response']->get_data(), true );
	}

	/**
	 * Render Purge Cache Button
	 */
	private function render_purge_cache_button() {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'uyond-cdn-purge-cache' ); ?>
		<input name="action" type="hidden" value="uyond_cdn_purge_cache" />
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Purge Cache">
		</form>
		<?php
	}

	/**
	 * Format bytes
	 *
	 * @param int $size size.
	 * @param int $precision precision.
	 */
	private function format_bytes( $size, $precision = 1 ) {
		$base     = log( $size, 1024 );
		$suffixes = array( 'B', 'KB', 'MB', 'GB', 'TB' );

		return round( pow( 1024, $base - floor( $base ) ), $precision ) . ' ' . $suffixes[ floor( $base ) ];
	}

	/**
	 * Display Size Diff.
	 *
	 * @param int $status_item Status Item.
	 */
	private function display_size_diff( $status_item ) {
		$size = $status_item['size'];

		if ( ! isset( $status_item['originalSize'] ) ) {
			return;
		}

		$original_size = $status_item['originalSize'];

		if ( ! $original_size ) {
			return;
		}

		$diff = 100 * ( $original_size - $size ) / $original_size;

		if ( $diff > 0 ) {
			echo sprintf( "<span class='uyond-span-reduced'>-%d%%</span>", esc_html( $diff ) );
		}
	}

	/**
	 * Purge Notice.
	 */
	public function purge_notice() {

		$screen = get_current_screen();

		if ( 'uyond-cdn_page_uyond-cdn-status' !== $screen->id ) {
			return;
		}

		// phpcs:ignore
		if ( ! isset( $_GET['purged'] ) ) {
			return;
		}

		// phpcs:ignore
		if ( 'true' !== $_GET['purged'] ) {
			return;
		}

		?>
		<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'All cached content is purged successfully. - Uyond CDN', 'uyond-cdn' ); ?></p>
		</div>
		<?php
	}
}
