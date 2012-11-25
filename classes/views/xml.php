<?php
/**
 * @package ShopRestAPI
 * @class   ShopXMLView
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class ShopXMLView extends ezcMvcView
{
	public function __construct( ezcMvcRequest $request, ezcMvcResult $result ) {
		parent::__construct( $request, $result );

		$result->content = new ezcMvcResultContent( '', 'application/xml', 'UTF-8' );
	}

	public function createZones( $layout ) {
		return array( new ShopOrdersXMLViewHandler( 'content' ) );
	}
}
