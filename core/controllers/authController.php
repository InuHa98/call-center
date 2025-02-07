<?php


class authController
{

	private const FORGOT_PASSWORD_EXPIRED = 30; // thời gian tồn tại yêu cầu lấy lại mật khẩu (phút)

	public const SUBMIT_NAME = 'submit'; 

	public function __construct()
	{
		Language::load('auth.lng');
	}

	public function login()
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$title = lang('login', 'title');

		$username = trim(Request::post(Auth::INPUT_USERNAME, null));
		$password = trim(Request::post(Auth::INPUT_PASSWORD, null));
		$stay     = boolval(Request::post(Auth::INPUT_STAYLOGIN, false));
		$referer = Request::server('HTTP_REFERER', APP_URL);

		$success = null;
		$error = null;

		if(isset($_POST[self::SUBMIT_NAME]))
		{
			
			Role::setup_default_role();

			if(Auth::login($username, $password, $stay) == true)
			{
				if(Auth::$data['is_ban'] != User::IS_NOT_BAN) {
					$error = lang('login', 'banned');
					User::update(Auth::$data['id'], [
						'auth_session' => ''
					]);
				} else {
					$success = lang('login', 'success_login');
					return Router::redirect('*', $referer);
				}
			}
			else
			{
				$error = lang('login', 'error_login');
			}
		}
		return View::render('auth.login', compact('title', 'username', 'password', 'stay', 'referer', 'success', 'error'));
	}


	public function register()
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$title = lang('register', 'title');
		
		$username   = trim(Request::post(Auth::INPUT_USERNAME, null));
		$password   = trim(Request::post(Auth::INPUT_PASSWORD, null));
		$rePassword = trim(Request::post(Auth::INPUT_REPASSWORD, null));
		$email      = trim(Request::post(Auth::INPUT_EMAIL, null));
		$registerKey = trim(Request::post(Auth::INPUT_REGISTER_KEY, null));

		$success = null;
		$error = null;
		$code_error = null;

		if(isset($_POST[self::SUBMIT_NAME]))
		{

			Role::setup_default_role();

			if(!Auth::check_length_username($username))
			{
				$code_error = 'error_username_length';
			}
			else if(!Auth::check_type_username($username))
			{
				$code_error = 'error_username_char';
			}
			else if(User::has(['username[~]' => $username]))
			{
				$code_error = 'error_username_exist';
			}
			else if(!Auth::check_length_password($password))
			{
				$code_error = 'error_password_length';
			}
			else if($password !== $rePassword)
			{
				$code_error = 'error_password_reinput';
			}
			else if(!Auth::check_type_email($email))
			{
				$code_error = 'error_email_format';
			}
			else if(User::has(['email[~]' => $email]))
			{
				$code_error = 'error_email_exist';
			}


			if($code_error != null)
			{
				switch($code_error)
				{
					case 'error_username_length':
						$error = lang('register', $code_error, [
							'min' => Auth::USERNAME_MIN_LENGTH,
							'max' => Auth::USERNAME_MAX_LENGTH
						]);
						break;
					case 'error_password_length':
						$error = lang('register', $code_error, [
							'min' => Auth::PASSWORD_MIN_LENGTH,
							'max' => Auth::PASSWORD_MAX_LENGTH
						]);
						break;
					default:
						$error = lang('register', $code_error);
						break;
				}
			}
			else
			{
				if(User::create([
					'username' => $username,
					'password' => $password,
					'email' => $email
				]) == true)
				{
					$success = lang('register', 'success_register');
				}
				else
				{
					$error = lang('system', 'default_error');
				}
			}
		}
		return View::render('auth.register', compact('title', 'username', 'password', 'rePassword', 'email', 'registerKey', 'success', 'error', 'code_error'));
	}

	public function forgot_password_request()
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$title = lang('forgot_password', 'title');

		$email = trim(Request::post(Auth::INPUT_EMAIL, null));

		$success = null;
		$error = null;
		$code_error = null;



		if(isset($_POST[self::SUBMIT_NAME]))
		{

			if($email == "")
			{
				$code_error = 'error_email_empty';
			}
			else if(!Auth::check_type_email($email))
			{
				$code_error = 'error_email_format';
			}
			else if(!$user = User::get(['email' => $email]))
			{
				$code_error = 'error_email_not_exist';
			}

			if($code_error != null)
			{
				$error = lang('forgot_password', $code_error);
			}
			else
			{
				$key = md5(uniqid($email ? $email : time(), true));
				$time = time() + (self::FORGOT_PASSWORD_EXPIRED * 60);
				$url = RouteMap::join('/'.$key, 'forgot_password');
								
		    	if(User::update($user['id'], [
			        'forgot_key' => $key,
			        'forgot_time' => $time
			    ]) > 0)
			    {
					$mailer = new Mailer();

					$subject = lang('forgot_password', 'mail_request_title', ['title' => env(DotEnv::APP_NAME)]);
					$message = lang('forgot_password', 'mail_request_content', [
						'username' => $user['username'],
						'expired' => self::FORGOT_PASSWORD_EXPIRED,
						'time' => date('H:i d/m/Y', $time),
						'url' => $url
					]);
					$footer = lang('forgot_password', 'mail_request_footer', ['url' => $url]);

					if($mailer::send($email, $subject, $mailer::template($message, ["footer" => $footer])) == true)
					{
						$success = lang('forgot_password', 'success_send_request', ['email' => $email, 'time' => self::FORGOT_PASSWORD_EXPIRED]);
					}
					else
					{
						$error = lang('system', 'default_error');
					}
			    }
			}
		}
		return View::render('auth.forgot_password_request', compact('title', 'email', 'success', 'error', 'code_error'));
	}

	public function forgot_password_change($key = null)
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$user = User::get(['forgot_key' => $key]);

		if(!$user || $user['forgot_time'] < time())
		{
			return $this->goToHome();
		}

		$title = lang('forgot_password', 'title');

		$password = trim(Request::post(Auth::INPUT_PASSWORD, null));
		$rePassword = trim(Request::post(Auth::INPUT_REPASSWORD, null));

		$success = null;
		$error = null;
		$code_error = null;


		if(isset($_POST[self::SUBMIT_NAME]))
		{
			if(!Auth::check_length_password($password))
			{
				$code_error = 'error_password_length';
				$error = lang('forgot_password', $code_error, [
					'min' => Auth::PASSWORD_MIN_LENGTH,
					'max' => Auth::PASSWORD_MAX_LENGTH
				]);
			}
			else if($password !== $rePassword)
			{
				$code_error = 'error_password_reinput';
				$error = lang('forgot_password', $code_error);
			}

			if($code_error == null)
			{
		    	if(User::update($user['id'], [
			        'forgot_key' => '',
			        'forgot_time' => 0,
			        'password' => Auth::encrypt_password($password)
			    ]) > 0)
			    {
			    	$success = lang('forgot_password', 'success_change_password');
			    }
			    else
			    {
			    	$error = lang('system', 'default_error');
			    }
			}
		}
		return View::render('auth.forgot_password_verify', compact('title', 'password', 'rePassword', 'success', 'error', 'code_error'));
	}

	

	public function change_password($new_password = null)
	{
		if(!Auth::isLogin())
		{
			return false;
		}

		if(!Auth::check_length_password($new_password))
		{
			return false;
		}

		if(User::update(Auth::$data['id'], [
			'password' => Auth::encrypt_password($new_password)
		]) > 0)
		{
			return true;
		}

		return false;
	}

	public function logout()
	{
		Security::clear();
		Auth::logout();
		return $this->goToHome();
	}

	private function goToHome()
	{
		return Router::redirect('*', RouteMap::get('dashboard'));
	}

}





?>