<?php


class callerController {

	const BLOCK_CALLING = 'Calling';
	const BLOCK_PENDING_CONFIRM = 'Pending-Confirm';
	const BLOCK_EDIT = 'Edit';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';

	public static function index($block, $action = null) {
		Language::load('order.lng');
		$team = Team::get(Auth::$data['team_id']);

		if(!$team || !UserPermission::is_caller()) {
			return ServerErrorHandler::error_403();
		}

		switch($block) {

			case self::BLOCK_EDIT:
				return self::block_edit($team, $action);

			case self::BLOCK_CALLING:
				return self::block_calling($team);

			default:
				return self::pending_confirm($team);
		}
		
	}

	private static function block_edit($team, $id) {
		$success = null;
		$error = null;
		$title = lang('order', 'txt_edit');
		$current_route = null;

		if(Auth::$data['is_ban_team'] == Team::IS_BAN) {
			return ServerErrorHandler::error_403();
		}
		
		$list_status = [
			Order::STATUS_AGREE_BUY,
			Order::STATUS_DELIVERY_DATE,
			Order::STATUS_REFUSE_BUY,
			Order::STATUS_WRONG_NUMBER,
			Order::STATUS_DUPLICATE,
			Order::STATUS_TRASH
		];
		
		$order = Order::select([
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::get([
			'id' => $id,
			'call_user_id' => Auth::$data['id'],
			'status' => $list_status
		]);



		if(!$order) {
			return ServerErrorHandler::error_404();
		}

		
		$referer = trim(Request::post(InterFaceRequest::REFERER, Request::referer()));
		$quantity = intval(Request::post(orderController::INPUT_QUANTITY, $order['quantity']));
		$price = floatval(Request::post(orderController::INPUT_PRICE, $order['price']));
		$order_first_name = trim(Request::post(orderController::INPUT_FIRST_NAME, $order['order_first_name']));
		$order_last_name = trim(Request::post(orderController::INPUT_LAST_NAME, $order['order_last_name']));
		$order_phone = Request::post(orderController::INPUT_ORDER_PHONE, json_decode($order['order_phone'], true));
		$province_id = intval(Request::post(orderController::INPUT_PROVINCE, $order['order_province_id']));
		$district_id = intval(Request::post(orderController::INPUT_DISTRICT, $order['order_district_id']));
		$ward_id = intval(Request::post(orderController::INPUT_WARD, $order['order_ward_id']));
		$address = trim(Request::post(orderController::INPUT_ADDRESS, $order['order_address']));
		$area_id = intval(Request::post(orderController::INPUT_AREA, $order['order_area_id']));
		$note_call = trim(Request::post(orderController::INPUT_CALL_NOTE, $order['note_call']));
		$status = intval(Request::post(orderController::INPUT_STATUS, $order['status']));

		$product = Product::get([
			'id' => $order['product_id'],
			'status' => Product::STATUS_ACTIVE
		]);

		if(Security::validate() == true) {
			$country = Country::get($team['country_id']);

			$order_phone = filter_phone($order_phone, $country);

			$is_agree_buy = in_array($status, [
				Order::STATUS_AGREE_BUY,
				Order::STATUS_DELIVERY_DATE
			]);

			if($is_agree_buy && $quantity < 1) {
				$error = lang('order', 'error_quantity_empty');
			}
			else if($is_agree_buy && $price < $product['price']) {
				$error = lang('order', 'error_price');
			}
			else if($order_first_name == '') {
				$error = lang('order', 'error_first_name');
			}
			else if(!$order_phone) {
				$error = lang('order', 'error_phone_empty');
			}
			else if(!in_array($status, $list_status)) {
				$error = lang('order', 'error_status');
			}
			else if($is_agree_buy && !Provinces::has([
				'id' => $province_id
			])) {
				$error = lang('order', 'error_province');
			}
			else if($is_agree_buy && !Districts::has([
				'id' => $district_id
			])) {
				$error = lang('order', 'error_district');
			}
			else if($is_agree_buy && $ward_id && !Wards::has([
				'id' => $ward_id
			])) {
				$error = lang('order', 'error_ward');
			}
			else if($is_agree_buy && !$address) {
				$error = lang('order', 'error_address');
			}
			else {

				$data = [
					'quantity' => $quantity,
					'price' => $price,
					'order_first_name' => $order_first_name,
					'order_last_name' => $order_last_name,
					'order_phone' => $order_phone,
					'order_province_id' => $province_id,
					'order_district_id' => $district_id,
					'order_ward_id' => $ward_id,
					'order_address' => $address,
					'order_area_id' => $area_id,
					'note_call' => $note_call,
					'status' => $status
				];

				if(!in_array($order['status'], [
					Order::STATUS_AGREE_BUY,
					Order::STATUS_DELIVERY_DATE
				]) && $is_agree_buy) {
					$data['ship_user_id'] = 0;
					$data['ship_team_id'] = 0;
					$data['profit_leader_ship'] = 0;
					$data['deduct_leader_ship'] = 0;
					$data['profit_member_ship'] = 0;
					$data['deduct_member_ship'] = 0;
				}

				if(Order::update($order['id'], $data) > 0) {
					Alert::push([
						'message' => lang('system', 'success_save'),
						'type' => 'success',
						'timeout' => 3000
					]);

					Order::update($order['id'], ['updated_at' => time()]);

					if(in_array($order['status'], [
						Order::STATUS_AGREE_BUY,
						Order::STATUS_DELIVERY_DATE
					]) && $order['ship_user_id'] && $order['ship_user_id'] != Auth::$data['id']) {
						Notification::create([
							'user_id' => $order['ship_user_id'],
							'from_user_id' => Auth::$data['id'],
							'type' => notificationController::TYPE_UPDATE_ORDER,
							'data' => [
								'order_id' => $order['id']
							]
						]);
					}
				} else {
					Alert::push([
						'message' => lang('system', 'error_save'),
						'type' => 'error',
						'timeout' => 3000
					]);

				}
			}
		}

		$list_province = Provinces::list([
			'country_id' => $team['country_id']
		]);

		$list_district = Districts::list([
			'country_id' => $team['country_id']
		]);

		$list_ward = Wards::list([
			'country_id' => $team['country_id']
		]);

		$list_area = Area::list([
			'country_id' => $team['country_id']
		]);
		


		return View::render('caller.edit', compact(
			'title',
			'current_route',
			'success',
			'error',
			'team',
			'order',
			'product',
			'list_province',
			'list_district',
			'list_ward',
			'list_status',
			'list_area',
			'quantity',
			'price',
			'order_first_name',
			'order_last_name',
			'order_phone',
			'province_id',
			'district_id',
			'ward_id',
			'address',
			'area_id',
			'note_call',
			'status',
			'referer'
		));
	}

	private static function block_calling($team) {
		$success = null;
		$error = null;
		$title = lang('order', 'txt_calling');
		$current_route = 'calling';

		$list_status = [
			Order::STATUS_AGREE_BUY,
			Order::STATUS_DELIVERY_DATE,
			Order::STATUS_REFUSE_BUY,
			Order::STATUS_BUSY_CALLBACK,
			Order::STATUS_WRONG_NUMBER,
			Order::STATUS_CAN_NOT_CALL,
			Order::STATUS_DUPLICATE,
			Order::STATUS_TRASH
		];
		
		$order = Order::select([
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::get([
			'call_user_id' => Auth::$data['id'],
			'status' => Order::STATUS_CALLING
		]);

		if(!$order) {
			return View::render('caller.calling', compact(
				'title',
				'current_route',
				'success',
				'error'
			));
		}

		$quantity = intval(Request::post(orderController::INPUT_QUANTITY, $order['quantity']));

		$price = floatval(Request::post(orderController::INPUT_PRICE, $order['price']));
		$order_first_name = trim(Request::post(orderController::INPUT_FIRST_NAME, $order['order_first_name']));
		$order_last_name = trim(Request::post(orderController::INPUT_LAST_NAME, $order['order_last_name']));
		$order_phone = Request::post(orderController::INPUT_ORDER_PHONE, json_decode($order['order_phone'], true));
		$province_id = intval(Request::post(orderController::INPUT_PROVINCE, $order['order_province_id']));
		$district_id = intval(Request::post(orderController::INPUT_DISTRICT, $order['order_district_id']));
		$ward_id = intval(Request::post(orderController::INPUT_WARD, $order['order_ward_id']));
		$address = trim(Request::post(orderController::INPUT_ADDRESS, $order['order_address']));
		$area_id = intval(Request::post(orderController::INPUT_AREA, $order['order_area_id']));
		$note_call = trim(Request::post(orderController::INPUT_CALL_NOTE, $order['note_call']));
		$status = intval(Request::post(orderController::INPUT_STATUS, $order['status']));

		$product = Product::get([
			'id' => $order['product_id'],
			'status' => Product::STATUS_ACTIVE
		]);

		if(Security::validate() == true) {
			$country = Country::get($team['country_id']);

			$order_phone = filter_phone($order_phone, $country);

			$is_agree_buy = in_array($status, [
				Order::STATUS_AGREE_BUY,
				Order::STATUS_DELIVERY_DATE
			]);

			if($is_agree_buy && $quantity < 1) {
				$error = lang('order', 'error_quantity_empty');
			}
			else if($is_agree_buy && $price < $product['price']) {
				$error = lang('order', 'error_price');
			}
			else if($order_first_name == '') {
				$error = lang('order', 'error_first_name');
			}
			else if(!$order_phone) {
				$error = lang('order', 'error_phone_empty');
			}
			else if(!in_array($status, $list_status)) {
				$error = lang('order', 'error_status');
			}
			else if($is_agree_buy && !Provinces::has([
				'id' => $province_id
			])) {
				$error = lang('order', 'error_province');
			}
			else if($is_agree_buy && !Districts::has([
				'id' => $district_id
			])) {
				$error = lang('order', 'error_district');
			}
			else if($is_agree_buy && $ward_id && !Wards::has([
				'id' => $ward_id
			])) {
				$error = lang('order', 'error_ward');
			}
			else if($is_agree_buy && !$address) {
				$error = lang('order', 'error_address');
			}
			else {

				$data = [
					'quantity' => $quantity,
					'price' => $price,
					'order_first_name' => $order_first_name,
					'order_last_name' => $order_last_name,
					'order_phone' => $order_phone,
					'order_province_id' => $province_id,
					'order_district_id' => $district_id,
					'order_ward_id' => $ward_id,
					'order_address' => $address,
					'order_area_id' => $area_id,
					'note_call' => $note_call,
					'status' => $status,
					'call_at' => time()
				];

				if(Order::update($order['id'], $data)) {
					$success = lang('system', 'success_save');
					//Auto_ban caller
					if(self::auto_ban_caller() == true) {
						return redirect_route('dashboard');
					}
					return redirect_route('caller', ['block' => self::BLOCK_PENDING_CONFIRM]);
				} else {
					$error = lang('system', 'error_save');
				}
			}
		}

		$list_province = Provinces::list([
			'country_id' => $team['country_id']
		]);

		$list_district = Districts::list([
			'country_id' => $team['country_id']
		]);

		$list_ward = Wards::list([
			'country_id' => $team['country_id']
		]);

		$list_area = Area::list([
			'country_id' => $team['country_id']
		]);
		


		return View::render('caller.calling', compact(
			'title',
			'current_route',
			'success',
			'error',
			'team',
			'order',
			'product',
			'list_province',
			'list_district',
			'list_ward',
			'list_status',
			'list_area',
			'quantity',
			'price',
			'order_first_name',
			'order_last_name',
			'order_phone',
			'province_id',
			'district_id',
			'ward_id',
			'address',
			'area_id',
			'note_call',
			'status'
		));
	}


	private static function pending_confirm($team) {
		$success = null;
		$error = null;

		$title = lang('system', 'order_pending_confirm');
		$current_route = 'pending_confirm';

		if(Auth::$data['is_ban_team'] == Team::IS_BAN) {
			return ServerErrorHandler::error_403();
		}
		
		$list_status = [
			Order::STATUS_PENDING_CONFIRM,
			Order::STATUS_BUSY_CALLBACK,
			Order::STATUS_CAN_NOT_CALL
		];

		if(Security::validate() == true) {

			$action_form = trim(Request::post(orderController::INPUT_ACTION, null));
			$id = intval(Request::post(orderController::INPUT_ID, null));

			switch($action_form) {
				case orderController::ACTION_GET_ORDER:


					$order = Order::get([
						'id' => $id,
						'status' => $list_status,
						'country_id' => $team['country_id'],
						'call_team_id' => [0, Auth::$data['team_id']]
					]);

					if(!$order) {
						Alert::push([
							'message' => lang('errors', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} elseif(Order::has([
						'call_user_id' => Auth::$data['id'],
						'status' => Order::STATUS_CALLING
					])) {
						Alert::push([
							'message' => lang('errors', 'receive_multiple_order'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						if(Order::update([
							'id' => $order['id'],
							'status' => $list_status,
							'country_id' => $team['country_id'],
							'call_team_id' => [0, Auth::$data['team_id']]
						], [
							'status' => Order::STATUS_CALLING,
							'call_user_id' => Auth::$data['id'],
							'call_team_id' => $team['id'],
							'profit_leader_call' => $team['profit_call'],
							'deduct_leader_call' => $team['deduct_call'],
							'profit_member_call' => Auth::$data['profit_call'],
							'deduct_member_call' => Auth::$data['deduct_call'],
							'updated_at' => time()
						])) {
							if($order['call_user_id']) {
								Notification::create([
									'user_id' => $order['call_user_id'],
									'from_user_id' => Auth::$data['id'],
									'type' => notificationController::TYPE_TAKEN_ORDER,
									'data' => [
										'order_id' => $order['id']
									]
								]);
							}

							return redirect_route('caller', ['block' => self::BLOCK_CALLING]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
					break;
			}
		}

		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_status = Request::get(orderController::FILTER_STATUS, InterFaceRequest::OPTION_ALL);
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));


		if(!is_array($filter_status)) {
			$filter_status = [$filter_status];
		}


		$where = [
			'status' => $list_status,
			'country_id' => $team['country_id'],
			'call_team_id' => [0, Auth::$data['team_id']]
		];

		if($filter_keyword != '') {
			if(preg_match("/^\#([0-9]+)$/si", $filter_keyword)) {
				$where['id'] = str_replace('#', '', $filter_keyword);
			}
			else if(preg_match("/^[+]?[0-9]{1,3}?[0-9]{1,9}$/", $filter_keyword)) {
				$where['order_phone[~]'] = '%"'.$filter_keyword.'"%';
			}
			else {
				$where['order_name[~]'] = '%'.$filter_keyword.'%';
			}
		}

		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where['product_id'] = $filter_product;
		}

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00'),
				'endDate' => strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59')
			];
		}

		if(!in_array(InterFaceRequest::OPTION_ALL ,$filter_status)) {
			$where['status'] = array_filter($filter_status, function($value) use($list_status) {
				return in_array($value, $list_status);
			});
		}

		$count = Order::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$list_order = Order::select([
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		
		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);



		return View::render('caller.pending_confirm', compact(
			'title',
			'current_route',
			'success',
			'error',
			'count',
			'team',
			'list_order',
			'list_product',
			'list_status',
			'filter_keyword',
			'filter_status',
			'filter_product',
			'filter_time',
			'startDate',
			'endDate',
			'pagination'
		));
	}

	public static function auto_ban_caller() {
		if(UserPermission::isAdmin() || !env(DotEnv::AUTO_BAN, 0)) {
			return false;
		}

		$limit_check = env(DotEnv::AUTO_BAN_ORDER_CHECK, DotEnv::AUTO_BAN_MIN_ORDER);

		$where = [
			'call_user_id' => Auth::$data['id'],
			'call_team_id' => Auth::$data['team_id'],
			'country_id' => Auth::$data['country_id'],
			'LIMIT' => $limit_check,
			'ORDER' => [
				'created_at' => 'DESC'
			]
		];

		$orders = Order::join([])::select(['status'])::list($where);


		if(!$orders || count($orders) < $limit_check) {
			return false;
		}

		$total_refuse = 0;
		foreach($orders as $order) {
			if($order['status'] == Order::STATUS_REFUSE_BUY) {
				$total_refuse += 1;
			}
		}

		$rpo_rate = round($total_refuse / $limit_check * 100, 2);
		
		if($rpo_rate >= env(DotEnv::AUTO_BAN_RPO, 100)) {
			$reason = lang('order', 'reason_auto_ban');

			if(User::update(Auth::$data['id'], [
				'is_ban_team' => Team::IS_BAN,
				'reason_ban_team' => $reason
			])) {
				Notification::create([
					'user_id' => Auth::$data['id'],
					'from_user_id' => Auth::$data['id'],
					'type' => notificationController::TYPE_USER_BAN_TEAM,
					'data' => [
						'reason' => $reason
					]
				]);

				$list_admin = User::list([
					'adm' => UserPermission::IS_ADMIN
				]);

				if($list_admin) {
					foreach($list_admin as $admin) {
						Notification::create([
							'user_id' => $admin['id'],
							'from_user_id' => Auth::$data['id'],
							'type' => notificationController::TYPE_AUTO_BAN,
							'data' => [
								'rate' => $rpo_rate
							]
						]);
					}
				}
				return true;
			}

			return true;
		}
		return false;
	}
}



?>