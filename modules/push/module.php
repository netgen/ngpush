<?php

$Module = array( 'name' => 'ngPush' );

$ViewList = array();

$ViewList['node'] = array(
	'script'	=> 'push_node.php',
	'params'	=> array( 'NodeID' )
);

$ViewList['save_access_token'] = array(
	'script'	=> 'save_access_token.php',
	'params'	=> array( 'SettingsBlock', 'AccessToken', 'Case' )
);

?>
