<?php

class ngPushFacebookFeed extends ngPushFacebookBase
{
	public function push($Account, $Arguments)
	{
		$NGPushIni = eZINI::instance( 'ngpush.ini' );
		$MakeToken = false;

		if ($Token = self::getToken($Account)) {
			$postfields = 'access_token=' . $Token;
			
			foreach($Arguments as $Name => $Value) {
				$postfields .= ($Value ? '&' . $Name . '=' . $Value : '');
			}

			$options = array(
				CURLOPT_URL				=> 'https://graph.facebook.com/' . $NGPushIni->variable($Account, 'Id') . '/feed',
				CURLOPT_USERAGENT		=> self::useragent,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_HEADER			=> 0,
				CURLOPT_POST			=> 1,
				CURLOPT_POSTFIELDS		=> $postfields
			);
			
			$ch = curl_init($url);
			curl_setopt_array($ch, $options);
			
			$content	= curl_exec($ch);
			$errno		= curl_errno($ch);
			$errmsg		= curl_error($ch);
			$header		= curl_getinfo($ch);

			curl_close($ch);
			
			if ($errno != 0) {
				self::$response['status'] = 'error';
				self::$response['messages'][] = 'CURL error ' . $errno . ' (' . $errmsg . ') while publishing feed';
			}
			else {
				if ($FacebookResponse = json_decode($content, true)) {
					self::$response['response'] = $FacebookResponse;
					
					//Analyizing Facebook JSON response
					if ($FacebookResponse['error']) {
						self::$response['status'] = 'error';
						self::$response['messages'][] = $FacebookResponse['error']['type'] . ': ' . $FacebookResponse['error']['message'];
						
						//Try requesting new token
						$MakeToken = true;
						
						//In case of invalid token or permissions error
						if (preg_match('/(#190|#200)/', $FacebookResponse['error']['message'])) {}
					}
					else {
						self::$response['status'] = 'success';
						
						if ($FacebookResponse['id']) {
							self::$response['messages'][] = 'Feed is published!';
						}
					}
				}
				else {
					self::$response['status'] = 'error';
					self::$response['messages'][] = 'Invalid response from Facebook (JSON expected)';
				}
			}
		}
		else {
			self::$response['status'] = 'error';
			self::$response['messages'][] = 'You need access token to use this application with Facebook.';
		}

		if (!$Token || $MakeToken) self::requestToken($Account);

		return self::$response;
	}
}

?>