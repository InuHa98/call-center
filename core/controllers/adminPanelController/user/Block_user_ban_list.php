<?php

trait Block_user_ban_list {
	private static function block_user_ban_list() {
		$title = lang('admin_panel', 'user_ban_list');

		$success = null;
		$error = null;

		$where = [
			'is_ban' => User::IS_BAN
		];

		$role = trim(Request::get(self::INPUT_ROLE, self::INPUT_ALL));
		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$type = trim(Request::get(InterFaceRequest::TYPE, null));

		if($role != self::INPUT_ALL) {
			$where['role_id'] = $role;
		}

		if($keyword != '') {
			switch($type) {
				case self::INPUT_EMAIL: break;
				default:
					$type = self::INPUT_USERNAME;
					break;
			}
			$where[$type.'[~]'] = '%'.$keyword.'%';
		}


		if(Security::validate() == true) {
			$action = trim(Request::post(self::INPUT_ACTION, null));
			$id = intval(Request::post(self::INPUT_ID, 0));

			$user = User::get(['id' => $id]);

			if(!$user) {
				$error = lang('errors', 'user_not_found');
			} else {
				if($user['id'] != Auth::$data['id']  && (UserPermission::isAdmin() || $user['role_level'] > Auth::$data['role_level'])) {

					if(UserPermission::has('admin_user_unban') && $action == self::ACTION_UNBAN) {
						if(User::update($user['id'], [
							'is_ban' => User::IS_NOT_BAN,
							'reason_ban' => ''
						])) {
							Notification::create([
								'user_id' => $user['id'],
								'from_user_id' => Auth::$data['id'],
								'type' => notificationController::TYPE_UNBAN_USER,
								'data' => []
							]);
							$success = lang('user_management', 'success_unban', [
								'username' => _echo($user['username'])
							]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}
			}
		}

		$count = User::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$user_list = User::select([
			'id',
			'username',
			'avatar',
			'email',
			'is_ban',
			'reason_ban',
			'<core_roles.name> AS <role_name>', 
			'<core_roles.color> AS <role_color>', 
			'<core_roles.level> AS <role_level>'
		])::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$list_role = Role::list();

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.user',
			'view_block' => 'admin_panel.block.user.ban_list',
			'data' => compact(
				'success',
				'error',
				'role',
				'keyword',
				'type',
				'list_role',
				'insertHiddenToken',
				'user_list',
				'pagination'
			)
		];
	}
}

?>