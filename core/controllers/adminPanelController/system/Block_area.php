<?php

trait Block_area {
	private static function block_area($action) {
		$success = null;
		$error = null;

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_area_add')) {
					break;
				}
				$title = lang('system_area', 'txt_add');
				$txt_description = $title;

				$name = trim(Request::post(self::INPUT_NAME, null));
				$country_id = intval(Request::post(self::INPUT_COUNTRY, 0));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_area', 'error_name');
					}
					else if(!Country::has([
						'id' => $country_id
					])) {
						$error = lang('system_area', 'error_country');
					}
					else {

						if(Area::create($name, $country_id)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_AREA]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$list_country = Country::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.area.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_country',
						'name',
						'country_id'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_area_edit')) {
					break;
				}

				$title = lang('system_area', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$area = Area::get([
					'id' => $id
				]);

				if(!$area) {
					return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_AREA]);
				}

				$name = trim(Request::post(self::INPUT_NAME, $area['name']));
				$country_id = intval(Request::post(self::INPUT_COUNTRY, $area['country_id']));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_area', 'error_name');
					}
					else if(!Country::has([
						'id' => $country_id
					])) {
						$error = lang('system_area', 'error_country');
					}
					else {
						if(Area::update($area['id'], [
							'name' => $name,
							'country_id' => $country_id
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$list_country = Country::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.area.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_country',
						'name',
						'country_id'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_area_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$area = Area::get([
					'id' => $id
				]);

				if(!$area) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_area', 'error_not_found')
					]);
				} else if(Provinces::has([
					'area_id' => $area['id']
				])) {
					Alert::push([
						'type' => 'warning',
						'message' => lang('system_area', 'error_delete')
					]);
				} else {
					if(Area::delete($area['id'])) {
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

				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_AREA])));

			default:
				$title = lang('admin_panel', 'system_area');

				$count = Area::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$area_list = Area::list([
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_create = UserPermission::has('admin_area_add');
				$is_access_edit = UserPermission::has('admin_area_edit');
				$is_access_delete = UserPermission::has('admin_area_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.area.index',
					'data' => compact(
						'count',
						'area_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_AREA]);
	}
}

?>