<?php


class User extends Model {
	protected static $table = 'core_users';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_roles> ON <{table}.role_id> = <core_roles.id>',
		'LEFT JOIN <core_teams> ON <{table}.team_id> = <core_teams.id>'
	];
	protected static $default_selects = [
		'<{table}.*>',
        '<core_roles.name> AS <role_name>', 
        '<core_roles.color> AS <role_color>', 
        '<core_roles.perms> AS <role_perms>', 
        '<core_roles.level> AS <role_level>',
        '<core_teams.name> AS <team_name>',
        '<core_teams.type> AS <team_type>',
		'(SELECT <country_id> FROM <core_teams> WHERE <id> = <{table}.team_id>) AS <country_id>'
	];
	protected static $order_by = [
		'username' => 'ASC'
	];


	public const DEFAULT_LIMIT_DEVICE = 3;
	public const DEFAULT_REP = 0;
	public const SEX_UNKNOWN = 'u';
	public const SEX_MALE = 'm';
	public const SEX_FEMALE = 'f';

	public const IS_NOT_BAN = 0;
	public const IS_BAN = 1;


	public static function create($data = [])
	{
		if(!isset($data['username']) || !isset($data['password']) || !isset($data['email']))
		{
			return false;
		}

		$data = array_merge([
			'username' => null,
			'password' => null,
			'email' => null,
			'name' => '',
			'sex' => self::SEX_UNKNOWN,
			'date_of_birth' => '',
			'avatar' => '',
			'cover' => '',
			'facebook' => '',
			'billing' => [
				'account_name' => '',
				'bank_name' => '',
				'bank_branch' => '',
				'account_number' => ''
			],
			'role_id' => Role::DEFAULT_ROLE_MEMBER,
			'perms' => [],
			'settings' => [
				'language' => env(DotEnv::DEFAULT_LANGUAGE)
			],
			'team_id' => 0,
			'forgot_key' => '',
			'forgot_time' => 0,
			'auth_session' => '',
			'limit_device' => self::DEFAULT_LIMIT_DEVICE,
			'is_ban' => self::IS_NOT_BAN,
			'is_ban_team' => self::IS_NOT_BAN,
			'reason_ban' => '',
			'reason_ban_team' => '',
			'profit_call' => 0,
			'deduct_call' => 0,
			'profit_ads' => 0,
			'deduct_ads' => 0,
			'profit_ship' => 0,
			'deduct_ship' => 0,
			'amount_deduct' => 0,
			'adm' => 0
		], $data);

		$data['password'] = Auth::encrypt_password($data['password']);

		if(parent::insert($data) == true)
		{
			return true;
		}

		return false;
	}

	public static function get_earnings($user_id = null, $where = []) {
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user) {
			return 0;
		}

		$get = Order::select([
			'SUM(CASE WHEN <{table}.ads_user_id> = '.$user['id'].' AND <{table}.payment_ads> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_member_ads> ELSE 0 END + CASE WHEN <{table}.call_user_id> = '.$user['id'].' AND <{table}.payment_call> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.quantity> * <{table}.profit_member_call> ELSE 0 END + CASE WHEN <{table}.ship_user_id> = '.$user['id'].' AND <{table}.payment_ship> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_member_ship> ELSE 0 END) AS <amount>'
		])::get(array_merge([
			'status' => [
				Order::STATUS_DELIVERED
			],
			'country_id' => $user['country_id'],
			'OR' => [
				'AND #1' => [
					'ads_user_id' => $user['id'],
					'payment_ads' => Order::IS_NOT_PAYMENT
				],
				'AND #2' => [
					'call_user_id' => $user['id'],
					'payment_call' => Order::IS_NOT_PAYMENT
				],
				'AND #3' => [
					'ship_user_id' => $user['id'],
					'payment_ship' => Order::IS_NOT_PAYMENT
				]
			]
		], $where));

		return $get ? $get['amount'] : 0;
	}

	public static function get_holdings($user_id = null, $where = []) {
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user) {
			return 0;
		}

		$get = Order::select([
			'SUM(CASE WHEN <{table}.ads_user_id> = '.$user['id'].' AND <{table}.payment_ads> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_member_ads> ELSE 0 END + CASE WHEN <{table}.call_user_id> = '.$user['id'].' AND <{table}.payment_call> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.quantity> * <{table}.profit_member_call> ELSE 0 END + CASE WHEN <{table}.ship_user_id> = '.$user['id'].' AND <{table}.payment_ship> = '.Order::IS_NOT_PAYMENT.' THEN <{table}.profit_member_ship> ELSE 0 END) AS <amount>'
		])::get(array_merge([
			'status' => [
				Order::STATUS_AGREE_BUY,
				Order::STATUS_DELIVERING,
				Order::STATUS_DELIVERY_DATE
			],
			'country_id' => $user['country_id'],
			'OR' => [
				'AND #1' => [
					'ads_user_id' => $user['id'],
					'payment_ads' => Order::IS_NOT_PAYMENT
				],
				'AND #2' => [
					'call_user_id' => $user['id'],
					'payment_call' => Order::IS_NOT_PAYMENT
				],
				'AND #3' => [
					'ship_user_id' => $user['id'],
					'payment_ship' => Order::IS_NOT_PAYMENT
				]
			]
		], $where));



		return $get ? $get['amount'] : 0;
	}

	public static function get_deduct($user_id = null, $where = [], $all_deduct = true) {
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user) {
			return 0;
		}

		$get = Order::select([
			'SUM(CASE WHEN <{table}.ads_user_id> = '.$user['id'].' AND <{table}.payment_ads> = '.Order::IS_PAYMENT.' THEN <{table}.deduct_member_ads> ELSE 0 END + CASE WHEN <{table}.call_user_id> = '.$user['id'].' AND <{table}.payment_call> = '.Order::IS_PAYMENT.' THEN <{table}.quantity> * <{table}.deduct_member_call> ELSE 0 END + CASE WHEN <{table}.ship_user_id> = '.$user['id'].' AND <{table}.payment_ship> = '.Order::IS_PAYMENT.' THEN <{table}.deduct_member_ship> ELSE 0 END) AS <amount>'
		])::get(array_merge([
			'status' => [
				Order::STATUS_RETURNED
			],
			'country_id' => $user['country_id'],
			'OR' => [
				'AND #1' => [
					'ads_user_id' => $user['id'],
					'payment_ads' => Order::IS_PAYMENT
				],
				'AND #2' => [
					'call_user_id' => $user['id'],
					'payment_call' => Order::IS_PAYMENT
				],
				'AND #3' => [
					'ship_user_id' => $user['id'],
					'payment_ship' => Order::IS_PAYMENT
				]
			]
		], $where));

		if($all_deduct == false) {
			return $get ? -$get['amount'] : 0;
		}

		return $get ? -($get['amount'] + $user['amount_deduct']) : 0;
	}

	public static function get_billing($user_id = null, $name = null, $default_value = null)
	{
		if(!is_numeric($user_id) && !is_array($user_id))
		{
			$default_value = $name;
			$name = $user_id;
			$user_id = null;
		}

		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return $default_value;
		}

		$billing = $user['billing'];
		if(!is_array($billing))
		{
			$billing = json_decode($billing, true);
			if(!isset($billing['account_name'])) {
				$billing['account_name'] = null;
			}
			if(!isset($billing['account_number'])) {
				$billing['account_number'] = null;
			}
			if(!isset($billing['bank_name'])) {
				$billing['bank_name'] = null;
			}
			if(!isset($billing['bank_branch'])) {
				$billing['bank_branch'] = null;
			}
		}

		if(!$name)
		{
			return $billing;
		}

		return isset($billing[$name]) ? $billing[$name] : $default_value;
	}

	public static function update_billing($user_id, $data_billing)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));

		$current_data = self::get_billing($user);
		if(!is_array($current_data)) {
			$current_data = [$current_data];
		}

		$data_billing = array_merge($current_data, $data_billing);

		if(parent::update($user['id'], [
			'billing' => $data_billing
		]) > 0)
		{
			if($user['id'] == Auth::$id)
			{
				Auth::$data['billing'] = $data_billing;
			}
			return true;
		}

		return false;
	}

	public static function get_setting($user_id = null, $name = null, $default_value = null)
	{
		if(!is_numeric($user_id) && !is_array($user_id))
		{
			$default_value = $name;
			$name = $user_id;
			$user_id = null;
		}

		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return $default_value;
		}

		$settings = $user['settings'];
		if(!is_array($settings))
		{
			$settings = json_decode($settings, true);
		}

		if(!$name)
		{
			return $settings;
		}

		return isset($settings[$name]) ? $settings[$name] : $default_value;
	}

	public static function update_setting($user_id, $data_settings)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));

		$data_settings = array_merge(self::get_setting($user), $data_settings);

		if(parent::update($user['id'], [
			'settings' => $data_settings
		]) > 0)
		{
			if($user['id'] == Auth::$id)
			{
				Auth::$data['settings'] = $data_settings;
			}
			return true;
		}

		return false;
	}

	public static function no_avatar($text = null)
	{
		$random_color = [
			'#9e9e9e',
			'#ba68c8',
			'#7986cb',
			'#e06055',
			'#a1887f',
			'#9ccc65',
			'#4dd0e1',
			'#f6bf26',
			'#8e96c2',
			'#57bb8a',
			'#5e97f6'
		];
		$background = $random_color[0];
		$color = '#FFFFFF';

		if($text != "")
		{
			$char = $text[0];
			$hex_array = str_split(ord($char));
			$index = array_reduce($hex_array, function($total, $value) {
				if(!is_numeric($value))
				{
					$value = 0;
				}
				return $total += $value;
			}, 0);

			$max = count($random_color) - 1;
			while($index > $max)
			{
				$index = $index - $max;
			}

			$background = $random_color[$index];
		}
		else
		{
			$char = '?';
			$background = '#e8eaed';
			$color = '#f54848';
		}

		return [
			'text' => strtoupper($char),
			'background' => $background,
			'color' => $color
		];
	}

	public static function get_username($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return null;
		}

		$is_banned = $user['is_ban'] != self::IS_NOT_BAN;
		$name = _echo($user['username']);
		if($is_banned) {
			$user['role_color'] = '#000';
		}
		return ($is_banned ? '<span class="user-banned">'.$name.'</span>' : $name);
	} 


	public static function get_display_name($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return null;
		}
		$is_banned = $user['is_ban'] != self::IS_NOT_BAN;
		$name = $user['name'] != "" ? _echo($user['name']) : _echo($user['username']);
		if($is_banned) {
			$user['role_color'] = '#000';
		}
		return '<span style="color: '.$user['role_color'].'">'.($is_banned ? '<span class="user-banned">'.$name.'</span>' : $name).'</span>';
	}

	public static function get_role($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return '<span class="user-role" style="background: #e2e3e8;color: #000;">Unknown</span>';
		}
		
		return $user['is_ban'] != self::IS_NOT_BAN ? '<span class="user-role" style="background: #f6f7f9;color: #111;">Banned</span>' : '<span class="user-role" style="background: '.$user['role_color'].'">'._echo($user['role_name']).'</span>';
	} 

	public static function get_sex($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));

		switch($user['sex'])
		{
			case self::SEX_MALE:
				return lang('system', 'sex_male');
			case self::SEX_FEMALE:
				return lang('system', 'sex_female');
			default:
				return lang('system', 'sex_unknown');
		}
	} 

	public static function get_avatar($user_id = null) 
	{
		return self::get_path_avatar_or_cover('avatar', $user_id);
	}

	public static function get_cover($user_id = null) 
	{
		return self::get_path_avatar_or_cover('cover', $user_id);
	}

	private static function get_path_avatar_or_cover($mode = 'avatar', $user_id = null)
	{

		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
	
		$no_image = null;
	
		if(!isset($user['avatar']))
		{
			return $no_image;
		}
	
		preg_match("/^(URL|PATH)=(.*)$/si", trim($user[$mode]), $get);
	
		$type = isset($get[1]) ? $get[1] : null;
		$image = isset($get[2]) ? $get[2] : null;
	
		if(is_null($type) || is_null($image))
		{
			return $no_image;
		}
	
		if(strtolower($type) === "path")
		{
			return APP_URL.'/storage/users/'.$mode.'/'.$image;
		}
		
		return $image;
	}

	public static function count_limit_device($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		return Auth::$limit_device == true && $user['limit_device'] != Auth::UNLIMITED_DEVICE ? max(1, $user['limit_device']).' device' : 'Unlimited';
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