<?php


class ServerErrorHandler {

	public static function error_404()
	{
		ob_start();
		header(Request::protocol()." 404 Not Found", true, 404);

		$title = lang('error_404', 'title');
		$text = lang('error_404', 'text');
		$desc = lang('error_404', 'desc');
		$button = lang('error_404', 'button');
		View::render('error.404', compact('title', 'text', 'desc', 'button'));
		exit();
	}

	public static function error_403()
	{
		ob_start();
		header(Request::protocol()." 403 Forbidden", true, 403);

		$title = lang('error_403', 'title');
		$text = lang('error_403', 'text');
		$desc = lang('error_403', 'desc');
		$button = lang('error_403', 'button');
		View::render('error.403', compact('title', 'text', 'desc', 'button'));
		exit();
	}
}


?>