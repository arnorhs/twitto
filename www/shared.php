<?php

// config og einhverjir utlity functionar einhverstaðar frá


$cfg = array (
	'twitter_user' => '',
	
	// notað til að tengjast spjaldinu
	'reddo_spjald_user' => '',
	'reddo_spjald_password' => '',
	
	// notað fyrir auth á http til að athuga hvort maður hafi aðgang að reddó
	'reddo_http_auth_user' => '',
	'reddo_http_auth_pass' => ''
);




function go ($url,$msg='')
{
	if ($msg) {
		$url .= ((strpos($url,'?')===false) ? '?' : '&').'msg='.urlencode($msg);
	}
	header('Location: '.$url);	
	die('Go to: '.$url);
}

// lesa cfg breytu
function cfg ($key) {
	global $cfg;
	// tékka með === hvort sé sett í kóða
	return isset($cfg[$key]) ? $cfg[$key] : false;
}


// extension of php's print_r(), second parameter is either an array of options
// or a single true/false boolean of if the function should return the text output
// (if it's false (the default) the function returns the variable again)
function i ($var,$opt=false)
{
	// a single argument provided will only tell us if the function should return or just echo
	
	if (!is_array($opt)) {
		$opt = array(
			'return' => $opt
		);
	}
	$opt = optionmerge(array(
		'return' => false, // says that it will return the output as string - if it's false, the function will return the variable again
		'html' => true,
		'single_line' => false
	),$opt);
	
	$o = print_r($var,true);	
	
	$o = $opt['single_line'] ?  str_replace(array("\n","\r"),' ',$o) : "\n".$o."\n";
	
	if ($opt['html']) {
		$o = '<pre style="border:3px solid #DDD;background:#F8F8F8;padding:10px;">'.$o."</pre>";
	}	
	if ($opt['return']) return $o;
	echo $o;
	return $var;
}


function req ($key,$default='',$array=NULL) {
	$array = is_array($array) ? $array : $_REQUEST;
	return isset($array[$key]) ? $array[$key] : $default;

}


function entities ($str) {
	return htmlentities($str, ENT_COMPAT, 'utf-8');
}

function optionmerge ($defaults, $options) {
	$options = is_array($options) ? $options : array();
	$options = array_merge($defaults, $options);
	return $options;

}

?>