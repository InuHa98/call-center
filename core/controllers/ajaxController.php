<?php


class ajaxController
{

	public const SEARCH_USER = 'search-user';
	public const MODE_STRICT = 'strict';

	public function __construct($name, $action = null)
	{
		$method = str_replace('-', '_', $name);
		if(!method_exists($this, $method))
		{
			return self::result(403, lang('api', 'method_not_found'));
		}
		$this->$method();
	}

	private static function result($code, $message, $data = null)
	{
		exit(json_encode([
			'code' => $code,
			'message' => $message,
			'data' => $data
		], JSON_PRETTY_PRINT));
	}

	private static function check_method_accept($accept_method)
	{
		if(!is_array($accept_method))
		{
			$accept_method = [strtoupper($accept_method)];
		}
		else
		{
			$accept_method = array_map(function($value) {
				return strtoupper($value);
			}, $accept_method);
		}
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		return in_array($method, $accept_method);
	}


	private function search_user()
	{
		if(!self::check_method_accept('get'))
		{
			return self::result(403, lang('api', 'access_is_denied'));
		}

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, null));
		$email = trim(Request::get(InterFaceRequest::EMAIL, null));
		$mode = trim(Request::get(InterFaceRequest::MODE, null));
		$exclude = trim(Request::get(InterFaceRequest::EXCLUDE, null));

		$where = [
			'OR' => []
		];

		if($keyword) {
			$where['OR']['username[~]'] = $mode == self::MODE_STRICT ? $keyword : '%'.$keyword.'%';
		}

		if($email) {
			$where['OR']['email[~]'] = $mode == self::MODE_STRICT ? $email : '%'.$email.'%';
		}

		if($exclude) {
			if(!is_array($exclude)) {
				$exclude = [$exclude];
			}
			$where['id[!]'] = $exclude;
		}

		$users = User::select([
			'id',
			'name',
			'username',
			'avatar',
			'is_ban',
			'<core_roles.color> AS <role_color>'
		])::list($where);

		if(!$users || ($keyword == '' && $email == ''))
		{
			return self::result(404, lang('system', 'empty_user'));
		}

		$users = array_map(function($user) {
			$user['display_name'] = User::get_display_name($user);
			$user['avatar'] = User::get_avatar($user);
			$user['no_avatar'] = no_avatar($user);
			return $user;
		}, $users);

		return self::result(200, count($users), $users);
	}

}





?>