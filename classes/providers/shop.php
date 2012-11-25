<?php
/**
 * @package ShopRestAPI
 * @class   ShopProvider
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class ShopProvider implements ezpRestProviderInterface
{
	public function getRoutes()	{
		return array(
			'exportOrders' => new ezpMvcRegexpRoute(
				'@^/orders/export(/(?P<onlyNew>[1|0]+))?(/(?P<output>[json|xml]+))?$@',
				'ShopController',
				'exportOrders',
				array(
					'onlyNew' => true,
					'output'  => 'xml'
				)
			)
		);
	}

	public function getViewController() {
		return new ShopViewController();
	}
}
