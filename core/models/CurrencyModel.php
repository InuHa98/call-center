<?php


class Currency extends Model {

	protected static $table = 'core_currencys';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [];
	protected static $default_selects = [
		'*',
		'ROUND(1 / <exchange_rate>, 2) AS <to_default_currency>'
	];
	protected static $order_by = [
		'name' => 'ASC'
	];

	public const DEFAULT_CURRENCY = 'VND';

	public static function create($name, $exchange_rate)
	{
		if (!$name || !$exchange_rate)
		{
			return false;
		}

		if(self::insert([
			'name' => strtoupper($name),
			'exchange_rate' => $exchange_rate
		]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function format($amount, $currency = null) {

		$split = explode('.', $amount);
		$value = isset($split[0]) ? intval($split[0]) : 0;
		$decimals = isset($split[1]) ? rtrim($split[1], 0) : 0;
		
		$result = number_format($value, 0, ",", ".");
		
		if (trim($decimals, 0) > 0) {
		  $result .= ','.rtrim($decimals);
		}
		return $result.''.$currency;
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