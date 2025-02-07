<?php

trait Block_province {
	private static function block_province($action = null) {
		
		$success = null;
		$error = null;



		$url_province = RouteMap::build_query([InterFaceRequest::ID => Request::get(InterFaceRequest::ID)], 'admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_PROVINCE]);

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_country_add')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				
				$country = Country::get($id);
				if(!$country) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY]);
				}

				$title = lang('system_province', 'txt_add').' ('._echo($country['name']).')';
				$txt_description = $title; 


				$name = trim(Request::post(self::INPUT_NAME, null));
				$area_id = intval(Request::post(self::INPUT_AREA, null));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_province', 'error_name');
					}
					else if(Provinces::has([
						'name[~]' => $name,
						'country_id' => $country['id']
					])) {
						$error = lang('system_province', 'error_name_exists');
					}
					else {
						if(Provinces::create($country['id'], $name, $area_id)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect($url_province);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$list_area = Area::list([
					'country_id' => $country['id']
				]);


				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.province.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_area',
						'country',
						'name',
						'area_id'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_country_edit')) {
					break;
				}
				$title = lang('system_province', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$province = Provinces::get([
					'id' => $id
				]);

				if(!$province) {
					return redirect($url_province);
				}

				$name = trim(Request::post(self::INPUT_NAME, $province['name']));
				$area_id = intval(Request::post(self::INPUT_AREA, $province['area_id']));

				if(Security::validate() == true)
				{
					
					if($name == '') {
						$error = lang('system_province', 'error_name');
					}
					else if(Provinces::has([
						'id[!]' => $province['id'],
						'name[~]' => $name,
						'country_id' => $province['country_id']
					])) {
						$error = lang('system_province', 'error_name_exists');
					}
					else {
						if(Provinces::update($province['id'], [
							'name' => $name,
							'area_id' => $area_id
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$list_area = Area::list([
					'country_id' => $province['country_id']
				]);
				$country = [
					'id' => $province['country_id']
				];
				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.province.add_edit',
					'data' => compact(
						'success',
						'error',
						'country',
						'txt_description',
						'list_area',
						'name',
						'area_id'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_country_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$province = Provinces::get([
					'id' => $id
				]);

				if(!$province) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_province', 'error_not_found')
					]);
				} else {
					if(Provinces::delete($province['id'])) {
						Districts::delete(['province_id' => $province['id']]);
						Wards::delete(['province_id' => $province['id']]);
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


				return redirect(Request::referer($url_province));

			default:
				$title = lang('admin_panel', 'system_province');

				$id = intval(Request::get(InterFaceRequest::ID, null));

						
				$country = Country::get($id);
				if(!$country) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY]);
				}

				$where = [
					'country_id' => $country['id']
				];

				$count = Provinces::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$province_list = Provinces::list(array_merge($where, [
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]));

				$is_access_create = UserPermission::has('admin_country_add');
				$is_access_edit = UserPermission::has('admin_country_edit');
				$is_access_delete = UserPermission::has('admin_country_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.province.index',
					'data' => compact(
						'country',
						'count',
						'province_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect($url_province);
	}
}

?>