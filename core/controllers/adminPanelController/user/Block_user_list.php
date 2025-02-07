<?php

trait Block_user_list {
	private static function block_user_list() {
		$title = lang('admin_panel', 'user_list');
		$success = null;
		$error = null;


		$where = [
			'is_ban' => User::IS_NOT_BAN
		];

		$role = trim(Request::get(self::INPUT_ROLE, self::INPUT_ALL));
		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$type = trim(Request::get(InterFaceRequest::TYPE, null));
		$team_type = trim(Request::get(self::INPUT_TEAM, self::INPUT_ALL));

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

		switch($team_type) {
			case 1:
				$where['team_id[!]'] = 0;
				break;

			case 2:
				$where['team_id'] = 0;
				break;
		}


		if(Security::validate() == true) {
			$action = trim(Request::post(self::INPUT_ACTION, null));
			$id = intval(Request::post(self::INPUT_ID, 0));

			$user = User::get(['id' => $id]);

			if(!$user) {
				$error = lang('errors', 'user_not_found');
			} else {
				if($user['id'] != Auth::$data['id']  && (UserPermission::isAdmin() || $user['role_level'] > Auth::$data['role_level'])) {

					if(UserPermission::has('admin_user_edit')) {
						switch($action) {
							case self::ACTION_CHANGE_USERNAME:
								$username = trim(Request::post(self::INPUT_USERNAME, null));
								if(!Auth::check_length_username($username))
								{
									$error = lang('errors', 'username_length', [
										'min' => Auth::USERNAME_MIN_LENGTH,
										'max' => Auth::USERNAME_MAX_LENGTH
									]);
								}
								else if(!Auth::check_type_username($username))
								{
									$error = lang('errors', 'username_type');
								}
								else if(User::has([
									'username[~]' => $username,
									'id[!]' => $id
								])) {
									$error = lang('errors', 'username_exists');
								} else {
									if(User::update($user['id'], [
										'username' => $username
									])) {
										Notification::create([
											'user_id' => $user['id'],
											'from_user_id' => Auth::$data['id'],
											'type' => notificationController::TYPE_CHANGE_USERNAME,
											'data' => [
												'username' => $username
											]
										]);
										$success = lang('user_management', 'success_change_username', [
											'username' => _echo($user['username']),
											'new_username' => _echo($username)
										]);
									} else {
										$error = lang('system', 'default_error');
									}
								}
								break;

							case self::ACTION_CHANGE_PASSWORD:
								$new_password = trim(Request::post(self::INPUT_PASSWORD, null));
								$new_password_confirm = trim(Request::post(self::INPUT_PASSWORD_CONFIRM, null));

								if(!Auth::check_length_password($new_password))
								{
									$error = lang('errors', 'password', [
										'min' => Auth::PASSWORD_MIN_LENGTH,
										'max' => Auth::PASSWORD_MAX_LENGTH
									]);
								}
								else if($new_password !== $new_password_confirm)
								{
									$error = lang('errors', 'confirm_password');
								}
								else
								{
									if(User::update($user['id'], [
										'password' => Auth::encrypt_password($new_password)
									]) > 0)
									{
										Notification::create([
											'user_id' => $user['id'],
											'from_user_id' => Auth::$data['id'],
											'type' => notificationController::TYPE_CHANGE_PASSWORD,
											'data' => [
												'password' => $new_password
											]
										]);
										$success = lang('user_management', 'success_change_password', [
											'username' => _echo($user['username'])
										]);
									} else {
										$error = lang('system', 'default_error');
									}
								}
								break;

							case self::ACTION_CHANGE_EMAIL:
								$new_email = trim(Request::post(self::INPUT_EMAIL, null));

								if(!Auth::check_type_email($new_email))
								{
									$error = lang('errors', 'email');
								}
								else if(User::has([
									'email[~]' => $new_email,
									'id[!]' => $user['id']
								]))
								{
									$error = lang('errors', 'email_exists');
								}
								else
								{
									if(User::update($user['id'], [
										'email' => $new_email
									]) > 0)
									{
										Notification::create([
											'user_id' => $user['id'],
											'from_user_id' => Auth::$data['id'],
											'type' => notificationController::TYPE_CHANGE_EMAIL,
											'data' => [
												'email' => $new_email
											]
										]);
										$success = lang('user_management', 'success_change_email', [
											'username' => _echo($user['username'])
										]);
									} else {
										$error = lang('system', 'default_error');
									}
								}
								break;


							case self::ACTION_CHANGE_PERMISSION:
								$new_permission = Request::post(self::INPUT_PERMISSION, []);

								if(!is_array($new_permission)) {
									$new_permission = [$new_permission];
								}

								$new_permission = array_filter($new_permission, function($value, $key) {
									return array_key_exists($key, UserPermission::list());
								}, ARRAY_FILTER_USE_BOTH);
			
								$role_permissions = json_decode($user['role_perms'], true);

								$permissions = [];
								foreach($new_permission as $key => $value) {
									$truthy = filter_var($value, FILTER_VALIDATE_BOOLEAN);
									if(in_array($key, $role_permissions) && !$truthy) {
										$permissions[$key] = false;
									} else if (!in_array($key, $role_permissions) && $truthy) {
										$permissions[$key] = true;
									}
								}

								if(User::update($user['id'], [
									'perms' => $permissions
								])) {
									$success = lang('user_management', 'success_change_permission', [
										'username' => _echo($user['username'])
									]);
								} else {
									$error = lang('system', 'default_error');
								}

								break;

							case self::ACTION_SET_TEAM:
								$team_id = intval(Request::post(self::INPUT_TEAM, null));
								$team = Team::get([
									'id' => $team_id,
									'is_ban' => Team::IS_NOT_BAN
								]);

								if(!$team) {
									$error = lang('errors', 'team_not_found');
									break;
								}

								if(User::update($user['id'], [
									'team_id' => $team['id']
								])) {
									if($user['role_id'] != Role::DEFAULT_ROLE_ADMIN) {
										$data_team = Team::get_data($team['type']);
										if(User::update($user['id'], [
											'role_id' => $data_team['role'],
											'profit_ads' => $team['profit_ads'],
											'profit_call' => $team['profit_call'],
											'profit_ship' => $team['profit_ship'],
											'deduct_ads' => $team['deduct_ads'],
											'deduct_call' => $team['deduct_call'],
											'deduct_ship' => $team['deduct_ship']
										])) {
											Notification::create([
												'user_id' => $user['id'],
												'from_user_id' => Auth::$data['id'],
												'type' => notificationController::TYPE_SET_TEAM,
												'data' => [
													'name' => $team['name']
												]
											]);
										}
									}
									$success = lang('user_management', 'success_set_team', [
										'team' => _echo($team['name'])
									]);
								} else {
									$error = lang('system', 'default_error');
								}
								break;
						}		
					}
					
					if(UserPermission::has('admin_user_ban') && $action == self::ACTION_BAN) {

						$reason = trim(Request::post(self::INPUT_REASON, null));

						if(User::update($user['id'], [
							'auth_session' => '',
							'is_ban' => User::IS_BAN,
							'reason_ban' => $reason
						])) {
							Notification::create([
								'user_id' => $user['id'],
								'from_user_id' => Auth::$data['id'],
								'type' => notificationController::TYPE_BAN_USER,
								'data' => [
									'reason' => $reason
								]
							]);
							$success = lang('user_management', 'success_ban', [
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
		$user_list = User::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$list_role = Role::list();
		$list_permission = UserPermission::list();
		$list_team = Team::list([
			'is_ban' => Team::IS_NOT_BAN
		]);

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.user',
			'view_block' => 'admin_panel.block.user.list',
			'data' => compact(
				'success',
				'error',
				'role',
				'keyword',
				'type',
				'team_type',
				'list_team',
				'list_role',
				'list_permission',
				'insertHiddenToken',
				'user_list',
				'pagination'
			)
		];
	}
}

?>