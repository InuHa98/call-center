<?php


class Payment extends Model {

	protected static $table = 'core_payments';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> ON <{table}.user_id> = <core_users.id>',
		'LEFT JOIN <core_teams> ON <{table}.team_id> = <core_teams.id>',
		'LEFT JOIN <core_country> ON <core_teams.country_id> = <core_country.id>',
		'LEFT JOIN <core_currencys> ON <core_country.currency_id> = <core_currencys.id>'
	];
	protected static $default_selects = [
		'*',
		'<core_currencys.name> AS <currency>',
		'<core_currencys.exchange_rate> AS <exchange_rate>',
		'ROUND(1 / <exchange_rate>, 2) AS <to_default_currency>',
		'<core_users.username> AS <user_username>',
		'<core_users.avatar> AS <user_avatar>',
		'<core_users.is_ban> AS <user_is_ban>',
		'<core_teams.type> AS <team_type>',
		'<core_teams.name> AS <team_name>'
	];
	protected static $order_by = [
		'status' => 'ASC',
		'created_at' => 'DESC',
		'id' => 'DESC'
	];


	const STATUS_PAID = 1;
	const STATUS_NOT_PAID = 0;

	public static function create($date, $team, $list_members)
	{
		if(!$date || !$team || !$list_members) {
			return false;
		}

		$total_amount = 0;
		$total_leader_profit = 0;
		$leader_deduct = 0;

		foreach($list_members as $member) {
			$amount = $member['earning'] - $member['deduct'];

			if($member['id'] == $team['leader_id']) {
				$leader_deduct = $amount < 0 ? abs($amount) : 0;
			}

			if($amount < 0) {
				$amount = 0;
			}

			$member_amount = $member['earning_member'] - $member['deduct_member'];
			if($member_amount < 0) {
				$member_amount = 0;
			}

			$total_leader_profit += $amount - $member_amount;
			$total_amount += $amount;
		}

		if($leader_deduct > 0) {
			$leader_deduct = $leader_deduct >= $total_leader_profit ? $total_leader_profit : $total_leader_profit - ($total_leader_profit - $leader_deduct);
		}
		
		$total_amount = $total_amount - $leader_deduct; 

		if(parent::insert([
			'status' => self::STATUS_NOT_PAID,
			'user_id' => Auth::$data['id'],
			'team_id' => $team['id'],
			'country_id' => $team['country_id'],
			'leader_profit' => $total_leader_profit,
			'leader_deduct' => $leader_deduct,
			'amount' => $total_amount,
			'created_at' => $date
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