<?php


class Team extends Model {

	protected static $table = 'core_teams';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> ON <{table}.leader_id> = <core_users.id>',
		'LEFT JOIN <core_country> ON <{table}.country_id> = <core_country.id>'
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<core_users.username> AS <leader_username>',
		'<core_users.avatar> AS <leader_avatar>',
		'<core_users.is_ban> AS <leader_is_ban>',
		'<core_country.name> AS <country_name>',
		'(SELECT <name> FROM <core_currencys> WHERE <id> = <core_country.currency_id>) AS <currency>',
		'(SELECT <color> FROM <core_roles> WHERE <id> = <core_users.role_id>) AS <leader_role_color>',
		'(SELECT COUNT(<id>) FROM <core_users> WHERE <team_id> = <{table}.id>) AS <total_members>'
	];
	protected static $order_by = [
		'name' => 'ASC'
	];

	public const IS_BAN = 1;
	public const IS_NOT_BAN = 0;

	public const TYPE_ONLY_CALL = 1;
	public const TYPE_ONLY_SHIP = 2;
	public const TYPE_ONLY_ADS = 3;
	public const TYPE_FULLSTACK = 4;

	public static function create($name, $type, $country_id, $product_id, $profit, $deduct)
	{
		if (!$name || $type == '' || !$country_id)
		{
			return false;
		}

		if(self::insert([
			'name' => $name,
			'type' => $type,
			'leader_id' => 0,
			'country_id' => $country_id,
			'product_id' => implode(',', $product_id),
			'profit_call' => $profit['profit_call'],
			'deduct_call' => $deduct['deduct_call'],
			'profit_ads' => $profit['profit_ads'],
			'deduct_ads' => $deduct['deduct_ads'],
			'profit_ship' => $profit['profit_ship'],
			'deduct_ship' => $deduct['deduct_ship'],
			'note' => '',
			'perms' => [],
			'is_ban' => self::IS_NOT_BAN,
			'reason_ban' => ''
		]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function get_data($team_type) {

		$icon = null;
        $color = null;
        $text = null;
		$role = null;

		if($team_type != '') {
			switch($team_type) {
				case Team::TYPE_ONLY_ADS:
					$text = Role::DEFAULT_NAME_ADVERTISER;
					$color = Role::DEFAULT_COLOR_ADVERTISER;
					$icon = '<i class="fas fa-ad"></i>';
					$role = Role::DEFAULT_ROLE_ADVERTISER;
					break;

				case Team::TYPE_ONLY_SHIP:
					$text = Role::DEFAULT_NAME_SHIPPER;
					$color = Role::DEFAULT_COLOR_SHIPPER;
					$icon = '<i class="fas fa-shipping-fast"></i>';
					$role = Role::DEFAULT_ROLE_SHIPPER;
					break;

				case Team::TYPE_FULLSTACK:
					$text = Role::DEFAULT_NAME_FULLSTACK;
					$color = Role::DEFAULT_COLOR_FULLSTACK;
					$icon = '<i class="fas fa-layer-group"></i>';
					$role = Role::DEFAULT_ROLE_FULLSTACK;
					break;

				default:
					$text = Role::DEFAULT_NAME_CALLER;
					$color = Role::DEFAULT_COLOR_CALLER;
					$icon = '<i class="fas fa-headset"></i>';
					$role = Role::DEFAULT_ROLE_CALLER;
					break;
			}			
		}

		return compact('icon', 'color', 'text', 'role');
	}

	public static function is_leader($team_id, $user_id = null) {
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : User::get($user_id));
		if(!$user) {
			return false;
		}

		return parent::has([
			'id' => $team_id,
			'leader_id' => $user['id']
		]);
	}


	public static function get_earnings($team_id, $where = []) {
		$team = is_array($team_id) ? $team_id : parent::get($team_id);
		if(!$team) {
			return 0;
		}

		$get = Order::select([
			'SUM(CASE WHEN <{table}.ads_team_id> = '.$team['id'].' AND <{table}.payment_ads> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_leader_ads> ELSE 0 END + CASE WHEN <{table}.call_team_id> = '.$team['id'].' AND <{table}.payment_call> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.quantity> * <{table}.profit_leader_call> ELSE 0 END + CASE WHEN <{table}.ship_team_id> = '.$team['id'].' AND <{table}.payment_ship> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_leader_ship> ELSE 0 END) AS <amount>'
		])::get(array_merge([
			'status' => [
				Order::STATUS_DELIVERED

			],
			'country_id' => $team['country_id'],
			'OR' => [
				'AND #1' => [
					'ads_team_id' => $team['id'],
					'payment_ads' => Order::IS_NOT_PAYMENT
				],
				'AND #2' => [
					'call_team_id' => $team['id'],
					'payment_call' => Order::IS_NOT_PAYMENT
				],
				'AND #3' => [
					'ship_team_id' => $team['id'],
					'payment_ship' => Order::IS_NOT_PAYMENT
				]
			]
		], $where));

		return $get ? $get['amount'] : 0;
	}

	public static function get_holdings($team_id, $where = []) {
		$team = is_array($team_id) ? $team_id : parent::get($team_id);
		if(!$team) {
			return 0;
		}

		$get = Order::select([
			'SUM(CASE WHEN <{table}.ads_team_id> = '.$team['id'].' AND <{table}.payment_ads> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_leader_ads> ELSE 0 END + CASE WHEN <{table}.call_team_id> = '.$team['id'].' AND <{table}.payment_call> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.quantity> * <{table}.profit_leader_call> ELSE 0 END + CASE WHEN <{table}.ship_team_id> = '.$team['id'].' AND <{table}.payment_ship> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_leader_ship> ELSE 0 END) AS <amount>'
		])::get(array_merge([
			'status' => [
				Order::STATUS_AGREE_BUY,
				Order::STATUS_DELIVERING,
				Order::STATUS_DELIVERY_DATE
			],
			'country_id' => $team['country_id'],
			'OR' => [
				'AND #1' => [
					'ads_team_id' => $team['id'],
					'payment_ads' => Order::IS_NOT_PAYMENT
				],
				'AND #2' => [
					'call_team_id' => $team['id'],
					'payment_call' => Order::IS_NOT_PAYMENT
				],
				'AND #3' => [
					'ship_team_id' => $team['id'],
					'payment_ship' => Order::IS_NOT_PAYMENT
				]
			]
		], $where));


		return $get ? $get['amount'] : 0;
	}

	public static function get_deduct($team_id, $where = [], $all_deduct = true) {
		$team = is_array($team_id) ? $team_id : parent::get($team_id);
		if(!$team) {
			return 0;
		}
		$get = Order::select([
			'SUM(CASE WHEN <{table}.ads_team_id> = '.$team['id'].' AND <{table}.payment_ads> = '.Order::IS_PAYMENT.' THEN <{table}.deduct_leader_ads> ELSE 0 END + CASE WHEN <{table}.call_team_id> = '.$team['id'].' AND <{table}.payment_call> = '.Order::IS_PAYMENT.' THEN <{table}.quantity> * <{table}.deduct_leader_call> ELSE 0 END + CASE WHEN <{table}.ship_team_id> = '.$team['id'].' AND <{table}.payment_ship> = '.Order::IS_PAYMENT.' THEN <{table}.deduct_leader_ship> ELSE 0 END) AS <amount>'
		])::get(array_merge([
			'status' => [
				Order::STATUS_RETURNED
			],
			'country_id' => $team['country_id'],
			'OR' => [
				'AND #1' => [
					'ads_team_id' => $team['id'],
					'payment_ads' => Order::IS_PAYMENT
				],
				'AND #2' => [
					'call_team_id' => $team['id'],
					'payment_call' => Order::IS_PAYMENT
				],
				'AND #3' => [
					'ship_team_id' => $team['id'],
					'payment_ship' => Order::IS_PAYMENT
				]
			]
		], $where));

		$user_deduct = User::select([
			'SUM(<amount_deduct>) AS <deduct>'
		])::get([
			'team_id' => $team['id']
		]);

		if($all_deduct == false) {
			return $get ? -$get['amount'] : 0;
		}
		
		return $get ? -($get['amount'] + ($user_deduct ? $user_deduct['deduct'] : 0)) : 0;
	}

	public static function get_product($team_id) {
		$team = is_array($team_id) ? $team_id : parent::get($team_id);
		if(!$team) {
			return null;
		}
		return Product::select([
            'id',
            'name',
            'image'
        ])::list([
			'status' => Product::STATUS_ACTIVE,
            '[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
                'ids' => $team['product_id']
            ]
        ]);;
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