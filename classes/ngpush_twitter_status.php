<?php

class ngPushTwitterStatus extends ngPushTwitterBase
{
    public function push( $Account, $TwitterStatus )
    {
        $NGPushIni = eZINI::instance( 'ngpush.ini' );

        $Token = self::getToken( $Account );

        if ( !$Token )
        {
            self::requestToken( $Account );
            $Token = self::getToken( $Account );
        }

        if ( $Token )
        {
            $tokenCredentials = explode( '%%%', $Token );
            $connection = new TwitterOAuth(
                $NGPushIni->variable( $Account, 'ConsumerKey' ),
                $NGPushIni->variable( $Account, 'ConsumerSecret' ),
                $tokenCredentials[0],
                $tokenCredentials[1] );

            $TwitterResponse = $connection->post( 'statuses/update', array( 'status' => $TwitterStatus ) );

            self::$response['response'] = $TwitterResponse;

            //Let's analyize some Twitter JSON response (lots of data but no clear structure and no status)

            if ( $TwitterResponse->error )
            {
                self::$response['status'] = 'error';
                self::$response['messages'][] = $TwitterResponse->error;
            }
            elseif ( $TwitterResponse->errors )
            {
                self::$response['status'] = 'error';
                foreach ( $TwitterResponse->errors as $TwitterResponseError )
                {
                    self::$response['messages'][] = $TwitterResponseError->message;
                }
            }
            else
            {
                self::$response['status'] = 'success';

                if ( $TwitterResponse->created_at )
                {
                    self::$response['messages'][] = 'Status is published!';
                }
            }
        }
        else
        {
            self::$response['status'] = 'error';
            self::$response['messages'][] = 'You need access token to use this application with Twitter.';
        }

        return self::$response;
    }
}

?>
