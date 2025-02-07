<?php

trait Block_configuration {

	private static function block_configuration($action = null) {
		$title = lang('admin_panel', 'system_configuration');

		$success = null;
		$error = null;

		$app_name = trim(Request::post(DotEnv::APP_NAME, env(DotEnv::APP_NAME)));
		$app_title = trim(Request::post(DotEnv::APP_TITLE, env(DotEnv::APP_TITLE)));
		$app_email = trim(Request::post(DotEnv::APP_EMAIL, env(DotEnv::APP_EMAIL)));
		$profile_upload_mode = trim(Request::post(DotEnv::PROFILE_UPLOAD_MODE, env(DotEnv::PROFILE_UPLOAD_MODE)));
		$limit_login = env(DotEnv::LIMIT_LOGIN, false);
		$default_language = trim(Request::post(DotEnv::DEFAULT_LANGUAGE, env(DotEnv::DEFAULT_LANGUAGE)));

		$limit_item_page = intval(Request::post(DotEnv::APP_LIMIT_ITEM_PAGE, env(DotEnv::APP_LIMIT_ITEM_PAGE, 60)));
		$imgur_client_id = Request::post(DotEnv::IMGUR_CLIENT_ID, env(DotEnv::IMGUR_CLIENT_ID, []));

		switch($profile_upload_mode) {
			case  App::PROFILE_UPLOAD_MODE_IMGUR:
				$profile_upload_mode = App::PROFILE_UPLOAD_MODE_IMGUR;
				break;
			default:
				$profile_upload_mode = App::PROFILE_UPLOAD_MODE_LOCALHOST;
				break;
		}

		$limit_login = filter_var($limit_login, FILTER_VALIDATE_BOOLEAN);

		if(!is_array($imgur_client_id)) {
			$imgur_client_id = [$imgur_client_id];
		}
		
		$imgur_client_id = array_filter($imgur_client_id);

		if(Security::validate() == true)
        {
			$limit_login = !!intval(Request::post(DotEnv::LIMIT_LOGIN, false));
			if(!is_numeric($limit_item_page) || $limit_item_page < 1) {
				$error = lang('admin_panel', 'error_limit_page');
			} else if(!array_key_exists($default_language, Language::list())) {
				$error = lang('admin_panel', 'error_language');
			} else {
				if(App::update_config([
					DotEnv::APP_NAME => $app_name,
					DotEnv::APP_TITLE => $app_title,
					DotEnv::APP_EMAIL => $app_email,
					DotEnv::PROFILE_UPLOAD_MODE => $profile_upload_mode,
					DotEnv::LIMIT_LOGIN => $limit_login,
					DotEnv::DEFAULT_LANGUAGE => $default_language,
					DotEnv::APP_LIMIT_ITEM_PAGE => $limit_item_page,
					DotEnv::IMGUR_CLIENT_ID => $imgur_client_id
				])) {
					$success = lang('system', 'success_save');
				} else {
					$error = lang('system', 'default_error');
				}
			}				
		}

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.system',
			'view_block' => 'admin_panel.block.system.configuration',
			'data' => compact(
				'success',
				'error',
				'app_name',
				'app_title',
				'app_email',
				'profile_upload_mode',
				'limit_login',
				'default_language',
				'limit_item_page',
				'imgur_client_id'
			)
		];
	}
}

?>