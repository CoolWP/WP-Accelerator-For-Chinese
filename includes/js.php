<?php
/**
 * @author: suifengtec coolwp.com
 * @date:   2016-01-31 14:17:28
 * @last Modified by:   suifengtec coolwp.com
 * @last Modified time: 2016-01-31 14:19:44
 */
/**
 * Plugin Name: Name
 * Plugin URI: http://coolwp.com/slug.html
 * Description: Description.
 * Version: 0.9.0
 * Author: suifengtec
 * Author URI:  http://coolwp.com
 * Author Email: support@coolwp.com
 * Requires at least: WP 3.8
 * Tested up to: WP 4.4
 * Text Domain: cwp
 * Domain Path: /languages
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

if (!class_exists('SpeedUP4CN_js')) {

	class SpeedUP4CN_js {

		protected static $_instance = null;
		protected $is_debug = false;

		public static function instance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __clone() {}
		public function __wakeup() {}

		public function __construct() {

			$this->is_debug = false;

			add_action('plugins_loaded', array($this, 'plugins_loaded'));

		}
		public function plugins_loaded() {

		}

	} /*//CLASS*/
	$GLOBALS['SpeedUP4CN_js'] = SpeedUP4CN_js::instance();

}
/*
Okay, You can  code your awesome plugin now!
 */