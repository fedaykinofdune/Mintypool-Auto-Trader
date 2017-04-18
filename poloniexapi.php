<?php
function Poloniex($method, array $req = array()) {
	global $pkey, $psecret;
	$key = $pkey;
	$secret = $psecret;
	
	if ($method == "tradingApi") {
	$url = 'https://poloniex.com/tradingApi';
	} else {
	$post_data = http_build_query($req, '', '&');
	$url = 'https://poloniex.com/public?'.$post_data;
	}
	
	if ($method == "tradingApi") {
	// generate a nonce to avoid problems with 32bit systems
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1].substr($mt[0], 2, 6);

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');
	$sign = hash_hmac('sha512', $post_data, $secret);

	// generate the extra headers
	$headers = array(
			'Key: '.$key,
			'Sign: '.$sign,
	);

	// curl handle (initialize if required)
	static $ch = null;
	if (is_null($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT,
					'Mozilla/4.0 (compatible; Poloniex PHP bot; '.php_uname('a').'; PHP/'.phpversion().')'
			);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	} else {
	static $ch = null;
	if (is_null($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT,
					'Mozilla/4.0 (compatible; Poloniex PHP bot; '.php_uname('a').'; PHP/'.phpversion().')'
			);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);		
	}
	// run the query
	$res = curl_exec($ch);

	if ($res === false) throw new Exception('Curl error: '.curl_error($ch));

	$dec = json_decode($res, true);
	if (!is_array($dec)){
		throw new Exception('Poloniex API has Failed: '.$res);
	} elseif (isset($dec['error']) == true) {
		throw new Exception('Poloniex API has Errored: '.$dec['error']);
	} else {
		return $dec;
	}
}
?>