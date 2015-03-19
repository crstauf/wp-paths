<?php
// version 0.1

if (defined('ABSPATH') && defined('WP_CONTENT_URL'))
	new WP_Path;

class WP_Path {

	public static $paths	= false;
	public static $urls		= false;

	public static $file		= false;
	public static $path		= false;

	public static $use_cache = true;

	function __construct() {
		self::$paths	= new stdClass();
		self::$urls		= new stdClass();
	}

	public static function __callStatic($name,$args) { return; }

	public static function data($item,$ref = 'urls') {
		switch($item) {

			case 'abspath':
				return ABSPATH;

			case 'siteurl':
			case 'site':
			case 'home':
			case 'url':
				return self::ref($ref,
					get_bloginfo('url'),
					ABSPATH
				);

			case 'wp_admin':
				return self::ref($ref,
					self::get('site') . '/wp-admin',
					ABSPATH . 'wp-admin'
				);

			case 'wp_content':
				return self::ref($ref,WP_CONTENT_URL,WP_CONTENT_DIR);

			case 'wp_includes':
				return self::ref($ref,
					self::get('site') . '/' . WPINC,
					ABSPATH . WPINC
				);

			case 'plugins':
				return self::ref($ref,WP_PLUGIN_URL,WP_PLUGIN_DIR);

			case 'mu_plugins':
				return self::ref($ref,WPMU_PLUGIN_URL,WPMU_PLUGIN_DIR);

			case 'themes':
				return self::ref($ref,WP_CONTENT_URL . '/themes',WP_CONTENT_DIR . '/themes');

			case 'parent_theme':
				return self::ref($ref,
					get_template_directory_uri(),
					get_template_directory()
				);

			case 'theme':
			case 'child_theme':
				return self::ref($ref,
					(function_exists('theme_url') ? theme_url() : get_stylesheet_directory_uri()),
					get_stylesheet_directory()
				);

			case 'uploads':
				return self::ref($ref,WP_CONTENT_URL . '/uploads',WP_CONTENT_DIR . '/uploads');

		};
	}

	public static function interpret($orig,$is_ref = false) {
		$orig = strtolower($orig);
		if (true == $is_ref)
			return 'url' == $orig ? 'urls' : 'paths';

		$new = str_replace('-','_',$orig);
		switch ($new) {
			case 'includes':
			case 'incs':
			case 'inc':
				return array_unique(array($new,'includes','incs','inc'));
			case 'images':
			case 'imgs':
			case 'img':
				return array_unique(array($new,'images','imgs','img'));
			default:
				return $new;
		}

		return $new;
	}

	public static function ref($ref,$url_value,$path_value) { return 'urls' == $ref ? $url_value : $path_value; }

	public static function save($value,$item,$ref = 'urls') { return (self::$$ref->$item = $value); }

	public static function value($item,$ref = 'url') {
		$item = self::interpret($item);
		$ref = self::interpret($ref,true);

		if (true === self::$use_cache && false !== ($return = self::cache($item,$ref)))
			return $return;

		$return = self::data($item,$ref);
		if (true === self::$use_cache)
			self::save($return,$item,$ref);

		return $return;
	}

		public static function get($item,$ref = 'url')	{ return self::value($item,$ref); }

	public static function the($item,$ref = 'url')	{ echo self::get($item,$ref); }

	public static function dir($file) {
		if ($file == self::$file) return self::$path;
		else self::$file = $file;
		return plugin_dir_path($file);
	}

	public static function theme($dirs = false,$theme = false,$ref = 'url',$echo = true) {
		// return parent/child theme directory path/url, e.g.: 'imgs','inc','js',etc.
		$dirs = self::interpret($dirs);
		if (false !== $dirs && !is_array($dirs))
			$dirs = array($dirs);
		if (false !== $theme) {
			$return = self::get($theme . '_theme',$ref);
			if (false !== $dirs) {
				$found = false;
				foreach ($dirs as $dir) {
					if (file_exists(self::get($theme . '_theme','path') . '/' . $dir)) {
						$found = true;
						$return .= '/' . $dir;
						break;
					}
				}
				if (false === $found)
					return false;
			}
		} else if (false === $theme && false !== $dirs) {
			if (self::get('child_theme',$ref) !== self::get('parent_theme',$ref)) {
				$foreach = array();
				foreach ($dirs as $dir) {
					$foreach[] = array(
						'path'	=> self::get('child_theme','path') . '/' . $dir,
						'url'	=> self::get('child_theme','path') . '/' . $dir,
					);
					$foreach[] = array(
						'path'	=> self::get('parent_theme','path') . '/' . $dir,
						'url'	=> self::get('parent_theme','path') . '/' . $dir,
					);
				}
				foreach ($foreach as $array)
					if (file_exists($array['path'])) {
						$return = $array[$ref];
						break;
					}
			} else {
				$found = false;
				foreach ($dirs as $dir)
					if (file_exists(self::get('child_theme','path') . '/' . $dir)) {
						$return = self::get('child_theme',$ref) . '/' . $dir;
						$found = true;
						break;
					}
				if (false === $found)
					return false;
			}
		} else if (false === $dirs)
			return false;

		if (false === $echo) return $return;
		echo $return;
	}

	public static function cache($item,$ref = 'urls') {
		if (isset(self::$$ref->$item)) return self::$$ref->$item;
		return false;
	}

}

?>