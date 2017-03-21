# wordpress-nonces
Composer Package that allows the functionality with WordPress Nonces (especially wp_nonce _ * () functions) in an object-oriented environment.

## Installation
```shell
	composer require thomaseckel/wordpress-nonces
```

## How to use

Create nonce
```php
	$nonce = \Eckel\Nonces\Nonce_Wrapper::wp_create_nonce();
```

Verify nonce
```php
	$isValid = \Eckel\Nonces\Nonce_Wrapper::wp_verify_nonce($nonce);
```

Create nonce hidden input
```php
	\Eckel\Nonces\Nonce_Wrapper::wp_nonce_field();
```

Generate nonce URL:
```php
	$url = \Eckel\Nonces\Nonce_Wrapper::wp_nonce_url('http://www.google.com');
```

Check if request was been referred from an admin screen:
```php
	$admin = \Eckel\Nonces\Nonce_Wrapper::check_admin_referer();
```

Verifies the AJAX request to prevent processing requests external of the blog.
```php
	$ajax = \Eckel\Nonces\Nonce_Wrapper::check_ajax_referer();
```

## Current Status
This package is still in the development mode. The unit tests are not yet working correctly.
