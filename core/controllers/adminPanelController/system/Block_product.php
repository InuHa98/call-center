<?php

trait Block_product {
	private static function block_product($action) {
		$success = null;
		$error = null;

		switch($action) {

			case self::ACTION_ADD:
				if(!UserPermission::has('admin_product_add')) {
					break;
				}
				$title = lang('system_product', 'txt_add');
				$txt_description = $title;

				$name = trim(Request::post(self::INPUT_NAME, null));
				$image = Request::files(self::INPUT_IMAGE, []);
				$description = Request::post(self::INPUT_DESC, []);
				$country_id = intval(Request::post(self::INPUT_COUNTRY, null));
				$price = abs(floatval(Request::post(self::INPUT_PRICE, 0)));
				$stock = abs(intval(Request::post(self::INPUT_STOCK, 0)));
				$ads_cost = abs(floatval(Request::post(self::INPUT_ADS_COST, 0)));
				$delivery_cost = abs(floatval(Request::post(self::INPUT_DELIVERY_COST, 0)));
				$import_cost = abs(floatval(Request::post(self::INPUT_IMPORT_COST, 0)));

				$image_preview = assetController::load_image(Product::NO_IMAGE);
				
				if(Security::validate() == true)
				{
					$description = array_filter($description);

					if($name == '') {
						$error = lang('system_product', 'error_name');
					}
					else if(!$description) {
						$error = lang('system_product', 'error_description');
					}
					else if(!Country::has([
						'id' => $country_id
					])) {
						$error = lang('system_product', 'error_country');
					}
					else if(Product::has([
						'name[~]' => $name,
						'country_id' => $country_id
					])) {
						$error = lang('system_product', 'error_name_exists');
					}
					else {

						$upload_image = Product::upload_image($image);
						if($upload_image['error'] === false) {
							if(Product::create($name, $upload_image['path'], $description, $country_id, $price, $stock, $ads_cost, $delivery_cost, $import_cost)) {
								Alert::push([
									'type' => 'success',
									'message' => lang('system', 'success_create')
								]);
								return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_PRODUCT]);
							} else {
								$error = lang('system', 'default_error');
							}							
						} else {
							$error = $upload_image['error'];
						}
					}
				}

				$list_country = Country::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.product.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_country',
						'image_preview',
						'name',
						'description',
						'country_id',
						'price',
						'stock',
						'ads_cost',
						'delivery_cost',
						'import_cost'
					)
				];

			case self::ACTION_EDIT:
				if(!UserPermission::has('admin_product_edit')) {
					break;
				}

				$title = lang('system_product', 'txt_edit');
				$txt_description = $title;

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$product = Product::get([
					'id' => $id
				]);

				if(!$product) {
					return redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_PRODUCT]);
				}

				$name = trim(Request::post(self::INPUT_NAME, $product['name']));
				$image = Request::files(self::INPUT_IMAGE, []);
				$description = Request::post(self::INPUT_DESC, json_decode($product['desc'], true));
				$country_id = intval(Request::post(self::INPUT_COUNTRY, $product['country_id']));
				$price = floatval(Request::post(self::INPUT_PRICE, $product['price']));
				$stock = abs(intval(Request::post(self::INPUT_STOCK, $product['stock'])));
				$ads_cost = abs(floatval(Request::post(self::INPUT_ADS_COST, $product['ads_cost'])));
				$delivery_cost = abs(floatval(Request::post(self::INPUT_DELIVERY_COST, $product['delivery_cost'])));
				$import_cost = abs(floatval(Request::post(self::INPUT_IMPORT_COST, $product['import_cost'])));

				$image_preview = $product['image'] ? Product::get_image($product['image']) : assetController::load_image(Product::NO_IMAGE);

				if(Security::validate() == true)
				{

					$description = array_filter($description);

					if($name == '') {
						$error = lang('system_product', 'error_name');
					}
					else if(!$description) {
						$error = lang('system_product', 'error_description');
					}
					else if(!Country::has([
						'id' => $country_id
					])) {
						$error = lang('system_product', 'error_country');
					}
					else if(Product::has([
						'id[!]' => $product['id'],
						'name[~]' => $name,
						'country_id' => $country_id
					])) {
						$error = lang('system_product', 'error_name_exists');
					}
					else {
						$image_path = $product['image'];
						if($image['tmp_name']) {
							$upload_image = Product::upload_image($image);
							if($upload_image['error'] === false) {
								$image_path = $upload_image['path'];
								$image_preview = Product::get_image($upload_image['path']);
								Product::delete_image($product['image']);
							} else {
								$error = $upload_image['error'];
							}
						}
						
						if(!$error) {
							if(Product::update($product['id'], [
								'name' => $name,
								'image' => $image_path,
								'desc' => $description,
								'country_id' => $country_id,
								'price' => $price,
								'stock' => $stock,
								'ads_cost' => $ads_cost,
								'delivery_cost' => $delivery_cost,
								'import_cost' => $import_cost
							])) {

								$success = lang('system', 'success_update');
							} else {
								$error = lang('system', 'default_error');
							}							
						}
					}
				}

				$list_country = Country::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.product.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'list_country',
						'image_preview',
						'name',
						'description',
						'country_id',
						'price',
						'stock',
						'ads_cost',
						'delivery_cost',
						'import_cost'
					)
				];

			case self::ACTION_DELETE:
				if(!UserPermission::has('admin_product_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$product = Product::get([
					'id' => $id
				]);

				if(!$product) {
					Alert::push([
						'type' => 'error',
						'message' => lang('system_product', 'error_not_found')
					]);
				} else {
					if(Product::delete($product['id'])) {
						Alert::push([
							'type' => 'success',
							'message' => lang('system', 'success_delete')
						]);
						Product::delete_image($product['image']);
					} else {
						Alert::push([
							'type' => 'error',
							'message' => lang('system', 'default_error')
						]);
					}					
				}


				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_PRODUCT])));

			default:
				$title = lang('admin_panel', 'system_product');


				if(Security::validate() == true) {
					$id = intval(Request::post(self::INPUT_ID, 0));
					$product = Product::get([
						'id' => $id
					]);
	
					if(!$product) {
						$error = lang('system_product', 'error_not_found');
					} else {
						if(Product::update($product['id'], [
							'status' => $product['status'] == Product::STATUS_ACTIVE ? Product::STATUS_INACTIVE : Product::STATUS_ACTIVE
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'error_update');
						}
					}
				}

				$where = [];

				$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
				$country_id = intval(Request::get(InterFaceRequest::COUNTRY, self::INPUT_ALL));
				$status = Request::get(InterFaceRequest::STATUS, self::INPUT_ALL);

				if($keyword != '') {
					$where['name[~]'] = '%'.$keyword.'%';
				}

				if($country_id && $country_id != self::INPUT_ALL) {
					$where['country_id'] = $country_id;
				}

				if($status != self::INPUT_ALL) {
					$where['status'] = $status;
				}

				$count = Product::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$product_list = Product::list(array_merge($where, [
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]));

				$is_access_create = UserPermission::has('admin_product_add');
				$is_access_edit = UserPermission::has('admin_product_edit');
				$is_access_delete = UserPermission::has('admin_product_delete');

				$list_country = Country::list();

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.product.index',
					'data' => compact(
						'success',
						'error',
						'count',
						'product_list',
						'list_country',
						'keyword',
						'country_id',
						'status',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => self::GROUP_SYSTEM, 'block' => self::BLOCK_PRODUCT]);
	}
}

?>