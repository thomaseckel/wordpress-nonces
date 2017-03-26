# wordpress-nonces
Composer Package that allows the functionality with WordPress Nonces (especially wp_nonce _ * () functions) in an object-oriented environment.

More about nonces in WordPress: https://codex.wordpress.org/Function_Reference/wp_nonce_url

## Installation
```shell
	composer require thomaseckel/wordpress-nonces
```

## How to use

Initialization.

The class Environment delivers the data which are normally delivered by WordPress. In this example the class deliveres the data directly. In practical use with WordPress we have to change it to read the data fron WordPress.
```php
	$environment = new Eckel\Nonces\Environment();
	$nonce_object = new Eckel\Nonces\Nonce();
	$nonce_object->set_environment( $environment );
```
Set an new user ID (example '9999')
```php
	$environment->set_user_id( '9999' );
```	

Create nonce for an action (example 'action-1')
```php
	$nonce = $nonce_object->wp_create_nonce( 'action-1' );
```

Verify nonce (example for a nonce created with action 'action-1')
```php
	$isValid = $nonce_object->wp_verify_nonce( $nonce, 'action-1' );
```

Create nonce hidden input (example with action 'action-1' and nonce name '_wpnonce')
```php
	$field = $nonce_object->wp_nonce_field( 'action-1', '_wpnonce', false, false );
```

Generate nonce URL (example with url 'http://action-1' and action 'action-1')
```php
	$nonce_url = $nonce_object->wp_nonce_url('http://action-1', 'action-1');
```

Check if request was been referred from an admin screen:
```php
	$admin = $nonce_object->check_admin_referer();
```



## Current Status
The described functions are working and tested with phpunit.
