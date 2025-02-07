<?php

trait Block_currency {
	private static function block_currency($action) {
		$success = null;
		$error = null;

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_curency_add')) {
					break;
				}
				$title = lang('system_currency', 'txt_add');
				$txt_description = $title;

				$name = strtoupper(trim(Request::post(self::INPUT_NAME, null)));
				$exchange_rate = abs(floatval(Request::post(self::INPUT_RATE, 0)));


				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_currency', 'error_name');
					}
					else if(Currency::has([
						'name[~]' => $name
					])) {
						$error = lang('system_currency', 'error_name_exists');
					}
					else {

						if(Currency::create($name, $exchange_rate)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_CURRENCY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.currency.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'exchange_rate'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_curency_edit')) {
					break;
				}

				$title = lang('system_currency', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$currency = Currency::get([
					'id' => $id
				]);

				if(!$currency) {
					return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_CURRENCY]);
				}

				$name = strtoupper(trim(Request::post(self::INPUT_NAME, $currency['name'])));
				$exchange_rate = abs(floatval(Request::post(self::INPUT_RATE, $currency['exchange_rate'])));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_currency', 'error_name');
					}
					else if(Currency::has([
						'id[!]' => $currency['id'],
						'name[~]' => $name
					])) {
						$error = lang('system_currency', 'error_name_exists');
					}
					else {
						if(Currency::update($currency['id'], [
							'name' => $name,
							'exchange_rate' => $exchange_rate
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.currency.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'exchange_rate'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_curency_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$currency = Currency::get([
					'id' => $id
				]);

				if(!$currency) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_currency', 'error_not_found')
					]);
				} else if(Country::has([
					'currency_id' => $currency['id']
				])) {
					Alert::push([
						'type' => 'warning',
						'message' => lang('system_currency', 'error_delete')
					]);
				} else {
					if(Currency::delete($currency['id'])) {
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


				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_CURRENCY])));

			default:
				$title = lang('admin_panel', 'system_currency');

				$count = Currency::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$currency_list = Currency::list([
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_create = UserPermission::has('admin_curency_add');
				$is_access_edit = UserPermission::has('admin_curency_edit');
				$is_access_delete = UserPermission::has('admin_curency_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.currency.index',
					'data' => compact(
						'count',
						'currency_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_CURRENCY]);
	}
}

?>