<?php


class profileController implements Interface_controller {
	use Trait_block;

	const BLOCK_INFOMATION = 'Infomation';
	const BLOCK_BILLING = 'Billing';
	const BLOCK_LOGINDEVICE = 'Login-Device';
	const BLOCK_CHANGEPASSWORD = 'Change-Password';
	const BLOCK_SETTINGS = 'Settings';
	const BLOCK_STATISTICS	= 'Statistics';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';

	public function me($block = null, $action = null)
	{
		if(Auth::$isLogin != true)
		{
			return Router::redirect('*', RouteMap::get('dashboard'));
		}
		Language::load('auth.lng');

		$form_action = Request::post(self::INPUT_FORM_ACTION, null);

		switch($form_action)
		{
			case self::ACTION_UPLOAD_IMAGE:
				$type = null;
				if(isset($_POST['save-avatar']))
				{
					$type = AvatarCover::TYPE_AVATAR;
				}
		
				if(isset($_POST['save-cover']))
				{
					$type = AvatarCover::TYPE_COVER;
				}
				Alert::push(AvatarCover::upload_avatar_cover_profile(Request::post(self::INPUT_FORM_DATA_IMAGE, null), $type));
				return Router::redirect('*', Request::referer());
				break;
		}

		$block_view = null;

		switch($block) {
			case self::BLOCK_BILLING:
				$block_view = self::block_billing();
				break;
			case self::BLOCK_LOGINDEVICE:
				$block_view = self::block_logindevice();
				break;
			case self::BLOCK_CHANGEPASSWORD:
				$block_view = self::block_changepassword();
				break;
			case self::BLOCK_SETTINGS:
				$block_view = self::block_settings();
				break;
			default:
				$block = self::BLOCK_INFOMATION;
				$block_view = self::block_infomation();
				break;
		}
	

		$display_name = User::get_username(Auth::$data);

		return View::render('profile.me', compact('block', 'block_view', 'display_name'));
	}

	public function user($id, $block = null)
	{
		$user = User::get($id);

		if(!$user)
		{
			return ServerErrorHandler::error_404();
		}
		Language::load('auth.lng');
		$title = lang('system', 'txt_user').' '.$user['username'];

		$block_view = null;

		switch($block) {
			case self::BLOCK_BILLING:
				$block_view = self::block_billing($user);
				break;

			case self::BLOCK_STATISTICS:
				Language::load('statistic.lng');
				$block_view = self::block_statistics($user);
				break;
				
			default:
				$block = self::BLOCK_INFOMATION;
				$block_view =self::block_infomation($user);
				break;
		}

		$display_name = User::get_username($user);

		return View::render('profile.user', compact('title', 'user', 'block', 'block_view', 'display_name'));
	}

	public static function insertHiddenAction($action_name)
	{
		return '<input type="hidden" name="'.self::INPUT_FORM_ACTION.'" value="'.$action_name.'">';
	}
}





?>