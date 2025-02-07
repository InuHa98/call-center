<?php 

class blacklistController {
    const ACTION_ADD = 'Add';
    const ACTION_EDIT = 'Edit';
    const ACTION_DELETE = 'Delete';

    const INPUT_COUNTRY = 'country';
    const INPUT_REASON = 'reason';
    const INPUT_NUMBER_PHONE = 'number_phone';

    public static function index($action)
    {
        if(!UserPermission::access_blacklist()) {
            return ServerErrorHandler::error_403();
        }

		Language::load('blacklist.lng');

        switch($action) {
            case self::ACTION_ADD:
                return self::add();

            case self::ACTION_EDIT:
                return self::edit();

            case self::ACTION_DELETE:
                return self::delete();

            default:
                return self::list();
        }
    }

    private static function list() {
        $success = null;
        $error = null;
		$title = lang('blacklist', 'txt_list');

		$filter_keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$filter_country = trim(Request::get(InterFaceRequest::COUNTRY, InterFaceRequest::OPTION_ALL));

		$where = [];

		if($filter_keyword != '') {
			$where['number_phone[~]'] = '%"'.$filter_keyword.'"%';
		}

		if($filter_country != InterFaceRequest::OPTION_ALL) {
			$where['country_id'] = $filter_country;
		}

		$count = Blacklist::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$list_blacklist = Blacklist::list(array_merge($where, [
            'ORDER' => [
                '[RAW] <country_name>' => 'ASC',
                'created_at' => 'DESC'
            ],
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));


        $list_country = Country::list();

		return View::render('blacklist.list', compact(
			'title',
			'success',
			'error',
			'filter_country',
			'filter_keyword',
            'list_country',
            'count',
            'list_blacklist',
			'pagination'
		));
    }

    private static function add() {
        $success = null;
		$error = null;
		$title = lang('blacklist', 'txt_add');

		$is_edit = false;

		$country_id = intval(Request::post(self::INPUT_COUNTRY, null));
		$number_phone = Request::post(self::INPUT_NUMBER_PHONE, []);
		$reason = trim(Request::post(self::INPUT_REASON, null));

		if(Security::validate() == true) {

			$country = Country::get($country_id);

			$number_phone = filter_phone($number_phone, $country);

			if(!$number_phone) {
				$error = lang('blacklist', 'error_phone');
			}
			else if(!$country) {
				$error = lang('placeholder', 'select_country');
			}
			else {
				if(Blacklist::create($number_phone, $country['id'], $reason)) {
					Alert::push([
						'message' => lang('system', 'success_create'),
						'type' => 'success',
						'timeout' => 3000
					]);
					return redirect_route('blacklist');
				} else {
					$error = lang('system', 'error_create');
				}
			}
		}

		$list_country = Country::list();
		

		return View::render('blacklist.add_edit', compact(
			'title',
			'is_edit',
			'success',
			'error',
			'list_country',
			'country_id',
			'number_phone',
			'reason'
		));
    }

    private static function edit() {
        $success = null;
		$error = null;
		$title = lang('blacklist', 'txt_edit');

		$is_edit = true;

        $id = intval(Request::get(InterFaceRequest::ID, null));
        $blacklist = Blacklist::get(['id' => $id]);

        if(!$blacklist) {
            return redirect_route('blacklist');
        }

		$country_id = intval(Request::post(self::INPUT_COUNTRY, $blacklist['country_id']));
		$number_phone = Request::post(self::INPUT_NUMBER_PHONE, json_decode($blacklist['number_phone'], true));
		$reason = trim(Request::post(self::INPUT_REASON, $blacklist['reason']));

		if(Security::validate() == true) {

			$country = Country::get($country_id);

			$number_phone = filter_phone($number_phone, $country);

			if(!$number_phone) {
				$error = lang('blacklist', 'error_phone');
			}
			else if(!$country) {
				$error = lang('placeholder', 'select_country');
			}
			else {
				if(Blacklist::update($blacklist['id'], [
                    'country_id' => $country_id,
                    'number_phone' => $number_phone,
                    'reason' => $reason
                ])) {
					$success = lang('system', 'success_update');
				} else {
					$error = lang('system', 'error_update');
				}
			}
		}

		$list_country = Country::list();
		

		return View::render('blacklist.add_edit', compact(
			'title',
			'is_edit',
			'success',
			'error',
			'blacklist',
			'list_country',
			'country_id',
			'number_phone',
			'reason'
		)); 
    }

    private static function delete() {
        $id = intval(Request::get(InterFaceRequest::ID, 0));
        $blacklist = Blacklist::get([
            'id' => $id
        ]);

        if(!$blacklist) {
            Alert::push([
                'type' => 'error',
                'message' => lang('blacklist', 'error_not_found')
            ]);
        } else {
            if(Blacklist::delete($blacklist['id'])) {
                Alert::push([
                    'type' => 'success',
                    'message' => lang('system', 'success_delete')
                ]);
            } else {
                Alert::push([
                    'type' => 'error',
                    'message' => lang('system', 'default_error')
                ]);
            }					
        }

        return redirect(Request::referer(RouteMap::get('blacklist')));
    }
}

?>