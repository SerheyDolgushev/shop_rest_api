<?php
/**
 * @package ShopRestAPI
 * @class   ShopViewController
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class ShopViewController implements ezpRestViewControllerInterface
{
	public function loadView( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result ) {
		return new ShopXMLView( $request, $result );
	}
}
