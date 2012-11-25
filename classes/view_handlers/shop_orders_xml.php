<?php
/**
 * @package ShopRestAPI
 * @class   ShopOrdersXMLViewHandler
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class ShopOrdersXMLViewHandler implements ezcMvcViewHandler
{
	protected $zoneName;
	protected $result;
	protected $variables = array();

	public function __construct( $zoneName, $templateLocation = null ) {
		$this->zoneName = $zoneName;
	}

	public function __get( $name ) {
		return $this->variables[ $name ];
	}

	public function __isset( $name ) {
		return array_key_exists( $name, $this->variables );
	}

	public function send( $name, $value ) {
		$this->variables[ $name ] = $value;
	}

    public function process( $last ) {
    	$dom = new DOMDocument( '1.0', 'utf-8' );
    	$dom->formatOutput = true;

		$response = $dom->createElement( 'orders' );
		$dom->appendChild( $response );

		$paymentObjectClass = class_exists( 'xrowPaymentObject' ) ? 'xrowPaymentObject' : 'eZPaymentObject';
		$orders = isset( $this->variables['orders'] ) ? $this->variables['orders'] : array();
		foreach( $orders as $order ) {
			if( $order instanceof eZOrder ) {
				$orderNode = $dom->createElement( 'order' );
				$response->appendChild( $orderNode );

				$exportHistory     = ezOrderExportHistory::fetchByOrderID( $order->attribute( 'id' ) );
				$wasExportedBefore = (int) ( $exportHistory instanceof ezOrderExportHistory );
				$productCollection = $order->attribute( 'productcollection' );
				$currency          = $productCollection->attribute( 'currency_code' );
				$priceAttributes   = array( 'product_total_inc_vat', 'product_total_ex_vat', 'total_inc_vat', 'total_ex_vat' );
				$productItems      = $order->attribute( 'product_items' );
				$paymentObject     = call_user_func( array( $paymentObjectClass, 'fetchByOrderID' ), $order->attribute( 'id' ) );
				$isPaid            = is_object( $paymentObject ) ? (int) $paymentObject->status : 0;

				$orderNode->appendChild( $dom->createElement( 'id', $order->attribute( 'id' ) ) );
				$orderNode->appendChild( $dom->createElement( 'order_nr', $order->attribute( 'order_nr' ) ) );
				$orderNode->appendChild( $dom->createElement( 'is_archived', $order->attribute( 'is_archived' ) ) );
				$orderNode->appendChild( $dom->createElement( 'was_exported_before', $wasExportedBefore ) );
				$orderNode->appendChild( $dom->createElement( 'status', $order->attribute( 'status_name' ) ) );
				$orderNode->appendChild( $dom->createElement( 'is_paid', $isPaid ) );
				$orderNode->appendChild( $dom->createElement( 'created', date( 'c', $order->attribute( 'created' ) ) ) );
				$orderNode->appendChild( $dom->createElement( 'updated', date( 'c', $order->attribute( 'status_modified' ) ) ) );
				foreach( $priceAttributes as $attribute ) {
					$orderNode->appendChild( $dom->createElement( $attribute, $order->attribute( $attribute ) . ' ' . $currency ) );
				}
				$orderNode->appendChild( $dom->createElement( 'account_name', $order->attribute( 'account_name' ) ) );
				$orderNode->appendChild( $dom->createElement( 'account_email', $order->attribute( 'account_email' ) ) );

				$productsNode = $dom->createElement( 'products' );
				$orderNode->appendChild( $productsNode );
				foreach( $productItems as $productItem ) {
					$productNode = $dom->createElement( 'product' );
					$productsNode->appendChild( $productNode );

					$discount = $productItem['price_inc_vat'] * $productItem['discount_percent'] . ' ' . $currency;

					$productNode->appendChild( $dom->createElement( 'SKU', 'IS_NOT_IMPLEMENTED_YET' ) );
					$productNode->appendChild( $dom->createElement( 'name', $productItem['object_name'] ) );
					$productNode->appendChild( $dom->createElement( 'count', $productItem['item_count'] ) );
					$productNode->appendChild( $dom->createElement( 'vat_value', $productItem['vat_value'] . ' ' . $currency ) );
					$productNode->appendChild( $dom->createElement( 'total_price_ex_vat', $productItem['total_price_ex_vat'] . ' ' . $currency ) );
					$productNode->appendChild( $dom->createElement( 'total_price_inc_vat', $productItem['total_price_inc_vat'] . ' ' . $currency ) );
					$productNode->appendChild( $dom->createElement( 'discount', $discount ) );
				}
			}
		}

    	$this->result = $dom->saveXML();
   	}

	public function getName() {
		return $this->zoneName;
	}

	public function getResult() {
		return $this->result;
	}
}
