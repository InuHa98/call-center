<?php

trait Block_busy_callback {
    private static function block_busy_callback($team, $action = null) {
        
        $success = null;
		$error = null;

		$title = lang('system', 'order_busy_callback');
		$current_route = 'order_busy_callback';

		$list_status = [
			Order::STATUS_BUSY_CALLBACK
		];

		if(Security::validate() == true && Auth::$data['is_ban_team'] == Team::IS_NOT_BAN) {

			$action_form = trim(Request::post(orderController::INPUT_ACTION, null));
			$id = intval(Request::post(orderController::INPUT_ID, null));

			switch($action_form) {
				case orderController::ACTION_GET_ORDER:
					$order = Order::get([
						'id' => $id,
						'status' => $list_status,
						'country_id' => $team['country_id'],
						'call_user_id' => Auth::$data['id']
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
                            'call_user_id' => Auth::$data['id']
						], [
							'status' => Order::STATUS_CALLING,
							'call_user_id' => Auth::$data['id'],
							'call_team_id' => $team['id'],
							'profit_leader_call' => $team['profit_call'],
							'deduct_leader_call' => $team['deduct_call'],
							'profit_member_call' => Auth::$data['profit_call'],
							'deduct_member_call' => Auth::$data['deduct_call']
						])) {
							return redirect_route('caller', ['block' => callerController::BLOCK_CALLING]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
					break;
			}
		}

		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));


		$where = [
			'status' => $list_status,
			'country_id' => $team['country_id'],
			'call_user_id' => Auth::$data['id']
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



		return View::render('caller.pending_confirm', compact(
			'title',
			'current_route',
			'success',
			'error',
			'count',
			'team',
			'list_order',
			'list_product',
			'filter_keyword',
			'filter_product',
			'filter_time',
			'startDate',
			'endDate',
			'pagination'
		));
    }
}

?>