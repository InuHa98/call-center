<?php

trait Block_ward {
	private static function block_ward($action = null) {
		
		$success = null;
		$error = null;

		$url_ward = RouteMap::build_query([InterFaceRequest::ID => Request::get(InterFaceRequest::ID)], 'admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_WARD]);

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_country_add')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				
				$district = Districts::get($id);
				if(!$district) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY]);
				}

				$title = lang('system_ward', 'txt_add').' ('._echo($district['name']).')';
				$txt_description = $title;


				$name = trim(Request::post(self::INPUT_NAME, null));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = lang('system_ward', 'error_name');
					}
					else if(Wards::has([
						'name[~]' => $name,
						'district_id' => $district['id']
					])) {
						$error = lang('system_ward', 'error_name_exists');
					}
					else {
						if(Wards::create($district['country_id'], $district['province_id'], $district['id'], $name)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect($url_ward);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}


				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.ward.add_edit',
					'data' => compact(
						'success',
						'error',
						'district',
						'txt_description',
						'name'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_country_edit')) {
					break;
				}
				$title = lang('system_ward', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$ward = Wards::get([
					'id' => $id
				]);

				if(!$ward) {
					return redirect($url_ward);
				}

				$name = trim(Request::post(self::INPUT_NAME, $ward['name']));


				if(Security::validate() == true)
				{
					
					if($name == '') {
						$error = lang('system_ward', 'error_name');
					}
					else if(Wards::has([
						'id[!]' => $ward['id'],
						'name[~]' => $name,
						'district_id' => $ward['district_id']
					])) {
						$error = lang('system_ward', 'error_name_exists');
					}
					else {
						if(Wards::update($ward['id'], [
							'name' => $name
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				$district = ['id' => $ward['district_id']];

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.ward.add_edit',
					'data' => compact(
						'success',
						'error',
						'district',
						'txt_description',
						'name'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_country_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$ward = Wards::get([
					'id' => $id
				]);

				if(!$ward) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_ward', 'error_not_found')
					]);
				} else {
					if(Wards::delete($ward['id'])) {
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


				return redirect(Request::referer($url_ward));

			default:
				$title = lang('admin_panel', 'system_ward');

				$id = intval(Request::get(InterFaceRequest::ID, null));

				$district = Districts::get($id);
				if(!$district) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY]);
				}

				$where = [
					'district_id' => $district['id']
				];

				$count = Wards::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$ward_list = Wards::list(array_merge($where, [
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
					'view_block' => 'admin_panel.block.system.ward.index',
					'data' => compact(
						'district',
						'count',
						'ward_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect($url_ward);
	}
}

?>