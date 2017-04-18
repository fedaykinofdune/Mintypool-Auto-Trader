<?php
/**
 * Interfaces with the MintPal API via cURL. Function handles, GET, POST & DELETE
 *
 * @param   string  $apiCall  The API function that we want to use
 * @param   array   $data     The data for the API function parameters
 * @param   string  $method   If we're using GET, POST or DELETE, uses GET by default
 * @return  array
 */
function Mintpal($apiCall, $data = array(), $method = 'GET')
{
	global $mkey, $msecret;
	$key = $mkey;
	$secret = $msecret;

  // Setup URL
  $apiUrl = "https://api.mintpal.com/v2/";

  // Set time to variable so it's definitely constant
  $time = time();

  // Start cURL
  $c = curl_init();
  curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

  // Check what type of API call we are making
  if ($method == 'GET') {

    // Start building the URL
    $apiCall = $apiUrl . $apiCall . "?time=" . $time . "&key=" . $key;

    // Add the array of data to the URL
    if (count($data)) {
      $apiCall .= "&".http_build_query($data);
    }

    // Hash the data
    $hash = hash_hmac("sha256", $apiCall, $secret);

    // Append the hash
    $apiCall .= "&hash=" . $hash;

    // Setup the remainder of the cURL request
    curl_setopt($c, CURLOPT_URL, $apiCall);

  } else if ($method == 'DELETE') {

    // DELETE requests use POSTFIELDS, so add the time and hash
    $data['time'] = $time;
    $data['key'] = $key;

    // Hash the data
    $data['hash'] = hash_hmac("sha256", $apiUrl . $apiCall . "?" . http_build_query($data), $secret);

    // Setup the remainder of the cURL request
    curl_setopt($c, CURLOPT_URL, $apiUrl . $apiCall);
    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($c, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: ' . $method));

  } else {

    // It's a POST request, so add in some additional data
    $data['time'] = $time;
    $data['key'] = $key;

    // Hash the data
    $data['hash'] = hash_hmac("sha256", $apiUrl . $apiCall . "?" . http_build_query($data), $secret);

    // Setup the remainder of the cURL request
    curl_setopt($c, CURLOPT_URL, $apiUrl . $apiCall);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));

  }

  // Execute the API call and return the response
  $result = curl_exec($c);
  curl_close($c);

  // Return the results of the API call
  $dec = json_decode($result, true);
	if (!$dec){
		throw new Exception('Bittrex API has Failed to respond'.$res);
	} else {
			return $dec;
	}
}

?>