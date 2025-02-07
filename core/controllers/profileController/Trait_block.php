<?php

trait Trait_block {


    public static function block_infomation($user = null)
    {
        $title = lang('profile', 'infomation');

        $data_form = [
            'username' => $user ? $user['username'] : Auth::$data['username'],
            'name' => trim(Request::post(self::INPUT_FORM_NAME, $user ? $user['name'] : Auth::$data['name'])),
            'date_of_birth' => trim(Request::post(self::INPUT_FORM_DATE_OF_BIRTH, $user ? $user['date_of_birth'] : Auth::$data['date_of_birth'])),
            'email' => $user ? $user['email'] : Auth::$data['email'],
            'sex' => trim(Request::post(self::INPUT_FORM_SEX, $user ? $user['sex'] : Auth::$data['sex'])),
            'facebook' => trim(Request::post(self::INPUT_FORM_FACEBOOK, $user ? $user['facebook'] : Auth::$data['facebook']))
        ];

        $error = null;
        $success = null;

        $form_action = Request::post(self::INPUT_FORM_ACTION, null);

        $is_edit = !$user;

        if($is_edit && Security::validate() == true)
        {
            switch($form_action)
            {
                case self::ACTION_INFOMATION:
                    if(!in_array($data_form['sex'], [User::SEX_UNKNOWN, User::SEX_MALE, User::SEX_FEMALE]))
                    {
                        $data_form['sex'] = User::SEX_UNKNOWN;
                    }
        
                    if($data_form['date_of_birth'] && !preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4,".strlen(date('Y'))."})$/u", $data_form['date_of_birth']))
                    {
                        $error = lang('profile', 'error_birth');
                    }
                    else if($data_form['facebook'] && !preg_match("/^https?:\/\/((.*)\.)?(fb\.com|facebook\.com)\/(.*?)$/u", $data_form['facebook']))
                    {
                        $error = lang('profile', 'error_facebook');
                    }
                    else
                    {
                        if(User::update(Auth::$id, $data_form))
                        {
                            Auth::trigger_data('name', $data_form['name']);
                            $success = lang('system', 'success_update');
                        }
                        else
                        {
                            $error = lang('system', 'error_update');
                        }
                    }
                    break;

                case self::ACTION_CHANGE_EMAIL:
                    $newEmail = trim(Request::post(self::INPUT_FORM_EMAIL, Auth::$data['email']));
                    $password = Request::post(Auth::INPUT_PASSWORD, null);
        
                    if(Auth::verify_password($password, Auth::$data['password']) != true)
                    {
                        $error = lang('forgot_password', 'error_password');
                    }
                    else if($newEmail == "")
                    {
                        $error = lang('forgot_password', 'error_email_empty');
                    }
                    else if(!Auth::check_type_email($newEmail))
                    {
                        $error = lang('forgot_password', 'error_email_format');
                    }
                    else if(User::has(['email[~]' => $newEmail, 'id[!]' => Auth::$id]))
                    {
                        $error = lang('errors', 'email_exists');
                    }
                    else 
                    {
                        if(User::update(Auth::$id, ['email' => $newEmail]) > 0)
                        {
                            $data_form['email'] = $newEmail;
                            $success = lang('system', 'success_update');
                        }
                        else
                        {
                            $error = lang('system', 'error_update');
                        }				
                    }
                    break;

                case self::ACTION_CHANGE_USERNAME:
                    $newUsername = trim(Request::post(self::INPUT_FORM_USERNAME, Auth::$data['username']));
                    $password = Request::post(Auth::INPUT_PASSWORD, null);
        
                    if(Auth::verify_password($password, Auth::$data['password']) != true)
                    {
                        $error = lang('forgot_password', 'error_password');
                    }
                    else if(!Auth::check_length_username($newUsername))
                    {
						$error = lang('register', 'error_username_length', [
							'min' => Auth::USERNAME_MIN_LENGTH,
							'max' => Auth::USERNAME_MAX_LENGTH
						]);
                    }
                    else if(!Auth::check_type_username($newUsername))
                    {
                        $error = lang('profile', 'error_username_type');
                    }
                    else if(User::has(['username[~]' => $newUsername, 'id[!]' => Auth::$id]))
                    {
                        $error = lang('errors', 'username_exists');
                    }
                    else 
                    {
                        if(User::update(Auth::$id, ['username' => $newUsername]) > 0)
                        {
                            $data_form['username'] = $newUsername;
                            $success = lang('system', 'success_update');
                        }
                        else
                        {
                            $error = lang('system', 'error_update');
                        }				
                    }
                    break;
            }            
        }


