<?php if (!defined('S')) die ("You've got no S"); 

// Hér er allt actionið
// uhh okk..

t('twitter to reddó.');


class home {
	
	var $_spjald_session_id = false;
	
	function index () {
		
		//home::_post_twitter_status_to_spjald ();
		$this->_list_spjald_faerslur();			
	}
	
	function _list_spjald_faerslur () {
		
		
		// Starta spjald session - skilar false ef login heppnast ekki	   
		$this->_spjald_start_session();
		
		// pósta á spjaldið
		$data = $this->_spjald_kall('get_posts',array (
			'count' => 50
		));
		
		i($data);
		
		// Enda spjald session
		$this->_spjald_session_end();		
		
	}
	
	function _post_twitter_status_to_spjald () {
		
		$url = 'http://twitter.com/statuses/user_timeline/'.cfg('twitter_user').'.json?count=10';
		$d = json_decode(file_get_contents($url),true);
		
		$css = 'margin:0;margin-top:2px;padding:6px;padding-left:20px;font-style:italic;background:#FFF;'.
			   '-moz-box-shadow:0px 1px 4px rgba(0,0,0,0.2);-webkit-box-shadow:0px 1px 4px rgba(0,0,0,0.2);box-shadow:0px 1px 4px rgba(0,0,0,0.2);';
	
		$spjald_texti = '<div><blockquote style="'.$css.'">'.$d[0]['text']."</blockquote></div>".
			'<p style="margin:0;text-align:right;"><a style="font-size:10px;" href="http://twitter.com/'.cfg('twitter_user').'/status/'.$d[0]['id_str'].'">Twitter status</a></p>';
		
		// Starta spjald session - skilar false ef login heppnast ekki	   
		$this->_spjald_start_session();
		
		// pósta á spjaldið
		$data = $this->_spjald_kall('post',array (
			'texti' => $spjald_texti,
			'flokk_id' => 52,
			'nick' => cfg('reddo_spjald_user') // einhverra hluta vegna líka hér
		));		
		
		// Enda spjald session
		$this->_spjald_session_end();
		
		
	}
	
	
	function _spjald_start_session () {
		
		/* fá sessionið */
		
		$data = $this->_spjald_kall('session_start',array (
			'timeout' => 20,
			'username' => cfg('reddo_spjald_user'),
			'hidden_session' => 0,
			'passhash' => md5(cfg('reddo_spjald_password'))
		));
		
		/* erum með session_id? */
		$session_id = $data['output']['session_id'];
		
		$this->_spjald_session_id = $session_id;
		
		// skilar true/false ef login heppnast/ekki
		return true;
		
	}
	
	
	function _spjald_kall ($func, $array) {

		if ($func != 'session_start') {
			if (!$this->_spjald_session_id) {
				die('Ekkert spjald session ID í gangi.');
			}
			$array['session_id'] = $this->_spjald_session_id;
		}
		
		/* posta skilaboðum */
		$data = curl_call(
			'http://vesen.hydra.is:7080/invoke/?plugin=Spjald&output=json',
			$v = array (
				'func' => $func,
				'param_json' => json_encode($array)
			),
			cfg('reddo_http_auth_user'),
			cfg('reddo_http_auth_pass')
		);
		
		i($v);
		
		return $data;
	}
	
	function _spjald_session_end () {
		$data = $this->_spjald_kall('session_end',array (
			'session_id' => $this->_spjald_session_id
		));
		
		
	}
		
	
}





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





?>