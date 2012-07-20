<?php
error_reporting(E_ALL);
require_once('./stripe-php-1.7.2/lib/Stripe.php');

// First we make sure we have a config file in the home directory.
$config = get_config();

$rt = get_customers($config['access_token']);
format_print($rt);


// ------------------ Functions -------------------- //

//
// Get all charges.
//
function get_customers($access_token)
{
	Stripe::setApiKey($access_token);
	$data = Stripe_Customer::all(array('count' =>  100));
	
	$rt = array();
	foreach($data['data'] AS $key => $row)
	{
		$rt[] = array('col1' => $row['description'], 'col2' => date('n/j/Y', $row['created']));
	}
	
	return $rt;
}

//
// Format and print the data.
//
function format_print($data)
{
	foreach($data AS $key => $row)
	{
		echo "$row[col1]\t$row[col2]\n";
	}

	echo "\n\033[32mTotal: " . count($data) . "\033[37m\r\n\n";
}

//
// Check to see if we have a config file.
// If we do not have one get the access token 
// and create the config file. 
//
function get_config()
{
	if(! is_file($_SERVER['HOME'] . '/.cloudmanic-stripe-pull.php'))
	{
		get_and_store_access_token();
	}
	
	include_once($_SERVER['HOME'] . '/.cloudmanic-stripe-pull.php');
	return $config;
}

//
// Get the user to enter thier stripe access token.
// We then store it in the dot file in the home directory.
// 
function get_and_store_access_token()
{
	// Get the access token and create the config file.
	$access_token = trim(shell_exec("read -p 'Enter your access token: ' access_token\necho \$access_token"));
	
	// Write out the config file.
	$file_cont = "<?php \n" . '$config[\'access_token\'] = \'' . $access_token . "';";
	file_put_contents($_SERVER['HOME'] . '/.cloudmanic-stripe-pull.php', $file_cont);
	chmod($_SERVER['HOME'] . '/.cloudmanic-stripe-pull.php', 0600);
}