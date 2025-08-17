# Hooks Helper

Never type `add_action` or `add_filter` in your WordPress projects ever again! It's boring and repetitive and bleurgh. Never again will you :facepalm: because you forgot to add the `$accepted_args` parameter and saw a WSOD.

## Usage

First, require this library with composer.

```php
composer require pj/hooks-helper
```

Then use the `Register` trait in your classes.

```php
class MyPlugin {

    use \PJ\HooksHelper\Register;

    ...
}
```

Simply name your methods after the hook you'd like to attach them to, prefixed with `action_` or `filter_` and Hooks Helper will take care of the rest.

```php
class MyPlugin {

    use \PJ\HooksHelper\Register;

    function action_wp_head() {
        echo '<!-- No fingers were harmed by typing add_action in the production of this comment -->';
    }
}
```

The `Register` trait automatically picks up on your method, determines the hook name and registers the hook for you.

## Accepted arguments?

If you're anything like me you almost never remember to add the fourth parameter, `$accepted_args`, to your `add_action()` and `add_filter()` calls. Here, the `Register` trait simply does this for you! It counts the number of arguments that your method actually receives and uses that number when registering the hook, leaving you free to add only the arguments you need and forget about the rest.

Check out this example:

```php
class MyPlugin {

    use \PJ\HooksHelper\Register;

    public function filter_wp_insert_post( $post_id, $post ) {
        // Do stuff here, maybe use $post as well.
        return $post_id;
    }
}
```

Here, `Register` would call `add_filter()` like so:

```php
add_filter(
    'wp_insert_post',
    [ 'MyPlugin', 'filter_wp_insert_post' ],
    10,
    2
);
```

## Setting priority

To set priority, Hooks Helper provides a PHP Attribute called, remarkably, `Priority`. This allows you to simply 'tag' your method with the priority level needed if not the default of 10. Let's make sure our example above fires later:

```php
class MyPlugin {

    use \PJ\HooksHelper\Register;

    #[\PJ\HooksHelper\Priority(15)]
    public function filter_wp_insert_post( $post_id, $post ) {
        // Do stuff here, maybe use $post as well.
        return $post_id;
    }
}
```

Now our `add_filter()` call in `Register` would change to:

```php
add_filter(
    'wp_insert_post',
    [ 'MyPlugin', 'filter_wp_insert_post' ],
    15,
    2
);
```

## Usage with existing constructor or init method

If you look in the `Register` trait you'll see that it has `__construct()` and `init()` methods. A common pattern in WordPress projects is to use a static `init()` method and to register hooks to static methods, so `init()` was chosen as the method in the trait because it's familiar, and helps us do away with existing `init()` methods full of nothing but `add_[action|filter]()` calls.

Having a constructor in the trait was necessary for those classes that use an instance rather than static methods. Classes that don't have a constructor already can simply add the trait and it'll do it's thing.

In cases where your class needs to have a `__construct()` and/or `init()` method you'll need to invoke the registration of your hooks manually.

### Using static methods with your own `init()`

```php
class MyPlugin {

    use \PJ\HooksHelper\Register {
        \PJ\HooksHelper\Register::init as hooksInit;
    }

    public static function init() {
        // Initialize the hooks for this class
        self::hooksInit();
    }

    public static function filter_wp_insert_post( $post_id, $post ) {
        // Do stuff here, maybe use $post as well.
        return $post_id;
    }
}
```

### Using an instance with your own `__construct()`

```php
class MyPlugin {

    use \PJ\HooksHelper\Register;

    public function __construct() {
        // Initialize the hooks for this instance
        // Passing `$this` is crucial - without it, WordPress will try to call your methods
        // statically which will result in a Fatal Error.
        self::init( $this );
    }

    public function filter_wp_insert_post( $post_id, $post ) {
        // Do stuff here, maybe use $post as well.
        return $post_id;
    }
}
```

## Limitations

### Hooks that have invalid characters

Some hooks are dynamic and may include characters that are not valid in a PHP function name. Let's say you register a post type called `book-review`. One of the possible hooks you could then use would be `registered_post_type_book-review` but if you try to create a function called `action_registered_post_type_book-review` PHP would rightfully and loudly shout at you for being a silly sausage.

In these cases, I'm afraid, you'll need to resort to using `add_action()` or `add_filter()`.

### Conditional hooks

In many cases you may register a hook within an existing code block and conditionally based on something else happening. Imagine this code in the middle of some routine somewhere:

```php
if ( $debug ) {
    add_action( 'shutdown', [ 'MyPlugin', 'log_all_the_things' ] );
}
```

Ideally we'd just remove this code and rename `log_all_the_things` to `action_shutdown` but unless we can access `$debug` we won't be able to stop it running unless the condition is met.

### The `use` keyword

Sometimes you'll register a hook with an anonymous function and pass in a variable with `use` like so:

```php
$template = get_the_template();
add_filter( 'the_content', function( $content ) use ( $template ) {
    load_template( $template );
    return $content;
} );
```

We would only have `$template` available at the point of adding the filter, so can't use the Hooks Helper for this one.

*[WSOD]: White Screen Of Death