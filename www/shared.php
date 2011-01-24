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


function fix_array_for_curl ($array)
{
	/*
		Curl doesn't handle multi-dimensional arrays values very well (you need to name the keys with an array suffix),
		..so an array that looks like this:
		
		'val' => array(
			'key1' => 'value1',
			'key2' => 'value2'
		)
		
		will have to be converted into:
		
		'val[key1]' => 'value1',
		'val[key2]' => 'value2'
		
		looks rediculous... but hey
	*/

	foreach ($array as $key => $val) {
		if (is_array($val)) {
			unset($array[$key]);
			foreach ($val as $k => $v) {
				$array[$key.'['.$k.']'] = $v;
			}			
		} elseif ($val[0] == '@') {
			die('Warning: a value starting with @ - will result in an error');
		}
		
		
	}	
	return $array;

}

// ugly ass curl functioninn minn
function curl_call ($URL, $postdata=null, $http_auth_user=null, $http_auth_pass=null) {	
	
	$ch = curl_init($URL);
	
	curl_setopt($ch,CURLOPT_HTTPHEADER,array (
		"Accept: text/xml,text/json,application/xml,application/xhtml+xml,",
		"text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
		"Cache-Control: max-age=0",
		"Connection: keep-alive",
		"Keep-Alive: 300",
		"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
		"Accept-Language: en-us,en;q=0.5",
		"Pragma: " // browsers keep this blank.
    ));
	
	if (!empty($postdata)) {
		if (is_array($postdata)) {
			$postdata = fix_array_for_curl($postdata);
		}	
		curl_setopt($ch, CURLOPT_POST, 1);
		
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	}
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
	curl_setopt($ch, CURLOPT_REFERER  ,$_SERVER['HTTP_HOST']);
	curl_setopt($ch, CURLOPT_TIMEOUT,15); 
	curl_setopt($ch, CURLOPT_USERAGENT  ,"ARNOR CURLARI");
	
	if (!is_null($http_auth_user) && !is_null($http_auth_pass)) {
		curl_setopt($ch, CURLOPT_USERPWD, $http_auth_user . ":" . $http_auth_pass);
	}
	
	curl_setopt($ch, CURLOPT_HEADER      ,0);  // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
	
	$data = curl_exec($ch);	
	
	// Not used yet ..?
	$request_info = curl_getinfo($ch);
	
	if (curl_error($ch)) {
		die (curl_error($ch));	
		i($request_info);
		i($data);
	}
	
	
	curl_close($ch);
	
	
		
	// For some reason the service was also returning 0xEF 0xBB & 0xBF at the beginning of the result
	// which caused json_decode to fail... maybe just on windows version of curl, need to test better
	$data = trim($data, "\xEF\xBB\xBF\x20\x09\x0A\x0D\x00\x0B");
	
	$data = json_decode($data,true);
	
	return $data;
	
	
}





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