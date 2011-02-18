<?php

$http = eZHTTPTool::instance();

$Module = $Params['Module'];

$nodeID = $Params['NodeID'];

if ( $nodeID )
{
	$node = eZContentObjectTreeNode::fetch( $nodeID );
}

if ( !$node )
{
	return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
}

$tpl = eZTemplate::factory();
$tpl->setVariable('node', $node);

$Result = array();
$Result['pagelayout'] = true;
$Result['content'] = $tpl->fetch( "design:push/push_node.tpl" );
$Result['path'] = array( array( 'url' => '/push/node/'. $nodeID,
                                'text' => 'Push Node' )
                  );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
	$contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
