<?php
/**
 * Plugin name: Hooks Helper demo
 */

require_once 'vendor/autoload.php';

class MyPlugin {

	use \PJ\HooksHelper\Register {
		\PJ\HooksHelper\Register::init as hooksInit;
	}

	public static function init() {
		// Initialize the hooks for this class
		self::hooksInit();
	}

	#[\PJ\HooksHelper\Priority(999)]
	public static function action_wp_head(): void {
		echo '<!-- This is a custom action from MyPlugin in the head -->';
	}

	#[\PJ\HooksHelper\Priority(13)]
	public static function filter_the_content( string $content ): string {
		return $content . '<!-- This is a custom filter from MyPlugin on the content -->';
	}

	public static function action_wp_body_open(): void {
		echo '<!-- This is a custom action from MyPlugin in the body open -->';
	}
}

MyPlugin::init();

class AnotherPlugin {

	use \PJ\HooksHelper\Register;

	public function __construct() {
		// Initialize the hooks for this instance
		self::init( $this );
	}

	public function action_wp_head(): void {
		echo '<!-- This is a custom action from AnotherPlugin in the head -->';
	}

	public function filter_the_content( string $content ): string {
		return $content . '<!-- This is a custom filter from AnotherPlugin on the content -->';
	}

	public function action_wp_body_open(): void {
		echo '<!-- This is a custom action from AnotherPlugin in the body open -->';
	}
}

$ap = new AnotherPlugin();
