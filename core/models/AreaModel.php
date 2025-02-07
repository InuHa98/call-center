<?php


class Area extends Model {

	protected static $table = 'core_areas';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [
		'LEFT JOIN <core_country> ON <{table}.country_id> = <core_country.id>'
	];
	protected static $default_selects = [
		'*',
		'<core_country.name> AS <country_name>'
	];
	protected static $order_by = [
		'core_country.name' => 'ASC',
		'name' => 'ASC'
	];


	public static function create($name, $country_id)
	{
		if (!$name || !$country_id)
		{
			return false;
		}

		if(self::insert([
			'name' => $name,
			'country_id' => $country_id
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