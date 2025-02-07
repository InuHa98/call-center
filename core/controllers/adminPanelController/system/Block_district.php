<?php

trait Block_district {
	private static function block_district($action = null) {
		
		$success = null;
		$error = null;

		$url_district = RouteMap::build_query([InterFaceRequest::ID => Request::get(InterFaceRequest::ID)], 'admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_DISTRICT]);

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_country_add')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				
				$province = Provinces::get($id);
				if(!$province) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY]);
				}

				$title = lang('system_district', 'txt_add').' ('._echo($province['name']).')';
				$txt_description = $title;


				$name = trim(Request::post(self::INPUT_NAME, null));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_district', 'error_name');
					}
					else if(Districts::has([
						'name[~]' => $name,
						'province_id' => $province['id']
					])) {
						$error = lang('system_district', 'error_name_exists');
					}
					else {
						if(Districts::create($province['country_id'], $province['id'], $name)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect($url_district);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.district.add_edit',
					'data' => compact(
						'success',
						'error',
						'province',
						'txt_description',
						'name'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_country_edit')) {
					break;
				}
				$title = lang('system_district', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$district = Districts::get([
					'id' => $id
				]);

				if(!$district) {
					return redirect($url_district);
				}

				$name = trim(Request::post(self::INPUT_NAME, $district['name']));


				if(Security::validate() == true)
				{
					
					if($name == '') {
						$error = lang('system_district', 'error_name');
					}
					else if(Districts::has([
						'id[!]' => $district['id'],
						'name[~]' => $name,
						'province_id' => $district['province_id']
					])) {
						$error = lang('system_district', 'error_name_exists');
					}
					else {
						if(Districts::update($district['id'], [
							'name' => $name
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$province = ['id' => $district['province_id']];

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.district.add_edit',
					'data' => compact(
						'success',
						'error',
						'province',
						'txt_description',
						'name'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_country_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$district = Districts::get([
					'id' => $id
				]);

				if(!$district) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_district', 'error_not_found')
					]);
				} else {
					if(Districts::delete($district['id'])) {
						Wards::delete(['district_id' => $district['id']]);
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


				return redirect(Request::referer($url_district));

			default:
				$title = lang('admin_panel', 'system_district');

				$id = intval(Request::get(InterFaceRequest::ID, null));

				$province = Provinces::get($id);
				if(!$province) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY]);
				}

				$where = [
					'province_id' => $province['id']
				];

				$count = Districts::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$district_list = Districts::list(array_merge($where, [
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
					'view_block' => 'admin_panel.block.system.district.index',
					'data' => compact(
						'province',
						'count',
						'district_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect($url_district);
	}
}

?>