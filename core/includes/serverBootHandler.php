<?php


class serverBootHandler {

	public static function web()
	{

		/// mặc định chuyển đến trang đăng nhập nếu chưa đăng nhập
		if( !Auth::isLogin() && Router::$current_route != 'is_auth_route' )
		{
			return Router::redirect('*', RouteMap::get('login'));
		}

		if(Auth::$isLogin) {
			if(!UserPermission::isAdmin() &&  UserPermission::is_caller() && current_url() != RouteMap::get('caller', ['block' => callerController::BLOCK_CALLING]) && Order::has([
				'call_user_id' => Auth::$data['id'],
				'status' => Order::STATUS_CALLING
			])) {
				return redirect_route('caller', ['block' => callerController::BLOCK_CALLING]);
			}


			$limit_page = intval(Request::get(InterFaceRequest::LIMIT_PAGE, 0));
			if($limit_page) {
				User::update_setting(Auth::$id, [
					'page' => $limit_page
				]);
				App::$pagination_limit = $limit_page;
			}
		}
	}

	public static function api()
	{

	}

	public static function boot_view()
	{

		View::addData('_version', env('APP_VERSION', 0));

		if(Auth::$isLogin == true)
		{
			View::addData('_count_message', Messenger::count_new_inbox());
			$_count_notification = Notification::count_new();
			View::addData('_count_notification', $_count_notification);
			
			if($_count_notification > 0)
			{
				View::addData('_notification', Notification::get_list_new());
			}
		}

	}

}


?>