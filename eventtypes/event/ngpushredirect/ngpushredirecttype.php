<?php

class ngPushRedirectType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_STRING = "ngpushredirect";

    public function __construct()
    {
        parent::__construct(self::EZ_WORKFLOW_TYPE_STRING, "Netgen Push Redirect");
    }

    public function execute( $process, $event )
    {
        $processParameters = $process->attribute( 'parameter_list' );
        $object  = eZContentObject::fetch( $processParameters['object_id'] );

        $node = $object->mainNode();
		$href = '/push/node/' . $node->attribute( 'node_id' );

		$version = eZContentObjectVersion::fetchVersion( $processParameters['version'], $processParameters['object_id'] );
		if ( $version instanceof eZContentObjectVersion )
		{
			$language = eZContentLanguage::fetch( $version->attribute( 'initial_language_id' ) );
			if ( $language instanceof eZContentLanguage )
				$href .= '/' . $language->attribute( 'locale' );
		}

		eZURI::transformURI($href, false, 'full');
		$http = eZHTTPTool::instance();
		$http->setSessionVariable( 'RedirectURIAfterPublish', $href );
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType(ngPushRedirectType::EZ_WORKFLOW_TYPE_STRING, 'ngpushredirecttype');

?>
