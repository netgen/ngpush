<?php

switch ($_GET['case']) {
	case 'twitter':
		$access_token	= base64_encode($_GET['oauth_verifier']);
		break;
	case 'facebook':
		$session		= json_decode(urldecode($_GET['session']));
		$access_token	= base64_encode($session->access_token);
		break;
	default:
		break;
}


$admin_url		= base64_decode(urldecode($_GET['admin']));
$settings_block	= urldecode($_GET['block']);

header('Location: ' . $admin_url . '/push/save_access_token/' . $settings_block . '/' . $access_token . '/' . $_GET['case'] . ($_GET['ot'] ? '?ot=' . $_GET['ot'] . '&ots=' . $_GET['ots'] : ''));

?>