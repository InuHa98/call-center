<?php


class dashboardController {

	public function index()
	{
		$title = lang('system', 'dashboard');
		$team = Team::get([
			'id' => Auth::$data['team_id']
		]);

		if(!$team) {
			$team = [
				'product_id' => 0,
				'country_id' => 0,
				'currency' => null,
				'type' => null
			];
		}

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
		$startDate_convert = implode('-', array_reverse(explode('-', $startDate))).' 00:00:00';
		$endDate_convert = implode('-', array_reverse(explode('-', $endDate))).' 23:59:59';

		$where = [];
		$all_deduct = true;
		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where['product_id'] = $filter_product;
		}

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => strtotime($startDate_convert),
				'endDate' => strtotime($endDate_convert)
			];
			$all_deduct = false;
		}


		$earning = User::get_earnings(Auth::$data, $where);
		$holding = User::get_holdings(Auth::$data, $where);
		$deduct = User::get_deduct(Auth::$data, $where, $all_deduct);

		$where_total_order = ['id' => 0];
		$where_unpaid_order = ['id' => 0];
		$where_earning_order = ['id' => 0];
		$where_holding_order = ['id' => 0];
		$where_deduct_order = ['id' => 0];

		if($team) {
			$where['country_id'] = $team['country_id'];
			$currency = _echo($team['currency']);
			switch($team['type']) {
				case Team::TYPE_ONLY_ADS:
					$tmp_where = [
						'ads_team_id' => $team['id'],
						'ads_user_id' => Auth::$data['id'],
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
					$tmp_where = [
						'call_team_id' => $team['id'],
						'call_user_id' => Auth::$data['id'],
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
					$tmp_where = [
						'ship_team_id' => $team['id'],
						'ship_user_id' => Auth::$data['id'],
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
					$tmp_where = [
						'OR' => [
							'AND #ads' => [
								'ads_team_id' => $team['id'],
								'ads_user_id' => Auth::$data['id'],
								'payment_ads' => [
									Order::IS_NOT_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #call' => [
								'call_team_id' => $team['id'],
								'call_user_id' => Auth::$data['id'],
								'payment_call' => [
									Order::IS_NOT_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #ship' => [
								'ship_team_id' => $team['id'],
								'ship_user_id' => Auth::$data['id'],
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
								'ads_user_id' => Auth::$data['id'],
								'payment_ads' => [
									Order::IS_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #call' => [
								'call_team_id' => $team['id'],
								'call_user_id' => Auth::$data['id'],
								'payment_call' => [
									Order::IS_PAYMENT,
									Order::IS_PAYING
								]
							],
							'AND #ship' => [
								'ship_team_id' => $team['id'],
								'ship_user_id' => Auth::$data['id'],
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
								'ads_team_id' => $team['id'],
								'ads_user_id' => Auth::$data['id']
							],
							'AND #call' => [
								'call_team_id' => $team['id'],
								'call_user_id' => Auth::$data['id']
							],
							'AND #ship' => [
								'ship_team_id' => $team['id'],
								'ship_user_id' => Auth::$data['id']
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


		$is_advertiser = UserPermission::is_advertisers();
		$is_caller = UserPermission::is_caller();
		$is_shipper = UserPermission::is_shipper();

		$status_orders = Order::select([
			'SUM(CASE WHEN <status> = '.Order::STATUS_PENDING_CONFIRM.' AND (<ads_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <pending_confirm>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_AGREE_BUY.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].' OR <ship_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <agree_buy>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERY_DATE.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].' OR <ship_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <delivery_date>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERING.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].' OR <ship_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <delivering>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERED.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].' OR <ship_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <delivered>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_UNRECEIVED.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].' OR <ship_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <unreceived>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_RETURNED.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].' OR <ship_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <returned>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_BUSY_CALLBACK.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <busy_callback>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_CAN_NOT_CALL.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <can_not_call>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_REFUSE_BUY.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <refuse_buy>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_WRONG_NUMBER.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <wrong_number>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DUPLICATE.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <duplicate>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_TRASH.' AND (<ads_user_id> = '.Auth::$data['id'].' OR <call_user_id> = '.Auth::$data['id'].') THEN 1 ELSE 0 END) AS <trash>'
		])::get(array_merge($where, [
			'OR' => [
				'ads_user_id' => Auth::$data['id'],
				'call_user_id' => Auth::$data['id'],
				'ship_user_id' => Auth::$data['id']
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

		$start_date = new DateTime($startDate_convert);
		$end_date = new DateTime($endDate_convert);

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
					'label' => lang('system', 'order'),
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

		return View::render('dashboard.index', compact(
			'title',
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
		));
	}
}





?>