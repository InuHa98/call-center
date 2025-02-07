<?php


class orderController {
	use Block_busy_callback;
	use Block_can_not_call;
	use Block_delivered;
	use Block_delivering;
	use Block_duplicate;
	use Block_pending_delivery;
	use Block_refuse_buy;
	use Block_returned;
	use Block_trash;
	use Block_unreceived;
	use Block_wrong_number;

	const BLOCK_DELTAI = 'Detail';
	const BLOCK_BUSY_CALLBACK = 'Busy-Callback';
	const BLOCK_CAN_NOT_CALL = 'Can-Not-Call';
	const BLOCK_PENDING_DELIVERY = 'Pending-Delivery';
	const BLOCK_DELIVERING = 'Delivering';
	const BLOCK_UNRECEIVED = 'Unreceived';
	const BLOCK_RETURNED = 'Returned';
	const BLOCK_DELIVERED = 'Delivered';
	const BLOCK_REFUSE_BUY = 'Refuse-Buy';
	const BLOCK_WRONG_NUMBER = 'Wrong-Number';
	const BLOCK_DUPLICATE = 'Duplicate';
	const BLOCK_TRASH = 'Trash';


	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';
	const ACTION_GET_ORDER = 'Get-Order';
	const ACTION_MARK_DELIVERING = 'Mark-Delivering';
	const ACTION_MARK_DELIVERED = 'Mark-Delivered';
	const ACTION_MARK_UNRECEIVED = 'Mark-Unreceived';
	const ACTION_DELETE_ORDER = 'Delete-Order';
	const ACTION_MARK_RETURNED = 'Mark-Returned';
	const ACTION_CANCEL_ORDER = 'Cancel-Order';
	const ACTION_EXPORT_EXCEL = 'Export-Excel';

	const INPUT_ACTION = '_action';
	const INPUT_ID = 'id';
	const INPUT_ORDER_NAME = 'order_name';
	const INPUT_FIRST_NAME = 'first_name';
	const INPUT_LAST_NAME = 'last_name';
	const INPUT_ORDER_PHONE = 'order_phone';
	const INPUT_QUANTITY = 'quantity';
	const INPUT_PRICE = 'price';
	const INPUT_PRODUCT = 'product_id';
	const INPUT_PROVINCE = 'province_id';
	const INPUT_DISTRICT = 'district_id';
	const INPUT_WARD = 'ward_id';
	const INPUT_ADDRESS = 'address';
	const INPUT_AREA = 'area';
	const INPUT_ADS_NOTE = 'ads_note';
	const INPUT_CALL_NOTE = 'call_note';
	const INPUT_SHIP_NOTE = 'ship_note';
	const INPUT_STATUS = 'status';

	const FILTER_ID = 'id';
	const FILTER_PRODUCT = 'product';
	const FILTER_TEAM = 'team';
	const FILTER_LANDING = 'landing';
	const FILTER_TIME = 'time';
	const FILTER_STATUS = 'status';
	const FILTER_PAYMENT = 'payment';
	const FILTER_AREA = 'area';
	const FILTER_VIEW = 'view';
	const FILTER_TYPE = 'type';

	const VIEW_TIME = 'time';
	const VIEW_DAY = 'day';
	const VIEW_MONTH = 'month';
	const VIEW_YEAR = 'year';
	const VIEW_MEMBER = 'member';
	const TYPE_CALL = 'call';
	const TYPE_ADS = 'ads';
	const TYPE_SHIP = 'ship';

	const COOKIE_FILTER_STATUS = '_filterStatus';

	public static function index($block, $action = null) {
		Language::load('order.lng');
		$team = Team::get(Auth::$data['team_id']);

		if(!$team) {
			return ServerErrorHandler::error_404();
		}

		switch($block) {
			case self::BLOCK_DELTAI:
				return self::block_detail($team, $action);

			case self::BLOCK_BUSY_CALLBACK:
				return self::block_busy_callback($team, $action);

			case self::BLOCK_CAN_NOT_CALL:
				return self::block_can_not_call($team, $action);

			case self::BLOCK_PENDING_DELIVERY:
				return self::block_pending_delivery($team, $action);

			case self::BLOCK_DELIVERING:
				return self::block_delivering($team, $action);

			case self::BLOCK_UNRECEIVED:
				return self::block_unreceived($team, $action);

			case self::BLOCK_RETURNED:
				return self::block_returned($team, $action);

			case self::BLOCK_DELIVERED:
				return self::block_delivered($team, $action);

			case self::BLOCK_REFUSE_BUY:
				return self::block_refuse_buy($team, $action);

			case self::BLOCK_WRONG_NUMBER:
				return self::block_wrong_number($team, $action);

			case self::BLOCK_DUPLICATE:
				return self::block_duplicate($team, $action);

			case self::BLOCK_TRASH:
				return self::block_trash($team, $action);

			default:
				return ServerErrorHandler::error_404();
		}
		
	}

