<?php


class Country extends Model {

	protected static $table = 'core_country';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [
		'LEFT JOIN <core_currencys> ON <{table}.currency_id> = <core_currencys.id>'
	];
	protected static $default_selects = [
		'*',
		'<core_currencys.name> AS <currency>',
		'<core_currencys.exchange_rate> AS <exchange_rate>',
		'ROUND(1 / <exchange_rate>, 2) AS <to_default_currency>',
		'(SELECT count(<id>) FROM <core_provinces> WHERE <country_id> = <{table}.id>) AS <total_provinces>',
		'(SELECT count(<id>) FROM <core_districts> WHERE <country_id> = <{table}.id>) AS <total_districts>',
		'(SELECT count(<id>) FROM <core_wards> WHERE <country_id> = <{table}.id>) AS <total_wards>'
	];
	protected static $order_by = [
		'name' => 'ASC'
	];


	public static function create($name, $code, $currency_id, $phone_code)
	{
		if (!$name || !$code || !$currency_id)
		{
			return false;
		}

		if(self::insert([
			'name' => $name,
			'code' => strtoupper($code),
			'currency_id' => $currency_id,
			'phone_code' => $phone_code
		]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function import_json_country($data_json, $country_id) {
		
		$list = json_decode($data_json, true);

		foreach($list as $province => $data_province) {
			$province = ucwords(strtolower($province));
			Provinces::create($country_id, $province, null);
			$id_province = Provinces::$insert_id;

			foreach($data_province as $district => $data_district) {
				$district = ucwords(strtolower($district));
				Districts::create($country_id, $id_province, $district);
				$district_id = Districts::$insert_id;
				foreach($data_district as $ward) {
					$ward = ucwords(strtolower($ward));
					Wards::create($country_id, $id_province, $district_id, $ward);
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