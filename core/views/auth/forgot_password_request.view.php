<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="UTF-8">
    	<title><?=$title;?></title>
   		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<link rel="icon" type="image/x-icon" href="<?=APP_URL;?>/assets/images/favico.ico">
		<link rel="shortcut icon" type="image/x-icon" href="<?=APP_URL;?>/assets/images/favico.ico">
		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/font-awesome/css/all.css" />

		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/login.css?t=<?=$_version;?>" />
	</head>
	<body>

		<div class="container">
			<div class="logo">Call<span>Center</span></div>
			<form class="form-box" method="POST">
				<div class="title">
					<span><?=lang('forgot_password', 'txt_title');?></span>
				</div>

			<?php
				if($error)
				{
					echo '<div class="alert_error">'.$error.'</div>';
				}
				else if($success)
				{
					echo '<div class="alert_success">'.$success.'</div>';
				}
			?>

				<div class="input-box <?=in_array($code_error, ['error_email_not_exist', 'error_email_empty']) ? 'error' : '';?>">
					<i class="fa fa-envelope form-control-feedback"></i>
					<input type="email" placeholder="<?=lang('forgot_password', 'txt_email');?>" name="<?=Auth::INPUT_EMAIL;?>" value="<?=_echo($email);?>" required>
				</div>
				<div class="option_div">
					<small><?=lang('forgot_password', 'html_note');?></small>
				</div>
				<div class="input-box button">
					<input type="submit" name="<?=authController::SUBMIT_NAME;?>" value="<?=lang('forgot_password', 'txt_submit');?>">
				</div>
				<div class="option"><?=lang('forgot_password', 'txt_already_account');?> <a href="<?=RouteMap::get('login');?>"><?=lang('forgot_password', 'txt_login_now');?></a></div>
			</form>
		</div>

	</body>
</html>