<?php

class View {
	
	private static $data = [];
	private const FILE_VIEW = '.view.php';

	private static $theme_path = null;


	public static function render($viewName = null, $data = null)
	{
		$path = rtrim(VIEW_PATH, '/').'/';
		$view = self::formatViewName($viewName);

		if(!is_file($path.$view))
		{
			$view = self::formatViewName($viewName, true);
			if(!is_file($path.$view))
			{
				die('File View not found: <b>'.$viewName.'</b>');
			}
		}

		if(self::$data)
		{
			extract(self::$data);
		}

		if($data)
		{
			if(!is_array($data))
			{
				$data = [$data];
			}
			extract($data);

		}
		include_once $path.$view;
	}

	private static function formatViewName($viewName = null, $replace_dot = false)
	{
		$viewName = trim(($replace_dot != false ? str_replace('.', '/', $viewName) : $viewName), '/');
		return $viewName.self::FILE_VIEW;
	}

	public static function addData($data = null, $value = null)
	{
		if(!is_array($data) && $value != "")
		{
			$data = [$data => $value];
		}

		if($data == "")
		{
			return false;
		}

		self::$data = array_merge(self::$data, is_array($data) ? $data : [$data]);
		return true;
	}

	public static function getData($key = null)
	{
		if($key == "")
		{
			return self::$data;
		}
		return isset(self::$data[$key]) ? self::$data[$key] : null;
	}
}

?>