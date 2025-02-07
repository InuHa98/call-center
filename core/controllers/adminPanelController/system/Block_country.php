<?php

trait Block_country {
	private static function block_country($action) {
		$success = null;
		$error = null;

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_country_add')) {
					break;
				}
				$title = lang('system_country', 'txt_add');
				$txt_description = $title;

				$name = trim(Request::post(self::INPUT_NAME, null));
				$code = trim(Request::post(self::INPUT_CODE, null));
				$currency_id = intval(Request::post(self::INPUT_CURRENCY, null));
				$phone_code = trim(Request::post(self::INPUT_PHONE_CODE, null));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_country', 'error_name');
					}
					else if(!$code) {
						$error = lang('system_country', 'error_code');
					}
					else if(!Currency::has([
						'id' => $currency_id
					])) {
						$error = lang('system_country', 'error_currency');
					}
					else if(!preg_match("/^\+[0-9+]{1,3}$/", $phone_code)) {
						$error = lang('system_country', 'error_phone_code');
					}
					else if(Country::has([
						'name[~]' => $name
					])) {
						$error = lang('system_country', 'error_name_exists');
					}
					else {

						if(Country::create($name, $code, $currency_id, $phone_code)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_COUNTRY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$list_currency = Currency::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.country.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_currency',
						'name',
						'code',
						'currency_id',
						'phone_code'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_country_edit')) {
					break;
				}

				$title = lang('system_country', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$country = Country::get([
					'id' => $id
				]);

				if(!$country) {
					return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_COUNTRY]);
				}

				$name = trim(Request::post(self::INPUT_NAME, $country['name']));
				$code = trim(Request::post(self::INPUT_CODE, $country['code']));
				$currency_id = intval(Request::post(self::INPUT_CURRENCY, $country['currency_id']));
				$phone_code = trim(Request::post(self::INPUT_PHONE_CODE, $country['phone_code']));



				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_country', 'error_name');
					}
					else if(!$code) {
						$error = lang('system_country', 'error_code');
					}
					else if(!Currency::has([
						'id' => $currency_id
					])) {
						$error = lang('system_country', 'error_currency');
					}
					else if(!preg_match("/^\+[0-9+]{1,3}$/", $phone_code)) {
						$error = lang('system_country', 'error_phone_code');
					}
					else if(Country::has([
						'id[!]' => $country['id'],
						'name[~]' => $name
					])) {
						$error = lang('system_country', 'error_name_exists');
					}
					else {
						if(Country::update($country['id'], [
							'name' => $name,
							'code' => $code,
							'currency_id' => $currency_id,
							'phone_code' => $phone_code
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$list_currency = Currency::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.country.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_currency',
						'name',
						'code',
						'currency_id',
						'phone_code'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_country_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$country = Country::get([
					'id' => $id
				]);

				if(!$country) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_country', 'error_not_found')
					]);
				} else if(Product::has([
					'country_id' => $country['id']
				])) {
					Alert::push([
						'type' => 'warning',
						'message' => lang('system_country', 'error_delete')
					]);
				} else {
					if(Country::delete($country['id'])) {
						Provinces::delete(['country_id' => $country['id']]);
						Districts::delete(['country_id' => $country['id']]);
						Wards::delete(['country_id' => $country['id']]);
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

				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_COUNTRY])));

			default:
				$title = lang('admin_panel', 'system_country');

				$count = Country::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$country_list = Country::list([
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_create = UserPermission::has('admin_country_add');
				$is_access_edit = UserPermission::has('admin_country_edit');
				$is_access_delete = UserPermission::has('admin_country_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.country.index',
					'data' => compact(
						'count',
						'country_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_COUNTRY]);
	}
}

?>