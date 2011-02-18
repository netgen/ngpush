<?php
class ngPushBase
{
	const useragent			= 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1';
	//Cache directory where tokens are saved
	const ngpush_cache_dir	= 'ngpush';
	//Tokens are named {token_prefix}_{settings_block_name}.txt
	const token_prefix		= '';
	//Base error structure
	protected static $response = array('status' => NULL, 'messages' => array(), 'response' => NULL);

	public function load_token( $Account, $TokenSuffix = false )
	{
		$ngpush_cache = eZSys::cacheDirectory() . ( self::ngpush_cache_dir ? '/' . self::ngpush_cache_dir : '' );
		$token_file = ( self::token_prefix ? '_' . self::token_prefix : '' ) . $Account . ( $TokenSuffix ? '_' . $TokenSuffix : '' ) . '.txt';

		return file_get_contents( $ngpush_cache . '/' . $token_file );
	}

	public function save_token( $SettingsBlock, $Token, $TokenSuffix = false )
	{
		$ngpush_cache = eZSys::cacheDirectory() . (self::ngpush_cache_dir ? '/' . self::ngpush_cache_dir : '');
		if ( !file_exists( $ngpush_cache ) ) mkdir( $ngpush_cache );

		$handle = fopen( $ngpush_cache . '/' . ( self::token_prefix ? '_' . self::token_prefix : '' ) . $SettingsBlock . ( $TokenSuffix ? '_' . $TokenSuffix : '' ) . '.txt', 'w' );
		$status = fwrite( $handle, $Token );
		fclose( $handle );

		return $status;
	}

	//Workaround for PHP CURL redirect security restrictions (when using open_basedir and/or safe_mode)
	public function curl_redir_exec($ch, $content = '')
	{
		static $curl_loops = 0;
		static $curl_max_loops = 20;

		if ($curl_loops++ >= $curl_max_loops)
		{
			$curl_loops = 0;
			return FALSE;
		}

		$data = curl_exec($ch);
		$data2 = $data . $content;

		list($header, $data) = explode("\n\n", $data2, 2);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 301 || $http_code == 302)
		{
			$matches = array();
			preg_match('/Location:(.*?)\n/', $header, $matches);
			$url = @parse_url(trim(array_pop($matches)));

			if (!$url) {
				$curl_loops = 0;
				return $data2;
			}

			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			//Handling only HTTP and HTTPS schemes
			if ($last_url['scheme'] != 'http' && $last_url['scheme'] != 'https') return false;

			if (!$url['scheme']) $url['scheme'] = $last_url['scheme'];
			if (!$url['host']) $url['host'] = $last_url['host'];
			if (!$url['path']) $url['path'] = $last_url['path'];

			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
			curl_setopt($ch, CURLOPT_URL, $new_url);

			return self::curl_redir_exec($ch, $data2);
		}
		else
		{
			$curl_loops=0;
			return $data2;
		}
	}
}
