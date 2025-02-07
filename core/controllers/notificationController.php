<?php


class notificationController {

	public const SUBMIT_NAME = 'form_submit';

	public const TYPE_SEEN = 'seen';
	public const TYPE_UNSEEN = 'unseen';

	public const NAME_FORM_ACTION = 'form_action';

	public const ACTION_MAKE_SEEN = 'make_seen';
	public const ACTION_MAKE_UNSEEN = 'make_unseen';
	public const ACTION_DELETE = 'delete_notifi';

	public const INPUT_ID = 'id_notifi';

	public const MAX_ITEM = 20; 


	public function __construct()
	{
		if(Auth::$isLogin != true)
		{
			return Router::redirect('*', RouteMap::get('dashboard'));
		}
		Language::load('notification.lng');
	}

	public function index($id = null)
	{
		if($id != "" && !in_array($id, [self::TYPE_SEEN, self::TYPE_UNSEEN]))
		{
			return $this->get($id);
		}

		

		$title = lang('notification', 'all_notifi');
		
		$error = null;
		$success = null;

		
		if(Security::validate() == true)
        {
			$id_notification = Request::post(self::INPUT_ID, null);
			$form_action = Request::post(self::NAME_FORM_ACTION, null);
            switch($form_action)
            {
				case self::ACTION_MAKE_SEEN:
					if(Notification::make_seen($id_notification) != true)
					{
						$error = lang('system', 'default_error');
					}
					break;

				case self::ACTION_MAKE_UNSEEN:

					if(Notification::make_unseen($id_notification) != true)
					{
						$error = lang('system', 'default_error');
					}
					break;

                case self::ACTION_DELETE:
					if(Notification::delete($id_notification) == true)
					{
						$success = lang('system', 'success_delete');
					}
					else
					{
						$error = lang('system', 'default_error');
					}
                    break;
            }
			View::addData('_count_notification', Notification::count_new());
			View::addData('_notification', Notification::get_list_new());   
        }

		$where = [
			'user_id' => Auth::$id
		];

		$type = $id;
		switch($type) {
			case self::TYPE_SEEN:
				$where['seen'] = Notification::SEEN;
				$title = lang('notification', 'notifi_seen');
				break;

			case self::TYPE_UNSEEN:
				$where['seen'] = Notification::UNSEEN;
				$title = lang('notification', 'notifi_unseen');
				break;
			default:
				$type = null;
				break;
		}

		$count = Notification::count(array_merge($where, [
			'seen' => Notification::UNSEEN
		]));

		if($type != self::TYPE_SEEN && $count > 0)
		{
			$title .= ' ('.$count.')';
		}
		

        new Pagination($count, self::MAX_ITEM);
		$pagination = Pagination::get();

        $notification_data = Notification::list(array_merge($where, [
			'ORDER' => [
				'created_at' => 'DESC',
				'seen' => 'ASC'
			],
            'LIMIT' => [
                $pagination['start'], $pagination['limit']
            ]
        ]));

		return View::render('notification.index', compact('title', 'error', 'success', 'type', 'count', 'notification_data', 'pagination'));
	}
	

	private function get($id = null)
	{
		$notification = Notification::get([
			'id' => $id,
			'user_id' => Auth::$id
		]);

		if(!$notification)
		{
			return Router::redirect('*', RouteMap::get('notification'));
		}

		$user_from = User::get($notification['from_user_id']);

		$title = lang('notification', 'title');
		
		$error = null;
		$success = null;

		if(Security::validate() == true)
        {
			$id_notification = Request::post(self::INPUT_ID, null);
			$form_action = Request::post(self::NAME_FORM_ACTION, null);
            switch($form_action)
            {
                case self::ACTION_DELETE:
					if(Notification::delete($id_notification) == true)
					{
						return redirect_route('notification');
					}
					else
					{
						$error = lang('system', 'default_error');
					}
                    break;
            }
        }

		Notification::make_seen($notification['id']);
		View::addData('_count_notification', Notification::count_new());
		View::addData('_notification', Notification::get_list_new());

		$referrer = Request::referer(RouteMap::get('notification'));

		return View::render('notification.get', compact('title', 'error', 'success', 'referrer', 'notification', 'user_from'));
	}



	public const TYPE_CHANGE_USERNAME = 1;
	public const TYPE_CHANGE_PASSWORD = 2;
	public const TYPE_CHANGE_EMAIL = 3;
	public const TYPE_BAN_USER = 4;
	public const TYPE_UNBAN_USER = 5;
	public const TYPE_CHANGE_NAME_TEAM = 6;
	public const TYPE_CHANGE_LEADER_TEAM = 7;
	public const TYPE_BAN_TEAM = 8;
	public const TYPE_UNBAN_TEAM = 9;
	public const TYPE_SET_TEAM = 10;
	public const TYPE_TAKEN_ORDER = 11;
	public const TYPE_RETURNED_ORDER = 12;
	public const TYPE_UPDATE_ORDER = 13;
	public const TYPE_USER_BAN_TEAM = 14;
	public const TYPE_USER_UNBAN_TEAM = 15;
	public const TYPE_LEADER_INVOICE = 16;
	public const TYPE_MEMBER_INVOICE = 17;
	public const TYPE_AUTO_BAN = 18;

