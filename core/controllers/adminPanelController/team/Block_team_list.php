<?php

trait Block_team_list {
	private static function block_team_list($action) {
		$title = lang('admin_panel', 'team_list');
		$success = null;
		$error = null;


		switch($action) {
			case self::ACTION_ADD:
				if(!UserPermission::has('admin_team_add')) {
					break;
				}
				$title = lang('team_management', 'txt_add');
				$txt_description = $title;

				$name = trim(Request::post(self::INPUT_NAME, null));
				$type = intval(Request::post(self::INPUT_TYPE, null));
				$country_id = intval(Request::post(self::INPUT_COUNTRY, null));
				$leader_id = intval(Request::post(self::INPUT_LEADER, 0));
				$product_id = Request::post(self::INPUT_PRODUCT, []);
				$profit_call = abs(floatval(Request::post(self::INPUT_PROFIT_CALL, 0)));
				$deduct_call = abs(floatval(Request::post(self::INPUT_DEDUCT_CALL, 0)));
				$profit_ads = abs(floatval(Request::post(self::INPUT_PROFIT_ADS, 0)));
				$deduct_ads = abs(floatval(Request::post(self::INPUT_DEDUCT_ADS, 0)));
				$profit_ship = abs(floatval(Request::post(self::INPUT_PROFIT_SHIP, 0)));
				$deduct_ship = abs(floatval(Request::post(self::INPUT_DEDUCT_SHIP, 0)));

				switch($type) {
					case Team::TYPE_FULLSTACK:
					case Team::TYPE_ONLY_ADS:
					case Team::TYPE_ONLY_CALL:
					case Team::TYPE_ONLY_SHIP:
						break;
					default:
						$type = Team::TYPE_ONLY_CALL;
						break;
				}

				if(!is_array($product_id)) {
					$product_id = [$product_id];
				}

				$product_id = array_values(array_filter($product_id, function($id) use ($country_id) {
					return Product::has([
						'id' => $id,
						'country_id' => $country_id
					]);
				}));


				if(Security::validate() == true)
				{

					if($profit_ads < $deduct_ads) {
						$deduct_ads = $profit_ads;
					}

					if($profit_call < $deduct_call) {
						$deduct_call = $profit_call;
					}

					if($profit_ship < $deduct_ship) {
						$deduct_ship = $profit_ship;
					}

					if($name == '') {
						$error = lang('team_management', 'error_name');
					}
					else if(!Country::has([
						'id' => $country_id
					])) {
						$error = lang('team_management', 'error_country');
					}
					else if(Product::has([
						'name[~]' => $name,
					])) {
						$error = lang('team_management', 'error_name_exists');
					}
					else {

						if(Team::create($name, $type, $country_id, $product_id, compact('profit_call', 'profit_ads', 'profit_ship'), compact('deduct_call', 'deduct_ads', 'deduct_ship'))) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => self::GROUP_TEAM, 'block' => self::BLOCK_TEAM_LIST]);
						} else {
							$error = lang('system', 'default_error');
						}							

					}
				}


				$list_country = Country::list();
				$list_product = Product::list([
					'status' => Product::STATUS_ACTIVE
				]);

				$list_member = [];

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.team',
					'view_block' => 'admin_panel.block.team.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_country',
						'list_product',
						'list_member',
						'name',
						'type',
						'country_id',
						'leader_id',
						'product_id',
						'profit_call',
						'deduct_call',
						'profit_ads',
						'deduct_ads',
						'profit_ship',
						'deduct_ship'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_team_edit')) {
					break;
				}
				$title = lang('team_management', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$team = Team::get([
					'id' => $id,
					'is_ban' => Team::IS_NOT_BAN
				]);

				if(!$team) {
					return redirect_route('admin_panel', ['group' => self::GROUP_TEAM, 'block' => self::BLOCK_TEAM_LIST]);
				}

				$name = trim(Request::post(self::INPUT_NAME, $team['name']));
				$type = intval(Request::post(self::INPUT_TYPE, $team['type']));
				$leader_id = intval(Request::post(self::INPUT_LEADER, $team['leader_id']));
				$country_id = intval(Request::post(self::INPUT_COUNTRY, $team['country_id']));
				$product_id = explode(',', $team['product_id']);
				$profit_call = abs(floatval(Request::post(self::INPUT_PROFIT_CALL, $team['profit_call'])));
				$deduct_call = abs(floatval(Request::post(self::INPUT_DEDUCT_CALL, $team['deduct_call'])));
				$profit_ads = abs(floatval(Request::post(self::INPUT_PROFIT_ADS, $team['profit_ads'])));
				$deduct_ads = abs(floatval(Request::post(self::INPUT_DEDUCT_ADS, $team['deduct_ads'])));
				$profit_ship = abs(floatval(Request::post(self::INPUT_PROFIT_SHIP, $team['profit_ship'])));
				$deduct_ship = abs(floatval(Request::post(self::INPUT_DEDUCT_SHIP, $team['deduct_ship'])));

				switch($type) {
					case Team::TYPE_FULLSTACK:
					case Team::TYPE_ONLY_ADS:
					case Team::TYPE_ONLY_CALL:
					case Team::TYPE_ONLY_SHIP:
						break;
					default:
						$type = Team::TYPE_ONLY_CALL;
						break;
				}


				if(Security::validate() == true)
				{

					$product_id = Request::post(self::INPUT_PRODUCT, []);
					
					if(!is_array($product_id)) {
						$product_id = [$product_id];
					}
	
					$product_id = array_values(array_filter($product_id, function($id) use ($country_id) {
						return Product::has([
							'id' => $id,
							'country_id' => $country_id
						]);
					}));
	

					if($name == '') {
						$error = lang('team_management', 'error_name');
					}
					else if($leader_id && !User::has([
						'id' => $leader_id,
						'team_id' => $team['id']
					])) {
						$error = lang('team_management', 'error_leader_not_found');
					}
					else if(!Country::has([
						'id' => $country_id
					])) {
						$error = lang('team_management', 'error_country');
					}
					else if(Product::has([
						'id[!]' => $team['id'],
						'name[~]' => $name,
					])) {
						$error = lang('team_management', 'error_name_exists');
					}
					else {

						if(Team::update($team['id'], [
							'name' => $name,
							'type' => $type,
							'leader_id' => $leader_id,
							'country_id' => $country_id,
							'product_id' => implode(',', $product_id),
							'profit_call' => $profit_call,
							'deduct_call' => $deduct_call,
							'profit_ads' => $profit_ads,
							'deduct_ads' => $deduct_ads,
							'profit_ship' => $profit_ship,
							'deduct_ship' => $deduct_ship
						])) {
							User::update($leader_id, [
								'profit_call' => $profit_call,
								'deduct_call' => $deduct_call,
								'profit_ads' => $profit_ads,
								'deduct_ads' => $deduct_ads,
								'profit_ship' => $profit_ship,
								'deduct_ship' => $deduct_ship
							]);

							$list_member = User::list([
								'id[!]' => $leader_id,
								'team_id' => $team['id']
							]);

							if($list_member) {
								foreach($list_member as $member) {
									$data = [];
									$data['profit_ads'] = $profit_ads < $member['profit_ads'] ? $profit_ads : min($member['profit_ads'], $profit_ads);
									$data['profit_call'] = $profit_call < $member['profit_call'] ? $profit_call : min($member['profit_call'], $profit_call);
									$data['profit_ship'] = $profit_ship < $member['profit_ship'] ? $profit_ship : min($member['profit_ship'], $profit_ship);
				
									$data['deduct_ads'] = $data['profit_ads'] < $deduct_ads ? $data['profit_ads'] : min($deduct_ads, $data['profit_ads']);
									$data['deduct_call'] = $data['profit_call'] < $deduct_call ? $data['profit_call'] : min($deduct_call, $data['profit_call']);
									$data['deduct_ship'] = $data['profit_ship'] < $deduct_ship ? $data['profit_ship'] : min($deduct_ship, $data['profit_ship']);

									User::update($member['id'], $data);
								}
							}

							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}							

					}
				}


				$list_country = Country::list();
				$list_product = Product::list([
					'status' => Product::STATUS_ACTIVE
				]);
				$list_member = User::list([
					'team_id' => $team['id'],
					'is_ban' => User::IS_NOT_BAN,
					'is_ban_team' => User::IS_NOT_BAN,
					'ORDER' => [
						'id' => [$team['leader_id']],
						'username' => 'DESC' 
					]
				]);

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.team',
					'view_block' => 'admin_panel.block.team.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'team',
						'list_country',
						'list_product',
						'list_member',
						'name',
						'type',
						'leader_id',
						'country_id',
						'product_id',
						'profit_call',
						'deduct_call',
						'profit_ads',
						'deduct_ads',
						'profit_ship',
						'deduct_ship'
					)
				];

			case self::ACTION_DELETE: break;
			default:

				$where = [
					'is_ban' => Team::IS_NOT_BAN
				];
		
				$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
				$country_id = intval(Request::get(InterFaceRequest::COUNTRY, 0));
				$type_id = intval(Request::get(InterFaceRequest::TYPE, 0));
		
				if($keyword != '') {
					$where['name[~]'] = '%'.$keyword.'%';
				}
		
				if($country_id && $country_id != self::INPUT_ALL) {
					$where['country_id'] = $country_id;
				}

				if($type_id && $type_id != self::INPUT_ALL) {
					$where['type'] = $type_id;
				}

		
				if(Security::validate() == true) {
					$action_form = trim(Request::post(self::INPUT_ACTION, null));
					$id = intval(Request::post(self::INPUT_ID, 0));
		
					$team = Team::get($id);
		
					if(!$team) {
						$error = lang('errors', 'team_not_found');
					} else {

						if(UserPermission::has('admin_team_edit') && $action_form == self::ACTION_CHANGE_PERMISSION) {
							$new_permission = Request::post(self::INPUT_PERMISSION, []);

							if(!is_array($new_permission)) {
								$new_permission = [$new_permission];
							}

							$new_permission = array_filter($new_permission, function($value, $key) {
								return array_key_exists($key, UserPermission::list());
							}, ARRAY_FILTER_USE_BOTH);
		

							$permissions = [];
							foreach($new_permission as $key => $value) {
								$truthy = filter_var($value, FILTER_VALIDATE_BOOLEAN);
								if(!$truthy) {
									$permissions[$key] = 0;
								}
							}


							if(Team::update($team['id'], [
								'perms' => $permissions
							])) {
								User::update([
									'team_id' => $team['id']
								], [
									'perms' => $permissions
								]);
								$success = lang('team_management', 'success_change_permission', [
									'team' => _echo($team['name'])
								]);
							} else {
								$error = lang('system', 'default_error');
							}

						}
						else if(UserPermission::has('admin_team_ban') && $action_form == self::ACTION_BAN) {
		
							$reason = trim(Request::post(self::INPUT_REASON, null));
		
							if(Team::update($team['id'], [
								'is_ban' => Team::IS_BAN,
								'reason_ban' => $reason
							])) {
								Notification::create([
									'user_id' => $team['leader_id'],
									'from_user_id' => Auth::$data['id'],
									'type' => notificationController::TYPE_BAN_TEAM,
									'data' => [
										'reason' => $reason
									]
								]);
								$success = lang('team_management', 'success_ban', [
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
				$is_access_create = UserPermission::has('admin_team_add');
				$is_access_edit = UserPermission::has('admin_team_edit');
				$is_access_delete = UserPermission::has('admin_team_delete');
				$is_access_ban = UserPermission::has('admin_team_ban');
		
				$list_country = Country::list();
				$list_permission = array_filter(UserPermission::list(), function($value, $key) {
					return in_array($key, UserPermission::fullstack_default());
				}, ARRAY_FILTER_USE_BOTH);

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.team',
					'view_block' => 'admin_panel.block.team.list',
					'data' => compact(
						'success',
						'error',
						'keyword',
						'insertHiddenToken',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'is_access_ban',
						'list_country',
						'list_permission',
						'country_id',
						'type_id',
						'count',
						'team_list',
						'pagination'
					)
				];

		}
	}
}

?>