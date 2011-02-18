<?php
class ngPushFacebookBase extends ngPushBase
{
	public function getToken($Account)
	{
		$NGPushIni = eZINI::instance('ngpush.ini');

		if ( $Token = self::load_token( $Account, 'main_token' ) )
		{
			if ( $NGPushIni->variable( $Account, 'EntityType' ) == 'page' )
			{
				return self::getPageToken( $Token, $NGPushIni->variable( $Account, 'Id' ) );
			}

			return $Token;
		}

		return false;
	}

	//In case of a page (instead of full Facebook account), impersonated token is needed to post on page's behalf (post as a page)
	public function getPageToken($Token, $Id)
	{
		$options = array(
			CURLOPT_URL				=> 'https://graph.facebook.com/me/accounts?access_token=' . $Token,
			CURLOPT_USERAGENT		=> self::useragent,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_HEADER			=> 0,
			CURLOPT_POST			=> 0
		);

		$ch = curl_init($url);
		curl_setopt_array($ch, $options);

		$content	= curl_exec($ch);
		$errno		= curl_errno($ch);
		$errmsg		= curl_error($ch);
		$header		= curl_getinfo($ch);

		if ($FacebookResponse = json_decode($content, true))
		{
			foreach ($FacebookResponse['data'] as $Account)
			{
				if ($Account['id'] == $Id) return $Account['access_token'];
			}
		}
		
		//In case of an error or no account, return regular access token (post as a page admin)
		return $Token;
	}

	public function requestToken( $Account )
	{
		$NGPushIni = eZINI::instance( 'ngpush.ini' );
		$SiteIni = eZINI::instance( 'site.ini' );
		
		$Facebook = new Facebook( array(
			'appId'			=> $NGPushIni->variable( $Account, 'AppAPIKey' ),
			'secret'		=> $NGPushIni->variable( $Account, 'AppSecret' ) ) );
		
		$Permissions = array(
			'publish_stream',
			'offline_access' );
		if ( $NGPushIni->variable( $Account, 'EntityType' ) == 'page' ) $Permissions[] =
			'manage_pages';

		$AdministrationUrl	= base64_encode('http://' . $SiteIni->variable( 'SiteSettings', 'SiteURL' ) );
		$SettingsBlock		= base64_encode( $Account );
		$LoginUrl = $Facebook->getLoginUrl( array(
			'cancel_url'	=> 'http://' . $NGPushIni->variable( 'PushNodeSettings', 'ConnectURL' ) . '/cancel.php',
			'next'			=> 'http://' . $NGPushIni->variable( 'PushNodeSettings', 'ConnectURL' ) . '/redirect.php/' . $AdministrationUrl . '/' . $SettingsBlock . '?case=facebook',
			'req_perms'		=> implode( $Permissions, ',' ),
			'display'		=> 'popup' ) );

		self::$response['RequestPermissionsUrl'] = $LoginUrl;
	}
}
