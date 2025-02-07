<?php

trait Block_team_ban_list {
	private static function block_team_ban_list() {
		$title = lang('admin_panel', 'team_ban_list');

		$success = null;
		$error = null;

		$where = [
			'is_ban' => Team::IS_BAN
		];

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$country_id = intval(Request::get(InterFaceRequest::COUNTRY, 0));
		$type_id = intval(Request::get(InterFaceRequest::TYPE, 0));

		if($keyword != '') {
			$where['name[~]'] = '%'.$keyword.'%';
		}

		if($country_id && $country_id != self::INPUT_ALL) {
			$where['country_id'] = '%'.$country_id.'%';
		}

		if($type_id && $type_id != self::INPUT_ALL) {
			$where['type'] = '%'.$type_id.'%';
		}

		if(Security::validate() == true) {
			$action = trim(Request::post(self::INPUT_ACTION, null));
			$id = intval(Request::post(self::INPUT_ID, 0));

			$team = Team::get($id);

			if(!$team) {
				$error = lang('errors', 'team_not_found');
			} else {

				if(UserPermission::has('admin_team_unban') && $action == self::ACTION_UNBAN) {
					if(Team::update($team['id'], [
						'is_ban' => Team::IS_NOT_BAN,
						'reason_ban' => ''
					])) {
						Notification::create([
							'user_id' => $team['leader_id'],
							'from_user_id' => Auth::$data['id'],
							'type' => notificationController::TYPE_UNBAN_TEAM,
							'data' => []
						]);
						$success = lang('team_management', 'success_unban', [
							'team' => _echo($team['name'])
						]);
					} else {
						$error = lang('system', 'default_error');
					}
				}
			}
		}


		$count = Team::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$team_list = Team::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$is_access_unban = UserPermission::has('admin_team_unban');

		$list_country = Country::list();

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.team',
			'view_block' => 'admin_panel.block.team.ban_list',
			'data' => compact(
				'success',
				'error',
				'keyword',
				'insertHiddenToken',
				'is_access_unban',
				'list_country',
				'country_id',
				'type_id',
				'count',
				'team_list',
				'pagination'
			)
		];
	}
}

?>