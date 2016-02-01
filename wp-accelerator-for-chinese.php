<?php
/**
 * Plugin Name: WP Accelerator for Chinese
 * Plugin URI: http://coolwp.com/wp-accelerator-for-chinese.html
 * Description: WordPress Accelerator for Chinese.
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

if (!class_exists('WP_AcceleratorForChinese')) {

	class WP_AcceleratorForChinese {

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
			register_uninstall_hook(__FILE__, array(__CLASS__, 'handle_uninstall_hook'));
			register_activation_hook(__FILE__, array($this, 'handle_activation_hook'));
			add_action('plugins_loaded', array($this, 'plugins_loaded'));

		}
		public function plugins_loaded() {

			$this->defaults_o = apply_filters('speedup4cn_default_o', array(
				'jquery' => '//cdn.bootcss.com/jquery/1.11.3/jquery.min.js',
				'jquery_migrate' => '//cdn.bootcss.com/jquery-migrate/1.2.1/jquery-migrate.min.js',
				'open_sans' => 1,
				'head_cleaner' => 1,
				'feed_link' => 0,
				'yahei' => 0,
				'ver_info' => 0,
				'meta_widget' => 0,
				'logo' => 0,
				'enable_emoji' => 0,
				'emoji' => '//twemoji.maxcdn.com/72x72/',
			));

			$this->o = $this->get_options();

			if ($this->o['open_sans']) {
				add_filter('gettext_with_context', array(__CLASS__, 'remove_default_google_fonts'), 10, 4);
			}
			if ($this->o['yahei']) {

				add_action('login_head', array(__CLASS__, 'change_admin_font_f'), 999);
				add_action('admin_head', array(__CLASS__, 'change_admin_font_f'), 999);
				//if (is_user_logged_in())
				add_action('wp_head', array(__CLASS__, 'change_admin_font_f'), 999);

			}

			if ($this->o['ver_info']) {
				add_filter('script_loader_src', array(__CLASS__, 'script_loader_src'), 12, 2);
				add_filter('style_loader_src', array(__CLASS__, 'script_loader_src'), 12, 2);

			}

			if ($this->o['meta_widget']) {
				add_action('widgets_init', array(__CLASS__, 'remove_widgets'), 11);
			}
			if ($this->o['logo']) {
				add_action('admin_bar_menu', array(__CLASS__, 'remove_wp_logo_from_admin_bar'), 24);
			}
			if ($this->o['head_cleaner']) {
				remove_action('wp_head', 'wp_generator');
				remove_action('wp_head', 'wlwmanifest_link');
				remove_action('wp_head', 'rsd_link');
				remove_action('wp_head', 'wp_shortlink_wp_head');
				remove_action('template_redirect', 'wp_shortlink_header', 11);
			}

			if (!$this->o['enable_emoji'] && !empty($this->o['emoji'])) {
				add_filter('emoji_url', array($this, 'emoji_url'));
			}

			if (is_admin()) {

				require_once 'includes/o.php';

				/* Hooks */
				add_action('admin_init', array('WP_AcceleratorForChinese_o', 'register_settings'));
				add_action('admin_menu', array('WP_AcceleratorForChinese_o', 'add_settings_page'));
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'add_action_link'));
			}
			add_action('wp_head', array($this, 'debugger'), 0);
			add_action('pre_ping', array(__CLASS__, 'donot_ping_self'));
			add_filter('xmlrpc_methods', array(__CLASS__, 'remove_xmlrpc_pingback_ping'));
			add_action('init', array($this, 'init'), 10);

			add_action('wp_dashboard_setup', array(__CLASS__, 'dashboard_widgets'), 11);
			add_filter('get_user_option_screen_layout_dashboard', array(__CLASS__, 'one_column_layout', 11));

		}

		public static function one_column_layout($cols) {
			if (current_user_can('basic_contributor')) {
				return 1;
			}

			return $cols;
		}
		public static function dashboard_widgets() {
			global $wp_meta_boxes;

			//remove_meta_box('dashboard_activity', 'dashboard', 'normal');
			remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // Right Now
			remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
			remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); // Incoming Links
			remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); // Plugins
			remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // Quick Press
			remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side'); // Recent Drafts
			remove_meta_box('dashboard_primary', 'dashboard', 'side'); // WordPress blog
			remove_meta_box('dashboard_secondary', 'dashboard', 'side'); // Other WordPress News

			// yoast seo
			unset($wp_meta_boxes['dashboard']['normal']['core']['yoast_db_widget']);
			// gravity forms
			//unset($wp_meta_boxes['dashboard']['normal']['core']['rg_forms_dashboard']);
			update_user_meta(get_current_user_id(), 'show_welcome_panel', false);

		}

		/**
		 * Disable Only Pingbacks
		 * @param  [type] $methods [description]
		 * @return [type]          [description]
		 */
		public static function remove_xmlrpc_pingback_ping($methods) {
			unset($methods['pingback.ping']);
			return $methods;
		}
		/**
		 * [donot_ping_self description]
		 * @param  [type] $links [description]
		 * @return [type]        [description]
		 */
		public static function donot_ping_self(&$links) {

			$home = get_option('home');
			foreach ($links as $l => $link) {
				if (0 === strpos($link, $home)) {
					unset($links[$l]);
				}
			}

		}
		/**
		 * Change emoji BASE URI.
		 * @param  [type] $base [description]
		 * @return [type]       [description]
		 */
		public function emoji_url($base) {

			$base = set_url_scheme($this->o['emoji']);

			return $base;

		}
		/**
		 * Prepare.
		 * @return [type] [description]
		 */
		public function handle_activation_hook() {
			add_option('speedup4cn', $this->defaults_o);
		}
		/**
		 * Bye!
		 * @return [type] [description]
		 */
		public static function handle_uninstall_hook() {
			delete_option('speedup4cn');
		}

		public function get_options() {
			return wp_parse_args(get_option('speedup4cn'), $this->defaults_o);
		}
		/**
		 * Setting btn.
		 * @param [type] $data [description]
		 */
		public static function add_action_link($data) {
			// check permission
			if (!current_user_can('manage_options')) {
				return $data;
			}

			return array_merge(
				$data,
				array(
					sprintf(
						'<a href="%s">%s</a>',
						add_query_arg(
							array(
								'page' => 'speedup4cn',
							),
							admin_url('options-general.php')
						),
						__("Settings")
					),
				)
			);
		}
		public function replace_default_js() {

			if (!is_admin()) {
				//$protocal = ('https' === parse_url(site_url(), PHP_URL_SCHEME)) ? 'https:' : 'http:';
				wp_deregister_script('jquery');
				wp_register_script('jquery', set_url_scheme($this->o['jquery']), false, '1.11.3');
				wp_enqueue_script('jquery');
				wp_deregister_script('jquery-migrate');
				wp_register_script('jquery-migrate', set_url_scheme($this->o['jquery_migrate']), array('jquery'), '1.2.1');
				wp_enqueue_script('jquery-migrate');

			}

		}

		/**
		 * X DNX Prefetch.
		 * @return [type] [description]
		 */
		public function debugger() {
			/*

				Firefox: 3.5+
				Chrome: ALL
				Safari 5+
				IE: 9
				<link rel="dns-prefetch" href="//cdn.bootcss.com">
			*/
			?><meta http-equiv="x-dns-prefetch-control" content="on"/><?php

		}

		public static function change_admin_font_f() {

			?>
            <style>body,input,select,radio,textarea,submit,.press-this a.wp-switch-editor,#wpadminbar .quicklinks>ul>li>a,#wpadminbar .quicklinks .menupop ul li .ab-item,#wpadminbar #wp-admin-bar-user-info .display-name,#wpadminbar>#wp-toolbar span.ab-label{
              font-family: "Microsoft Yahei",STXihei,"Source Sans Pro",sans-serif !important;}.avatar{max-width:60px;max-height:60px;}</style>
              <?php

		}
		/**
		 * Remove WP Logo From admin bar.
		 * @param  [type] $wp_admin_bar [description]
		 * @return [type]               [description]
		 */
		public static function remove_wp_logo_from_admin_bar($wp_admin_bar) {

			$wp_admin_bar->remove_node('wp-logo');

		}

		/**
		 * Clean the default meta widget.
		 * @return [type] [description]
		 */
		public static function remove_widgets() {

			unregister_widget('WP_Widget_Meta');
			require_once dirname(__FILE__) . '/includes/widgets.php';
			register_widget('WP_Widget_Meta_Mod');

		}
		/**
		 * Remove fonts load by default themes.
		 * @param  [type] $translations [description]
		 * @param  [type] $text         [description]
		 * @param  [type] $context      [description]
		 * @param  [type] $domain       [description]
		 * @return [type]               [description]
		 */
		public static function remove_default_google_fonts($translations, $text, $context, $domain) {
			if (

				('Open Sans font: on or off' == $context && 'on' == $text)
				/*for twentyfourteen*/
				|| ('Lato font: on or off' == $context && 'on' == $text)
				/*for twentyfifteen*/
				|| ('Noto Sans font: on or off' == $context && 'on' == $text)
				|| ('Noto Serif font: on or off' == $context && 'on' == $text)
				/*for twentysixteen*/
				|| ('Inconsolata font: on or off' == $context && 'on' == $text)
				|| ('Merriweather font: on or off' == $context && 'on' == $text)
				|| ('Montserrat font: on or off' == $context && 'on' == $text)
			) {
				$translations = 'off';
			}
			return $translations;
		}
		/**
		 * Remove the "?ver=x.x.x" from links of scripts.
		 * @param  [type] $src    [description]
		 * @param  [type] $handle [description]
		 * @return [type]         [description]
		 */
		public static function script_loader_src($src, $handle) {

			return remove_query_arg('ver', $src);

		}
		/**
		 * Misc.
		 * @return [type] [description]
		 */
		public function init() {

			//remove_meta_box($id, 'dashboard', $context);

			if (!empty($this->o['jquery']) && !empty($this->o['jquery_migrate'])) {

				$this->replace_default_js();
			}

			if ($this->o['feed_link']) {
				remove_action('wp_head', 'feed_links', 2);
				remove_action('wp_head', 'feed_links_extra', 3);
			}

			if ($this->o['open_sans']) {
				wp_deregister_style('open-sans');
				wp_register_style('open-sans', false);
				wp_enqueue_style('open-sans', '');
			}
			/*
				From Disable Emojis v1.5 by Ryan Hellyer
				url: https://geek.hellyer.kiwi/plugins/disable-emojis/
			*/

			if ($this->o['enable_emoji']) {

				remove_action('wp_head', 'print_emoji_detection_script', 7);
				remove_action('admin_print_scripts', 'print_emoji_detection_script');
				remove_action('wp_print_styles', 'print_emoji_styles');
				remove_action('admin_print_styles', 'print_emoji_styles');

				remove_action('embed_head', 'print_emoji_detection_script');
				remove_filter('comment_text', 'convert_smilies', 20);
				remove_filter('the_excerpt', 'convert_smilies');
				remove_filter('the_content', 'convert_smilies');
				remove_filter('the_content_feed', 'wp_staticize_emoji');
				remove_filter('comment_text_rss', 'wp_staticize_emoji');
				remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

				add_filter('tiny_mce_plugins', array(__CLASS__, 'enable_emojis_tinymce'));

			}
		}
		/**
		 * Remove emoji helper.
		 * @param  [type] $plugins [description]
		 * @return [type]          [description]
		 */
		public static function enable_emojis_tinymce($plugins) {

			if (is_array($plugins)) {
				return array_diff($plugins, array('wpemoji'));
			} else {
				return array();
			}

		}

	} /*//CLASS*/
	$GLOBALS['WP_AcceleratorForChinese'] = WP_AcceleratorForChinese::instance();

}