PayPal REST API Wrapper
=============

Very Easy to use PayPal REST Enabled API Wrapper Class in PHP.

Requires PHP 5.4 and curl extension.

Installation
------------

You can install the paypal-rest-api using Composer. Just add the following to your composer.json:

    {
        "require": {
            "vatps/paypal-rest-api": "dev-master"
        }
    }

You will then need to:
* run ``composer install`` to get these dependencies added to your vendor directory
* add the autoloader to your application with this line: ``require("vendor/autoload.php")``

Alternatively you can just download the PayPal.php file and include it manually.

Laravel Installation
--------------------

Run ``composer require vatps/paypal-rest-api`` in terminal.

Code Example (Create a Payment)
---------------

	<?php
	use \VPS\PayPal;
	
	$client_id = 'ATTUpBCpxG7h9PLUpENHKmhMiJsZqsJ-4tib0_oflCL9WFs8enQEGOxxIGZJ';
	$client_secret = 'EM9xPhC2KJUiIoZ3l6965T0b7_X5QQX035JJfc9ijHtMKn4bGmE_qyTR45A9';
	$sandbox = true;										

	$pp = new PayPal($client_id, $client_secret, $sandbox);

	$data = array(
		'intent' => 'sale',
		'payer' => array(
			'payment_method' => 'paypal'
		),
		'transactions' => array(
			array(
				'amount' => array(
					'total' => '9.99',
					'currency' => 'USD'
				),
				'description' => 'Demo Request'
			)
		),
		'redirect_urls' => array(
			'return_url' => 'http://mydomain.dev/return.php',
			'cancel_url' => 'http://mydomain.dev/cancel.php'
		)
	);

	$request = $pp->post('/v1/payments/payment', $data);

	var_dump($request);

You can find all available API operations at https://developer.paypal.com/docs/api/
	
Contact me if you need any help.	
