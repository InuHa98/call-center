<?php


class Wards extends Model {

	protected static $table = 'core_wards';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [];
	protected static $default_selects = [
		'*',
		'(SELECT <name> FROM <core_country> WHERE <id> = <{table}.country_id>) AS <country_name>',
		'(SELECT <name> FROM <core_provinces> WHERE <id> = <{table}.province_id>) AS <province_name>',
		'(SELECT <name> FROM <core_districts> WHERE <id> = <{table}.district_id>) AS <district_name>'
	];
	protected static $order_by = [
		'name' => 'ASC'
	];


	public static function create($country_id, $province_id, $district_id, $name)
	{
		if (!$country_id || !$province_id || !$district_id || !$name)
		{
			return false;
		}

		if(self::insert([
			'country_id' => $country_id,
			'province_id' => $province_id,
			'district_id' => $district_id,
			'name' => $name
		]) > 0)
		{
			return true;
		}
		return false;
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