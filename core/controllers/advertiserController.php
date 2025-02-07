<?php


class advertiserController {

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';
	const ACTION_LANDING_PAGE = 'Landing-Page';


	public static function index($action = null) {
		Language::load('order.lng');
		
		$team = Team::get(Auth::$data['team_id']);

		if(!$team || !UserPermission::is_advertisers()) {
			return ServerErrorHandler::error_403();
		}

		switch($action) {

			case orderController::ACTION_ADD:
				return self::add_conversion($team);

			case orderController::ACTION_EDIT:
				return self::edit_conversion($team);

			case self::ACTION_LANDING_PAGE:
				return self::landing_page($team);
				
			default:
				return self::list_conversion($team);
		}
		
	}

	private static function add_conversion($team) {
		$success = null;
		$error = null;
		$title = lang('system', 'add_conversion');
		$current_route = 'add_conversion';

		$is_edit = false;
		$can_edit = true;

		if(Auth::$data['is_ban_team'] == Team::IS_BAN) {
			return ServerErrorHandler::error_403();
		}
		
		$list_status = [
			Order::STATUS_PENDING_CONFIRM,
			//Order::STATUS_AGREE_BUY,
			//Order::STATUS_DELIVERY_DATE
		];


		$product_id = intval(Request::post(orderController::INPUT_PRODUCT, null));
		$quantity = intval(Request::post(orderController::INPUT_QUANTITY, 0));
		$price = floatval(Request::post(orderController::INPUT_PRICE, 0));
		$order_first_name = trim(Request::post(orderController::INPUT_FIRST_NAME, null));
		$order_last_name = trim(Request::post(orderController::INPUT_LAST_NAME, null));
		$order_phone = Request::post(orderController::INPUT_ORDER_PHONE, []);
		$province_id = intval(Request::post(orderController::INPUT_PROVINCE, 0));
		$district_id = intval(Request::post(orderController::INPUT_DISTRICT, 0));
		$ward_id = intval(Request::post(orderController::INPUT_WARD, 0));
		$address = trim(Request::post(orderController::INPUT_ADDRESS, null));
		$area_id = intval(Request::post(orderController::INPUT_AREA, 0));
		$note_ads = trim(Request::post(orderController::INPUT_ADS_NOTE, null));
		$status = intval(Request::post(orderController::INPUT_STATUS, null));

		if(Security::validate() == true) {
			$country = Country::get($team['country_id']);

			$product = Product::get([
				'id' => $product_id,
				'status' => Product::STATUS_ACTIVE
			]);

			$order_phone = filter_phone($order_phone, $country);

			$is_agree_buy = in_array($status, [
				Order::STATUS_AGREE_BUY,
				Order::STATUS_DELIVERY_DATE
			]);

			if(!$product) {
				$error = lang('order', 'error_product_empty');
			}
			else if($is_agree_buy && $quantity < 1) {
				$error = lang('order', 'error_quantity_empty');
			}
			else if($is_agree_buy && $price < $product['price']) {
				$error = lang('error_price');
			}
			else if($order_first_name == '') {
				$error = lang('order', 'error_first_name');
			}
			else if(!$order_phone) {
				$error = lang('order', 'error_phone_empty');
			}
			else if(Blacklist::is_ban($order_phone, $country['id'])) {
				$error = lang('order', 'error_phone_blacklist');
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
					'ads_team_id' => $team['id'],
					'country_id' => $country['id'],
					'ads_user_id' => Auth::$data['id'],
					'product_id' => $product['id'],
					'product_name' => $product['name'],
					'product_image' => $product['image'],
					'product_price' => $product['price'],
					'currency_id' => $country['currency_id'],
					'currency_exchange_rate' => $country['exchange_rate'],
					'currency' => $country['currency'],
					'ads_cost' => $product['ads_cost'],
					'delivery_cost' => $product['delivery_cost'],
					'import_cost' => $product['import_cost'],
					'profit_leader_ads' => $team['profit_ads'],
					'deduct_leader_ads' => $team['deduct_ads'],
					'profit_member_ads' => Auth::$data['profit_ads'],
					'deduct_member_ads' => Auth::$data['deduct_ads'],
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
					'note_ads' => $note_ads,
					'status' => $status,
				];

				if($is_agree_buy) {
					$data['call_team_id'] = $team['id'];
					$data['call_user_id'] = Auth::$data['id'];
					$data['profit_leader_call'] = $team['profit_call'];
					$data['deduct_leader_call'] = $team['deduct_call'];
					$data['profit_member_call'] = Auth::$data['profit_call'];
					$data['deduct_member_call'] = Auth::$data['deduct_call'];
					$data['call_at'] = time();
				}

				if(Order::create($data)) {

					Alert::push([
						'message' => lang('system', 'success_create'),
						'type' => 'success',
						'timeout' => 3000
					]);
					
					return redirect_route('advertiser');
				} else {
					$error = lang('system', 'error_create');
				}
			}
		}

		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

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
		

