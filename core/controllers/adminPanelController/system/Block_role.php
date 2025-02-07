<?php

trait Block_role {
	private static function block_role($action) {
		$success = null;
		$error = null;



		switch($action) {


			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_role_edit')) {
					break;
				}

				$title = lang('system_role', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$role = Role::get([
					'id' => $id
				]);

				if(!$role) {
					return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_ROLE]);
				}

				$name = trim(Request::post(self::INPUT_NAME, $role['name']));
				$color = trim(Request::post(self::INPUT_COLOR, $role['color']));
				$level = intval(Request::post(self::INPUT_LEVEL, $role['level']));
				$perms = json_decode($role['perms'], true);

				$all_perms = false;
				if(in_array(UserPermission::ALL_PERMS, $perms)) {
					$all_perms = true;
					$perms = array_keys(UserPermission::list());
				}

				if(Security::validate() == true)
				{

					if(!$all_perms) {
						$perms = Request::post(self::INPUT_PERMISSION, []);
				
						if(!is_array($perms)) {
							$perms = [$perms];
						}
						$perms = array_filter($perms, function($value, $key) {
							return array_key_exists($key, UserPermission::list()) && $value;
						}, ARRAY_FILTER_USE_BOTH);
		
						$perms = array_keys($perms);						
					}

					
					if($name == '') {
						$error = lang('system_role', 'error_role_name');
					}
					else if(Role::has([
						'id[!]' => $role['id'],
						'name[~]' => $name
					])) {
						$error = lang('system_role', 'error_role_exists');
					}
					else {
						if(Role::update($role['id'], [
							'name' => $name,
							'level' => $level,
							'color' => $color,
							'perms' => $all_perms ? [UserPermission::ALL_PERMS] : $perms
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
					'view_block' => 'admin_panel.block.system.role.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'level',
						'color',
						'perms'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_role_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$role = Role::get([
					'id' => $id,
					'is_default[!]' => Role::IS_DEFAULT
				]);

				if(!$role) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_role', 'error_role_not_found')
					]);
				} else {

					if(Role::delete($role['id'])) {
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

				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_ROLE])));

			default:
				$title = lang('admin_panel', 'system_role');

				Role::setup_default_role();
				
				$count = Role::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$role_list = Role::list([
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_edit = UserPermission::has('admin_role_edit');
				$is_access_delete = UserPermission::has('admin_role_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.role.index',
					'data' => compact(
						'role_list',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_ROLE]);
	}
}

?>