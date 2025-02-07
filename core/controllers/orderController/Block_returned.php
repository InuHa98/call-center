<?php

trait Block_returned {
    private static function block_returned($team, $action = null) {
        $success = null;
		$error = null;
		$title = lang('system', 'order_returned');
		$current_route = 'order_returned';

		$list_status = [
			Order::STATUS_RETURNED
		];

		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
        $filter_payment = trim(Request::get(orderController::FILTER_PAYMENT, InterFaceRequest::OPTION_ALL));
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

        if($filter_payment != InterFaceRequest::OPTION_ALL) {
            $where['OR'] = [
                'AND #1' => [
                    'call_user_id' => Auth::$data['id'],
                    'payment_call' => $filter_payment == Order::IS_PAYMENT_AND_DEDUCT ? Order::IS_PAYMENT_AND_DEDUCT : Order::IS_PAYMENT
                ],
                'AND #2' => [
                    'ship_user_id' => Auth::$data['id'],
                    'payment_ship' => $filter_payment == Order::IS_PAYMENT_AND_DEDUCT ? Order::IS_PAYMENT_AND_DEDUCT : Order::IS_PAYMENT
                ]
            ];
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


		return View::render('order.returned', compact(
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
			'filter_product',
            'filter_payment',
			'filter_time',
			'startDate',
			'endDate',
			'pagination'
		));
    }
}

?>