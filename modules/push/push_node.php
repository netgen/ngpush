<?php

$http = eZHTTPTool::instance();

$Module = $Params['Module'];

$nodeID = $Params['NodeID'];
$languageCode = ( isset( $Params['LanguageCode'] ) && strlen( $Params['LanguageCode'] ) > 0 ) ? $Params['LanguageCode'] : false;

if ( !is_numeric( $nodeID ) )
	return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );

$foundInRequestedLanguage = false;

$node = eZContentObjectTreeNode::fetch( (int) $nodeID, $languageCode );
if ( !$node instanceof eZContentObjectTreeNode )
{
	$node = eZContentObjectTreeNode::fetch( (int) $nodeID );
	if ( !$node instanceof eZContentObjectTreeNode )
		return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}
else if ( $languageCode )
	$foundInRequestedLanguage = true;

// Hacky solution to tackle a bug in eZ Publish prior to version 4.5
// that always fetched url_alias in locale defined in site.ini.
// Not very happy about it, but it works
$urlAlias = $node->urlAlias();
if ( $foundInRequestedLanguage )
{
	$originalPrioritizedLanguages = eZContentLanguage::prioritizedLanguageCodes();
	eZContentLanguage::setPrioritizedLanguages( array( $languageCode ) );
	$urlAlias = $node->urlAlias();
	eZContentLanguage::setPrioritizedLanguages( $originalPrioritizedLanguages );
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'node', $node );
$tpl->setVariable( 'url_alias', $urlAlias );

$Result = array();
$Result['pagelayout'] = true;
$Result['content'] = $tpl->fetch( 'design:push/push_node.tpl' );

if ( $languageCode )
	$Result['path'] = array( array( 'url' => '/push/node/'. $nodeID . '/' . $languageCode, 'text' => 'Push Node' ) );
else
	$Result['path'] = array( array( 'url' => '/push/node/'. $nodeID, 'text' => 'Push Node' ) );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
	$contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
