<?php 

class paymentController {
    const BLOCK_DETAIL = 'Detail';
    const BLOCK_HISTORY = 'History';
    const BLOCK_CREATE = 'Create';

	const INPUT_TEAM = 'team';
	const INPUT_ACTION = 'action';
	const INPUT_DATE = 'date';
	const ACTION_CREATE = 'create_invoice';

    public static function index($block, $action = null)
    {
		Language::load('payment.lng');
		switch($block) {

			case self::BLOCK_HISTORY:
				return self::history($action);

			case self::BLOCK_DETAIL:
				return self::detail($action);

			case self::BLOCK_CREATE:
				return self::create($action);
			
			default:
				return self::list($action);
		}
    }


	private static function list($action = null) {
        $success = null;
        $error = null;
		$title = lang('system', 'payment');

		if(!UserPermission::access_payment_team()) {
			return ServerErrorHandler::error_403();
		}

		$filter_type = Request::get(InterFaceRequest::TYPE, InterFaceRequest::OPTION_ALL);
		$filter_status = Request::get(InterFaceRequest::STATUS, InterFaceRequest::OPTION_ALL);
		$filter_time = trim(Request::get(InterFaceRequest::TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));

		switch($filter_type) {
			case Team::TYPE_ONLY_CALL:
			case Team::TYPE_ONLY_ADS:
			case Team::TYPE_ONLY_SHIP:
			case Team::TYPE_FULLSTACK:
				$filter_type = intval($filter_type);
				break;
			default:
				$filter_type = InterFaceRequest::OPTION_ALL;
				break;
		}

		switch($filter_status) {
			case Payment::STATUS_PAID:
			case Payment::STATUS_NOT_PAID:
				$filter_status = intval($filter_status);
				break;
			default:
				$filter_status = InterFaceRequest::OPTION_ALL;
				break;
		}

		$where = [];

		if($filter_type != InterFaceRequest::OPTION_ALL) {
			$where['core_teams.type'] = $filter_type;
		}

		if($filter_status != InterFaceRequest::OPTION_ALL) {
			$where['status'] = $filter_status;
		}

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00'),
				'endDate' => strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59')
			];
		}

		$count = Payment::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$list_payment = Payment::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));



		return View::render('payment.list', compact(
			'title',
			'success',
			'error',
			'filter_type',
			'filter_status',
			'filter_time',
			'startDate',
			'endDate',
            'count',
            'list_payment',
			'pagination'
		));
	}

	private static function create($action = null) {
        $success = null;
        $error = null;
		$title = lang('payment', 'new');

		if(!UserPermission::access_payment_team()) {
			return ServerErrorHandler::error_403();
		}

		$invoice_date = Request::post(self::INPUT_DATE, strtotime('23:59:59', strtotime('-1 day', strtotime('today'))));
		$team_id = intval(Request::post(self::INPUT_TEAM, null));
		$_action = trim(Request::post(self::INPUT_ACTION, null));

		$join = [
			'LEFT JOIN <core_country> ON <{table}.country_id> = <core_country.id>',
			'LEFT JOIN <core_currencys> ON <core_country.currency_id> = <core_currencys.id>'
		];
		$select = [
			'<{table}.id>',
			'<{table}.name>',
			'<{table}.type>',
			'<{table}.leader_id>',
			'<{table}.country_id>',
			'<core_currencys.name> AS <currency>',
			'<core_currencys.exchange_rate> AS <exchange_rate>',
			'ROUND(1 / <exchange_rate>, 2) AS <to_default_currency>',
			'(SELECT <created_at> FROM <core_payments> WHERE <team_id> = <{table}.id> ORDER BY <created_at> DESC LIMIT 1) AS <last_pay>',
			'(SELECT SUM(CASE WHEN <ads_team_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.' THEN <profit_leader_ads> ELSE 0 END + CASE WHEN <call_team_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.' THEN <quantity> * <profit_leader_call> ELSE 0 END + CASE WHEN <ship_team_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.' THEN <profit_leader_ship> ELSE 0 END) AS <amount> FROM <core_orders> WHERE <status> = '.Order::STATUS_DELIVERED.' AND <country_id> = <{table}.country_id> AND ((<ads_team_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') OR (<call_team_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.') OR (<ship_team_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.')) AND <created_at> <= '.$invoice_date.') AS <earning>',
			'IFNULL((SELECT SUM(<amount_deduct>) AS <deduct> FROM <core_users> WHERE <team_id> = <{table}.id>), 0) AS <amount_deduct>',
			'(IFNULL((SELECT SUM(<amount_deduct>) AS <deduct> FROM <core_users> WHERE <team_id> = <{table}.id>), 0) + IFNULL((SELECT SUM(CASE WHEN <ads_team_id> = <{table}.id> AND <payment_ads> = '.Order::IS_PAYMENT.' THEN <deduct_leader_ads> ELSE 0 END + CASE WHEN <call_team_id> = <{table}.id> AND <payment_call> = '.Order::IS_PAYMENT.' THEN <quantity> * <deduct_leader_call> ELSE 0 END + CASE WHEN <ship_team_id> = <{table}.id> AND <payment_ship> = '.Order::IS_PAYMENT.' THEN <deduct_leader_ship> ELSE 0 END) AS <amount> FROM <core_orders> WHERE <status> = '.Order::STATUS_RETURNED.' AND <country_id> = <{table}.country_id> AND ((<ads_team_id> = <{table}.id> AND <payment_ads> = '.Order::IS_PAYMENT.') OR (<call_team_id> = <{table}.id> AND <payment_call> = '.Order::IS_PAYMENT.') OR (<ship_team_id> = <{table}.id> AND <payment_ship> = '.Order::IS_PAYMENT.'))  AND <created_at> <= '.$invoice_date.'), 0)) AS <deduct>'
		];

		$where = [];


		if(date('d-m-Y', $invoice_date) != date('d-m-Y', strtotime('-1 day', strtotime('today')))) {
			$invoice_date = strtotime('23:59:59', strtotime('-1 day', strtotime('today')));
		}

		$where['[RAW] <{table}.created_at> <= :invoide_date'] = [
			'invoide_date' => $invoice_date
		];

		$list_team = Team::join($join)::select($select)::list($where);

		$check_payment = false;
		$team = null;
		$list_members = [];
		$leader = null;
		$pay_to = null;
		$currency = null;

		if(Security::validate() == true) {
			$team = Team::join($join)::select($select)::get([
				'id' => $team_id
			]);

			if($team) {
				$currency = _echo($team['currency']);
				$leader = User::get(['id' => $team['leader_id'], 'team_id' => $team['id']]);
				$pay_to = User::get_billing($leader);

				$list_members = User::select([
					'(SELECT SUM(CASE WHEN <ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.' THEN <profit_leader_ads> ELSE 0 END + CASE WHEN <call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.' THEN <quantity> * <profit_leader_call> ELSE 0 END + CASE WHEN <ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.' THEN <profit_leader_ship> ELSE 0 END) AS <amount> FROM <core_orders> WHERE <status> = '.Order::STATUS_DELIVERED.' AND <country_id> = '.$team['country_id'].' AND ((<ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') OR (<call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.') OR (<ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.')) AND <created_at> <= '.$invoice_date.') AS <earning>',
					'(SELECT SUM(CASE WHEN <ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.' THEN <profit_member_ads> ELSE 0 END + CASE WHEN <call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.' THEN <quantity> * <profit_member_call> ELSE 0 END + CASE WHEN <ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.' THEN <profit_member_ship> ELSE 0 END) AS <amount> FROM <core_orders> WHERE <status> = '.Order::STATUS_DELIVERED.' AND <country_id> = '.$team['country_id'].' AND ((<ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') OR (<call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.') OR (<ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.')) AND <created_at> <= '.$invoice_date.') AS <earning_member>',
					'(<amount_deduct> + IFNULL((SELECT SUM(CASE WHEN <ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_PAYMENT.' THEN <deduct_leader_ads> ELSE 0 END + CASE WHEN <call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_PAYMENT.' THEN <quantity> * <deduct_leader_call> ELSE 0 END + CASE WHEN <ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_PAYMENT.' THEN <deduct_leader_ship> ELSE 0 END) AS <amount> FROM <core_orders> WHERE <status> = '.Order::STATUS_RETURNED.' AND <country_id> = '.$team['country_id'].' AND ((<ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_PAYMENT.') OR (<call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_PAYMENT.') OR (<ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_PAYMENT.')) AND <created_at> <= '.$invoice_date.'), 0)) AS <deduct>',
					'(<amount_deduct> + IFNULL((SELECT SUM(CASE WHEN <ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_PAYMENT.' THEN <deduct_member_ads> ELSE 0 END + CASE WHEN <call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_PAYMENT.' THEN <quantity> * <deduct_member_call> ELSE 0 END + CASE WHEN <ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_PAYMENT.' THEN <deduct_member_ship> ELSE 0 END) AS <amount> FROM <core_orders> WHERE <status> = '.Order::STATUS_RETURNED.' AND <country_id> = '.$team['country_id'].' AND ((<ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_PAYMENT.') OR (<call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_PAYMENT.') OR (<ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_PAYMENT.')) AND <created_at> <= '.$invoice_date.'), 0)) AS <deduct_member>',
					'(SELECT SUM(<quantity>) AS <amount> FROM <core_orders> WHERE <status> = '.Order::STATUS_DELIVERED.' AND <country_id> = '.$team['country_id'].' AND ((<ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') OR (<call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.') OR (<ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.')) AND <created_at> <= '.$invoice_date.') AS <sales>'
				], false)::list([
					'team_id' => $team['id']
				]);

				switch($_action) {
					case self::ACTION_CREATE:
						$check_payment = true;

						if(Payment::create($invoice_date, $team, $list_members)) {
							$payment_id = Payment::$insert_id;
							$payment = Payment::get($payment_id);
							foreach($list_members as $member) {
								Invoice::create($payment, $leader['id'], $invoice_date, $member['id'], $team, $member);
							}

							if(!Invoice::has([
								'payment_id' => $payment_id,
								'status' => Invoice::STATUS_NOT_PAID
							])) {
								Payment::update($payment_id, [
									'status' => Payment::STATUS_PAID
								]);
							}

							return redirect_route('payment');
						} else {
							$error = lang('system', 'default_error');
						}

						break;

					default:

						if(!User::has([
							'id' => $team['leader_id'],
							'team_id' => $team['id']
						])) {
							$error = lang('payment', 'error_team_not_leader');
						}
						else if(Payment::has([
							'team_id' => $team['id'],
							'status' => Payment::STATUS_NOT_PAID
						])) {
							$error = lang('payment', 'error_unpaid');
						} else {
							$check_payment = true;
						}
						break;
				}
			} else {
				$error = lang('payment', 'error_team_not_found');
			}
		}

		
		$list_team = array_filter($list_team, function($team) {
			return ($team['earning'] > 0 || ($team['deduct'] > 0 && $team['amount_deduct'] != $team['deduct'])) && !Payment::has([
				'team_id' => $team['id'],
				'status' => Payment::STATUS_NOT_PAID
			]);
		});

		return View::render('payment.create', compact(
			'title',
			'success',
			'error',
			'list_team',
			'team_id',
			'team',
			'currency',
			'leader',
			'check_payment',
			'invoice_date',
			'pay_to',
			'list_members'
		));
	}

	private static function detail($payment_id) {


		if(!UserPermission::access_payment_team()) {
			return ServerErrorHandler::error_403();
		}

		$success = null;
		$error = null;
		$title = lang('payment', 'detail');

		$payment = Payment::get(['id' => $payment_id]);

		if(!$payment) {
			return redirect_route('payment');
		}

		$title .= ' - #'.$payment['id'];

		$where = [
			'payment_id' => $payment['id']
		];

		$team = Team::get(['id' => $payment['team_id']]);
		$leader = User::get(['id' => $team['leader_id']]);
		$pay_to = User::get_billing($leader);
		$list_invoices = Invoice::list($where);
		$currency = _echo($team['currency']);

		return View::render('payment.detail', compact(
			'success',
			'error',
			'title',
			'payment',
			'team',
			'leader',
			'pay_to',
			'currency',
            'list_invoices'
		));
	}

	private static function history($action = null) {
		$title = lang('system', 'payment_history');

		$team = Team::get(['id' => Auth::$data['team_id']]);
		
		$filter_time = trim(Request::get(InterFaceRequest::TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));

		$where = [
			'user_id' => Auth::$data['id'],
			'team_id' => Auth::$data['team_id']
		];

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00'),
				'endDate' => strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59')
			];
		}

		$total = Invoice::select([
			'SUM(<earning>) AS <earning>',
			'SUM(<deduct>) AS <deduct>',
			'SUM(CASE WHEN <status> = '.Invoice::STATUS_PAID.' THEN <amount> ELSE 0 END) AS <paid>',
			'SUM(CASE WHEN <status> = '.Invoice::STATUS_NOT_PAID.' THEN <amount> ELSE 0 END) AS <unpaid>',
		])::get($where);

		if($total['paid'] < 0) {
			$total['paid'] = 0;
		}
		$count = Invoice::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$list_invoice = Invoice::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		

		$currency = isset($team['currency']) ? _echo($team['currency']) : null;
		return View::render('payment.history', compact(
			'title',
			'total',
			'currency',
			'filter_time',
			'startDate',
			'endDate',
            'count',
            'list_invoice',
			'pagination'
		));
	}
}

?>