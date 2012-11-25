<?php
/**
 * @package ShopRestAPI
 * @class   ezOrderExportHistory
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class ezOrderExportHistory extends eZPersistentObject
{
	public function __construct( $row = array() ) {
		$this->eZPersistentObject( $row );

		if( $this->attribute( 'user_id' ) === null ) {
			$this->setAttribute( 'user_id', eZUser::currentUserID() );
		}
	}

	public static function definition() {
		return array(
			'fields'              => array(
				'order_id' => array(
					'name'     => 'OrderID',
					'datatype' => 'integer',
					'default'  => null,
					'required' => true
				),
				'user_id' => array(
					'name'     => 'UserID',
					'datatype' => 'integer',
					'default'  => null,
					'required' => true
				),
				'date' => array(
					'name'     => 'Date',
					'datatype' => 'integer',
					'default'  => time(),
					'required' => true
				)
			),
			'function_attributes' => array(),
			'keys'                => array( 'order_id' ),
			'sort'                => array( 'order_id' => 'asc' ),
			'class_name'          => __CLASS__,
			'name'                => 'ezorder_export_history'
		);
	}

	public static function fetchByOrderID( $id ) {
		return eZPersistentObject::fetchObject(
			self::definition(),
			null,
			array( 'order_id' => $id ),
			true
		);
	}

	public static function fetchList( $conditions = null, $limitations = null ) {
		return eZPersistentObject::fetchObjectList(
			self::definition(),
			null,
			$conditions,
			null,
			$limitations
		);
	}
}
