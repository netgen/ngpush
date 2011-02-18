<?php

class ezjscoreNGPush extends ezjscServerFunctions
{
	private static $noAccessResponse = array(
		'status' => 'error',
		'message' => 'You do not have access to the requested module.<br /><a href="#" onclick="window.close()">Close this window</a>');

	private static function userHasAccessToModule() {
		$user = eZUser::currentUser();
		if ( $user instanceof eZUser ) {
			$result = $user->hasAccessTo('push');
			if ($result['accessWord'] == 'no') return false;
		}
		return true;
	}

	public static function push( $args )
	{
		if (!self::userHasAccessToModule()) return self::$noAccessResponse;

		$http = eZHTTPTool::instance();
		
		if ( $http->hasPostVariable( 'nodeID' ) && $http->hasPostVariable( 'accountID' ) )
		{
			$NGPushIni = eZINI::instance('ngpush.ini');
			$NGPushAccount = $http->postVariable( 'accountID' );
			$NGPushNodeID = $http->postVariable( 'nodeID' );
			
			switch ($NGPushIni->variable( $NGPushAccount, 'Type' )) {
				
				case 'twitter':
					$TwitterStatus	= $http->postVariable( 'tw_status' );
					return ngPushTwitterStatus::push($NGPushAccount, $TwitterStatus);
					break;
				
				case 'facebook_feed':
					$Arguments = array(
						'name'				=> $http->postVariable('fb_name'),
						'description'		=> $http->postVariable('fb_description'),
						'message'			=> $http->postVariable('fb_message'),
						'link'				=> $http->postVariable('fb_link'),
						'picture'			=> $http->postVariable('fb_picture')
					);
					return ngPushFacebookFeed::push($NGPushAccount, $Arguments);
					break;
				
				default:
					break;
			}
		}

		return array('status' => 'error', 'message' => 'Account not found!');
	}
}
?>