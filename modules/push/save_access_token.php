<?php

$http = eZHTTPTool::instance();

$Module				= $Params['Module'];

$settingsBlock		= base64_decode( $Params['SettingsBlock'] );
$accessToken		= base64_decode( $Params['AccessToken'] );
$saveStatus			= false;

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

	default:
		break;
}

if ( $saveStatus )
{
	$message = 'Access token has been successfully obtained and saved.<br />You can continue using your Netgen Push application.';
}
else
{
	$message = 'An error has occured.<br />Your Your access token could not be saved, please contact your Netgen Push administrator.';
}

$tpl = eZTemplate::factory();
$tpl->setVariable('message', $message);

$Result = array();
$Result['pagelayout'] = false;
$Result['content'] = $tpl->fetch( "design:push/message.tpl" );
