<?php
/**
 * Class Test_Standard_Class
 *
 * @package PJ\HooksHelper
 */

/**
 * Tests for a standard class.
 */
class Test_Standard_Class extends WP_UnitTestCase {

    /**
     * The plugin class instance.
     *
     * @var ExampleStandardPluginClass
     */
    protected $plugin_class;

    public function setUp(): void {
        parent::setUp();
        
        // This will register the hooks for the ExampleStandardPluginClass.
        $this->plugin_class = new ExampleStandardPluginClass();
    }

	/**
	 * Test that the hooks are registered correctly.
	 */
	public function test_hooks_registered() {
		global $wp_filter;
        $this->assertArrayHasKey( 'wp_head', $wp_filter );
        $this->assertTrue( $wp_filter['wp_head']->has_filters() );

        // Check the wp_head hook. `has_filter` will return the priority if the hook is registered.
        $wp_head_hook = $wp_filter['wp_head']->has_filter( [ $this->plugin_class, 'action_wp_head' ] );
        $this->assertTrue( $wp_head_hook !== false, 'Expected ExampleStaticPluginClass::action_wp_head to be registered on wp_head.' );
        $this->assertEquals( 999, $wp_head_hook, 'Expected ExampleStaticPluginClass::action_wp_head to have priority 999.' );

        // Check the the_content filter.
        $the_content_hook = $wp_filter['the_content']->has_filter( [ $this->plugin_class, 'filter_the_content' ] );
        $this->assertTrue( $the_content_hook !== false, 'Expected ExampleStaticPluginClass::filter_the_content to be registered on the_content.' );
        $this->assertEquals( 13, $the_content_hook, 'Expected ExampleStaticPluginClass::filter_the_content to have priority 13.' );

        // Check the wp_body_open hook.
        $wp_body_open_hook = $wp_filter['wp_body_open']->has_filter( [ $this->plugin_class, 'action_wp_body_open' ] );
        $this->assertTrue( $wp_body_open_hook !== false, 'Expected ExampleStaticPluginClass::action_wp_body_open to be registered on wp_body_open.' );
        $this->assertEquals( 10, $wp_body_open_hook, 'Expected ExampleStaticPluginClass::action_wp_body_open to have default priority 10.' );
	}

    /**
     * Test that the wp_head action works correctly.
     */
    public function test_action_wp_head() {
        ob_start();
        do_action( 'wp_head' );
        $output = ob_get_clean();
        $this->assertStringContainsString( '<!-- This is a custom action from MyPlugin in the head -->', $output );
    }

    /**
     * Test that the the_content filter works correctly.
     */
    public function test_filter_the_content() {
        $content = 'This is a test content.';
        $filtered_content = apply_filters( 'the_content', $content );
        $this->assertStringContainsString( '<!-- This is a custom filter from MyPlugin on the content -->', $filtered_content );
    }

    /**
     * Test that the wp_body_open action works correctly.
     */
    public function test_action_wp_body_open() {
        ob_start();
        do_action( 'wp_body_open' );
        $output = ob_get_clean();
        $this->assertStringContainsString( '<!-- This is a custom action from MyPlugin in the body open -->', $output );
    }
}

class ExampleStandardPluginClass {

    use \PJ\HooksHelper\Register;

    #[\PJ\HooksHelper\Priority( 999 )]
    public function action_wp_head(): void {
        echo '<!-- This is a custom action from MyPlugin in the head -->';
    }

    #[\PJ\HooksHelper\Priority( 13 )]
    /**
     * @param string $content
     * @return mixed
     */
    public function filter_the_content( string $content ): string {
        return $content . '<!-- This is a custom filter from MyPlugin on the content -->';
    }

    public function action_wp_body_open(): void {
        echo '<!-- This is a custom action from MyPlugin in the body open -->';
    }
}