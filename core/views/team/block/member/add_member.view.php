<?php
if($error)
{
    echo '<div class="alert alert--error">'.$error.'</div>';
}
else if($success)
{
    echo '<div class="alert alert--success">'.$success.'</div>';
}


?>


<div class="box">
	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <div class="box__header"><?=lang('team', 'txt_add');?></div>
		<div class="box__body">
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'username');?></label>
                <div class="form-control">
                    <span class="form-control-feedback"><i class="fas fa-user"></i></span>
                    <input id="username" class="form-input" name="<?=Auth::INPUT_USERNAME;?>" placeholder="<?=lang('label', 'username');?>" type="text" value="<?=_echo($username);?>">
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'password');?></label>
                <div class="form-control">
                    <span class="form-control-feedback"><i class="fas fa-key"></i></span>
                    <input id="password" class="form-input" name="<?=Auth::INPUT_PASSWORD;?>" placeholder="<?=lang('label', 'password');?>" type="password" value="<?=_echo($password);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'confirm_password');?></label>
                <div class="form-control">
                    <span class="form-control-feedback"><i class="fas fa-key"></i></span>
                    <input id="confirm-password" class="form-input" name="<?=Auth::INPUT_REPASSWORD;?>" placeholder="<?=lang('label', 'confirm_password');?>" type="password" value="<?=_echo($rePassword);?>">
                </div>
			</div>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'email');?></label>
                <div class="form-control">
                    <span class="form-control-feedback"><i class="far fa-envelope"></i></span>
                    <input id="email" class="form-input" name="<?=Auth::INPUT_EMAIL;?>" placeholder="<?=lang('label', 'email');?>" type="email" value="<?=_echo($email);?>">
                </div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('button', 'add');?></button>
			<a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>

<?=assetController::load_js('form-validator.js');?>

<script type="text/javascript">
	$(document).ready(function() {
        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            rules: {
                '#username': [
                    Validator.isRequired(),
                    Validator.isUsername('<?=lang('errors', 'username');?>'),
                    Validator.minLength(<?=Auth::USERNAME_MIN_LENGTH;?>, '<?=lang('errors', 'username_length', ['min' => Auth::USERNAME_MIN_LENGTH, 'max' => Auth::USERNAME_MAX_LENGTH]);?>'),
                    Validator.maxLength(<?=Auth::USERNAME_MAX_LENGTH;?>, '<?=lang('errors', 'username_length', ['min' => Auth::USERNAME_MIN_LENGTH, 'max' => Auth::USERNAME_MAX_LENGTH]);?>')
                ],
                '#password': [
                    Validator.isRequired(),
                    Validator.minLength(<?=Auth::PASSWORD_MIN_LENGTH;?>, '<?=lang('errors', 'min_password', ['max' => Auth::PASSWORD_MIN_LENGTH]);?>'),
                    Validator.maxLength(<?=Auth::PASSWORD_MAX_LENGTH;?>, '<?=lang('errors', 'max_password', ['max' => Auth::PASSWORD_MAX_LENGTH]);?>')
                ],
                '#confirm-password': [
                    Validator.isRequired(),
                    Validator.isConfirmed($('#password')[0], '<?=lang('errors', 'confirm_password');?>')
                ],
                '#email': [
                    Validator.isRequired(),
                    Validator.isEmail('<?=lang('errors', 'email');?>')
                ]
            }
        });
	});
</script>