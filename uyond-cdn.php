<?php
/**
 * Plugin Name: Uyond CDN
 * Plugin URI: https://uyond.com/cdn
 * Description: High Performance CDN Service for WordPress Only

 * Author: Uyond
 * Author URI: https://www.uyond.com/cdn
 * Version: 1.0.9
 * Text Domain: uyond-cdn
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2020, HIS IT Solution Limited (info@hishk.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Uyond-Cdn
 * @author    UYOND DEV
 * @category  Plugin
 * @copyright Copyright (c) 2021, UYOND (hi@uyond.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/* Check & Quit */
defined( 'ABSPATH' ) || exit;

define( 'UYOND_CDN_VERSION', '1.0.9' );

define( 'UYOND_CDN_FILE', __FILE__ );
define( 'UYOND_CDN_URL', plugin_dir_url( UYOND_CDN_FILE ) );
define( 'UYOND_CDN_ASSETS_URL', UYOND_CDN_URL . 'assets/' );
define( 'UYOND_CDN_ASSETS_CSS_URL', UYOND_CDN_ASSETS_URL . 'css/' );
define( 'UYOND_CDN_ASSETS_IMG_URL', UYOND_CDN_ASSETS_URL . 'img/' );

require_once dirname( __FILE__ ) . '/includes/class-uyond-cdn.php';

Uyond_CDN::instance();
