<?php


class Product extends Model {

	protected static $table = 'core_products';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [
		'LEFT JOIN <core_country> ON <{table}.country_id> = <core_country.id>',
		'LEFT JOIN <core_currencys> ON <core_country.currency_id> = <core_currencys.id>'
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<core_country.name> AS <country_name>',
		'<core_currencys.name> AS <currency>',
		'ROUND(1 / <core_currencys.exchange_rate>, 2) AS <to_default_currency>'
	];
	protected static $order_by = [
		'name' => 'ASC'
	];

	public const STATUS_ACTIVE = 1;
	public const STATUS_INACTIVE = 0;

	public const FOLDER_IMAGE = 'products';
	public const NO_IMAGE = 'no-preview.jpeg';

    public static $allow_image_extensions = [
		'image/jpg',
		'image/jpeg',
		'image/png'
	];

	public static function create($name, $image, $desc, $country_id, $price, $stock, $ads_cost, $delivery_cost, $import_cost)
	{
		if (!$name || !$image || !$desc || !$country_id)
		{
			return false;
		}

		if(self::insert([
			'name' => $name,
			'image' => $image,
			'desc' => $desc,
			'status' => self::STATUS_INACTIVE,
			'country_id' => $country_id,
			'price' => $price,
			'stock' => $stock,
			'ads_cost' => $ads_cost,
			'delivery_cost' => $delivery_cost,
			'import_cost' => $import_cost
		]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function upload_image($file) {
		$error = false;
		$path = null;
		$tmp_name = isset($file['tmp_name']) ? $file['tmp_name'] : null;

		if($tmp_name) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime_type = finfo_file($finfo, $tmp_name);
			finfo_close($finfo);
			
			if(!in_array($mime_type, self::$allow_image_extensions))
			{
				$error = lang('system', 'error_invalid_image_format');
			}
			else
			{

				if(!file_exists(STORAGE_PATH.'/'.self::FOLDER_IMAGE))
				{
					mkdir(STORAGE_PATH.'/'.self::FOLDER_IMAGE, 0755);
				}

				$name = time().'-'.md5($file['name']).'.'.preg_replace("/^(.*)\/(.*)$/si", "$2", $mime_type);
				$path = self::FOLDER_IMAGE.'/'.$name;
				if(move_uploaded_file($tmp_name, STORAGE_PATH.'/'.$path)) {
					$error = false;
				}
			}
		}

		return [
			'error' => $error,
			'path' => $path
		];
	}

	public static function get_image($path) {

		if(is_array($path)) {
			$path = $path['image'];
		}
	
		if(!$path || !is_file(STORAGE_PATH.'/'.$path))
		{
			return assetController::load_image(self::NO_IMAGE);
		}
		return APP_URL.'/storage/'.$path;
	}

	public static function delete_image($path) {
		$path = STORAGE_PATH.'/'.$path;
		if(is_file($path)) {
			unlink($path);
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