        $data_form['is_edit'] = $is_edit;
        $data_form['success'] = $success;
        $data_form['error'] = $error;

        return [
            'title' => $title,
            'view' => 'profile.block.infomation',
            'data' => $data_form
        ];
    }

    public static function block_billing($user = null)
    {
        $title = lang('profile', 'billing');

        $success = null;
        $error = null;

        $billing = User::get_billing($user);

        $account_name = trim(Request::post(self::INPUT_BILLING_ACCOUNT_NAME, $billing['account_name']));
        $account_number = trim(Request::post(self::INPUT_BILLING_ACCOUNT_NUMBER, $billing['account_number']));
        $bank_name = trim(Request::post(self::INPUT_BILLING_BANK_NAME, $billing['bank_name']));
        $bank_branch = trim(Request::post(self::INPUT_BILLING_BANK_BRANCH, $billing['bank_branch']));

        $form_action = Request::post(self::INPUT_FORM_ACTION, null);

        $is_edit = !$user;

        if($is_edit && Security::validate() == true)
        {
            if($form_action == self::ACTION_BILLING)
            {
                if(User::update_billing(Auth::$id, [
                    'account_name' => $account_name,
                    'account_number' => $account_number,
                    'bank_name' => $bank_name,
                    'bank_branch' => $bank_branch
                ]) == true)
                {
                    $success = lang('system', 'success_save');
                }
                else
                {
                    $error = lang('system', 'default_error');
                }
            }
        }


        return [
            'title' => $title,
            'view' => 'profile.block.billing',
            'data' => compact(
                'success',
                'error',
                'is_edit',
                'account_name', 
                'account_number', 
                'bank_name',
                'bank_branch'
            )
        ];
    }

    public static function block_logindevice()
    {
        $title = 'Thiết bị đã đăng nhập';

        $error = null;
        $success = null;

        $form_action = Request::post(self::INPUT_FORM_ACTION, null);

        if(Security::validate() == true)
        {
            switch($form_action)
            {
                case self::ACTION_LOGOUT_ALL:
                    if(Auth::delete_auth_session() === true)
                    {
                        $success = lang('success', 'logout_all');
                    }
                    else
                    {
                        $error = lang('error', 'logout_all');
                    }
                    break;

                case self::ACTION_LOGOUT_DEVICE:

                    $auth_session = Request::post(self::INPUT_FORM_AUTH_SESSION, null);

                    if($auth_session != "" && Auth::delete_auth_session($auth_session) === true)
                    {
                        $success = lang('success', 'logout');
                    }
                    else
                    {
                        $error = lang('error', 'logout');
                    }
                    break;
            }
        }

        $auth_sessions = array_filter(explode("\n", trim(Auth::$data['auth_session'])));
		$count = count($auth_sessions);

        new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();

        $auth_sessions = array_values(array_slice($auth_sessions, $pagination['start'], $pagination['limit']));

        $pass_auth_session = Auth::pass_auth_session();

        if($auth_sessions)
        {
            $auth_sessions = array_map(function($session) use ($pass_auth_session) {
                $auth_session = Auth::escape_session($session);
                $decrypt = Auth::decrypt_login($auth_session, $pass_auth_session);
                $decrypt['user_agent'] = getBrowser($decrypt['user_agent']);
                $decrypt['auth_session'] = $auth_session;
                return $decrypt;
            }, array_reverse($auth_sessions));            

        }

        return [
            'title' => $title,
            'view' => 'profile.block.logindevice',
            'data' => compact('success', 'error', 'auth_sessions', 'pagination', 'count')
        ];
    }

    public static function block_changepassword()
    {
        $title = lang('profile', 'change_password');

        $error = null;
        $success = null;
        $new_password = trim(Request::post(self::INPUT_FORM_NEW_PASSWORD, null));
        $confirm_password = trim(Request::post(self::INPUT_FORM_CONFIRM_PASSWORD, null));
        $password = trim(Request::post(self::INPUT_FORM_PASSWORD, null));

        $form_action = Request::post(self::INPUT_FORM_ACTION, null);

        if(Security::validate() == true)
        {
            if($form_action == self::ACTION_CHANGEPASSWORD)
            {
                if(Auth::verify_password($password, Auth::$data['password']) != true)
                {
                    $error = lang('forgot_password', 'error_password');
                }
                else if(!Auth::check_length_password($new_password))
                {
                    $error = lang('forgot_password', 'error_password_length', [
                        'min' => Auth::PASSWORD_MIN_LENGTH,
                        'max' => Auth::PASSWORD_MAX_LENGTH
                    ]);
                }
                else if($new_password !== $confirm_password)
                {
                    $error = lang('profile', 'error_password_reinput');
                }
                else
                {
                    if(Controller::load('authController@change_password', $new_password) == true)
                    {
                        $success = lang('success', 'change_password');
                    }
                    else
                    {
                        $error = lang('system', 'default_error');
                    }
                }
            }
        }

        return [
            'title' => $title,
            'view' => 'profile.block.changepassword',
            'data' => compact('error', 'success')
        ];
    }

    public static function block_settings()
    {
        $title = lang('profile', 'setting');

        $error = null;
        $success = null;

        $language = trim(Request::post(self::INPUT_FORM_LANGUAGE, User::get_setting('language')));
        $limit_page = intval(Request::post(self::INPUT_FORM_PAGE, User::get_setting('page')));

        $form_action = Request::post(self::INPUT_FORM_ACTION, null);

        if(Security::validate() == true)
        {
            if($form_action == self::ACTION_SETTINGS)
            {
                if(User::update_setting(Auth::$id, [
                    'page' => $limit_page,
                    'language' => $language
                ]) == true)
                {
                    $success = lang('system', 'success_save');
                }
                else
                {
                    $error = lang('system', 'default_error');
                }
            }
        }

        return [
            'title' => $title,
            'view' => 'profile.block.settings',
            'data' => compact('error', 'success', 'limit_page', 'language')
        ];
    }
    

    public static function block_statistics($user) {
        $title = lang('profile', 'statistic');

        $is_caller = UserPermission::is_caller($user);
		$is_shipper = UserPermission::is_shipper($user);
		$is_advertiser = UserPermission::is_advertisers($user);	

        $team = Team::get($user['team_id']);

		$currency = null;
		if($team) {
			$currency = _echo($team['currency']);
		}

		$list_product = Product::list([
			'[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
				'ids' => $team['product_id']
			],
			'status' => Product::STATUS_ACTIVE
		]);
        $filter_type = trim(Request::get(orderController::FILTER_TYPE, null));
		$filter_product = trim(Request::get(orderController::FILTER_PRODUCT, InterFaceRequest::OPTION_ALL));
		$filter_view = trim(Request::get(orderController::FILTER_VIEW, orderController::VIEW_DAY));
		$filter_time = trim(Request::get(orderController::FILTER_TIME, InterFaceRequest::OPTION_ALL));
		$startDate = trim(Request::get(InterFaceRequest::START_DATE, date('d-m-Y')));
		$endDate = trim(Request::get(InterFaceRequest::END_DATE, date('d-m-Y')));
		$startDate_convert = strtotime(implode('-', array_reverse(explode('-', $startDate))).' 00:00:00');
		$endDate_convert = strtotime(implode('-', array_reverse(explode('-', $endDate))).' 23:59:59');


		$where = [
			'country_id' => $team['country_id']
		];


		if($filter_product != InterFaceRequest::OPTION_ALL) {
			$where['product_id'] = $filter_product;
		}

		if($filter_time != InterFaceRequest::OPTION_ALL) {
			$where['[RAW] <{table}.created_at> >= :startDate AND <{table}.created_at> <= :endDate'] = [
				'startDate' => $startDate_convert,
				'endDate' => $endDate_convert
			];
		}

		$join_range = [];
		$select_range = [];
		$order_Type = 'ASC';

		switch($team['type']) {
			case Team::TYPE_ONLY_ADS:
				$filter_type = orderController::TYPE_ADS;
				break;

			case Team::TYPE_ONLY_CALL:
				$filter_type = orderController::TYPE_CALL;

				break;

			case Team::TYPE_ONLY_SHIP:
				$filter_type = orderController::TYPE_SHIP;
				break;

			case Team::TYPE_FULLSTACK:

				if($filter_type) {
					break;
				}

				if($is_caller) {
					$filter_type = orderController::TYPE_CALL;
				} else if($is_shipper) {
					$filter_type = orderController::TYPE_SHIP;
				} else {
					$filter_type = orderController::TYPE_ADS;
				}
				break;
		}

		switch($filter_type) {
			case orderController::TYPE_ADS:
				$where['ads_team_id'] = $team['id'];
                $where['ads_user_id'] = $user['id'];
				$select_range = [
					'<ads_user_id> AS <user_id>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_DELIVERED.' AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ads>) ELSE 0 END) AS <total_earning>',
					'SUM(CASE WHEN (<status> IN ('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERY_DATE.','.Order::STATUS_DELIVERING.')  AND <payment_ads> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ads>) ELSE 0 END) AS <total_holding>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_RETURNED.' AND <payment_ads> IN ('.Order::IS_PAYMENT.')) THEN (<deduct_leader_ads>) ELSE 0 END) AS <total_deduct>'
				];
				break;

			case orderController::TYPE_CALL:
				$where['call_team_id'] = $team['id'];
                $where['call_user_id'] = $user['id'];
				$select_range = [
					'<call_user_id> AS <user_id>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_DELIVERED.' AND <payment_call> = '.Order::IS_NOT_PAYMENT.') THEN (<quantity> * <profit_leader_call>) ELSE 0 END) AS <total_earning>',
					'SUM(CASE WHEN (<status> IN ('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERY_DATE.','.Order::STATUS_DELIVERING.')  AND <payment_call> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_call>) ELSE 0 END) AS <total_holding>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_RETURNED.' AND <payment_call> IN ('.Order::IS_PAYMENT.')) THEN (<quantity> * <deduct_leader_call>) ELSE 0 END) AS <total_deduct>'
				];
				break;

			case orderController::TYPE_SHIP:
				$where['ship_team_id'] = $team['id'];
                $where['ship_user_id'] = $user['id'];
				$select_range = [
					'<ship_user_id> AS <user_id>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_DELIVERED.' AND <payment_ship> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ship>) ELSE 0 END) AS <total_earning>',
					'SUM(CASE WHEN (<status> IN ('.Order::STATUS_AGREE_BUY.','.Order::STATUS_DELIVERY_DATE.','.Order::STATUS_DELIVERING.')  AND <payment_ship> = '.Order::IS_NOT_PAYMENT.') THEN (<profit_leader_ship>) ELSE 0 END) AS <total_holding>',
					'SUM(CASE WHEN (<status> = '.Order::STATUS_RETURNED.' AND <payment_ship> IN ('.Order::IS_PAYMENT.')) THEN (<deduct_leader_ship>) ELSE 0 END) AS <total_deduct>'
				];
				break;
		}



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

		$where = array_merge($where, [
			'GROUP' => 'order_range',
			'ORDER' => [
				'[RAW] <order_range>' => $order_Type
			]
		]);

		$count = Order::count([], "SELECT COUNT(*) FROM (SELECT ".implode(',', $select_range)." FROM <{table}> ".App::$database->build_raw_where(Order::build_where($where)).") AS <total>");

		$select_range[] = 'SUM(CASE WHEN <status> IN ('.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.', '.Order::STATUS_DELIVERING.') THEN <quantity> ELSE 0 END) AS <pre_sales>';
		$select_range[] = 'SUM(CASE WHEN <status> = '.Order::STATUS_DELIVERED.' THEN <quantity> ELSE 0 END) AS <sales>';
		$select_range[] = 'COUNT(<id>) AS <total_order>';
        $select_range[] = 'SUM(CASE WHEN (<status> IN ('.Order::STATUS_DELIVERING.', '.Order::STATUS_AGREE_BUY.', '.Order::STATUS_DELIVERY_DATE.')) THEN 1 ELSE 0 END) AS <total_pending_delivery>';
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

		return [
			'title' => lang('profile', 'statistic').' - '.$user['username'],
			'view' => 'profile.block.statistics',
			'data' => compact(
				'team',
				'currency',
				'list_product',
				'filter_type',
				'filter_product',
				'filter_view',
				'filter_time',
				'startDate',
				'endDate',
				'is_advertiser',
				'is_caller',
				'is_shipper',
				'list_status',
				'filter_status',
				'count',
				'list_statistics',
				'pagination'
			)
		];
    }

}

?>