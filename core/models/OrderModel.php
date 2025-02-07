<?php


class Order extends Model {

	protected static $table = 'core_orders';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [
		'LEFT JOIN <core_users> AS <user_call> ON <{table}.call_user_id> = <user_call.id>',
		'LEFT JOIN <core_users> AS <user_ship> ON <{table}.ship_user_id> = <user_ship.id>',
		'LEFT JOIN <core_users> AS <user_ads> ON <{table}.ads_user_id> = <user_ads.id>'
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<user_call.id> AS <call_id>',
		'<user_call.username> AS <call_username>',
		'<user_call.avatar> AS <call_avatar>',
		'<user_call.is_ban> AS <call_is_ban>',
		'<user_ship.id> AS <ship_id>',
		'<user_ship.username> AS <ship_username>',
		'<user_ship.avatar> AS <ship_avatar>',
		'<user_ship.is_ban> AS <ship_is_ban>',
		'<user_ads.id> AS <ads_id>',
		'<user_ads.username> AS <ads_username>',
		'<user_ads.avatar> AS <ads_avatar>',
		'<user_ads.is_ban> AS <ads_is_ban>'
	];
	protected static $order_by = [
		'created_at' => 'DESC'
	];

	public const IS_NOT_PAYMENT = 0;
	public const IS_PAYMENT = 1;
	public const IS_PAYMENT_AND_DEDUCT = 2;
	public const IS_PAYING = 3;


	public const IS_CAN_NOT_EDIT = 1;
	public const IS_CAN_EDIT = 0;

	public const STATUS_PENDING_CONFIRM = 0; // chờ xác nhận
	public const STATUS_BUSY_CALLBACK = 1; // bận - gọi lại
	public const STATUS_WRONG_NUMBER = 2; // số đt không chính xác
	public const STATUS_CAN_NOT_CALL = 3; // không thể gọi
	public const STATUS_REFUSE_BUY = 4; // từ chối mua hàng
	public const STATUS_AGREE_BUY = 5; // đồng ý mua hàng
	PUBLIC CONST STATUS_DUPLICATE = 6; // TRÙNG LẶP 
	public const STATUS_TRASH = 7; // đơn hàng rác
	public const STATUS_DELIVERING = 8; // đang giao hàng
	public const STATUS_DELIVERY_DATE = 9; // hẹn ngày giao hàng
	public const STATUS_UNRECEIVED = 10; // không nhận hàng
	public const STATUS_DELIVERED = 11; // giao hàng thành công
	public const STATUS_RETURNED = 12; // trả lại hàng
	public const STATUS_CALLING = 13; // đang gọi

	public static function create($data)
	{

		$data = array_merge([
			'status' => self::STATUS_PENDING_CONFIRM,
			'ads_team_id' => 0,
			'call_team_id' => 0,
			'ship_team_id' => 0,
			'landing_id' => 0,
			'country_id' => 0,
			'product_id' => 0,
			'ads_user_id' => 0,
			'call_user_id' => 0,
			'ship_user_id' => 0,
			'product_name' => '',
			'product_image' => '',
			'product_price' => 0,
			'currency_id' => 0,
			'currency_exchange_rate' => 1,
			'currency' => Currency::DEFAULT_CURRENCY,
			'quantity' => 0,
			'price' => 0,
			'ads_cost' => 0,
			'delivery_cost' => 0,
			'import_cost' => 0,
			'profit_leader_call' => 0,
			'profit_leader_ads' => 0,
			'profit_leader_ship' => 0,
			'deduct_leader_call' => 0,
			'deduct_leader_ads' => 0,
			'deduct_leader_ship' => 0,
			'profit_member_call' => 0,
			'profit_member_ads' => 0,
			'profit_member_ship' => 0,
			'deduct_member_call' => 0,
			'deduct_member_ads' => 0,
			'deduct_member_ship' => 0,
			'order_first_name' => '',
			'order_last_name' => '',
			'order_phone' => '',
			'order_address' => '',
			'order_province_id' => 0,
			'order_district_id' => 0,
			'order_ward_id' => 0,
			'order_area_id' => 0,
			'note_ads' => '',
			'note_call' => '',
			'note_ship' => '',
			'payment_ads' => self::IS_NOT_PAYMENT,
			'payment_call' => self::IS_NOT_PAYMENT,
			'payment_ship' => self::IS_NOT_PAYMENT,
			'can_edit' => self::IS_CAN_EDIT,
			'call_at' => 0,
			'delivery_at' => 0,
			'created_at' => time(),
			'updated_at' => 0
		], $data);

		if(self::insert($data) > 0)
		{
			return true;
		}
		return false;
	}

