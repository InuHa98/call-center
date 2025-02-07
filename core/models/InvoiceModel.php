<?php


class Invoice extends Model {

	protected static $table = 'core_invoices';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> ON <{table}.user_id> = <core_users.id>'
	];
	protected static $default_selects = [
		'*',
		'<core_users.username> AS <user_username>',
		'<core_users.avatar> AS <user_avatar>',
		'<core_users.is_ban> AS <user_is_ban>',
		'<core_users.billing> AS <user_billing>'
	];
	protected static $order_by = [
		'status' => 'ASC',
		'id' => 'DESC',
		'created_at' => 'DESC'
	];


	const STATUS_PAID = 1;
	const STATUS_NOT_PAID = 0;

	public static function create($payment, $leader_id, $date, $user_id, $team, $member)
	{
		if(!$payment || !$leader_id || !$date || !$user_id || !$team) {
			return false;
		}

		$amount = $member['earning_member'] - $member['deduct_member'];
		$is_leader = $leader_id == $user_id;
		$status = $is_leader || $amount < 0 ? self::STATUS_PAID : self::STATUS_NOT_PAID;

		if(parent::insert([
			'payment_id' => $payment['id'],
			'status' => $status,
			'user_id' => $user_id,
			'team_id' => $team['id'],
			'country_id' => $team['country_id'],
			'earning' => $member['earning'],
			'deduct' => $member['deduct'],
			'earning_member' => $member['earning_member'],
			'deduct_member' => $member['deduct_member'],
			'amount' => $member['earning'] - $member['deduct'],
			'created_at' => $date
		]) > 0)
		{
			Order::make_paying($date, $user_id);

			if($is_leader) {

				Notification::create([
					'user_id' => $leader_id,
					'from_user_id' => Auth::$data['id'],
					'type' => notificationController::TYPE_LEADER_INVOICE,
					'data' => [
						'team_id' => $team['id']
					]
				]);
			} else {
				Notification::create([
					'user_id' => $user_id,
					'from_user_id' => $leader_id,
					'type' => notificationController::TYPE_MEMBER_INVOICE,
					'data' => []
				]);
			}

			if($amount < 0 || $is_leader) {
				
				Order::make_payment($date, $user_id);

				if($amount < 0) {
					$amount = abs($amount);
					if($is_leader) {
						$amount = $amount - $payment['leader_deduct'];
					}
					User::update($user_id, [
						'amount_deduct' => $amount
					]);					
				}
			}

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