		return View::render('advertiser.add_edit', compact(
			'title',
			'is_edit',
			'can_edit',
			'current_route',
			'success',
			'error',
			'team',
			'list_product',
			'list_province',
			'list_district',
			'list_ward',
			'list_status',
			'list_area',
			'product_id',
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
			'note_ads',
			'status'
		));
	}

	private static function edit_conversion($team) {
		$success = null;
		$error = null;

		
		$title = lang('system', 'edit_conversion');
		$current_route = 'my_conversion';

		
		if(Auth::$data['is_ban_team'] == Team::IS_BAN) {
			return ServerErrorHandler::error_403();
		}
		
		$id = intval(Request::get(InterFaceRequest::ID, 0));
		$order = Order::select([
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::get([
			'id' => $id,
			'ads_user_id' => Auth::$data['id']
		]);

		if(!$order) {
			return ServerErrorHandler::error_404();
		}

		$is_edit = true;
		
		$list_status = [
			Order::STATUS_PENDING_CONFIRM,
			Order::STATUS_AGREE_BUY,
			Order::STATUS_DELIVERY_DATE
		];

		$product_id = intval(Request::post(orderController::INPUT_PRODUCT, $order['product_id']));
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
		$note_ads = trim(Request::post(orderController::INPUT_ADS_NOTE, $order['note_ads']));
		$status = intval(Request::post(orderController::INPUT_STATUS, $order['status']));

		$can_edit_status = (
			$order['can_edit'] == Order::IS_CAN_EDIT
			&& in_array($status, [
				Order::STATUS_PENDING_CONFIRM,
				Order::STATUS_AGREE_BUY,
				Order::STATUS_DELIVERY_DATE
			])
			&& in_array($order['call_user_id'], [0, Auth::$data['id']]) 
		);

		$can_edit = $can_edit_status;
		/*
		$can_edit = ($order['can_edit'] == Order::IS_CAN_EDIT
			&& in_array($order['call_user_id'], [0, Auth::$data['id']]) && !in_array($status, [
				Order::STATUS_CALLING,
				Order::STATUS_RETURNED,
				Order::STATUS_DELIVERED,
				Order::STATUS_UNRECEIVED
			])
		);
		*/



		if(!$can_edit_status) {
			$status = $order['status'];
			$list_status = [$order['status']];
		}

		if($can_edit && Security::validate() == true) {
			$country = Country::get($team['country_id']);

			$product = Product::get([
				'id' => $product_id,
				'status' => Product::STATUS_ACTIVE
			]);

			$order_phone = filter_phone($order_phone, $country);

			$is_agree_buy = in_array($status, [
				Order::STATUS_AGREE_BUY,
				Order::STATUS_DELIVERY_DATE
			]);

			if(!$product) {
				$error = lang('order', 'error_product_empty');
			}
			else if($is_agree_buy && $quantity < 1) {
				$error = lang('order', 'error_quantity_empty');
			}
			else if($is_agree_buy && $price < $product['price']) {
				$error = lang('error_price');
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
					'product_id' => $product['id'],
					'product_name' => $product['name'],
					'product_image' => $product['image'],
					'product_price' => $product['price'],
					'ads_cost' => $product['ads_cost'],
					'delivery_cost' => $product['delivery_cost'],
					'import_cost' => $product['import_cost'],
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
					'note_ads' => $note_ads,
					'status' => $status
				];

				if($is_agree_buy) {
					$data['call_team_id'] = $team['id'];
					$data['call_user_id'] = Auth::$data['id'];
					$data['profit_leader_call'] = Auth::$data['profit_call'];
					$data['deduct_leader_call'] = Auth::$data['deduct_call'];
					$data['profit_member_call'] = Auth::$data['profit_call'];
					$data['deduct_member_call'] = Auth::$data['deduct_call'];
					$data['call_at'] = time();
				} else {
					$data['call_team_id'] = 0;
					$data['call_user_id'] = 0;
					$data['profit_leader_call'] = 0;
					$data['deduct_leader_call'] = 0;
					$data['profit_member_call'] = 0;
					$data['deduct_member_call'] = 0;
					$data['call_at'] = 0;
				}

				if(Order::update($order['id'], $data) > 0) {

					Order::update($order['id'], ['updated_at' => time()]);

					if($order['call_user_id'] && $order['call_user_id'] != Auth::$data['id']) {
						Notification::create([
							'user_id' => $order['call_user_id'],
							'from_user_id' => Auth::$data['id'],
							'type' => notificationController::TYPE_UPDATE_ORDER,
							'data' => [
								'order_id' => $order['id']
							]
						]);
					}

					if($order['ship_user_id'] && $order['ship_user_id'] != Auth::$data['id']) {
						Notification::create([
							'user_id' => $order['ship_user_id'],
							'from_user_id' => Auth::$data['id'],
							'type' => notificationController::TYPE_UPDATE_ORDER,
							'data' => [
								'order_id' => $order['id']
							]
						]);
					}

					$success = lang('system', 'success_save');
				} else {
					$error = lang('system', 'error_save');
				}
			}
		}

		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

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
		




		return View::render('advertiser.add_edit', compact(
			'title',
			'is_edit',
			'can_edit',
			'can_edit_status',
			'current_route',
			'success',
			'error',
			'team',
			'list_product',
			'list_province',
			'list_district',
			'list_ward',
			'list_status',
			'list_area',
			'product_id',
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
			'note_ads',
			'status'
		));
	}

	private static function list_conversion($team) {
		$success = null;
		$error = null;
		$title = lang('system', 'list_conversion');
		$current_route = 'my_conversion';

		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));


		$where = [
			'country_id' => $team['country_id'],
			'ads_user_id' => Auth::$data['id']
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


		return View::render('advertiser.list', compact(
			'title',
			'current_route',
			'success',
			'error',
			'count',
			'team',
			'list_order',
			'list_product',
			'filter_product',
			'filter_time',
			'filter_keyword',
			'startDate',
			'endDate',
			'pagination'
		));
	}

	private static function landing_page($team) {
		$title = lang('system', 'landing_page');
		$current_route = 'landing_page';


		if(Auth::$data['is_ban_team'] == Team::IS_BAN) {
			return ServerErrorHandler::error_403();
		}

		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));

		$where = [
			'product_id' => array_map(function($product) {
				return $product['id'];
			}, $list_product)
		];

		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where = [
				'product_id' => $filter_product
			];
		}

		$count = LandingPage::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$list_landing = LandingPage::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		return View::render('advertiser.landing_page', compact(
			'title',
			'current_route',
			'team',
			'filter_product',
			'list_product',
			'count',
			'list_landing',
			'pagination'
		));
	}


}



?>