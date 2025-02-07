<?php

trait Block_delivering {
    private static function block_delivering($team, $action = null) {
        $success = null;
		$error = null;
		$title = lang('system', 'order_delivering');
		$current_route = 'order_delivering';

		$list_status = [
			Order::STATUS_DELIVERING
		];

		if(Security::validate() == true && Auth::$data['is_ban_team'] == Team::IS_NOT_BAN) {

			$action_form = trim(Request::post(orderController::INPUT_ACTION, null));
			$id = intval(Request::post(orderController::INPUT_ID, null));
			
			switch($action_form) {

				case orderController::ACTION_MARK_DELIVERED:
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
							'status' => $list_status,
							'country_id' => $team['country_id'],
							'ship_user_id' => Auth::$data['id']
						];

						$count_update = Order::update($where, [
							'status' => Order::STATUS_DELIVERED
						]);

						if($count_update > 0) {
							Alert::push([
								'message' => lang('success', 'mark_delivered', [
									'count' => $count_update
								]),
								'type' => 'success',
								'timeout' => 3000
							]);
							return redirect_route('order', ['block' => orderController::BLOCK_DELIVERING]);
						} else {
							$error = lang('system', 'default_error');
						}

					}
					break;

				case orderController::ACTION_MARK_UNRECEIVED:
					$ids = Request::post(orderController::INPUT_ID, []);
					$note = trim(Request::post(orderController::INPUT_SHIP_NOTE, null));

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
						foreach($orders as $order) {
							$list_id[] = $order['id'];

							if(!isset($quantity[$order['product_id']])) {
								$quantity[$order['product_id']] = $order['quantity'];
							} else {
								$quantity[$order['product_id']] += $order['quantity'];
							}
						}

						$where = [
							'id' => $list_id,
							'status' => $list_status,
							'country_id' => $team['country_id'],
							'ship_user_id' => Auth::$data['id']
						];

						$count_update = Order::update($where, [
							'status' => Order::STATUS_UNRECEIVED,
							'note_ads' => $note
						]);

						if($count_update > 0) {
							foreach($quantity as $id_product => $num) {
								if($num > 0) {
									Product::update($id_product, [
										'stock[+]' => $num
									]);
								}
							}

							Alert::push([
								'message' => lang('success', 'mark_unreceived', [
									'count' => $count_update
								]),
								'type' => 'success',
								'timeout' => 3000
							]);
							return redirect_route('order', ['block' => orderController::BLOCK_DELIVERING]);
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
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));


		$where = [
			'status' => $list_status,
			'country_id' => $team['country_id'],
			'OR' => [
				'call_user_id' => Auth::$data['id'],
				'ship_user_id' => Auth::$data['id']
			]
			
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


		return View::render('order.delivering', compact(
			'title',
			'current_route',
			'success',
			'error',
			'count',
			'team',
			'list_order',
			'list_product',
			'list_status',
			'filter_id',
			'filter_keyword',
			'filter_product',
			'filter_time',
			'count_id',
			'startDate',
			'endDate',
			'pagination'
		));
    }
}

?>