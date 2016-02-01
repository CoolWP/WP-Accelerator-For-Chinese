<?php
/**
 * @author: suifengtec coolwp.com
 * @date:   2016-01-31 14:17:43
 * @last Modified by:   suifengtec coolwp.com
 * @last Modified time: 2016-01-31 14:18:34
 */

defined('ABSPATH') || exit;
if (!class_exists('SpeedUP4CN_img')) {

	class SpeedUP4CN_img {

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
	$GLOBALS['SpeedUP4CN_img'] = SpeedUP4CN_img::instance();

}
/*
Okay, You can  code your awesome plugin now!
 */