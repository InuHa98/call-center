<?php


class Blacklist extends Model {

	protected static $table = 'core_blacklists';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> ON <{table}.user_id> = <core_users.id>',
	];
	protected static $default_selects = [
		'*',
		'<core_users.username> AS <user_username>',
		'<core_users.avatar> AS <user_avatar>',
		'<core_users.is_ban> AS <user_is_ban>',
		'(SELECT <name> FROM <core_country> WHERE <id> = <{table}.country_id>) AS <country_name>'
	];
	protected static $order_by = [
		'created_at' => 'DESC'
	];


	public static function create($number_phone, $country_id, $reason = null)
	{
		if(!$number_phone || !$country_id) {
			return false;
		}

		if(parent::insert([
			'country_id' => $country_id,
			'user_id' => Auth::$data['id'],
			'number_phone' => $number_phone,
			'reason' => $reason
		]) > 0)
		{
			return true;
		}

		return false;
	}

	public static function is_ban($number_phone, $country_id) {
		$where = [
			'country_id' => $country_id,
			'OR' => []
		];

		if(!is_array($number_phone)) {
			$number_phone = [$number_phone];
		}
		foreach($number_phone as $phone) {
			$where['OR']['number_phone[~]'] = '%"'.$phone.'"%';
		}

		return Blacklist::has($where);
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