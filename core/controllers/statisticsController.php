<?php


class statisticsController {

	const BLOCK_ORDER = 'Order';
	const BLOCK_LANDING = 'Landing';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';

	public function index($block = null)
	{
		Language::load('statistic.lng');
		switch($block) {
			case self::BLOCK_ORDER:
				return self::block_order();

			case self::BLOCK_LANDING:
				return self::block_landing();

			default:
				return ServerErrorHandler::error_404();
		}
	
	}

	private static function block_order() {
		if(!UserPermission::access_order_statistics()) {
			return ServerErrorHandler::error_403();
		}
		$current_route = 'statistics_order';
		$title = lang('system', 'order_statistics');

		$currency = null;

		$list_product = Product::list([
			'status' => Product::STATUS_ACTIVE
		]);

		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, isset($list_product[0]['id']) ? $list_product[0]['id'] : 0));
		$filter_view = trim(Request::get(orderController::FILTER_VIEW, orderController::VIEW_DAY));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));
		$startDate_convert = strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00');
		$endDate_convert = strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59');


		$product = Product::get(['id' => $filter_product]);

		if($product) {
			$title .= ' - '._echo($product['name']);
			$currency = _echo($product['currency']);
		}

		$where = [
			'product_id' => $product['id']
		];


		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => $startDate_convert,
				'endDate' => $endDate_convert
			];
		}

		$join_range = [];
		$select_range = [];
		$order_Type = 'ASC';


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

			default:
				$filter_view = orderController::VIEW_DAY;
				$select_range[] = 'DATE_FORMAT(FROM_UNIXTIME(<created_at>), \'%Y-%m-%d\') AS <order_range>';
				break;
		}


		if($filter_time == InterFaceRequest::OPTION_ALL) {
			$order_Type = 'DESC';
		}

		$total_orders = Order::join([])::select([
			'status',
			'COUNT(<id>) AS <total>',
			'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERED.' THEN <quantity> ELSE 0 END) AS <sales>'
		])::list(array_merge($where, [
			'GROUP' => 'status'
		]));

		$list_order = array_fill_keys(array_keys((array) orderController::render_status(null)), 0);
		$list_order['sales'] = 0;
		foreach($total_orders as $o) {
			if(isset($list_order[$o['status']])) {
				$list_order[$o['status']] += $o['total'];
			}
			$list_order['sales'] += $o['sales'];
		}

		$where = array_merge($where, [
			'GROUP' => 'order_range',
			'ORDER' => [
				'[RAW] <order_range>' => $order_Type
			]
		]);

		$count = Order::count([], "SELECT COUNT(*) FROM (SELECT ".implode(',', $select_range)." FROM <{table}> ".App::$database->build_raw_where(Order::build_where($where)).") AS <total>");

		$select_range[] = 'SUM(CASE WHEN (<status> IN ('.Order::STATUS_DELIVERED.', '.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.', '.Order::STATUS_DELIVERING.')) THEN (<price> - <ads_cost> - <delivery_cost> - <import_cost> - (<quantity> * <profit_leader_call>) - <profit_leader_ads> - <profit_leader_ship>) WHEN (<status> IN ('.Order::STATUS_UNRECEIVED.', '.Order::STATUS_RETURNED.')) THEN (0 - <ads_cost> - <delivery_cost>) ELSE (0 - <ads_cost>) END) AS <estimated_profit>';
		$select_range[] = 'SUM(CASE WHEN (<status> IN ('.Order::STATUS_DELIVERED.')) THEN (<price> - <ads_cost> - <delivery_cost> - <import_cost> - (<quantity> * <profit_leader_call>) - <profit_leader_ads> - <profit_leader_ship>) ELSE 0 END) AS <profit>';
		
		$select_range[] = 'SUM(CASE WHEN <status> IN ('.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.', '.Order::STATUS_DELIVERING.') THEN <quantity> ELSE 0 END) AS <pre_sales>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERED.' THEN <quantity> ELSE 0 END) AS <sales>';
		$select_range[] = 'COUNT(<id>) AS <total_order>';
		$select_range[] = 'SUM(CASE WHEN (<status> IN ('.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.', '.Order::STATUS_DELIVERING.')) THEN 1 ELSE 0 END) AS <total_pending_delivery>';
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
		
		return View::render('statistics.order', compact(
			'current_route',
			'title',
			'product',
			'currency',
			'list_product',
			'filter_product',
			'filter_view',
			'filter_time',
			'startDate',
			'endDate',
			'list_status',
			'filter_status',
			'count',
			'list_order',
			'list_statistics',
			'pagination'
		));
	}

	private static function block_landing() {
		if(!UserPermission::access_order_statistics()) {
			return ServerErrorHandler::error_403();
		}
		$current_route = 'statistics_landing';
		$title = lang('system', 'landing_statistics');


		$list_product = Product::list([
			'status' => Product::STATUS_ACTIVE
		]);

		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, isset($list_product[0]['id']) ? $list_product[0]['id'] : 0));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));
		$startDate_convert = strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00');
		$endDate_convert = strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59');


		$product = Product::get(['id' => $filter_product]);

		if($product) {
			$title .= ' - '._echo($product['name']);
			$currency = _echo($product['currency']);
		}

		$where = [
			'product_id' => $product['id']
		];


		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => $startDate_convert,
				'endDate' => $endDate_convert
			];
		}


		$where_group = array_merge($where, [
			'GROUP' => 'landing_id'
		]);


		$count = Order::count([], "SELECT COUNT(*) FROM (SELECT <id> FROM <{table}> ".App::$database->build_raw_where(Order::build_where($where_group)).") AS <total>");


		$count = LandingStatistics::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();

		$build_where_select = App::$database->build_raw_where(Order::build_where(array_merge($where, ['[RAW] <landing_id> = <{table}.landing_id>'])));
		$select_range = [
			'(SELECT <domain> FROM <core_landing_pages> WHERE <id> = <{table}.landing_id>) AS <domain>',
			'(SELECT COUNT(<id>) FROM <core_orders> '.$build_where_select.') AS <total_conversion>',
			'(SELECT SUM(CASE WHEN <status> IN ('.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.', '.Order::STATUS_DELIVERING.') THEN <quantity> ELSE 0 END) AS <pre_sales> FROM <core_orders> '.$build_where_select.') AS <pre_sales>',
			'(SELECT SUM(CASE WHEN <status> IN ('.Order::STATUS_DELIVERED.') THEN <quantity> ELSE 0 END) AS <sales> FROM <core_orders> '.$build_where_select.') AS <sales>',
			'(SELECT SUM(CASE WHEN <status> IN ('.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERING.', '.Order::STATUS_DELIVERED.', '.Order::STATUS_DELIVERY_DATE.') THEN 1 ELSE 0 END) AS <total> FROM <core_orders> '.$build_where_select.') AS <total_agree_buy>',
			'(SELECT SUM(CASE WHEN <status> IN ('.Order::STATUS_REFUSE_BUY.') THEN 1 ELSE 0 END) AS <total> FROM <core_orders> '.$build_where_select.') AS <total_refuse_buy>',
			'(SELECT SUM(CASE WHEN <status> IN ('.Order::STATUS_DUPLICATE.') THEN 1 ELSE 0 END) AS <total> FROM <core_orders> '.$build_where_select.') AS <total_duplicate>',
			'(SELECT SUM(CASE WHEN <status> IN ('.Order::STATUS_TRASH.') THEN 1 ELSE 0 END) AS <total> FROM <core_orders> '.$build_where_select.') AS <total_trash>',
			'SUM(<{table}.view>) AS <total_page_view>'
		];

		$list_statistics = LandingStatistics::select($select_range)::list(array_merge($where_group, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));
		
		
		return View::render('statistics.landing', compact(
			'current_route',
			'title',
			'product',
			'list_product',
			'filter_product',
			'filter_time',
			'startDate',
			'endDate',
			'count',
			'list_statistics',
			'pagination'
		));
	}

}





?>