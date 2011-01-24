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







?>