	public static function make_paying($date, $user_id) {
		$where = [
			'[RAW] <{table}.created_at> <= :date' => [
				'date' => $date
			]
		];

		$where_earning = array_merge($where, [
			'status' => Order::STATUS_DELIVERED,
			'OR' => [
				'AND #1' => [
					'ads_user_id' => $user_id,
					'payment_ads' => Order::IS_NOT_PAYMENT,
				],
				'AND #2' => [
					'call_user_id' => $user_id,
					'payment_call' => Order::IS_NOT_PAYMENT,
				],
				'AND #3' => [
					'ship_user_id' => $user_id,
					'payment_ship' => Order::IS_NOT_PAYMENT,
				]
			]
		]);

		$where_deduct = array_merge($where, [
			'status' => Order::STATUS_RETURNED,
			'OR' => [
				'AND #1' => [
					'ads_user_id' => $user_id,
					'payment_ads' => Order::IS_PAYMENT,
				],
				'AND #2' => [
					'call_user_id' => $user_id,
					'payment_call' => Order::IS_PAYMENT,
				],
				'AND #3' => [
					'ship_user_id' => $user_id,
					'payment_ship' => Order::IS_PAYMENT,
				]
			]
		]);

		Order::update($where_earning, [
			'[RAW] <payment_ads> = CASE WHEN <ads_user_id> = :user_id AND <payment_ads> = :not_payment THEN :paying ELSE <payment_ads> END' => [
				'user_id' => $user_id,
				'not_payment' => Order::IS_NOT_PAYMENT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_call> = CASE WHEN <call_user_id> = :user_id AND <payment_call> = :not_payment THEN :paying ELSE <payment_call> END' => [
				'user_id' => $user_id,
				'not_payment' => Order::IS_NOT_PAYMENT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_ship> = CASE WHEN <ship_user_id> = :user_id AND <payment_ship> = :not_payment THEN :paying ELSE <payment_ship> END' => [
				'user_id' => $user_id,
				'not_payment' => Order::IS_NOT_PAYMENT,
				'paying' => Order::IS_PAYING
			]
		]);

		Order::update($where_deduct, [
			'[RAW] <payment_ads> = CASE WHEN <ads_user_id> = :user_id AND <payment_ads> = :payment THEN :paying ELSE <payment_ads> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_call> = CASE WHEN <call_user_id> = :user_id AND <payment_call> = :payment THEN :paying ELSE <payment_call> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_ship> = CASE WHEN <ship_user_id> = :user_id AND <payment_ship> = :payment THEN :paying ELSE <payment_ship> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT,
				'paying' => Order::IS_PAYING
			]
		]);
	}

	public static function make_payment($date, $user_id) {
		$where = [
			'[RAW] <{table}.created_at> <= :date' => [
				'date' => $date
			],
			'OR' => [
				'AND #1' => [
					'ads_user_id' => $user_id,
					'payment_ads' => Order::IS_PAYING,
				],
				'AND #2' => [
					'call_user_id' => $user_id,
					'payment_call' => Order::IS_PAYING,
				],
				'AND #3' => [
					'ship_user_id' => $user_id,
					'payment_ship' => Order::IS_PAYING,
				]
			]
		];

		$where_earning = array_merge($where, [
			'status' => Order::STATUS_DELIVERED
		]);

		$where_deduct = array_merge($where, [
			'status' => Order::STATUS_RETURNED
		]);

		Order::update($where_earning, [
			'[RAW] <payment_ads> = CASE WHEN <ads_user_id> = :user_id AND <payment_ads> = :paying THEN :payment ELSE <payment_ads> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_call> = CASE WHEN <call_user_id> = :user_id AND <payment_call> = :paying THEN :payment ELSE <payment_call> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_ship> = CASE WHEN <ship_user_id> = :user_id AND <payment_ship> = :paying THEN :payment ELSE <payment_ship> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT,
				'paying' => Order::IS_PAYING
			]
		]);

		Order::update($where_deduct, [
			'[RAW] <payment_ads> = CASE WHEN <ads_user_id> = :user_id AND <payment_ads> = :paying THEN :payment ELSE <payment_ads> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT_AND_DEDUCT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_call> = CASE WHEN <call_user_id> = :user_id AND <payment_call> = :paying THEN :payment ELSE <payment_call> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT_AND_DEDUCT,
				'paying' => Order::IS_PAYING
			],
			'[RAW] <payment_ship> = CASE WHEN <ship_user_id> = :user_id AND <payment_ship> = :paying THEN :payment ELSE <payment_ship> END' => [
				'user_id' => $user_id,
				'payment' => Order::IS_PAYMENT_AND_DEDUCT,
				'paying' => Order::IS_PAYING
			]
		]);
	}

	public static function send_notification_returned($orders) {
		if($orders) {
			foreach($orders as $order) {
				$deduct_ads = 0;
				$deduct_call = 0;
				$deduct_ship = 0;
				if($order['payment_ads'] == Order::IS_PAYMENT && $order['ads_user_id'] == Auth::$data['id']) {
					$deduct_ads = $order['deduct_member_ads'];
				}
				if($order['payment_call'] == Order::IS_PAYMENT && $order['call_user_id'] == Auth::$data['id']) {
					$deduct_call = $order['deduct_member_call'] * $order['quantity'];
				}
				if($order['payment_ship'] == Order::IS_PAYMENT && $order['ship_user_id'] == Auth::$data['id']) {
					$deduct_ship = $order['deduct_member_ship'];
				}

				if(($deduct_ads + $deduct_call + $deduct_ship) < 1) {
					continue;
				}

				if($order['ads_user_id'] == $order['call_user_id'] && $order['call_user_id'] == $order['ship_user_id']) {
					Notification::create([
						'user_id' => $order['ads_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'ads' => $deduct_ads,
							'call' => $deduct_call,
							'ship' => $deduct_ship
						]
					]);
				}
				else if($order['ads_user_id'] == $order['call_user_id'] && $order['call_user_id'] != $order['ship_user_id']) {
					Notification::create([
						'user_id' => $order['ads_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'ads' => $deduct_ads,
							'call' => $deduct_call
						]
					]);
					Notification::create([
						'user_id' => $order['ship_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'ship' => $deduct_ship
						]
					]);
				}
				else if($order['ads_user_id'] != $order['call_user_id'] && $order['call_user_id'] == $order['ship_user_id']) {
					Notification::create([
						'user_id' => $order['ads_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'ads' => $deduct_ads
						]
					]);
					Notification::create([
						'user_id' => $order['call_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'call' => $deduct_call,
							'ship' => $deduct_ship
						]
					]);
				}
				else if($order['ads_user_id'] == $order['ship_user_id'] && $order['ship_user_id'] != $order['call_user_id']) {
					Notification::create([
						'user_id' => $order['ads_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'ads' => $deduct_ads,
							'ship' => $deduct_ship
						]
					]);
					Notification::create([
						'user_id' => $order['call_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'call' => $deduct_call
						]
					]);
				} else {
					Notification::create([
						'user_id' => $order['ads_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'ads' => $deduct_ads
						]
					]);
					Notification::create([
						'user_id' => $order['call_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'call' => $deduct_call
						]
					]);
					Notification::create([
						'user_id' => $order['ship_user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => notificationController::TYPE_RETURNED_ORDER,
						'data' => [
							'order_id' => $order['id'],
							'currency' => $order['currency'],
							'ship' => $deduct_ship
						]
					]);
				}
			}
		}
	}

	protected static function onBeforeInsert($data = null)
	{

	}

	protected static function onSuccessInsert($insert_id = null)
	{

	}

	protected static function onErrorInsert()
	{

	}

	protected static function onBeforeUpdate($data = null, $where = null)
	{

	}

	protected static function onSuccessUpdate($count_items = 0)
	{

	}

	protected static function onErrorUpdate()
	{

	}

	protected static function onBeforeDelete($where = null)
	{
	}

	protected static function onSuccessDelete($count_items = 0)
	{

	}

	protected static function onErrorDelete()
	{

	}
}





?>