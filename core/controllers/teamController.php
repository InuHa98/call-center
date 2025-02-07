<?php


class teamController {


	const BLOCK_MEMBER = 'Members';
	const BLOCK_DASHBOARD = 'Dashboard';
	const BLOCK_STATISTICS = 'Statistics';
	const BLOCK_ORDERS = 'Orders';
	const BLOCK_PAYMENT = 'Payment';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';
	const ACTION_DETAIL = 'Detail';
	const ACTION_BAN = 'Ban';
	const ACTION_UNBAN = 'UnBan';

	public const INPUT_ID = 'id';
	public const INPUT_STATUS = 'status';
	public const INPUT_ACTION = 'action';
	public const INPUT_PROFIT_ADS = 'profit_ads';
	public const INPUT_PROFIT_CALL = 'profit_call';
	public const INPUT_PROFIT_SHIP = 'profit_ship';
	public const INPUT_REASON = 'reason';

	public function index($id = null, $block = null, $action = null)
	{

		$team = Team::get([
			'id' => $id ? $id : Auth::$data['team_id']
		]);

		if(!$team) {
			return ServerErrorHandler::error_404();
		}

		Language::load('team.lng');

		$new_payment = Payment::count([
			'team_id' => $team['id'],
			'status' => Payment::STATUS_NOT_PAID
		]);

		$data_team = Team::get_data($team['type']);
		$leader = [
			'id' => $team['leader_id'],
			'username' => $team['leader_username'],
			'is_ban' => $team['leader_is_ban'],
			'avatar' => $team['leader_avatar']
		];

		$is_access = ($leader['id'] == Auth::$data['id'] || UserPermission::isAdmin());

		if(!$is_access) {
			return ServerErrorHandler::error_403();
		}


		$block_view = null;
		switch($block) {
			case self::BLOCK_MEMBER:
				$block_view = self::block_member($team, $data_team, $action);
				break;

			case self::BLOCK_STATISTICS:
				Language::load('statistic.lng');
				$block_view = self::block_statistics($team, $data_team, $action);
				break;

			case self::BLOCK_ORDERS:
				Language::load('order.lng');
				$block_view = self::block_orders($team, $data_team, $action);
				break;

			case self::BLOCK_PAYMENT:
				Language::load('payment.lng');
				$block_view = self::block_payment($team, $data_team, $action);
				$new_payment = 0;
				break;

			default:
				$block = self::BLOCK_DASHBOARD;
				$block_view = self::block_dashboard($team, $data_team, $action);
				break;

		}

		$title = isset($block_view['title']) ? $block_view['title'] : null;

		$products = Team::get_product($team);
		$currency = _echo($team['currency']);
		return View::render('team.index', compact(
			'title',
			'team',
			'leader',
			'data_team',
			'products',
			'block',
			'block_view',
			'currency',
			'new_payment'
		));
	}


