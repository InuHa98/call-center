<?php


class LandingStatistics extends Model {

	protected static $table = 'core_landing_statistics';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'created_at' => 'DESC'
	];


	public static function add_view($landing_id)
	{
		if(!$landing_id) {
			return false;
		}

		$landing = self::get_landing($landing_id);

		if($landing) {
			if(parent::update($landing['id'], [
				'view[+]' => 1
			]) > 0)
			{
				return true;
			}
		} else {
			$landing = LandingPage::get($landing_id);
			if($landing) {
				if(parent::insert([
					'landing_id' => $landing['id'],
					'product_id' => $landing['product_id'],
					'view' => 1,
					'conversion' => 0
				]) > 0)
				{
					return true;
				}				
			}

		}

		return false;
	}

	public static function add_conversion($landing_id)
	{
		if(!$landing_id) {
			return false;
		}

		$landing = self::get_landing($landing_id);

		if($landing) {
			if(parent::update($landing['id'], [
				'conversion[+]' => 1
			]) > 0)
			{
				return true;
			}
		} else {
			$landing = LandingPage::get($landing_id);
			if($landing) {
				if(parent::insert([
					'landing_id' => $landing['id'],
					'product_id' => $landing['product_id'],
					'view' => 1,
					'conversion' => 1
				]) > 0)
				{
					return true;
				}
			}

		}

		return false;
	}

	private static function get_landing($id) {
		return parent::get([
			'landing_id' => $id,
			'[RAW] DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'%Y-%m-%d\') = :date' => [
				'date' => date('Y-m-d')
			]
		]);
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