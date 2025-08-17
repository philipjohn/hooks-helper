<?php
/**
 * RegisterHooks.class.php
 * 
 * Contains the RegisterHooks trait which automatically registers WordPress hooks
 * based on class method names, using attributes to define hook priorities.
 */

namespace PJ\HooksHelper;

use ReflectionClass;
use ReflectionMethod;

/**
 * Trait RegisterHooks
 *
 * This trait provides a way to register WordPress hooks automatically
 * using class method names instead of manually calling `add_action()`
 * or `add_filter()`.
 */
trait Register {

	public function __construct() {
		// Initialize the hooks for this instance
		self::init( $this );
	}

	/**
	 * @param $instance
	 */
	public static function init( $instance = null ): void {

		// Get the public methods of the current class, whether static or instance methods.
		$class = new ReflectionClass( $instance ?? self::class );

		// Filter out methods whose name does not start with 'action_' or 'filter_'
		$hook_methods = array_filter(
			$class->getMethods( ReflectionMethod::IS_PUBLIC ),
			function ( $method ) {
				return preg_match( '/^(action_|filter_)/', $method->getName() );
			}
		);

		// Register hooks for each method
		foreach ( $hook_methods as $method ) {
			// Extract the hook name from the method name.
			// Remove 'action_' or 'filter_'
			$hook_name = substr( $method->getName(), 7 );

			// Assemble the callback, accounting for static vs instance.
			if ( $method->isStatic() ) {
				$callback = [
					$instance ?? self::class,
					$method->getName(),
				];
			} else if ( $instance ) {
				$callback = [
					$instance,
					$method->getName(),
				];
			}

			// Get the priority from the method attributes.
			$attributes = $method->getAttributes( Priority::class );
			$priority   = ( ! empty( $attributes ) )
				? array_shift( $attributes )->newInstance()->value
				: 10; // Default priority if not specified.

			// Get the number of arguments to the method.
			$accepted_args = $method->getNumberOfParameters();

			// Check we definitely have a callback.
			if ( ! isset( $callback ) || ! is_callable( $callback ) ) {
				$class_name  = $instance ? get_class( $instance ) : self::class;
				$method_name = $method->getName();

				trigger_error(
					sprintf(
						'Failed to register hook as %s::%s is not callable.',
						$class_name,
						$method_name
					),
					E_USER_WARNING
				);
				continue;
			}

			// In core, `add_action()` just passes to `add_filter()` anyway so let's do the same.
			add_filter(
				$hook_name,
				$callback,
				$priority,
				$accepted_args ?: null
			);
		}

	}

}