	private static function block_detail($team, $order_id) {
		$title = lang('order', 'detail_order');
		$current_route = null;
		
		$order = Order::select([
			'(SELECT <name> FROM <core_provinces> WHERE <id> = <{table}.order_province_id>) AS <order_province>',
			'(SELECT <name> FROM <core_districts> WHERE <id> = <{table}.order_district_id>) AS <order_district>',
			'(SELECT <name> FROM <core_wards> WHERE <id> = <{table}.order_ward_id>) AS <order_ward>',
			'(SELECT <name> FROM <core_areas> WHERE <id> = <{table}.order_area_id>) AS <order_area>',
			'(SELECT count(<id>) FROM <{table}> AS <table_orders> WHERE <table_orders.id> != <{table}.id> AND (JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[0]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[1]\')) OR JSON_CONTAINS(<table_orders.order_phone>, JSON_EXTRACT(<{table}.order_phone>, \'$[2]\')))) AS <duplicate>'
		], false)::get([
			'id' => $order_id,
			'OR' => [
				'ads_user_id' => Auth::$data['id'],
				'call_user_id' => Auth::$data['id'],
				'ship_user_id' => Auth::$data['id']
			]
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
		$view_in_list = orderController::view_in_list($order);
		$referer = Request::referer();
		return View::render('order.detail', compact(
			'title',
			'current_route',
			'team',
			'order',
			'user_ads',
			'user_call',
			'user_ship',
			'order_phone',
			'view_in_list',
			'referer'
		));
	}

	public static function view_in_list($order) {

		$is_advertiser = UserPermission::is_advertisers();
		$is_caller = UserPermission::is_caller();
		$is_shipper = UserPermission::is_shipper();

		switch($order['status']) {
			case Order::STATUS_AGREE_BUY:
			case Order::STATUS_DELIVERY_DATE:
				if($is_shipper) {
					return RouteMap::get('shipper', ['block' => shipperController::BLOCK_PENDING_DELIVERY]);
				}
				if($is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_PENDING_DELIVERY]);
				}
				break;
			case Order::STATUS_CALLING:
				if($is_caller) {
					return RouteMap::get('caller', ['block' => callerController::BLOCK_CALLING]);
				}
				break;
			case Order::STATUS_PENDING_CONFIRM:
				if($is_caller) {
					return RouteMap::get('caller', ['block' => callerController::BLOCK_PENDING_CONFIRM]);
				}
				break;
			case Order::STATUS_BUSY_CALLBACK:
				if($is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_BUSY_CALLBACK]);
				}
				break;
			case Order::STATUS_CAN_NOT_CALL:
				if($is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_CAN_NOT_CALL]);
				}
				break;
			case Order::STATUS_DELIVERING:
				if($is_shipper || $is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_DELIVERING]);
				}
				break;
			case Order::STATUS_DELIVERED:
				if($is_shipper || $is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_DELIVERING]);
				}
				break;
			case Order::STATUS_RETURNED:
				if($is_shipper || $is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_RETURNED]);
				}
				break;
			case Order::STATUS_UNRECEIVED:
				if($is_shipper || $is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_UNRECEIVED]);
				}
				break;
			case Order::STATUS_REFUSE_BUY:
				if($is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_REFUSE_BUY]);
				}
				break;
			case Order::STATUS_WRONG_NUMBER:
				if($is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_WRONG_NUMBER]);
				}
				break;
			case Order::STATUS_DUPLICATE:
				if($is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_DUPLICATE]);
				}
				break;
			case Order::STATUS_TRASH:
				if($is_caller) {
					return RouteMap::get('order', ['block' => orderController::BLOCK_TRASH]);
				}
				break;
		}

		if($is_advertiser && $order['ads_user_id'] == Auth::$data['id']) {
			return RouteMap::get('advertiser');
		}

		return null;
	}

	public static function render_status($status) {
		$list = [
			Order::STATUS_PENDING_CONFIRM => '<span class="status-order pending-confirm">'.lang('system', 'order_pending_confirm').'</span>',
			Order::STATUS_CALLING => '<span class="status-order calling">'.lang('system', 'order_calling').'</span>',
			Order::STATUS_BUSY_CALLBACK => '<span class="status-order busy-call-back">'.lang('system', 'order_busy_callback').'</span>',
			Order::STATUS_WRONG_NUMBER => '<span class="status-order wrong-number">'.lang('system', 'order_wrong_number').'</span>',
			Order::STATUS_CAN_NOT_CALL => '<span class="status-order can-not-call">'.lang('system', 'order_can_not_call').'</span>',
			Order::STATUS_REFUSE_BUY => '<span class="status-order refuse-buy">'.lang('system', 'order_refuse_buy').'</span>',
			Order::STATUS_AGREE_BUY => '<span class="status-order agree-buy">'.lang('system', 'order_agree_buy').'</span>',
			Order::STATUS_DELIVERY_DATE => '<span class="status-order pending-delivery-date">'.lang('system', 'order_delivery_date').'</span>',
			Order::STATUS_DELIVERING => '<span class="status-order delivering">'.lang('system', 'order_delivering').'</span>',
			Order::STATUS_UNRECEIVED => '<span class="status-order unreceived">'.lang('system', 'order_unreceived').'</span>',
			Order::STATUS_RETURNED => '<span class="status-order returned">'.lang('system', 'order_returned').'</span>',
			Order::STATUS_DELIVERED => '<span class="status-order delivered">'.lang('system', 'order_delivered').'</span>',
			Order::STATUS_DUPLICATE => '<span class="status-order duplicate">'.lang('system', 'order_duplicate').'</span>',
			Order::STATUS_TRASH => '<span class="status-order trash">'.lang('system', 'order_trash').'</span>',
		];

		return isset($list[$status]) ? $list[$status] : $list;
	}


	public static function export_excel($orders) {
		
		if(!$orders) {
			return;
		}

		require_once INCLUDE_PATH.'/vendor/autoload.php';

		$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Date');
		$sheet->setCellValue('B1', 'Waybill');
		$sheet->setCellValue('C1', 'Product');
		$sheet->setCellValue('D1', 'Quantity');
		$sheet->setCellValue('E1', 'Price');
		$sheet->setCellValue('F1', 'Currency');
		$sheet->setCellValue('G1', 'Phone');
		$sheet->setCellValue('H1', 'First Name');
		$sheet->setCellValue('I1', 'Last Name');
		$sheet->setCellValue('J1', 'Address');
		$sheet->setCellValue('K1', 'Province/State');
		$sheet->setCellValue('L1', 'District/City');
		$sheet->setCellValue('M1', 'Ward/Commune');
		
		$boldFontStyle = [
			'font' => [
				'bold' => true,
			],
			'alignment' => [
				'horizontal' => PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				'vertical' => PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
			],
		];

		$sheet->getStyle('A1:M1')->applyFromArray($boldFontStyle);


		$rowTitle = 1;
		$colorTitle = '8bc34a';
		foreach (range('A', 'M') as $column) {
			$cellValue = $sheet->getCell($column . $rowTitle)->getValue();
			$cellWidth = $sheet->getColumnDimension($column)->getWidth();
			$newWidth = max(strlen($cellValue) * 1.5, $cellWidth, 20);
			$sheet->getColumnDimension($column)->setWidth($newWidth);

			$cellStyle = $sheet->getStyle($column . $rowTitle);
			$cellStyle->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($colorTitle);
		}
		
		$row = 2;
		foreach($orders as $order) {
			$sheet->setCellValue('A'.$row, date('Y-m-d', $order['created_at']));
			$sheet->setCellValue('B'.$row, $order['id']);
			$sheet->setCellValue('C'.$row, $order['product_name']);
			$sheet->setCellValue('D'.$row, $order['quantity']);
			$sheet->setCellValue('E'.$row, $order['price']);
			$sheet->setCellValue('F'.$row, $order['currency']);
			$sheet->setCellValue('G'.$row, implode(", ", json_decode($order['order_phone'], true)));
			$sheet->setCellValue('H'.$row, $order['order_first_name']);
			$sheet->setCellValue('I'.$row, $order['order_last_name']);
			$sheet->setCellValue('J'.$row, $order['order_address']);
			$sheet->setCellValue('K'.$row, $order['order_province']);
			$sheet->setCellValue('L'.$row, $order['order_district']);
			$sheet->setCellValue('M'.$row, $order['order_ward']);
			$sheet->getStyle('A'.$row.':M1'.$row)->applyFromArray([
				'alignment' => [
					'horizontal' => PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
					'vertical' => PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
				]
			]);
			
			$row++;
		}



		$date = date('d-m-Y_'.substr((string)microtime(), 1, 8));
		$date = str_replace(".", "", $date);
		$filename = "export_".$date.".xlsx";
		$filePath = __DIR__ . DIRECTORY_SEPARATOR . $filename;

		try {
			$writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			$writer->save($filePath);
		} catch(Exception $e) {
		}

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		if(file_exists($filePath)) {
			unlink($filePath);
		}
	}
}



?>