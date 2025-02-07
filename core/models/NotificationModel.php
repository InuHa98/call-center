<?php


class Notification extends Model {

	protected static $table = 'core_notification';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> AS <core_user> ON <{table}.from_user_id> = <core_user.id>'
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<core_user.id> AS <user_from_id>',
		'<core_user.name> AS <user_from_name>',
		'<core_user.username> AS <user_from_username>',
		'<core_user.avatar> AS <user_from_avatar>',
		'<core_user.is_ban> AS <user_from_is_ban>',
		'(SELECT <color> FROM <core_roles> WHERE <id> = <core_user.role_id>) AS <user_from_role_color>'
	];
	protected static $order_by = [
		'created_at' => 'DESC',
		'seen' => 'ASC'
	];

	protected static $items = null;


	public const LIMIT_NEW_ITEM = 10;
	
	public const SEEN = 1;
	public const UNSEEN = 0;



	public static function create($data = [])
	{
		if(!$data['user_id'] || !$data['from_user_id'] || !$data['type'])
		{
			return false;
		}

		$data = array_merge([
			'seen' => self::UNSEEN,
			'data' => []
		], $data);

		if(self::insert($data) > 0)
		{
			return true;
		}
		return false;
	}

	public static function count_new()
	{
		return parent::count([
			'user_id' => Auth::$id,
			'seen' => self::UNSEEN
		]);
	}

	public static function make_seen($ids = [])
	{
		$where = [
			'user_id' => Auth::$id
		];

		if($ids)
		{
			$where['id'] = $ids;
		}

		if(parent::update($where, ['seen' => self::SEEN]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function make_unseen($ids = [])
	{
		$where = [
			'user_id' => Auth::$id
		];

		if($ids)
		{
			$where['id'] = $ids;
		}

		if(parent::update($where, ['seen' => self::UNSEEN]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function delete($ids = [])
	{
		$where = [
			'user_id' => Auth::$id
		];

		if($ids)
		{
			$where['id'] = $ids;
		}

		if(parent::delete($where) > 0)
		{
			return true;
		}
		return false;
	}

	public static function get_list_new($limit = null)
	{
		$where = [
			'user_id' => Auth::$id,
			'seen' => self::UNSEEN
		];


		$where['LIMIT'] = is_numeric($limit) ? $limit : self::LIMIT_NEW_ITEM;

		return parent::list($where);
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