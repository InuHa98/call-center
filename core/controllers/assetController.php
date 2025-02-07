<?php


class assetController {

	public static function load_image($path = null)
	{
		if($path == "")
		{
			return null;
		}

		return APP_URL.'/assets/images/'.trim($path, '/');
	}

	public static function load_css($path = null, $type = "text/css")
	{
		if($path == "")
		{
			return null;
		}

		return '<link rel="stylesheet" type="'.$type.'" href="'.APP_URL.'/assets/css/'.trim($path, '/').'?v='.env('APP_VERSION', 0).'" />';
	}

	public static function load_js($path = null, $type = "text/javascript")
	{
		if($path == "")
		{
			return null;
		}

		return '<script type="'.$type.'" src="'.APP_URL.'/assets/js/'.trim($path, '/').'?v='.env('APP_VERSION', 0).'"></script>';
	}
}





?>