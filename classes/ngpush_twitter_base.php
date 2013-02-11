<?php

class ngPushTwitterBase extends ngPushBase
{
	public function getToken( $Account )
	{
		if ( $Token = self::load_token( $Account, 'main_token' ) )
		{
			return $Token;
		}

		return false;
	}

	public function requestToken( $Account )
	{
		$NGPushIni = eZINI::instance( 'ngpush.ini' );

		$ConsumerKey        = $NGPushIni->variable( $Account, 'ConsumerKey' );
		$ConsumerSecret     = $NGPushIni->variable( $Account, 'ConsumerSecret' );

        $AccessToken        = $NGPushIni->variable( $Account, 'AccessToken');
        $AccessTokenSecret  = $NGPushIni->variable( $Account, 'AccessTokenSecret');

        // If access tokens are given
        if ($AccessToken && $AccessTokenSecret)
        {
            // Save request signing tokens to cache
            ngPushBase::save_token( $Account, $AccessToken, 'request_sign_oauth_token' );
            ngPushBase::save_token( $Account, $AccessTokenSecret, 'request_sign_oauth_token_secret' );
            ngPushBase::save_token( $Account, $AccessToken . '%%%' . $AccessTokenSecret, 'main_token' );
        }
        else // Request tokens with oAuth
        {
            $connection = new TwitterOAuth( $ConsumerKey, $ConsumerSecret );

            $AdministrationUrl = '/';
            eZURI::transformURI( $AdministrationUrl, false, 'full' );
            $AdministrationUrl = base64_encode( $AdministrationUrl );
            $SettingsBlock = base64_encode( $Account );

            $temporary_credentials = $connection->getRequestToken( 'http://' . $NGPushIni->variable( 'PushNodeSettings', 'ConnectURL' ) . '/redirect.php/' . $AdministrationUrl . '/' . $SettingsBlock . '?case=twitter' );

            // Save request signing tokens to cache
            ngPushBase::save_token( $Account, $temporary_credentials['oauth_token'], 'request_sign_oauth_token' );
            ngPushBase::save_token( $Account, $temporary_credentials['oauth_token_secret'], 'request_sign_oauth_token_secret' );

            $redirect_url = $connection->getAuthorizeURL( $temporary_credentials, FALSE );

            self::$response['RequestPermissionsUrl'] = $redirect_url;
        }
	}
}

?>
