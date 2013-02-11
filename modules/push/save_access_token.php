<?php

$http = eZHTTPTool::instance();

$Module             = $Params['Module'];

$settingsBlock      = base64_decode( $Params['SettingsBlock'] );
$accessToken        = base64_decode( $Params['AccessToken'] );
$saveStatus         = false;

$NGPushIni = eZINI::instance( 'ngpush.ini' );

switch ($Params['Case']) {
    case 'twitter':
        $connection = new TwitterOAuth(
            $NGPushIni->variable( $settingsBlock, 'ConsumerKey'),
            $NGPushIni->variable( $settingsBlock, 'ConsumerSecret' ),
            ngPushBase::load_token( $settingsBlock, 'request_sign_oauth_token' ),
            ngPushBase::load_token( $settingsBlock, 'request_sign_oauth_token_secret' ) );

        $AccessTokenVerifier = $accessToken;
        $token_credentials = $connection->getAccessToken( $AccessTokenVerifier );

        $saveStatus = ngPushBase::save_token( $settingsBlock, $token_credentials['oauth_token'] . '%%%' . $token_credentials['oauth_token_secret'], 'main_token' );
        break;

    case 'facebook':
        $saveStatus = ngPushBase::save_token( $settingsBlock, $accessToken, 'main_token' );
        break;
    case 'facebook_oauth':
        $AdministrationUrl = '/';
        eZURI::transformURI( $AdministrationUrl, false, 'full' );
        $AdministrationUrl = base64_encode( $AdministrationUrl );

        $redirectUrl = 'http://' . $NGPushIni->variable( 'PushNodeSettings', 'ConnectURL' ) . '/redirect.php/' . $AdministrationUrl . '/' . $Params['SettingsBlock'] . '?case=facebook';

         $token_url = "https://graph.facebook.com/oauth/access_token?"
           . "client_id=" . $NGPushIni->variable( $settingsBlock, 'AppId') . "&redirect_uri=" . urlencode($redirectUrl)
           . "&client_secret=" . $NGPushIni->variable( $settingsBlock, 'AppSecret') . "&code=" . $accessToken;

         $response = file_get_contents($token_url);
         $params = null;
         parse_str($response, $params);

         if ( !empty( $params['access_token'] ) )
         {
            $saveStatus = ngPushBase::save_token( $settingsBlock, $params['access_token'], 'main_token' );
         }
        break;

    default:
        break;
}

if ( $saveStatus )
{
    $message = ezpI18n::tr( 'ngpush/ui', 'Access token has been successfully obtained and saved.%brYou can continue using your Netgen Push application.', null, array( '%br' => '<br />' ) );
}
else
{
    $message = ezpI18n::tr( 'ngpush/ui', 'An error has occured.%brYour access token could not be saved, please contact your Netgen Push administrator.', null, array( '%br' => '<br />' ) );
}

$tpl = eZTemplate::factory();
$tpl->setVariable('message', $message);

$Result = array();
$Result['pagelayout'] = false;
$Result['content'] = $tpl->fetch( "design:push/message.tpl" );
