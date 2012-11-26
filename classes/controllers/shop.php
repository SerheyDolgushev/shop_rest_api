<?php
/**
 * @package ShopRestAPI
 * @class   ShopController
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class ShopController extends ezpRestMvcController
{
	private static $priceAttributes = array(
		'product_total_inc_vat',
		'product_total_ex_vat',
		'total_inc_vat',
		'total_ex_vat'
	);
	private static $billingAttributes = array(
		'first_name',
		'last_name',
		'address1',
		'address2',
		'city',
		'zip',
		'country',
		'state',
		'phone'
	);

	public function doExportOrders() {
		$orders = $this->fetchOrders();
		if( (bool) $this->request->variables['onlyNew'] === true ) {
			foreach( $orders as $order ) {
				$this->markOrderAsExported( $order );
			}
		}

		$feed = array(
			'_tag'       => 'orders',
			'collection' => array()
		);
		$paymentObjectClass = class_exists( 'xrowPaymentObject' ) ? 'xrowPaymentObject' : 'eZPaymentObject';
		foreach( $orders as $order ) {
			if( $order instanceof eZOrder === false ) {
				continue;
			}

			$exportHistory     = ezOrderExportHistory::fetchByOrderID( $order->attribute( 'id' ) );
			$productCollection = $order->attribute( 'productcollection' );
			$currency          = $productCollection->attribute( 'currency_code' );
			$productItems      = $order->attribute( 'product_items' );
			$paymentObject     = call_user_func( array( $paymentObjectClass, 'fetchByOrderID' ), $order->attribute( 'id' ) );
			$isPaid            = is_object( $paymentObject ) ? (int) $paymentObject->attribute( 'status' ) : 0;
			$accountInfo       = $order->attribute( 'account_information' );
			$paymentGateway    = is_object( $paymentObject ) ? $paymentObject->attribute( 'payment_string' ) : null;
			if(
				class_exists( $paymentGateway . 'Gateway' )
				&& is_callable( array( $paymentGateway. 'Gateway', 'name' ) )
			) {
				$paymentGateway = call_user_func( array( $paymentGateway. 'Gateway', 'name' ) );
			}

			$orderInfo                        = array( '_tag' => 'order' );
			$orderInfo['id']                  = $order->attribute( 'id' );
			$orderInfo['order_nr']            = $order->attribute( 'order_nr' );
			$orderInfo['is_archived']         = $order->attribute( 'is_archived' );
			$orderInfo['was_exported_before'] = (int) ( $exportHistory instanceof ezOrderExportHistory );
			$orderInfo['status']              = $order->attribute( 'status_name' );
			$orderInfo['is_paid']             = $isPaid;
			$orderInfo['payment_gateway']     = $paymentGateway;
			$orderInfo['created']             = date( 'c', $order->attribute( 'created' ) );
			$orderInfo['updated']             = date( 'c', $order->attribute( 'status_modified' ) );
			$orderInfo['account_name']        = $order->attribute( 'account_name' );
			$orderInfo['account_email']       = $order->attribute( 'account_email' );
			foreach( self::$priceAttributes as $attribute ) {
				$orderInfo[ $attribute ] = $order->attribute( $attribute ) . ' ' . $currency;
			}

			$orderInfo['billing_info']	 = array();
			$orderInfo['shipping_info'] = array();
			foreach( self::$billingAttributes as $attribute ) {
				$value = isset( $accountInfo[ $attribute ] ) ? $accountInfo[ $attribute ] : null;
				$orderInfo['billing_info'][ $attribute ] = $value;

				$spinningAttribute = 's_' . $attribute;
				$value = isset( $accountInfo[ $spinningAttribute ] ) ? $accountInfo[ $spinningAttribute ] : null;
				$orderInfo['shipping_info'][ $attribute ] = $value;
			}

			$orderInfo['products'] = array();
			foreach( $productItems as $productItem ) {
				$productInfo = array( '_tag' => 'product' );
				$discount    = $productItem['price_inc_vat'] * $productItem['discount_percent'] . ' ' . $currency;

				$productInfo['SKU']                 = 'IS_NOT_IMPLEMENTED_YET';
				$productInfo['name']                = $productItem['object_name'];
				$productInfo['count']               = $productItem['item_count'];
				$productInfo['vat_value']           = $productItem['vat_value'] . ' ' . $currency;
				$productInfo['total_price_ex_vat']  = $productItem['total_price_ex_vat'] . ' ' . $currency;
				$productInfo['total_price_inc_vat'] = $productItem['total_price_inc_vat'] . ' ' . $currency;
				$productInfo['discount']            = $discount;
				$orderInfo['products'][] = $productInfo;
			}

			$feed['collection'][] = $orderInfo;
		}

		$result = new ezpRestMvcResult();
		$result->variables['feed'] = $feed;
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
