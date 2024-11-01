<?php
/**
 * Uyond_CDN_Rewritter class.
 *
 * @since 1.0.0
 * @package Uyond_CND\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * CDN_Enabler_Rewriter
 *
 * @since 0.0.1
 */
class Uyond_CDN_Rewritter {

	/**
	 * Origin Url.
	 *
	 * @var string
	 */
	protected $blog_url = null;

	/**
	 * CDN URl
	 *
	 * @var string
	 */
	protected $cdn_url = null;

	/**
	 * Included directories.
	 *
	 * @var array
	 */
	protected $dirs = null;

	/**
	 * Excludes.
	 *
	 * @var array
	 */
	protected $excludes = array();

	/**
	 * Use CDN on relative paths.
	 *
	 * @var bool
	 */
	protected $relative = false;

	/**
	 * Use CDN on HTTPS.
	 *
	 * @var bool
	 */
	protected $https = false;

	/**
	 * Optional API key for KeyCDN.
	 *
	 * @var string
	 */
	protected $keycdn_api_key = null;

	/**
	 * Optional KeyCDN Zone ID.
	 *
	 * @var string
	 */
	protected $keycdn_zone_id = null;

	protected const ACCEPTED_EXTS = array(
		'3gp',
		'gif',
		'jpg',
		'jpeg',
		'png',
		'ico',
		'asx',
		'pls',
		'mp3',
		'mid',
		'wav',
		'swf',
		'flv',
		'html',
		'htm',
		'txt',
		'js',
		'css',
		'uha',
		'woff',
		'ttf',
		'svg',
		'eot',
		'webp',
	);

	/**
	 * Constructor.
	 *
	 * @param string $blog_url       Blog URL.
	 * @param string $cdn_url        CDN URL.
	 * @param string $dirs           Dirs.
	 * @param array  $excludes       Excludes.
	 * @param string $relative       Relative.
	 * @param string $https          Https.
	 * @param string $keycdn_api_key API Key.
	 */
	public function __construct(
		$blog_url,
		$cdn_url,
		$dirs,
		array $excludes,
		$relative,
		$https,
		$keycdn_api_key
	) {
		$this->blog_url       = $blog_url;
		$this->cdn_url        = $cdn_url;
		$this->dirs           = $dirs;
		$this->excludes       = $excludes;
		$this->relative       = $relative;
		$this->https          = $https;
		$this->keycdn_api_key = $keycdn_api_key;
	}

	/**
	 * Exclude assets that should not be rewritten.
	 *
	 * @param  string $asset current asset.
	 * @return boolean true if need to be excluded.
	 */
	protected function exclude_asset( &$asset ) {
		foreach ( $this->excludes as $exclude ) {
			if ( ! ! $exclude && stristr( $asset, $exclude ) !== false ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Relative url.
	 *
	 * @param  string $url a full url.
	 * @return string protocol relative url.
	 */
	protected function relative_url( $url ) {
		return substr( $url, strpos( $url, '//' ) );
	}

	/**
	 * Exclude Ext
	 *
	 * @param  string $url a full url.
	 * @return boolean true or false.
	 */
	protected function exclude_ext( $url ) {
		$ext = pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION );
		return ! in_array( strtolower( $ext ), $this::ACCEPTED_EXTS, true );
	}

	/**
	 * Rewrite url.
	 *
	 * @param  string $asset current asset.
	 * @return string updated url if not excluded.
	 */
	protected function rewrite_url( &$asset ) {
		if ( $this->exclude_asset( $asset[0] ) ) {
			return $asset[0];
		}

		if ( $this->exclude_ext( $asset[0] ) ) {
			return $asset[0];
		}

		// Don't rewrite if in preview mode.
		if ( is_admin_bar_showing()
			&& is_preview()
		) {
			return $asset[0];
		}

		$blog_url   = $this->relative_url( $this->blog_url );
		$subst_urls = array( 'http:' . $blog_url );

		// rewrite both http and https URLs if we ticked 'enable CDN for HTTPS connections.
		if ( $this->https ) {
			$subst_urls = array(
				'http:' . $blog_url,
				'https:' . $blog_url,
			);
		}

		// is it a protocol independent URL?
		if ( strpos( $asset[0], '//' ) === 0 ) {
			return str_replace( $blog_url, $this->cdn_url . $this->keycdn_api_key, $asset[0] );
		}

		// check if not a relative path.
		if ( ! $this->relative || strstr( $asset[0], $blog_url ) ) {
			return str_replace( $subst_urls, $this->cdn_url . $this->keycdn_api_key, $asset[0] );
		}

		// relative URL.
		return $this->cdn_url . $this->keycdn_api_key . $asset[0];
	}


	/**
	 * Get directory scope.
	 *
	 * @return string directory scope
	 */
	protected function get_dir_scope() {
		$input = explode( ',', $this->dirs );

		// default.
		if ( '' === $this->dirs || count( $input ) < 1 ) {
			return 'wp\-content|wp\-includes';
		}

		return implode( '|', array_map( 'quotemeta', array_map( 'trim', $input ) ) );
	}


	/**
	 * Rewrite url.
	 *
	 * @param  string $html current raw HTML doc.
	 * @return string updated HTML doc with CDN links.
	 */
	public function rewrite( $html ) {
		// check if HTTPS and use CDN over HTTPS enabled.
		if ( ! $this->https && isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
			return $html;
		}

		// get dir scope in regex format.
		$dirs     = $this->get_dir_scope();
		$blog_url = $this->https
		? '(https?:|)' . $this->relative_url( quotemeta( $this->blog_url ) )
		: '(http:|)' . $this->relative_url( quotemeta( $this->blog_url ) );

		// regex rule start.
		$regex_rule = '#(?<=[(\"\'])';

		// check if relative paths.
		if ( $this->relative ) {
			$regex_rule .= '(?:' . $blog_url . ')?';
		} else {
			$regex_rule .= $blog_url;
		}

		// regex rule end.
		$regex_rule .= '/(?:((?:' . $dirs . ')[^\"\')]+)|([^/\"\']+\.[^/\"\')]+))(?=[\"\')])#';

		// call the cdn rewriter callback.
		$cdn_html = preg_replace_callback( $regex_rule, array( $this, 'rewrite_url' ), $html );

		return $cdn_html;
	}
}
