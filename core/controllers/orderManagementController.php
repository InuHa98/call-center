<?php


class orderManagementController {

	const BLOCK_LIST = 'List';
	const BLOCK_DUPLICATE = 'Duplicate';
	const BLOCK_DELTAI = 'Detail';
	const BLOCK_EDIT = 'Edit';


	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';

	public static function index($block, $action = null) {
		Language::load('order.lng');
		if(!UserPermission::access_order_management()) {
			return ServerErrorHandler::error_403();
		}

		switch($block) {
			case self::BLOCK_DELTAI:
				return self::block_detail($action);

			case self::BLOCK_EDIT:
				return self::block_edit($action);

			case self::BLOCK_DUPLICATE:
				return self::block_list($action);

			default:
				return self::block_list();
		}
		
	}

	private static function block_detail($order_id) {
		$title = lang('order', 'detail_order');
		$current_route = null;
		$order = Order::select([
			'(SELECT <name> FROM <core_provinces> WHERE <id> = <{table}.order_province_id>) AS <order_province>',
			'(SELECT <name> FROM <core_districts> WHERE <id> = <{table}.order_district_id>) AS <order_district>',
			'(SELECT <name> FROM <core_wards> WHERE <id> = <{table}.order_ward_id>) AS <order_ward>',
			'(SELECT <name> FROM <core_areas> WHERE <id> = <{table}.order_area_id>) AS <order_area>',
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::get([
			'id' => $order_id
		]);

		if(!$order) {
			return ServerErrorHandler::error_404();
		}

		$title .= ' - #'.$order['id'];
		$user_ads = [
			'id' => $order['ads_id'],
			'username' => $order['ads_username'],
			'avatar' => $order['ads_avatar'],
			'is_ban' => $order['ads_is_ban']
		];
		$user_call = [
			'id' => $order['call_id'],
			'username' => $order['call_username'],
			'avatar' => $order['call_avatar'],
			'is_ban' => $order['call_is_ban']
		];
		$user_ship = [
			'id' => $order['ship_id'],
			'username' => $order['ship_username'],
			'avatar' => $order['ship_avatar'],
			'is_ban' => $order['ship_is_ban']
		];
		$order_phone = json_decode($order['order_phone'], true);
		$view_in_list = false;
		$referer = RouteMap::get('order_management');
		return View::render('order.detail', compact(
			'title',
			'current_route',
			'order',
			'user_ads',
			'user_call',
			'user_ship',
			'order_phone',
			'view_in_list',
			'referer'
		));
	}


	private static function block_list($id_duplicate = null) {
		$success = null;
		$error = null;
		$title = lang('system', 'order_management');


		$list_status = array_keys((array) orderController::render_status(null));
		
		$list_product = Product::list([
			'status' => Product::STATUS_ACTIVE
		]);


		$filter_id = trim(Request::get(orderController::FILTER_ID, null));
		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_product = intval(Request::get(orderController::FILTER_PRODUCT, $list_product ? $list_product[0]['id'] : 0));
		$filter_team = trim(Request::get(orderController::FILTER_TEAM, InterFaceRequest::OPTION_ALL));
		$filter_status = Request::get(orderController::FILTER_STATUS, InterFaceRequest::OPTION_ALL);
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));

		$list_team = Team::list([
			'[RAW] FIND_IN_SET(:id, <{table}.product_id>)' => [
				'id' => $filter_product
			],
		]);

		$order_duplicate = Order::select([
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::get([
			'id' => $id_duplicate
		]);

		$where = [
			'product_id' => $filter_product
		];

		if($order_duplicate && $order_duplicate['duplicate']) {
			$phones = json_decode($order_duplicate['order_phone'], true);
			$sql = [];
			$map_phone = [];
			$i = 1;
			foreach($phones as $phone) {
				$sql[] = 'JSON_CONTAINS(<{table}.order_phone>, :phone'.$i.')';
				$map_phone['phone'.$i] = '"'.$phone.'"';
				$i++;
			}
			$where['[RAW] '.implode(' OR ', $sql)] = $map_phone;
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

		if($filter_team != InterFaceRequest::OPTION_ALL) {
			$where['OR #team'] = [
				'ads_team_id' => $filter_team,
				'call_team_id' => $filter_team,
				'ship_team_id' => $filter_team
			];
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


		if(Security::validate() == true) {

			$action_form = trim(Request::post(orderController::INPUT_ACTION, null));
			$id = intval(Request::post(orderController::INPUT_ID, null));
			
			switch($action_form) {

				case orderController::ACTION_MARK_DELIVERED:

					$order = Order::get([
						'id' => $id,
						'status' => Order::STATUS_DELIVERING
					]);

					if(!$order) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						$where = [
							'id' => $order['id'],
							'status' => Order::STATUS_DELIVERING
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
						} else {
							$error = lang('system', 'default_error');
						}

					}
					break;

				case orderController::ACTION_MARK_UNRECEIVED:
					$note = trim(Request::post(orderController::INPUT_SHIP_NOTE, null));
					$order = Order::get([
						'id' => $id,
						'status' => Order::STATUS_DELIVERING
					]);

					if(!$order) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						$where = [
							'id' => $order['id']
						];

						$count_update = Order::update($where, [
							'status' => Order::STATUS_UNRECEIVED,
							'note_ads' => $note
						]);

						if($count_update > 0) {
							Alert::push([
								'message' => lang('success', 'mark_unreceived', [
									'count' => $count_update
								]),
								'type' => 'success',
								'timeout' => 3000
							]);
						} else {
							$error = lang('system', 'default_error');
						}

					}
					break;

				case orderController::ACTION_DELETE_ORDER:
					$order = Order::get([
						'id' => $id,
						'status' => Order::STATUS_TRASH
					]);

					if(!$order) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						$where = [
							'id' => $order['id'],
							'status' => Order::STATUS_TRASH
						];

						$count_delete = Order::delete($order['id']);
						if($count_delete) {
							Alert::push([
								'message' => lang('success', 'delete', [
									'count' => $count_delete
								]),
								'type' => 'success',
								'timeout' => 3000
							]);
						} else {
							$error = lang('system', 'default_error');
						}

					}
					break;

				case orderController::ACTION_MARK_RETURNED:
					$note = trim(Request::post(orderController::INPUT_SHIP_NOTE, null));

					$order = Order::get([
						'id' => $id,
						'status' => Order::STATUS_DELIVERED
					]);

					if(!$order) {
						Alert::push([
							'message' => lang('system', 'order_not_found'),
							'type' => 'error',
							'timeout' => 3000
						]);
					} else {
						$where = [
							'id' => $order['id']
						];

						$count_update = Order::update($where, [
							'status' => Order::STATUS_RETURNED,
							'note_ads' => $note
						]);

						if($count_update > 0) {
							Order::send_notification_returned([$order]);

							Alert::push([
								'message' => lang('success', 'mark_returned', [
									'count' => $count_update
								]),
								'type' => 'success',
								'timeout' => 3000
							]);
						} else {
							$error = lang('system', 'default_error');
						}

					}
					break;


				case orderController::ACTION_EXPORT_EXCEL:

					$orders = Order::select([
						'(SELECT <name> FROM <core_provinces> WHERE <id> = <{table}.order_province_id>) AS <order_province>',
						'(SELECT <name> FROM <core_districts> WHERE <id> = <{table}.order_district_id>) AS <order_district>',
						'(SELECT <name> FROM <core_wards> WHERE <id> = <{table}.order_ward_id>) AS <order_ward>',
						'(SELECT <name> FROM <core_areas> WHERE <id> = <{table}.order_area_id>) AS <order_area>'
					], false)::list($where);

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
			}
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


		return View::render('order_management.list', compact(
			'title',
			'success',
			'error',
			'count',
			'order_duplicate',
			'list_order',
			'list_status',
			'list_team',
			'list_product',
			'list_status',
			'filter_id',
			'filter_keyword',
			'filter_product',
			'filter_team',
			'filter_status',
			'filter_time',
			'count_id',
			'startDate',
			'endDate',
			'pagination'
		));
	}


	private static function block_edit($id) {
		$success = null;
		$error = null;
		$title = lang('order', 'txt_edit');
		$current_route = null;
		
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
			'id' => $id
		]);


		if(!$order) {
			return ServerErrorHandler::error_404();
		}

		switch($order['status']) {
			case Order::STATUS_CALLING:
				$list_status[] = Order::STATUS_CALLING;
				$list_status[] = Order::STATUS_BUSY_CALLBACK;
				$list_status[] = Order::STATUS_CAN_NOT_CALL;
				break;

			case Order::STATUS_BUSY_CALLBACK:
				$list_status[] = Order::STATUS_BUSY_CALLBACK;
				break;

			case Order::STATUS_CAN_NOT_CALL:
				$list_status[] = Order::STATUS_CAN_NOT_CALL;
				break;

			case Order::STATUS_DELIVERING:
				$list_status[] = Order::STATUS_DELIVERING;
				break;

			case Order::STATUS_DELIVERED:
				$list_status[] = Order::STATUS_DELIVERED;
				break;

			case Order::STATUS_UNRECEIVED:
				$list_status[] = Order::STATUS_UNRECEIVED;
				break;

			case Order::STATUS_RETURNED:
				$list_status[] = Order::STATUS_RETURNED;
				break;
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
		$note_ads = trim(Request::post(orderController::INPUT_ADS_NOTE, $order['note_ads']));
		$note_call = trim(Request::post(orderController::INPUT_CALL_NOTE, $order['note_call']));
		$note_ship = trim(Request::post(orderController::INPUT_SHIP_NOTE, $order['note_ship']));
		$status = intval(Request::post(orderController::INPUT_STATUS, $order['status']));

		$product = Product::get([
			'id' => $order['product_id'],
			'status' => Product::STATUS_ACTIVE
		]);

		if(Security::validate() == true) {
			$country = Country::get($order['country_id']);

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
					'note_ads' => $note_ads,
					'note_call' => $note_call,
					'note_ship' => $note_ship,
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

					if($status != $order['status']) {
						switch($order['status']) {
							case Order::STATUS_DELIVERED:
							case Order::STATUS_DELIVERING:
								if($order['quantity'] > 0) {
									Product::update($order['product_id'], [
										'stock[+]' => $order['quantity']
									]);									
								}
								break;
						}
					}

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
			'country_id' => $order['country_id']
		]);

		$list_district = Districts::list([
			'country_id' => $order['country_id']
		]);

		$list_ward = Wards::list([
			'country_id' => $order['country_id']
		]);

		$list_area = Area::list([
			'country_id' => $order['country_id']
		]);
		


		return View::render('caller.edit', compact(
			'title',
			'current_route',
			'success',
			'error',
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
			'note_ads',
			'note_call',
			'note_ship',
			'status',
			'referer'
		));
	}
}



?>