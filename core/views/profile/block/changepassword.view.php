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

	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <?=profileController::insertHiddenAction(Interface_controller::ACTION_CHANGEPASSWORD);?>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('profile', 'new_password');?></label>
                <div class="form-control">
                    <input class="form-input" id="new_password" name="<?=Interface_controller::INPUT_FORM_NEW_PASSWORD;?>" placeholder="<?=lang('profile', 'new_password');?>" type="password">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('profile', 'confirm_new_password');?></label>
                <div class="form-control">
				    <input class="form-input" id="confirm_password" name="<?=Interface_controller::INPUT_FORM_CONFIRM_PASSWORD;?>" placeholder="<?=lang('profile', 'confirm_new_password');?>" type="password">
                </div>
            </div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('profile', 'old_password');?></label>
                <div class="form-control">
				    <input class="form-input" id="current_password" name="<?=Interface_controller::INPUT_FORM_PASSWORD;?>" placeholder="<?=lang('profile', 'old_password');?>" type="password">
                </div>
            </div>

		<div class="margin-y-2">
			<button type="submit" class="btn btn--round pull-right"><?=lang('button', 'change_password');?></button>
		</div>
	</form>



<script type="text/javascript">
	$(document).ready(function() {
        var min = <?=Auth::PASSWORD_MIN_LENGTH;?>;
        var max = <?=Auth::PASSWORD_MAX_LENGTH;?>;
        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            rules: {
                '#new_password': [
                    Validator.minLength(min, '<?=lang('errors', 'min_password', ['min' => Auth::PASSWORD_MIN_LENGTH]);?>'),
                    Validator.maxLength(max, '<?=lang('errors', 'max_password', ['max' => Auth::PASSWORD_MAX_LENGTH]);?>')
                ],
                '#confirm_password': [
                    Validator.isRequired(),
                    Validator.isConfirmed(document.querySelector('#new_password'), '<?=lang('errors', 'confirm_password');?>')
                ],
                '#current_password': [
                    Validator.isRequired()
                ]
            }
        });
    });
</script>