	public static function renderHTML($notification = [], $strip_tags = false)
	{
		if(!isset($notification['type']) || !isset($notification['data']))
		{
			return null;
		}

		$data = json_decode($notification['data'], true);

		$user = [
			'id' => $notification['user_from_id'],
			'name' => $notification['user_from_name'],
			'username' => $notification['user_from_username'],
			'avatar' => $notification['user_from_avatar'],
			'is_ban' => $notification['user_from_is_ban'],
			'role_color' => $notification['user_from_role_color']
		];

		$html = null;
		switch($notification['type'])
		{

			case self::TYPE_CHANGE_USERNAME:
				$html = lang('notification', 'change_username', [
					'username' => _echo($data['username'])
				]);
				break;

			case self::TYPE_CHANGE_PASSWORD:
				$html = lang('notification', 'change_password', [
					'password' => _echo($data['password'])
				]);
				break;

			case self::TYPE_CHANGE_EMAIL:
				$html = lang('notification', 'change_email', [
					'email' => _echo($data['email'])
				]);
				break;

			case self::TYPE_BAN_USER:
				$html = lang('notification', 'ban_user');
				if($data['reason']) {
					$html .= lang('notification', 'reason', [
						'reason' => _echo($data['reason'], true)
					]);
				}
				break;

			case self::TYPE_UNBAN_USER:
				$html = lang('notification', 'unban_user');
				break;	

			case self::TYPE_CHANGE_NAME_TEAM:
				$html = lang('notification', 'change_name_team', [
					'name' => _echo($data['name'])
				]);
				break;

			case self::TYPE_CHANGE_LEADER_TEAM:
				$url = '<a class="btn btn-outline-info btn--small margin-t-2 margin-l-2" href="'.RouteMap::get('team', ['id' => $data['team_id']]).'">'._echo($data['team_name']).'</a>';
				if($data['user_id'] == Auth::$data['id']) {
					$html = lang('notification', 'change_leader_team', [
						'url' => $url
					]);
				} else {
					$html = lang('notification', 'change_leader_team_user', [
						'url' => $url
					]);
				}
				break;

			case self::TYPE_BAN_TEAM:
				$html = lang('notification', 'ban_team');
				if($data['reason']) {
					$html .= lang('notification', 'reason', [
						'reason' => _echo($data['reason'], true)
					]);
				}
				break;

			case self::TYPE_UNBAN_TEAM:
				$html = lang('notification', 'unban_team');
				break;

			case self::TYPE_SET_TEAM:
				$html = lang('notification', 'set_team', [
					'name' => _echo($data['name'])
				]);
				break;

			case self::TYPE_TAKEN_ORDER:
				$html = lang('notification', 'taken_order', [
					'link' => RouteMap::get('order', ['block' => orderController::BLOCK_DELTAI, 'action' => $data['order_id']]),
					'id' => $data['order_id'],
					'from' => _echo($notification['user_from_username'])
				]);
				break;

			case self::TYPE_RETURNED_ORDER:

				$deduct_ads = isset($data['ads']) ? $data['ads'] : 0;
				$deduct_call = isset($data['call']) ? $data['call'] : 0;
				$deduct_ship = isset($data['ship']) ? $data['ship'] : 0;

				$insert = '<span class="badge rounded-pill bg-danger">'.($deduct_ads + $deduct_call + $deduct_ship).' '._echo($data['currency']).'</span> (';

				if($deduct_ads) {
					$insert .= ' <i class="fas fa-ad"></i> <span class="badge rounded-pill bg-danger">-'.$deduct_ads.'</span> ';
				}
				if($deduct_call) {
					$insert .= ' <i class="fas fa-headset"></i> <span class="badge rounded-pill bg-danger">-'.$deduct_call.'</span> ';
				}
				if($deduct_ship) {
					$insert .= ' <i class="fas fa-shipping-fast"></i> <span class="badge rounded-pill bg-danger">-'.$deduct_ship.'</span> ';
				}
				$insert .= ') ';

				$html = lang('notification', 'return_order', [
					'link' => RouteMap::get('order', ['block' => orderController::BLOCK_DELTAI, 'action' => $data['order_id']]),
					'id' => $data['order_id']
				]);
				break;

			case self::TYPE_UPDATE_ORDER:
				$html = lang('notification', 'update_order', [
					'link' => RouteMap::get('order', ['block' => orderController::BLOCK_DELTAI, 'action' => $data['order_id']]),
					'id' => $data['order_id']
				]);
				break;

			case self::TYPE_USER_BAN_TEAM:
				$html = lang('notification', 'user_ban_team');
				if($data['reason']) {
					$html .= lang('notification', 'reason', [
						'reason' => _echo($data['reason'], true)
					]);
				}
				break;

			case self::TYPE_USER_UNBAN_TEAM:
				$html = lang('notification', 'user_unban_team');
				break;

			case self::TYPE_LEADER_INVOICE:
				$html = lang('notification', 'leader_invoice', [
					'link' => RouteMap::get('team', ['id' => $data['team_id'], 'block' => teamController::BLOCK_PAYMENT])
				]);

				break;

			case self::TYPE_MEMBER_INVOICE:
				$html = lang('notification', 'member_invoice', [
					'link' => RouteMap::get('payment', ['block' => paymentController::BLOCK_HISTORY])
				]);
				break;

			case self::TYPE_AUTO_BAN:
				$html = lang('notification', 'auto_ban', [
					'rate' => $data['rate']
				]);
				break;

			default:
				return null;
		}
		return $strip_tags ? strip_tags($html) : $html;
	}


	public static function insertHiddenAction($action_name)
	{
		return '<input type="hidden" name="'.self::NAME_FORM_ACTION.'" value="'.$action_name.'">';
	}

	public static function insertHiddenID($id)
	{
		return '<input type="hidden" name="'.self::INPUT_ID.'" value="'.$id.'">';
	}
}





?>