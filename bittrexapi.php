<?php
function Bittrex($method, array $req = array()) {
        global $bkey, $bsecret;
		$key = $bkey;
		$secret = $bsecret;
		$nonce=time();
		$post_data = '&'.http_build_query($req, '', '&');
		$uri = 'https://bittrex.com/api/v1.1/'.$method.'?apikey='.$key.'&nonce='.$nonce.$post_data;
		$sign=hash_hmac('sha512',$uri,$secret);
		$ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
		$res = curl_exec($ch);
        if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
        $dec = json_decode($res, true);
        if (!$dec){
				throw new Exception('Bittrex API has Failed to respond'.$res);
		} else {
				return $dec;
		}
}
?>