<?php


class Role extends Model {

	protected static $table = 'core_roles';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'is_default' => 'DESC',
		'level' => 'ASC'
	];

	protected static $items = null;


    public const DEFAULT_ROLE_ADMIN = 1;
    public const DEFAULT_ROLE_MEMBER = 2;
    public const DEFAULT_ROLE_CALLER = 3;
    public const DEFAULT_ROLE_SHIPPER = 4;
    public const DEFAULT_ROLE_ADVERTISER = 5;
    public const DEFAULT_ROLE_FULLSTACK = 6;

	public const DEFAULT_NAME_ADMIN = 'Administrator';
	public const DEFAULT_NAME_MEMBER = 'Member';
	public const DEFAULT_NAME_CALLER = 'Caller';
	public const DEFAULT_NAME_SHIPPER = 'Shipper';
	public const DEFAULT_NAME_ADVERTISER = 'Advertiser';
	public const DEFAULT_NAME_FULLSTACK = 'Fullstack';


	public const DEFAULT_COLOR_ADMIN = '#ff0066';
	public const DEFAULT_COLOR_MEMBER = '#474747';
	public const DEFAULT_COLOR_CALLER = '#a7bd00';
	public const DEFAULT_COLOR_SHIPPER = '#ffae00';
	public const DEFAULT_COLOR_ADVERTISER = '#00bfff';
	public const DEFAULT_COLOR_FULLSTACK = '#009688';

    public const MIN_LEVEL = 0;
    public const MAX_LEVEL = 100;

	public const IS_DEFAULT = 1;


	public static function create($data = [])
	{
		if (!isset($data['name']) && $data['name'] == "")
		{
			return false;
		}


		$data = array_merge([
			"name" => '',
			"perms" => "[]",
			"color" => "#000000",
			"level" => self::MAX_LEVEL,
			"is_default" => 0
		], $data);

		if(self::insert($data) > 0)
		{
			return true;
		}
		return false;
	}


	public static function setup_default_role()
	{

		if(!self::has(['id' => self::DEFAULT_ROLE_ADMIN]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_ADMIN,
				"name" => self::DEFAULT_NAME_ADMIN,
				"perms" => [
					UserPermission::ALL_PERMS
				],
				"color" => self::DEFAULT_COLOR_ADMIN,
				"level" => self::MIN_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
		}

		if(!self::has(['id' => self::DEFAULT_ROLE_MEMBER]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_MEMBER,
				"name" => self::DEFAULT_NAME_MEMBER,
				"perms" => UserPermission::member_default(),
				"color" => self::DEFAULT_COLOR_MEMBER,
				"level" => self::MAX_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
		}

		if(!self::has(['id' => self::DEFAULT_ROLE_CALLER]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_CALLER,
				"name" => self::DEFAULT_NAME_CALLER,
				"perms" => UserPermission::caller_default(),
				"color" => self::DEFAULT_COLOR_CALLER,
				"level" => self::MAX_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
		}

		if(!self::has(['id' => self::DEFAULT_ROLE_SHIPPER]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_SHIPPER,
				"name" => self::DEFAULT_NAME_SHIPPER,
				"perms" => UserPermission::shipper_default(),
				"color" => self::DEFAULT_COLOR_SHIPPER,
				"level" => self::MAX_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
		}

		if(!self::has(['id' => self::DEFAULT_ROLE_ADVERTISER]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_ADVERTISER,
				"name" => self::DEFAULT_NAME_ADVERTISER,
				"perms" => UserPermission::advertiser_default(),
				"color" => self::DEFAULT_COLOR_ADVERTISER,
				"level" => self::MAX_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
		}

		if(!self::has(['id' => self::DEFAULT_ROLE_FULLSTACK]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_FULLSTACK,
				"name" => self::DEFAULT_NAME_FULLSTACK,
				"perms" => UserPermission::fullstack_default(),
				"color" => self::DEFAULT_COLOR_FULLSTACK,
				"level" => self::MAX_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
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
		self::$items = self::select([self::$primary_key])::list($where);
	}

	protected static function onSuccessDelete($count_items = 0)
	{
		self::setup_default_role();

		User::update([
			'role_id' => array_column(self::$items, self::$primary_key)],
			[
				'role_id' => self::DEFAULT_ROLE_MEMBER
			]
		);
	}

	protected static function onErrorDelete()
	{

	}
}





?>