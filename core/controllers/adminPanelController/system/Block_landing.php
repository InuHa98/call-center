<?php

trait Block_landing {
	private static function block_landing($action) {
		$success = null;
		$error = null;

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_landing_add')) {
					break;
				}
				$title = lang('system_landing_page', 'txt_add');
				$txt_description = $title;

				$domain = trim(Request::post(self::INPUT_DOMAIN, null));
				$postback = trim(Request::post(self::INPUT_POSTBACK, null));
				$product_id = intval(Request::post(self::INPUT_PRODUCT, null));
				$key = trim(Request::post(self::INPUT_KEY, null));

				
				if(Security::validate() == true)
				{

					if($domain == '' || !preg_match("/^https?:\/\/(.*)$/", $domain)) {
						$error = lang('system_landing_page', 'error_domain');
					}
					else if(!strpos($postback, postbackController::POSTBACK_ADVERTISER)) {
						$error = lang('system_landing_page', 'error_postback', [
							'param' => postbackController::POSTBACK_ADVERTISER
						]);
					}
					else if(!Product::has([
						'id' => $product_id
					])) {
						$error = lang('system_landing_page', 'error_product');
					}
					else if(LandingPage::has([
						'domain[~]' => $domain,
						'product_id' => $product_id
					])) {
						$error = lang('system_landing_page', 'error_domain_exists');
					}
					else {

						if(LandingPage::create($domain, $postback, $product_id, $key)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_LANDING]);
						} else {
							$error = lang('system', 'default_error');
						}							
	
					}
				}

				$list_product = Product::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.landing.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_product',
						'domain',
						'postback',
						'product_id',
						'key'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_landing_edit')) {
					break;
				}

				$title = lang('system_landing_page', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$landing = LandingPage::get([
					'id' => $id
				]);

				if(!$landing) {
					return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_LANDING]);
				}

				$domain = trim(Request::post(self::INPUT_DOMAIN, $landing['domain']));
				$postback = trim(Request::post(self::INPUT_POSTBACK, $landing['postback']));
				$product_id = intval(Request::post(self::INPUT_PRODUCT, $landing['product_id']));
				$key = trim(Request::post(self::INPUT_KEY, $landing['key']));


				if(Security::validate() == true)
				{


					if($domain == '' || !preg_match("/^https?:\/\/(.*)$/", $domain)) {
						$error = lang('system_landing_page', 'error_domain');
					}
					else if(!strpos($postback, postbackController::POSTBACK_ADVERTISER)) {
						$error = lang('system_landing_page', 'error_postback', [
							'param' => postbackController::POSTBACK_ADVERTISER
						]);
					}
					else if(!Product::has([
						'id' => $product_id
					])) {
						$error = lang('system_landing_page', 'error_product');
					}
					else if(LandingPage::has([
						'id[!]' => $landing['id'],
						'domain[~]' => $domain,
						'product_id' => $product_id
					])) {
						$error = lang('system_landing_page', 'error_domain_exists');
					}
					else {
						if($key == '') {
							$key = LandingPage::generateKey(12);
						}
						if(LandingPage::update($landing['id'], [
							'domain' => $domain,
							'postback' => $postback,
							'product_id' => $product_id,
							'key' => $key
						])) {

							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}							
						
					}
				}

				$list_product = Product::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.landing.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_product',
						'domain',
						'postback',
						'product_id',
						'key'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_landing_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$landing = LandingPage::get([
					'id' => $id
				]);

				if(!$landing) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_landing_page', 'error_not_found')
					]);
				} else {
					if(LandingPage::delete($landing['id'])) {
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


				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_LANDING])));

			default:
				$title = lang('admin_panel', 'system_landing_page');

				$where = [];

				$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
				$product_id = intval(Request::get(InterFaceRequest::PRODUCT, self::INPUT_ALL));

				if($keyword != '') {
					$where['domain[~]'] = '%'.$keyword.'%';
				}

				if($product_id && $product_id != self::INPUT_ALL) {
					$where['product_id'] = $product_id;
				}


				$count = LandingPage::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$landing_list = LandingPage::list(array_merge($where, [
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]));

				$is_access_create = UserPermission::has('admin_landing_add');
				$is_access_edit = UserPermission::has('admin_landing_edit');
				$is_access_delete = UserPermission::has('admin_landing_delete');

				$list_product = Product::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.landing.index',
					'data' => compact(
						'success',
						'error',
						'count',
						'landing_list',
						'list_product',
						'keyword',
						'product_id',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_LANDING]);
	}
}

?>