	private static function block_member($team, $data_team, $action = null) {

		$success = null;
		$error = null;

		switch($action) {

			case self::ACTION_ADD:
				Language::load('auth');
				$username   = trim(Request::post(Auth::INPUT_USERNAME, null));
				$password   = trim(Request::post(Auth::INPUT_PASSWORD, null));
				$rePassword = trim(Request::post(Auth::INPUT_REPASSWORD, null));
				$email      = trim(Request::post(Auth::INPUT_EMAIL, null));

				$code_error = null;
		
				if(Security::validate() == true)
				{
		
					Role::setup_default_role();
		
					if(!Auth::check_length_username($username))
					{
						$code_error = 'error_username_length';
					}
					else if(!Auth::check_type_username($username))
					{
						$code_error = 'error_username_char';
					}
					else if(User::has(['username[~]' => $username]))
					{
						$code_error = 'error_username_exist';
					}
					else if(!Auth::check_length_password($password))
					{
						$code_error = 'error_password_length';
					}
					else if($password !== $rePassword)
					{
						$code_error = 'error_password_reinput';
					}
					else if(!Auth::check_type_email($email))
					{
						$code_error = 'error_email_format';
					}
					else if(User::has(['email[~]' => $email]))
					{
						$code_error = 'error_email_exist';
					}

		
		
					if($code_error != null)
					{
						switch($code_error)
						{
							case 'error_username_length':
								$error = lang('register', $code_error, [
									'min' => Auth::USERNAME_MIN_LENGTH,
									'max' => Auth::USERNAME_MAX_LENGTH
								]);
								break;
							case 'error_password_length':
								$error = lang('register', $code_error, [
									'min' => Auth::PASSWORD_MIN_LENGTH,
									'max' => Auth::PASSWORD_MAX_LENGTH
								]);
								break;
							default:
								$error = lang('register', $code_error);
								break;
						}
					}
					else
					{
						if(User::create([
							'username' => $username,
							'password' => $password,
							'email' => $email,
							'team_id' => $team['id'],
							'role_id' => $data_team['role'],
							'profit_ads' => $team['profit_ads'],
							'profit_call' => $team['profit_call'],
							'profit_ship' => $team['profit_ship'],
							'deduct_ads' => $team['deduct_ads'],
							'deduct_call' => $team['deduct_call'],
							'deduct_ship' => $team['deduct_ship']
						]) == true)
						{
							Alert::push([
								'message' => lang('register', 'success_register'),
								'type' => 'success',
								'timeout' => 3000
							]);
							redirect_route('team', ['id' => $team['id']]);
						}
						else
						{
							$error = lang('system', 'default_error');
						}
					}
				}
				
				return [
					'title' => lang('team', 'txt_add').' - '.$team['name'],
					'view' => 'team.block.member.add_member',
					'data' => compact(
						'success',
						'error',
						'team',
						'username',
						'password',
						'rePassword',
						'email'
					)
				];

			case self::ACTION_EDIT:
				$id = intval(Request::get(InterFaceRequest::ID, null));
				$user = User::get([
					'id' => $id,
					'team_id' => $team['id']
				]);

				if(!$user || $user['id'] == Auth::$data['id'] || $team['leader_id'] == $user['id']) {
					return redirect_route('team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER]);
				}

				$is_advertiser = UserPermission::is_advertisers($user);
				$is_caller = UserPermission::is_caller($user);
				$is_shipper = UserPermission::is_shipper($user);
				$is_admin = UserPermission::isAdmin();


				$profit_call = abs(floatval(Request::post(self::INPUT_PROFIT_CALL, $user['profit_call'])));
				$profit_ads = abs(floatval(Request::post(self::INPUT_PROFIT_ADS, $user['profit_ads'])));
				$profit_ship = abs(floatval(Request::post(self::INPUT_PROFIT_SHIP, $user['profit_ship'])));
				

				if(Security::validate() == true)
				{

					if($profit_ads > $team['profit_ads']) {
						$profit_ads = $team['profit_ads'];
					}
					if($profit_call > $team['profit_call']) {
						$profit_call = $team['profit_call'];
					}
					if($profit_ship > $team['profit_ship']) {
						$profit_ship = $team['profit_ship'];
					}
					$data = [];

					$data['profit_ads'] = $is_advertiser ? $profit_ads : 0;
					$data['profit_call'] = $is_caller ? $profit_call : 0;
					$data['profit_ship'] = $is_shipper ? $profit_ship : 0;


					$data['deduct_ads'] = $data['profit_ads'] < $team['deduct_ads'] ? $data['profit_ads'] : min($team['deduct_ads'], $data['profit_ads']);
					$data['deduct_call'] = $data['profit_call'] < $team['deduct_call'] ? $data['profit_call'] : min($team['deduct_call'], $data['profit_call']);
					$data['deduct_ship'] = $data['profit_ship'] < $team['deduct_ship'] ? $data['profit_ship'] : min($team['deduct_ship'], $data['profit_ship']);


					if(User::update($user['id'], $data)) {
						Alert::push([
							'type' => 'success',
							'message' => lang('system', 'success_save'),
							'timeout' => 3000
						]);
						return redirect_route('team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER]);
					} else {
						$error = lang('system', 'error_save');
					}

				}

				$country = Country::get(['id' => $team['country_id']]);

				return [
					'title' => lang('team', 'txt_edit').' - '.$user['username'],
					'view' => 'team.block.member.edit_member',
					'data' => compact(
						'success',
						'error',
						'team',
						'user',
						'country',
						'is_advertiser',
						'is_caller',
						'is_shipper',
						'is_admin',
						'profit_call',
						'profit_ads',
						'profit_ship'
					)
				];

			
			case self::ACTION_DELETE:
				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$user = User::get([
					'id' => $id,
					'team_id' => $team['id'],
					'id[!]' => Auth::$data['id']
				]);

		
				if(!$user) {
					Alert::push([
						'type' => 'error',
						'message' => lang('team', 'error_member_not_found')
					]);
				} else if($user['id'] == $team['leader_id']) {
					Alert::push([
						'type' => 'error',
						'message' => lang('team', 'error_delete_leader')
					]);
				} else {
					$earning = User::get_earnings($user);
					$holding = User::get_holdings($user);

					if($earning > 0 || $holding > 0) {
						Alert::push([
							'type' => 'error',
							'message' => lang('team', 'error_payment_exists')
						]);
					} else {
						$perms = json_decode($user['perms'], true);
						if(isset($perms['access_advertisers'])) {
							unset($perms['access_advertisers']);
						}
						if(isset($perms['access_caller'])) {
							unset($perms['access_caller']);
						}
						if(isset($perms['access_shipper'])) {
							unset($perms['access_shipper']);
						}
						if(User::update($user['id'], [
							'team_id' => 0,
							'amount_deduct' => 0,
							'deduct_ads' => 0,
							'deduct_call' => 0,
							'deduct_ship' => 0,
							'profit_ads' => 0,
							'profit_call' => 0,
							'profit_ship' => 0,
							'perms' => $perms
						])) {
							Alert::push([
								'type' => 'success',
								'message' => lang('success', 'delete_member')
							]);
						} else {
							Alert::push([
								'type' => 'error',
								'message' => lang('system', 'default_error')
							]);
						}				
					}
				
				}
				return redirect_route('team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER]);
		}

		if(Security::validate() == true)
		{
			$action_form = trim(Request::post(self::INPUT_ACTION, null));
			$id = intval(Request::post(self::INPUT_ID, null));
		
			$user = User::get([
				'id' => $id,
				'team_id' => $team['id']
			]);
			
			if($user && $user['id'] != Auth::$data['id']) {
				switch($action_form) {
					case self::ACTION_BAN:
						
						$reason = trim(Request::post(self::INPUT_REASON, null));
		
						if(User::update($user['id'], [
							'is_ban_team' => Team::IS_BAN,
							'reason_ban_team' => $reason
						])) {
							Notification::create([
								'user_id' => $user['id'],
								'from_user_id' => Auth::$data['id'],
								'type' => notificationController::TYPE_USER_BAN_TEAM,
								'data' => [
									'reason' => $reason
								]
							]);
							Alert::push([
								'type' => 'success',
								'message' => lang('success', 'ban', ['username' => _echo($user['username'])])
							]);
						} else {
							Alert::push([
								'type' => 'error',
								'message' => lang('system', 'default_error')
							]);
						}

						break;
			
					case self::ACTION_UNBAN:

						if(User::update($user['id'], [
							'is_ban_team' => Team::IS_NOT_BAN,
							'reason_ban_team' => ''
						])) {
							Notification::create([
								'user_id' => $user['id'],
								'from_user_id' => Auth::$data['id'],
								'type' => notificationController::TYPE_USER_UNBAN_TEAM,
								'data' => []
							]);
							Alert::push([
								'type' => 'success',
								'message' => lang('success', 'unban', ['username' => _echo($user['username'])])
							]);
						} else {
							Alert::push([
								'type' => 'error',
								'message' => lang('system', 'default_error')
							]);
						}

						break;
				}
			} else {
				Alert::push([
					'type' => 'error',
					'message' => lang('team', 'error_member_not_found')
				]);
			}

		}


		$where = [
			'team_id' => $team['id']
		];

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$status = trim(Request::get(InterFaceRequest::STATUS, InterFaceRequest::OPTION_ALL));

		switch($status) {
			case Team::IS_BAN:
			case Team::IS_NOT_BAN:
				break;
			default:
				$status = InterFaceRequest::OPTION_ALL;
				break;
		}

		if($keyword !== '') {
			$where['username[~]'] = '%'.$keyword.'%';
		}

		if($status != InterFaceRequest::OPTION_ALL) {
			$where['is_ban_team'] = $status;
		}

		if($team['leader_id']) {
			$where['ORDER'] = [
				'id' => [$team['leader_id']],
				'name' => 'ASC'
			];
		}

		$select = [];


		switch($team['type']) {
			case Team::TYPE_ONLY_ADS:
				$select[] = '(SELECT count(<id>) FROM <core_orders> WHERE <core_orders.country_id> = <country_id> AND <ads_team_id> = <{table}.team_id> AND <ads_user_id> = <{table}.id>) AS <total_orders>';
				$select[] = '(SELECT COUNT(<id>) FROM <core_orders> WHERE <status> IN('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERED.','.Order::STATUS_DELIVERING.','.Order::STATUS_DELIVERY_DATE.') AND <core_orders.country_id> = <country_id> AND <ads_team_id> = <{table}.team_id> AND (<ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.')) AS <total_unpaid_orders>';
				break;

			case Team::TYPE_ONLY_CALL:
				$select[] = '(SELECT count(<id>) FROM <core_orders> WHERE <core_orders.country_id> = <country_id> AND <call_team_id> = <{table}.team_id> AND <call_user_id> = <{table}.id>) AS <total_orders>';
				$select[] = '(SELECT COUNT(<id>) FROM <core_orders> WHERE <status> IN('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERED.','.Order::STATUS_DELIVERING.','.Order::STATUS_DELIVERY_DATE.') AND <core_orders.country_id> = <country_id> AND <call_team_id> = <{table}.team_id> AND (<call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.')) AS <total_unpaid_orders>';
				break;

			case Team::TYPE_ONLY_SHIP:
				$select[] = '(SELECT count(<id>) FROM <core_orders> WHERE <core_orders.country_id> = <country_id> AND <ship_team_id> = <{table}.team_id> AND <ship_user_id> = <{table}.id>) AS <total_orders>';
				$select[] = '(SELECT COUNT(<id>) FROM <core_orders> WHERE <status> IN('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERED.','.Order::STATUS_DELIVERING.','.Order::STATUS_DELIVERY_DATE.') AND <core_orders.country_id> = <country_id> AND <ship_team_id> = <{table}.team_id> AND (<ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.')) AS <total_unpaid_orders>';
				break;

			case Team::TYPE_FULLSTACK:
				$select[] = '(SELECT count(<id>) FROM <core_orders> WHERE <core_orders.country_id> = <country_id> AND ((<ads_team_id> = <{table}.team_id> AND <ads_user_id> = <{table}.id>) OR (<call_team_id> = <{table}.team_id> AND <call_user_id> = <{table}.id>) OR (<ship_team_id> = <{table}.team_id> AND <ship_user_id> = <{table}.id>))) AS <total_orders>';
				$select[] = '(SELECT COUNT(<id>) FROM <core_orders> WHERE <status> IN('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERED.','.Order::STATUS_DELIVERING.','.Order::STATUS_DELIVERY_DATE.') AND <core_orders.country_id> = <country_id> AND (<ads_team_id> = <{table}.team_id> OR <call_team_id> = <{table}.team_id> OR <ship_team_id> = <{table}.team_id>) AND ((<ads_user_id> = <{table}.id> AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') OR (<call_user_id> = <{table}.id> AND <payment_call> = '.Order::IS_NOT_PAYMENT.') OR (<ship_user_id> = <{table}.id> AND <payment_ship> = '.Order::IS_NOT_PAYMENT.'))) AS <total_unpaid_orders>';
				break;
		}


		$count = User::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$list_user = User::select($select, false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		return [
			'title' => lang('team', 'txt_member').' - '.$team['name'],
			'view' => 'team.block.member.list',
			'data' => compact(
				'success',
				'error',
				'count',
				'team',
				'keyword',
				'status',
				'data_team',
				'list_user',
				'pagination'
			)
		];
	}

	private static function block_payment($team, $data_team, $action = null) {
		switch($action) {
			case self::ACTION_DETAIL:
				$title = lang('payment', 'detail');
		
				$payment_id = intval(Request::get(InterFaceRequest::ID, null));
				$payment = Payment::get([
					'id' => $payment_id,
					'team_id' => $team['id'],
					'country_id' => $team['country_id']
				]);
		
				if(!$payment) {
					return redirect_route('team', ['id' => $team['id'], 'block' => self::BLOCK_PAYMENT]);
				}
		
				$title .= ' - #'.$payment['id'];
		
				if(Security::validate() == true) {
					$invoice_id = intval(Request::post(InterFaceRequest::ID, null));
					$invoice = Invoice::get([
						'id' => $invoice_id,
						'status' => Invoice::STATUS_NOT_PAID,
						'team_id' => $team['id'],
						'country_id' => $team['country_id']
					]);

					if(!$invoice) {
						Alert::push([
							'type' => 'error',
							'message' => lang('payment', 'error_invoice_not_found'),
							'timeout' => 3000
						]);
					} else {
						if(Invoice::update($invoice['id'], [
							'status' => Invoice::STATUS_PAID
						])) {

							Order::make_payment($invoice['created_at'], $invoice['user_id']);

							if(!Invoice::has([
								'payment_id' => $invoice['payment_id'],
								'status' => Invoice::STATUS_NOT_PAID
							])) {
								Payment::update($invoice['payment_id'], [
									'status' => Payment::STATUS_PAID
								]);
							}
							Alert::push([
								'type' => 'success',
								'message' => lang('success', 'mark_paid'),
								'timeout' => 3000
							]);
						} else {
							Alert::push([
								'type' => 'error',
								'message' => lang('system', 'default_error'),
								'timeout' => 3000
							]);
						}
					}
				}


				$where = [
					'payment_id' => $payment['id']
				];
		
				$list_invoices = Invoice::list($where);
				$currency = _echo($team['currency']);
		
				$is_admin = UserPermission::isAdmin();
				return [
					'title' => $title,
					'view' => 'team.block.payment.detail',
					'data' => compact(
						'is_admin',
						'payment',
						'team',
						'currency',
						'list_invoices'
					)
				];

			default:
				$title = lang('payment', 'invoice');
		
				$filter_status = Request::get(InterFaceRequest::STATUS, InterFaceRequest::OPTION_ALL);
				$filter_time = trim(Request::get(InterFaceRequest::TIME, InterFaceRequest::OPTION_ALL));
				$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
				$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));

				switch($filter_status) {
					case Payment::STATUS_PAID:
					case Payment::STATUS_NOT_PAID:
						$filter_status = intval($filter_status);
						break;
					default:
						$filter_status = InterFaceRequest::OPTION_ALL;
						break;
				}
		
				$where = [
					'team_id' => $team['id'],
					'country_id' => $team['country_id']
				];
		
		
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
		
				$is_admin = UserPermission::isAdmin();
				$currency = _echo($team['currency']);

				return [
					'title' => $title,
					'view' => 'team.block.payment.list',
					'data' => compact(
						'title',
						'filter_status',
						'filter_time',
						'startDate',
						'endDate',
						'is_admin',
						'currency',
						'count',
						'list_payment',
						'pagination'
					)
				];
		}
	}

	private static function block_statistics($team, $data_team, $action = null) {
		
		$is_caller = false;
		$is_shipper = false;
		$is_advertiser = false;	

		$currency = null;
		if($team) {
			$currency = _echo($team['currency']);
		}

		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

		$filter_type = trim(Request::get(orderController::FILTER_TYPE, null));
		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_view = trim(Request::get(orderController::FILTER_VIEW, orderController::VIEW_DAY));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));
		$startDate_convert = implode('-', array_reverse(explode('-', $startDate))).' 00:00:00';
		$endDate_convert = implode('-', array_reverse(explode('-', $endDate))).' 23:59:59';


		$where = [
			'country_id' => $team['country_id']
		];




		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where['product_id'] = $filter_product;
		}

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => strtotime($startDate_convert),
				'endDate' => strtotime($endDate_convert)
			];
		}

		$join_range = [];
		$select_range = [];
		$group_by = null;
		$order_Type = 'ASC';

		switch($team['type']) {
			case Team::TYPE_ONLY_ADS:
				$is_advertiser = true;
				$filter_type = orderController::TYPE_ADS;
				break;

			case Team::TYPE_ONLY_CALL:
				$is_caller = true;
				$filter_type = orderController::TYPE_CALL;

				break;

			case Team::TYPE_ONLY_SHIP:
				$is_shipper = true;
				$filter_type = orderController::TYPE_SHIP;
				break;

			case Team::TYPE_FULLSTACK:
				$is_caller = true;
				$is_shipper = true;
				$is_advertiser = true;

				$perms = json_decode($team['perms'], true);
				foreach ($perms as $key => $value)
				{
					$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
					if($value != true)
					{
						if($key == 'access_caller') {
							$is_caller = false;
						}
						else if($key == 'access_advertisers') {
							$is_advertiser = false;
						}
						else if($key == 'access_shipper') {
							$is_shipper = false;
						}
					}
				}

				if($filter_type) {
					break;
				}

				if($is_caller) {
					$filter_type = orderController::TYPE_CALL;
				} else if($is_shipper) {
					$filter_type = orderController::TYPE_SHIP;
				} else {
					$filter_type = orderController::TYPE_ADS;
				}
				break;
		}

		switch($filter_type) {
			case orderController::TYPE_ADS:
				$where['ads_team_id'] = $team['id'];
				$select_range = [
					'<ads_user_id> AS <user_id>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_DELIVERED.' AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ads>) ELSE 0 END) AS <total_earning>',
					'SUM(CASE WHEN (<status> IN ('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERY_DATE.','.Order::STATUS_DELIVERING.')  AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ads>) ELSE 0 END) AS <total_holding>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_RETURNED.' AND <payment_ads> IN ('.Order::IS_PAYMENT.')) THEN (<deduct_leader_ads>) ELSE 0 END) AS <total_deduct>'
				];
				break;

			case orderController::TYPE_CALL:
				$where['call_team_id'] = $team['id'];
				$select_range = [
					'<call_user_id> AS <user_id>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_DELIVERED.' AND <payment_call> = '.Order::IS_NOT_PAYMENT.') THEN (<quantity> * <profit_leader_call>) ELSE 0 END) AS <total_earning>',
					'SUM(CASE WHEN (<status> IN ('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERY_DATE.','.Order::STATUS_DELIVERING.')  AND <payment_call> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_call>) ELSE 0 END) AS <total_holding>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_RETURNED.' AND <payment_call> IN ('.Order::IS_PAYMENT.')) THEN (<quantity> * <deduct_leader_call>) ELSE 0 END) AS <total_deduct>'
				];
				break;

			case orderController::TYPE_SHIP:
				$where['ship_team_id'] = $team['id'];
				$select_range = [
					'<ship_user_id> AS <user_id>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_DELIVERED.' AND <payment_ship> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ship>) ELSE 0 END) AS <total_earning>',
					'SUM(CASE WHEN (<status> IN ('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERY_DATE.','.Order::STATUS_DELIVERING.')  AND <payment_ship> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ship>) ELSE 0 END) AS <total_holding>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_RETURNED.' AND <payment_ship> IN ('.Order::IS_PAYMENT.')) THEN (<deduct_leader_ship>) ELSE 0 END) AS <total_deduct>'
				];
				break;
		}



		switch($filter_view) {
			case orderController::VIEW_TIME:
				$select_range[] = 'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'%H:00 %p\') AS <order_range>';
				break;

			case orderController::VIEW_MONTH:
				$select_range[] = 'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \''.lang('system', 'month').' %m\') AS <order_range>';
				break;

			case orderController::VIEW_YEAR:
				$select_range[] = 'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'Năm %Y\') AS <order_range>';
				break;

			case orderController::VIEW_MEMBER:
				$group_by = 'user_id';
				break;

			default:
				$filter_view = orderController::VIEW_DAY;
				$select_range[] = 'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'%Y-%m-%d\') AS <order_range>';
				break;
		}


		if($filter_time == InterFaceRequest::OPTION_ALL) {
			$order_Type = 'DESC';
		}

		$where = array_merge($where, [
			'GROUP' => 'order_range',
			'ORDER' => [
				'[RAW] <order_range>' => $order_Type
			]
		]);

		if($group_by) {
			$where['GROUP'] = $group_by;
			$where['ORDER'] = [
				'[RAW] <total_order>' => $order_Type
			];
		}

		$count = Order::count([], "SELECT COUNT(*) FROM (SELECT ".implode(',', $select_range)." FROM <{table}> ".App::$database->build_raw_where(Order::build_where($where)).") AS <total>");

		$select_range[] = 'SUM(CASE WHEN <status> IN ('.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.', '.Order::STATUS_DELIVERING.') THEN <quantity> ELSE 0 END) AS <pre_sales>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERED.' THEN <quantity> ELSE 0 END) AS <sales>';
		$select_range[] = 'COUNT(<id>) AS <total_order>';
		$select_range[] = 'SUM(CASE WHEN (<status> IN ('.Order::STATUS_DELIVERING.', '.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.')) THEN 1 ELSE 0 END) AS <total_pending_delivery>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_PENDING_CONFIRM.' THEN 1 ELSE 0 END) AS <total_pending_confirm>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_CALLING.' THEN 1 ELSE 0 END) AS <total_calling>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_AGREE_BUY.' THEN 1 ELSE 0 END) AS <total_agree_buy>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_BUSY_CALLBACK.' THEN 1 ELSE 0 END) AS <total_busy_callback>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_CAN_NOT_CALL.' THEN 1 ELSE 0 END) AS <total_can_not_call>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_WRONG_NUMBER.' THEN 1 ELSE 0 END) AS <total_wrong_number>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERY_DATE.' THEN 1 ELSE 0 END) AS <total_delivery_date>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERING.' THEN 1 ELSE 0 END) AS <total_delivering>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERED.' THEN 1 ELSE 0 END) AS <total_delivered>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_UNRECEIVED.' THEN 1 ELSE 0 END) AS <total_unreceived>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_RETURNED.' THEN 1 ELSE 0 END) AS <total_returned>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_REFUSE_BUY.' THEN 1 ELSE 0 END) AS <total_refuse_buy>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_DUPLICATE.' THEN 1 ELSE 0 END) AS <total_duplicate>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_TRASH.' THEN 1 ELSE 0 END) AS <total_trash>';


		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();

		$list_statistics = Order::join($join_range)::select($select_range)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));
		
		$list_status = (array) orderController::render_status(null);
		$filter_status = isset($_COOKIE[orderController::COOKIE_FILTER_STATUS]) ? json_decode($_COOKIE[orderController::COOKIE_FILTER_STATUS], true) : array_keys($list_status);
		return [
			'title' => lang('team', 'statistic').' - '.$team['name'],
			'view' => 'team.block.statistics',
			'data' => compact(
				'team',
				'currency',
				'list_product',
				'filter_type',
				'filter_product',
				'filter_view',
				'filter_time',
				'startDate',
				'endDate',
				'is_advertiser',
				'is_caller',
				'is_shipper',
				'list_status',
				'filter_status',
				'count',
				'list_statistics',
				'pagination'
			)
		];
	}

	private static function block_dashboard($team, $data_team, $action = null) {

		$total_order = 0;
		$unpaid_order = 0;
		$earning_order = 0;
		$holding_order = 0;
		$deduct_order = 0;
		$currency = null;

		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));
		$startDate_convert = strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00');
		$endDate_convert = strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59');

		$where = [];
		$all_deduct = true;
		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where['product_id'] = $filter_product;
		}

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => $startDate_convert,
				'endDate' => $endDate_convert
			];
			$all_deduct = false;
		}


		$earning = Team::get_earnings($team, $where);
		$holding = Team::get_holdings($team, $where);
		$deduct = Team::get_deduct($team, $where, $all_deduct);

		$is_caller = false;
		$is_shipper = false;
		$is_advertiser = false;

		if($team) {
			$where['country_id'] = $team['country_id'];
			$currency = _echo($team['currency']);
			switch($team['type']) {
				case Team::TYPE_ONLY_ADS:
					$is_advertiser = true;
					$tmp_where = [
						'ads_team_id' => $team['id'],
						'payment_ads' => [
							Order::IS_NOT_PAYMENT,
							Order::IS_PAYING
						]
					];

					$where_unpaid_order = array_merge($where, $tmp_where);
					$where_earning_order = array_merge($where, $tmp_where);
					$where_holding_order = array_merge($where, $tmp_where);
					$tmp_where['payment_ads'] = [
						Order::IS_PAYMENT,
						Order::IS_PAYING
					];
					$where_deduct_order = array_merge($where, $tmp_where);
					unset($tmp_where['payment_ads']);
					$where_total_order = array_merge($where, $tmp_where);

					break;

				case Team::TYPE_ONLY_CALL:
					$is_caller = true;
					$tmp_where = [
						'call_team_id' => $team['id'],
						'payment_call' => [
							Order::IS_NOT_PAYMENT,
							Order::IS_PAYING
						]
					];

					$where_unpaid_order = array_merge($where, $tmp_where);
					$where_earning_order = array_merge($where, $tmp_where);
					$where_holding_order = array_merge($where, $tmp_where);
					$tmp_where['payment_call'] = [
						Order::IS_PAYMENT,
						Order::IS_PAYING
					];
					$where_deduct_order = array_merge($where, $tmp_where);
					unset($tmp_where['payment_call']);
					$where_total_order = array_merge($where, $tmp_where);
					break;

				case Team::TYPE_ONLY_SHIP:
					$is_shipper = true;
					$tmp_where = [
						'ship_team_id' => $team['id'],
						'payment_ship' => [
							Order::IS_NOT_PAYMENT,
							Order::IS_PAYING
						]
					];

					$where_unpaid_order = array_merge($where, $tmp_where);
					$where_earning_order = array_merge($where, $tmp_where);
					$where_holding_order = array_merge($where, $tmp_where);
					$tmp_where['payment_ship'] = [
						Order::IS_PAYMENT,
						Order::IS_PAYING
					];
					$where_deduct_order = array_merge($where, $tmp_where);
					unset($tmp_where['payment_ship']);
					$where_total_order = array_merge($where, $tmp_where);
					break;

				case Team::TYPE_FULLSTACK:
					$is_caller = true;
                    $is_shipper = true;
                    $is_advertiser = true;

                    $perms = json_decode($team['perms'], true);
                    foreach ($perms as $key => $value)
                    {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        if($value != true)
                        {
                            if($key == 'access_caller') {
                                $is_caller = false;
                            }
                            else if($key == 'access_advertisers') {
                                $is_advertiser = false;
                            }
                            else if($key == 'access_shipper') {
                                $is_shipper = false;
                            }
                        }
                    }

					$tmp_where = [
						'OR' => [
							'AND #ads' => [
								'ads_team_id' => $team['id'],
								'payment_ads' => [
									Order::IS_NOT_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #call' => [
								'call_team_id' => $team['id'],
								'payment_call' => [
									Order::IS_NOT_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #ship' => [
								'ship_team_id' => $team['id'],
								'payment_ship' => [
									Order::IS_NOT_PAYMENT,
									Order::IS_PAYING
								]
							]
						]
					];

					$where_unpaid_order = array_merge($where, $tmp_where);
					$where_earning_order = array_merge($where, $tmp_where);
					$where_holding_order = array_merge($where, $tmp_where);
					$where_deduct_order = array_merge($where, [
						'OR' => [
							'AND #ads' => [
								'ads_team_id' => $team['id'],
								'payment_ads' => [
									Order::IS_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #call' => [
								'call_team_id' => $team['id'],
								'payment_call' => [
									Order::IS_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #ship' => [
								'ship_team_id' => $team['id'],
								'payment_ship' => [
									Order::IS_PAYMENT,
									Order::IS_PAYING
								]
							]
						]
					]);
					$where_total_order = array_merge($where, [
						'OR' => [
							'AND #ads' => [
								'ads_team_id' => $team['id']
							],
							'AND #call' => [
								'call_team_id' => $team['id']
							],
							'AND #ship' => [
								'ship_team_id' => $team['id']
							]
						]
					]);
					
					break;
			}
			
			$total_order = Order::count($where_total_order);
			$where_unpaid_order = array_merge($where_unpaid_order, [
				'status' => [
					Order::STATUS_AGREE_BUY,
					Order::STATUS_DELIVERING,
					Order::STATUS_DELIVERY_DATE,
					Order::STATUS_DELIVERED
				]
			]);
			$where_earning_order = array_merge($where_earning_order, [
				'status' => [
					Order::STATUS_DELIVERED
				]
			]);
			$where_holding_order = array_merge($where_holding_order, [
				'status' => [
					Order::STATUS_AGREE_BUY,
					Order::STATUS_DELIVERING,
					Order::STATUS_DELIVERY_DATE
				]
			]);
			$where_deduct_order = array_merge($where_deduct_order, [
				'status' => [
					Order::STATUS_RETURNED
				]
			]);
			$unpaid_order = Order::count($where_unpaid_order);
			$earning_order = Order::count($where_earning_order);
			$holding_order = Order::count($where_holding_order);
			$deduct_order = Order::count($where_deduct_order);

		}

		$status_orders = Order::select([
			'SUM(CASE WHEN <status> = '.Order::STATUS_PENDING_CONFIRM.' AND (<ads_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <pending_confirm>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_AGREE_BUY.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].' OR <ship_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <agree_buy>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERY_DATE.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].' OR <ship_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <delivery_date>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERING.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].' OR <ship_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <delivering>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERED.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].' OR <ship_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <delivered>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_UNRECEIVED.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].' OR <ship_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <unreceived>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_RETURNED.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].' OR <ship_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <returned>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_BUSY_CALLBACK.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <busy_callback>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_CAN_NOT_CALL.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <can_not_call>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_REFUSE_BUY.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <refuse_buy>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_WRONG_NUMBER.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <wrong_number>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DUPLICATE.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <duplicate>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_TRASH.' AND (<ads_team_id> = '.$team['id'].' OR <call_team_id> = '.$team['id'].') THEN 1 ELSE 0 END) AS <trash>'
		])::get(array_merge($where, [
			'OR' => [
				'ads_team_id' => $team['id'],
				'call_team_id' => $team['id'],
				'ship_team_id' => $team['id']
			]
		]));

	
		if($filter_time == InterFaceRequest::OPTION_ALL) {
			$first_order = Order::get(array_merge($where_total_order, [
				'ORDER' => [
					'created_at' => 'ASC'
				],
				'LIMIT' => 1
			]));
			$last_order = Order::get(array_merge($where_total_order, [
				'ORDER' => [
					'created_at' => 'DESC'
				],
				'LIMIT' => 1
			]));

			if($first_order && $last_order) {
				$startDate_convert = date('Y-m-d H:i:s', $first_order['created_at']);
				$endDate_convert = date('Y-m-d H:i:s', $last_order['created_at']);				
			}
		}

		$start_date = new DateTime();
		$start_date->setTimestamp($startDate_convert);
		$end_date = new DateTime();
		$end_date->setTimestamp($endDate_convert);

		$interval_date = $end_date->diff($start_date);
		$intervalInDays = $interval_date->days;


		$select_range = [];

		if ($intervalInDays >= 365) {
			$dateRange = new DatePeriod($start_date, new DateInterval('P1Y'), $end_date);
			$labels = [];
			foreach ($dateRange as $date) {
				$labels[] = $date->format('Y');
			}
			$select_range = [
				'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'%Y\') AS <order_range>',
				'COUNT(<id>) AS <count_orders>'
			];
		} elseif ($intervalInDays >= 31) {
			$labels = [];
			for($i = 1; $i <= 12; $i++) {
				$month = '0'.$i;
				if(strlen($month) > 2) {
					$month = ltrim($month, 0);
				}
				$labels[] = lang('system', 'month').' '.$month;
			}
			$select_range = [
				'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \''.lang('system', 'month').' %m\') AS <order_range>',
				'COUNT(<id>) AS <count_orders>'
			];
		} elseif ($intervalInDays >= 1) {
			$dateRange = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);
			$labels = [];
			foreach ($dateRange as $date) {
				$labels[] = $date->format('d-m-Y');
			}
			$select_range = [
				'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'%d-%m-%Y\') AS <order_range>',
				'COUNT(<id>) AS <count_orders>'
			];
		} else {
			$labels = [];
			$dateRange = new DatePeriod($start_date, new DateInterval('PT1H'), $end_date);
			foreach ($dateRange as $date) {
				$labels[] = $date->format('H:i A');
			}
			$select_range = [
				'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'%H:00 %p\') AS <order_range>',
				'COUNT(<id>) AS <count_orders>'
			];
		}

		$where_range = [
			'GROUP' => 'order_range',
			'ORDER' => [
				'created_at' => 'ASC'
			]
		];

		$range_total_order = Order::join([])::select($select_range)::list(array_merge($where_total_order, $where_range));
		$range_earning_order = Order::join([])::select($select_range)::list(array_merge($where_earning_order, $where_range));
		$range_holding_order = Order::join([])::select($select_range)::list(array_merge($where_holding_order, $where_range));
		$range_deduct_order = Order::join([])::select($select_range)::list(array_merge($where_deduct_order, $where_range));

		$data_total = [];
		$data_earning = [];
		$data_holding = [];
		$data_deduct = [];

		foreach($range_total_order as $order) {
			$data_total[$order['order_range']] = $order['count_orders'];
		}
		$data_total = array_merge(array_fill_keys($labels, 0), $data_total);

		foreach($range_earning_order as $order) {
			$data_earning[$order['order_range']] = $order['count_orders'];
		}
		$data_earning = array_merge(array_fill_keys($labels, 0), $data_earning);

		foreach($range_holding_order as $order) {
			$data_holding[$order['order_range']] = $order['count_orders'];
		}
		$data_holding = array_merge(array_fill_keys($labels, 0), $data_holding);

		foreach($range_deduct_order as $order) {
			$data_deduct[$order['order_range']] = $order['count_orders'];
		}
		$data_deduct = array_merge(array_fill_keys($labels, 0), $data_deduct);


		$data_chart_statistics = [
			'labels' => $labels,
			'datasets' => [
				[
					'label' => 'Deduct',
                    'data' => $data_deduct,
                    'fill' => true,
					'borderDash' => [5],
                    'borderColor' => '#E91E63',
					'backgroundColor' => 'rgb(251 216 228 / 60%)',
					'spanGaps' => true,
					'cubicInterpolationMode' => 'monotone',
                    'tension' => 0.4
				],
				[
					'label' => 'Earning',
                    'data' => $data_earning,
                    'fill' => true,
                    'borderColor' => '#8bc34a',
					'backgroundColor' => 'rgb(223 240 216 / 60%)',
					'spanGaps' => true,
                    'tension' => 0.4
				],
				[
					'label' => 'Holding',
                    'data' => $data_holding,
                    'fill' => true,
                    'borderColor' => '#ff9800',
					'backgroundColor' => 'rgb(252 248 227 / 60%)',
					'spanGaps' => true
				],
				[
					'label' => 'Total',
                    'data' => $data_total,
                    'fill' => true,
                    'borderColor' => '#eeeeee',
					'backgroundColor' => 'rgb(238 238 238 / 60%)',
					'spanGaps' => true,
					'cubicInterpolationMode' => 'monotone',
                    'tension' => 0.1
				]
			]
		];

		$data_chart_status = [
			'labels' => [],
			'datasets' => [
				[
					'label' => lang('label', 'order'),
					'data' => [],
					'backgroundColor' => [],
					'hoverOffset' => 4					
				]
			]
		];

		if($is_advertiser) {
			$data_chart_status['labels'][] = lang('system', 'order_pending_confirm');
			$data_chart_status['datasets'][0]['data'][] = $status_orders['pending_confirm'];
			$data_chart_status['datasets'][0]['backgroundColor'][] = '#eeeeee';
		}

		$data_chart_status['labels'][] = lang('system', 'order_agree_buy');
		$data_chart_status['datasets'][0]['data'][] = $status_orders['agree_buy'];
		$data_chart_status['datasets'][0]['backgroundColor'][] = '#00bcd4';

		$data_chart_status['labels'][] = lang('system', 'order_delivery_date');
		$data_chart_status['datasets'][0]['data'][] = $status_orders['delivery_date'];
		$data_chart_status['datasets'][0]['backgroundColor'][] = '#03a9f4';

		$data_chart_status['labels'][] = lang('system', 'order_delivering');
		$data_chart_status['datasets'][0]['data'][] = $status_orders['delivering'];
		$data_chart_status['datasets'][0]['backgroundColor'][] = '#ffc107';

		$data_chart_status['labels'][] = lang('system', 'order_delivered');
		$data_chart_status['datasets'][0]['data'][] = $status_orders['delivered'];
		$data_chart_status['datasets'][0]['backgroundColor'][] = '#8bc34a';

		$data_chart_status['labels'][] = lang('system', 'order_unreceived');
		$data_chart_status['datasets'][0]['data'][] = $status_orders['unreceived'];
		$data_chart_status['datasets'][0]['backgroundColor'][] = '#ff5722';

		$data_chart_status['labels'][] = lang('system', 'order_returned');
		$data_chart_status['datasets'][0]['data'][] = $status_orders['returned'];
		$data_chart_status['datasets'][0]['backgroundColor'][] = '#e91e63';

		if($is_advertiser || $is_caller) {
			$data_chart_status['labels'][] = lang('system', 'order_busy_callback');
			$data_chart_status['datasets'][0]['data'][] = $status_orders['busy_callback'];
			$data_chart_status['datasets'][0]['backgroundColor'][] = '#8285c0';

			$data_chart_status['labels'][] = lang('system', 'order_can_not_call');
			$data_chart_status['datasets'][0]['data'][] = $status_orders['can_not_call'];
			$data_chart_status['datasets'][0]['backgroundColor'][] = '#795548';

			$data_chart_status['labels'][] = lang('system', 'order_refuse_buy');
			$data_chart_status['datasets'][0]['data'][] = $status_orders['refuse_buy'];
			$data_chart_status['datasets'][0]['backgroundColor'][] = '#333333';

			$data_chart_status['labels'][] = lang('system', 'order_wrong_number');
			$data_chart_status['datasets'][0]['data'][] = $status_orders['wrong_number'];
			$data_chart_status['datasets'][0]['backgroundColor'][] = '#c7a500';

			$data_chart_status['labels'][] = lang('system', 'order_duplicate');
			$data_chart_status['datasets'][0]['data'][] = $status_orders['duplicate'];
			$data_chart_status['datasets'][0]['backgroundColor'][] = '#e3e5ec';

			$data_chart_status['labels'][] = lang('system', 'order_trash');
			$data_chart_status['datasets'][0]['data'][] = $status_orders['trash'];
			$data_chart_status['datasets'][0]['backgroundColor'][] = '#818181';
		}

		return [
			'title' => lang('team', 'dashboard').' - '.$team['name'],
			'view' => 'dashboard.index',
			'data' => compact(
				'team',
				'currency',
				'list_product',
				'filter_product',
				'filter_time',
				'startDate',
				'endDate',
				'total_order',
				'unpaid_order',
				'earning_order',
				'holding_order',
				'deduct_order',
				'earning',
				'holding',
				'deduct',
				'is_advertiser',
				'is_caller',
				'is_shipper',
				'data_chart_statistics',
				'data_chart_status'
			)
		];
	}

	private static function block_orders($team, $data_team, $action = null) {

		$title = lang('team', 'order').' - '.$team['name'];


		$list_status = array_keys((array) orderController::render_status(null));
		
		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

		$filter_id = trim(Request::get(orderController::FILTER_ID, null));
		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_product = intval(Request::get(orderController::FILTER_PRODUCT, $list_product ? $list_product[0]['id'] : 0));
		$filter_status = Request::get(orderController::FILTER_STATUS, InterFaceRequest::OPTION_ALL);
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));


		$where = [
			'product_id' => $filter_product,
			'country_id' => $team['country_id']
		];

		switch($team['type']) {
			case Team::TYPE_ONLY_CALL:
				$where['call_team_id'] = $team['id'];
				break;
	
			case Team::TYPE_ONLY_ADS:
				$where['ads_team_id'] = $team['id'];
				break;
	
			case Team::TYPE_ONLY_SHIP:
				$where['ship_team_id'] = $team['id'];
				break;
	
			case Team::TYPE_FULLSTACK:
				$is_advertiser = true;
				$is_caller = true;
				$is_shipper = true;
				$perms = json_decode($team['perms'], true);
				foreach ($perms as $key => $value)
				{
					$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
					if($value != true)
					{
						if($key == 'access_caller') {
							$is_caller = false;
						}
						else if($key == 'access_advertisers') {
							$is_advertiser = false;
						}
						else if($key == 'access_shipper') {
							$is_shipper = false;
						}
					}
				}
	
				$where['OR'] = [];
				
				if($is_caller) {
					$where['OR']['call_team_id'] = $team['id'];
				}
	
				if($is_advertiser) {
					$where['OR']['ads_team_id'] = $team['id'];
				}
	
				if($is_shipper) {
					$where['OR']['ship_team_id'] = $team['id'];
				}
	
				break;
		}


		$count_id = 0;
		if($filter_id != '') {
			$ids = str_replace(',', "\n", $filter_id);
			$ids = str_replace('#', '', $ids);
			$ids = array_filter(explode("\n", $ids), function($id) {
				$id = trim($id);
				return $id != '';
			});
			$count_id = count($ids);
			$where['id'] = $ids;
		}


		if($filter_keyword != '') {
			if(preg_match("/^[+]?[0-9]{1,3}?[0-9]{1,9}$/", $filter_keyword)) {
				$where['order_phone[~]'] = '%"'.$filter_keyword.'"%';
			}
			else {
				$where['order_name[~]'] = '%'.$filter_keyword.'%';
			}
		}

		if($filter_status != InterFaceRequest::OPTION_ALL) {
			$where['status'] = intval($filter_status);
		}

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00'),
				'endDate' => strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59')
			];
		}


		$count = Order::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$list_order = Order::select([
			'(SELECT <name> FROM <core_provinces> WHERE <id> = <{table}.order_province_id>) AS <order_province>',
			'(SELECT <name> FROM <core_districts> WHERE <id> = <{table}.order_district_id>) AS <order_district>',
			'(SELECT <name> FROM <core_wards> WHERE <id> = <{table}.order_ward_id>) AS <order_ward>',
			'(SELECT <name> FROM <core_areas> WHERE <id> = <{table}.order_area_id>) AS <order_area>',
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		return [
			'title' => $title,
			'view' => 'team.block.list_order',
			'data' => compact(
				'title',
				'team',
				'count',
				'list_order',
				'list_status',
				'list_product',
				'list_status',
				'filter_id',
				'filter_keyword',
				'filter_product',
				'filter_status',
				'filter_time',
				'count_id',
				'startDate',
				'endDate',
				'pagination'
			)
		];
	}
}





?>