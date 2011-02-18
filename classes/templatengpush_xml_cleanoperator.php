<?php

class TemplateNgpush_xml_cleanOperator
{
		/*!
			Constructor, does nothing by default.
		*/
		function TemplateNgpush_xml_cleanOperator()
		{
		}

		/*!
		 \return an array with the template operator name.
		*/
		function operatorList()
		{
				return array( 'ngpush_xml_clean' );
		}

		/*!
		 \return true to tell the template engine that the parameter list exists per operator type,
						 this is needed for operator classes that have multiple operators.
		*/
		function namedParameterPerOperator()
		{
				return true;
		}

		/*!
		 See eZTemplateOperator::namedParameterList
		*/
		function namedParameterList()
		{
				return array( 'ngpush_xml_clean' =>
					array( 'first_param' => array(
						'type' => 'string',
						'required' => false,
						'default' => 'default text' ),
						'second_param' => array( 'type' => 'integer',
						'required' => false,
						'default' => 0 ) ) );
		}


		/*!
		 Executes the PHP function for the operator cleanup and modifies \a $operatorValue.
		*/
		function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
		{
				$firstParam = $namedParameters['first_param'];
				$secondParam = $namedParameters['second_param'];
				// Example code. This code must be modified to do what the operator should do. Currently it only trims text.
				switch ( $operatorName )
				{
						case 'ngpush_xml_clean':
						{
								//trim( $operatorValue );
								$operatorValue = strip_tags($operatorValue, "<p>");
								$operatorValue = preg_replace("/(?:&nbsp;)+/", " ", $operatorValue);
								$operatorValue = preg_replace("/[ \s]+/", " ", $operatorValue);
								$operatorValue = str_replace("</p>", "", $operatorValue);
								$operatorValue = preg_replace("/<p.*?>/", "\n", $operatorValue);
								$operatorValue = preg_replace("/(?: \n)+/", "\n", $operatorValue);
								$operatorValue = preg_replace("/(?:\n )+/", "\n", $operatorValue);
								$operatorValue = trim($operatorValue);
						} break;
				}
		}
}

?>
