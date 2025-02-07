<?php

class RouteMap {

	public const ROUTES = [
		'dashboard' => '/Dashboard',
		'login' => '/Login',
		'logout' => '/Logout',
		'change_password' => '/Change-Password',
		'forgot_password' => '/Forgot-Password',
		'profile' => '/User/{id(me|[0-9]+)}/{block?}/{action?}',
		'notification' => '/Notification/{id(seen|unseen|\d+)?}',
		'admin_panel' => '/Admin-Panel/{group?}/{block?}/{action?}',
		'team' => '/Team/{id?}/{block?}/{action?}',
		'advertiser' => '/Advertiser/{action?}',
		'caller' => '/Caller/{block?}/{action?}',
		'shipper' => '/Shipper/{block?}/{action?}',
		'order' => '/Order/{block?}/{action?}',
		'messenger' => '/Messenger/{block(\w+)?}/{id(seen|unseen|\d+)?}',
		'statistics' => '/Statistics/{block}',
		'blacklist' => '/Blacklist/{action?}',
		'payment' => '/Payment/{block?}/{action?}',
		'order_management' => '/Order-Management/{block?}/{action?}',
		'postback' => '/Postback/{name}/{action?}',
		'ajax' => '/Ajax/{name}/{action?}'
	];

	private const ERROR = '#route_not_found';

	public static function get($name = null, $data = [], $api = false)
	{
		if($name == "")
		{
			return null;
		}

		if(!isset(self::ROUTES[$name]))
		{
			return self::ERROR;
		}

		$route = self::ROUTES[$name];
		$api = $api ? Router::NAMESPACE_API.'/' : null;
		$api = ltrim($api, '/');
		
		if(!$data)
		{
			return App::$url.'/'.$api.trim(preg_replace("/\{(\w+)(\(.*\))?(\??)\}/U", "", $route), '/');
		}

		$i = -1;
		$route = preg_replace_callback("/\{(\w+)(\(.*\))?(\??)\}/U", function($matches) use (&$i, $data) {
			$i++;
			return urlencode(isset($data[$matches[1]]) ? $data[$matches[1]] : (isset($data[$i]) ? $data[$i] : null));
		}, $route);

		return App::$url.'/'.$api.trim($route, '/');
	}

	public static function join($string = null, $route_name = null, $data = []) 
	{
		$route = self::get($route_name, $data);
		return trim($route, '/').$string;
	}

	public static function get_api($name = null, $data = []) {
		return self::get($name, $data, true);
	}

	public static function join_api($string = null, $route_name = null, $data = []) {
		return self::join($string, $route_name, $data, true);
	}

	public static function build_query($params, $route_name = null, $data = []) {
		$route = self::get($route_name, $data);
		return trim($route, '?').'?'.http_build_query($params);
	}

	public static function build_query_api($params, $route_name = null, $data = []) {
		$route = self::get($route_name, $data, true);
		return trim($route, '?').'?'.http_build_query($params);
	}
}

?>