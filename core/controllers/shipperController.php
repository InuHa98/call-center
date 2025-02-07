<?php


class shipperController {

	const BLOCK_NEW_ORDER = 'New-Orders';
	const BLOCK_PENDING_DELIVERY = 'Pending-Delivery';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';

	public static function index($block, $action = null) {
		$team = Team::get(Auth::$data['team_id']);

		if(!$team || !UserPermission::is_shipper()) {
			return ServerErrorHandler::error_403();
		}
		Language::load('order.lng');
		switch($block) {

			case self::BLOCK_PENDING_DELIVERY:
				return self::block_pending_delivery($team);

			default:
				return self::block_new_orders($team);
		}
		
	}

	private static function block_new_orders($team) {
		$success = null;
		$error = null;
		
		$title = lang('system', 'new_order');
		$current_route = 'new_order';

		if(Auth::$data['is_ban_team'] == Team::IS_BAN) {
			return ServerErrorHandler::error_403();
		}
		
		$list_status = [
			Order::STATUS_AGREE_BUY,
			Order::STATUS_DELIVERY_DATE
		];

		if(Security::validate() == true) {

			$action_form = trim(Request::post(orderController::INPUT_ACTION, null));
			$id = intval(Request::post(orderController::INPUT_ID, null));

			switch($action_form) {
				case orderController::ACTION_GET_ORDER:
					$ids = Request::post(orderController::INPUT_ID, []);

					$get_all = !isset($_POST[orderController::INPUT_ID]) ? true : false;

					$orders = Order::select(['id'])::list([
						'id' => $ids,
						'status' => $list_status,
						'country_id' => $team['country_id'],
						'ship_user_id' => 0
					]);

					if(!$get_all && !$orders) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						$where = [
							'id' => array_map(function($arr) { return $arr['id'];}, $orders),
							'country_id' => $team['country_id'],
							'status' => $list_status,
							'ship_user_id' => 0
						];

						if($get_all) {
							unset($where['id']);
						}
						$count_update = Order::update($where, [
							'ship_user_id' => Auth::$data['id'],
							'ship_team_id' => $team['id'],
							'profit_leader_ship' => $team['profit_ship'],
							'deduct_leader_ship' => $team['deduct_ship'],
							'profit_member_ship' => Auth::$data['profit_ship'],
							'deduct_member_ship' => Auth::$data['deduct_ship']
						]);

						if($count_update > 0) {
							Alert::push([
								'message' => lang('success', 'mark_received', [
									'count' => $count_update
								]),
								'type' => 'success',
								'timeout' => 3000
							]);
							return redirect_route('shipper', ['block' => self::BLOCK_PENDING_DELIVERY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
					break;
			}
		}


		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_status = Request::get(orderController::FILTER_STATUS, InterFaceRequest::OPTION_ALL);
		$filter_area = trim(Request::get(orderController::FILTER_AREA, InterFaceRequest::OPTION_ALL));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));

		if(!is_array($filter_status)) {
			$filter_status = [$filter_status];
		}

		$where = [
			'status' => $list_status,
			'country_id' => $team['country_id'],
			'ship_user_id' => 0
		];


		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where['product_id'] = $filter_product;
		}

		if($filter_area != InterFaceRequest::OPTION_ALL) {
			$where['order_area_id'] = $filter_area;
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

		
		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

		$list_area = Area::list([
			'country_id' => $team['country_id']
		]);



		return View::render('shipper.new_orders', compact(
			'title',
			'current_route',
			'success',
			'error',
			'count',
			'team',
			'list_order',
			'list_product',
			'list_status',
			'list_area',
			'filter_status',
			'filter_product',
			'filter_area',
			'filter_time',
			'startDate',
			'endDate',
			'pagination'
		));
	}


	private static function block_pending_delivery($team) {
		$success = null;
		$error = null;
		$title = lang('system', 'pending_delivery');
		$current_route = 'pending_delivery';

		$list_status = [
			Order::STATUS_AGREE_BUY,
			Order::STATUS_DELIVERY_DATE
		];

		$count_agree_buy = Order::count([
			'status' => Order::STATUS_AGREE_BUY,
			'country_id' => $team['country_id'],
			'ship_user_id' => Auth::$data['id']
		]);

		$count_delivery_date = Order::count([
			'status' => Order::STATUS_DELIVERY_DATE,
			'country_id' => $team['country_id'],
			'ship_user_id' => Auth::$data['id']
		]);

		if(Security::validate() == true) {

			$action_form = trim(Request::post(orderController::INPUT_ACTION, null));
			$id = intval(Request::post(orderController::INPUT_ID, null));
			


			switch($action_form) {

				case orderController::ACTION_EXPORT_EXCEL:
					$ids = Request::post(orderController::INPUT_ID, []);
					$orders = Order::select([
						'(SELECT <name> FROM <core_provinces> WHERE <id> = <{table}.order_province_id>) AS <order_province>',
						'(SELECT <name> FROM <core_districts> WHERE <id> = <{table}.order_district_id>) AS <order_district>',
						'(SELECT <name> FROM <core_wards> WHERE <id> = <{table}.order_ward_id>) AS <order_ward>',
						'(SELECT <name> FROM <core_areas> WHERE <id> = <{table}.order_area_id>) AS <order_area>'
					], false)::list([
						'id' => $ids,
						'status' => $list_status,
						'country_id' => $team['country_id'],
						'ship_user_id' => Auth::$data['id']
					]);

					if(!$orders) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						return orderController::export_excel($orders);
					}
					break;

				case orderController::ACTION_MARK_DELIVERING:
					$ids = Request::post(orderController::INPUT_ID, []);

					$orders = Order::select([
						'id', 
						'quantity', 
						'product_id'
					])::list([
						'id' => $ids,
						'status' => $list_status,
						'country_id' => $team['country_id'],
						'ship_user_id' => Auth::$data['id']
					]);

					if(!$orders) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						$quantity = [];
						$list_id = [];
						$stocks = [];
						foreach($orders as $order) {
							$list_id[] = $order['id'];

							if(!isset($quantity[$order['product_id']])) {
								$quantity[$order['product_id']] = $order['quantity'];
							} else {
								$quantity[$order['product_id']] += $order['quantity'];
							}

							if(!isset($stocks[$order['product_id']])) {
								$product = Product::get(['id' => $order['product_id']]);
								$stocks[$order['product_id']] = $product['stock'];
							}
						}

						$outstock = false;
						foreach($stocks as $id_product => $stock) {
							if($stock < $quantity[$id_product]) {
								$outstock = true;
								break;
							}
						}

						if($outstock) {
							Alert::push([
								'message' => lang('errors', 'outstock'),
								'type' => 'error',
								'timeout' => 3000
							]);
							break;
						}

						$where = [
							'id' => $list_id,
							'country_id' => $team['country_id'],
							'status' => $list_status,
							'ship_user_id' => Auth::$data['id']
						];

						$count_update = Order::update($where, [
							'status' => Order::STATUS_DELIVERING,
							'delivery_at' => time()
						]);

						if($count_update > 0) {
							Alert::push([
								'message' => lang('success', 'mark_delivering', [
									'count' => $count_update
								]),
								'type' => 'success',
								'timeout' => 3000
							]);

							foreach($stocks as $id_product => $stock) {
								Product::update($id_product, [
									'stock[-]' => $quantity[$id_product]
								]);
							}

							Product::update([
								'stock[<]' => 0
							], [
								'stock' => 0
							]);

							return redirect_route('shipper', ['block' => self::BLOCK_PENDING_DELIVERY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
					break;

				case orderController::ACTION_CANCEL_ORDER:
					$ids = Request::post(orderController::INPUT_ID, []);

					$orders = Order::select(['id'])::list([
						'id' => $ids,
						'status' => $list_status,
						'country_id' => $team['country_id'],
						'ship_user_id' => Auth::$data['id']
					]);

					if(!$orders) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						$where = [
							'id' => array_map(function($arr) { return $arr['id'];}, $orders),
							'country_id' => $team['country_id'],
							'status' => $list_status,
							'ship_user_id' => Auth::$data['id']
						];

						$count_update = Order::update($where, [
							'ship_user_id' => 0,
							'ship_team_id' => 0,
							'profit_leader_ship' => 0,
							'deduct_leader_ship' => 0,
							'profit_member_ship' => 0,
							'deduct_member_ship' => 0,
							'delivery_at' => 0
						]);

						if($count_update > 0) {
							Alert::push([
								'message' => lang('success', 'mark_cancel', [
									'count' => $count_update
								]),
								'type' => 'success',
								'timeout' => 3000
							]);
							return redirect_route('shipper', ['block' => self::BLOCK_PENDING_DELIVERY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
					break;
			}
		}

		$filter_id = trim(Request::get(orderController::FILTER_ID, null));
		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_status = intval(Request::get(orderController::FILTER_STATUS, $count_agree_buy == 0 && $count_delivery_date > 0 ? Order::STATUS_DELIVERY_DATE : Order::STATUS_AGREE_BUY));
		$filter_area = trim(Request::get(orderController::FILTER_AREA, InterFaceRequest::OPTION_ALL));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));


		$where = [
			'status' => $list_status,
			'country_id' => $team['country_id'],
			'ship_user_id' => Auth::$data['id']
		];

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

		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where['product_id'] = $filter_product;
		}

		if($filter_status) {
			$where['status'] = $filter_status;
		}

		if($filter_area != InterFaceRequest::OPTION_ALL) {
			$where['order_area_id'] = $filter_area;
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

		
		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);

		$list_area = Area::list([
			'country_id' => $team['country_id']
		]);



		return View::render('shipper.pending_delivery', compact(
			'title',
			'current_route',
			'success',
			'error',
			'count',
			'team',
			'list_order',
			'list_product',
			'list_status',
			'list_area',
			'filter_id',
			'filter_keyword',
			'filter_product',
			'filter_status',
			'filter_area',
			'filter_time',
			'count_id',
			'startDate',
			'endDate',
			'pagination'
		));
	}



}



?>