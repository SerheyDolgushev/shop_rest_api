<?php
/**
 * @package ShopRestAPI
 * @class   ShopController
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class ShopController extends ezpRestMvcController
{
	public function doExportOrders() {
		$orders = $this->fetchOrders();
		if( (bool) $this->request->variables['onlyNew'] === true ) {
			foreach( $orders as $order ) {
				$this->markOrderAsExported( $order );
			}
		}

		$result = new ezpRestMvcResult();
		$result->variables['orders'] = $orders;
		return $result;
	}

	private function fetchOrders() {
		/**
		 * eZPersistentObject does not support NOT IN SQL statement. Thats why all
		 * orders should be fetched and filterd the new ones (if it is required)
		 **/
		$orders = eZPersistentObject::fetchObjectList(
			eZOrder::definition(),
			null,
			array( 'is_temporary' => 0 ),
			array( 'created' => 'asc' )
		);

		if( (bool) $this->request->variables['onlyNew'] === true ) {
			foreach( $orders as $key => $order ) {
				$exportHistory = ezOrderExportHistory::fetchByOrderID( $order->attribute( 'id' ) );
				if( $exportHistory instanceof ezOrderExportHistory ) {
					unset( $orders[ $key ] );
				}
			}
		}

		return $orders;
	}

	private function markOrderAsExported( eZOrder $order ) {
		$exportHistory = new ezOrderExportHistory( array( 'order_id' => $order->attribute( 'id' ) ) );
		$exportHistory->store();
	}
}
