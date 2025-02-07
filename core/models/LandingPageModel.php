<?php


class LandingPage extends Model {

	protected static $table = 'core_landing_pages';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'domain' => 'ASC'
	];


	public static function create($domain, $postback, $product_id, $key = null)
	{
		if (!$domain || !$postback || !$product_id)
		{
			return false;
		}

		$key = $key ? $key : self::generateKey(12);

		if(self::insert([
			'product_id' => $product_id,
			'domain' => $domain,
			'postback' => $postback,
			'key' => $key
		]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function generateKey($length = 24, $explode = 6, $explode_string = "-")
	{
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++)
	    {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $explode > 0 ? implode($explode_string, str_split($randomString, $explode)) : $randomString;
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