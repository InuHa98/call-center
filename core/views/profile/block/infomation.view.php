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

	<form id="form-validate" method="POST" class="<?=(!$is_edit ? 'only-read' : null);?>">
        <?=Security::insertHiddenToken();?>
        <?=profileController::insertHiddenAction(Interface_controller::ACTION_INFOMATION);?>

        <?php if($is_edit): ?>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'username');?></label>
                <div class="form-control">
                    <div class="input-group">
				        <input id="username" class="form-input disabled" placeholder="<?=lang('label', 'username');?>" type="text" value="<?=_echo($username);?>">
                        <div class="input-group-append">
                            <button type="button" role="change-username" class="btn btn-outline-gray btn-dim"><?=lang('button', 'change');?></button>
                        </div>
                    </div>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'email');?></label>
                <div class="form-control">
                    <div class="input-group">
				        <input id="email" class="form-input disabled" placeholder="<?=lang('label', 'email');?>" type="email" value="<?=_echo($email);?>">
                        <div class="input-group-append">
                            <button type="button" role="change-email" class="btn btn-outline-gray btn-dim"><?=lang('button', 'change');?></button>
                        </div>
                    </div>
                </div>
			</div>
        <?php endif; ?>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'full_name');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=Interface_controller::INPUT_FORM_NAME;?>" placeholder="<?=lang('label', 'full_name');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('system', 'date_of_birth');?></label>
                <div class="form-control">
				    <input class="form-input" id="valid_date_of_birth" name="<?=Interface_controller::INPUT_FORM_DATE_OF_BIRTH;?>" placeholder="<?=date('d/m/Y');?>" type="text" value="<?=_echo($date_of_birth);?>">
                </div>
            </div>
			<div class="form-group">
				<label class="control-label"><?=lang('system', 'sex');?></label>
                
                <div class="form-control">
                    <div class="form-radio">
                        <input type="radio" id="sex_unknown" name="<?=Interface_controller::INPUT_FORM_SEX;?>" value="<?=User::SEX_UNKNOWN;?>" checked>
                        <label for="sex_unknown"><?=lang('system', 'sex_unknown');?></label>
                    </div>
                    <div class="form-radio">
                        <input type="radio" id="sex_male" name="<?=Interface_controller::INPUT_FORM_SEX;?>" value="<?=User::SEX_MALE;?>" <?=($sex == User::SEX_MALE ? 'checked' : null);?>>
                        <label for="sex_male"><?=lang('system', 'sex_male');?></label>
                    </div>
                    <div class="form-radio">
                        <input type="radio" id="sex_female" name="<?=Interface_controller::INPUT_FORM_SEX;?>" value="<?=User::SEX_FEMALE;?>" <?=($sex == User::SEX_FEMALE ? 'checked' : null);?>>
                        <label for="sex_female"><?=lang('system', 'sex_female');?></label>
                    </div>
				</div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label">Facebook</label>
                <div class="form-control">
				    <input id="facebook" class="form-input" name="<?=Interface_controller::INPUT_FORM_FACEBOOK;?>" placeholder="https://facebook.com/xxx" type="text" value="<?=_echo($facebook);?>">
                </div>
			</div>

        <?php if($is_edit): ?>
		<div class="margin-y-2">
			<button type="submit" class="btn btn--round pull-right"><?=lang('button', 'update');?></button>
		</div>
        <?php endif; ?>
	</form>


<?php if($is_edit): ?>
<script type="text/javascript">
	$(document).ready(function(){

        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            class_message: null,
            rules: {
                '#valid_date_of_birth': function(value) {
                    return !value || /^([0-9]+){1,2}\/([0-9]+){1,2}\/([0-9]+){4,4}$/g.test(value) ? undefined : '<?=lang('profile', 'error_birth');?>';
                },
                '#facebook': function(value) {
                    return !value || /^(\s+)?https?:\/\/((.*)\.)?(fb\.com|facebook\.com)\/(.*?)$/u.test(value) ? undefined : '<?=lang('profile', 'error_facebook');?>';
                }
            }
        });

        $(document).on('click', '[role=change-email]', function() {
            var idForm = 'dialogForm';

            $.dialogShow({
                title: '<?=lang('profile', 'change_email');?>',
                content: '\
                    <form id="'+idForm+'" method="post">\
                        <?=Security::insertHiddenToken();?>\
                        <?=profileController::insertHiddenAction(Interface_controller::ACTION_CHANGE_EMAIL);?>\
                        <div class="dialog-label"><?=lang('profile', 'new_email');?>:</div>\
                        <div class="form-group">\
                            <div class="form-control">\
                                <input type="email" class="form-input" name="<?=Interface_controller::INPUT_FORM_EMAIL;?>" placeholder="<?=lang('profile', 'new_email');?>" value="'+$('#email').val()+'">\
                            </div>\
                        </div>\
                        <div class="dialog-label margin-t-2"><?=lang('label', 'password');?>:</div>\
                        <div class="form-group">\
                            <div class="form-control">\
                                <input type="password" class="form-input" name="password" placeholder="<?=lang('label', 'password');?>">\
                            </div>\
                        </div>\
                    </form>',
                button: {
                    confirm: '<?=lang('button', 'change');?>',
                    cancel: '<?=lang('button', 'cancel');?>'
                },
                isCenter: false,
                bgHide: false,
                onInit: function() {
                    Validator({
                        form: '#'+idForm,
                        selector: '.form-control',
                        class_error: 'error',
                        class_message: null,
                        rules: {
                            '[type=email]': [
                                Validator.isRequired(),
                                Validator.isEmail()
                            ],
                            '[type=password]': Validator.isRequired()
                        }
                    });
                },
                onBeforeConfirm: function() {
                    $.ajax({
                        type: "GET",
                        url: "<?=RouteMap::get('ajax', ['name' => ajaxController::SEARCH_USER]);?>",
                        data: {
                            <?=InterFaceRequest::EMAIL;?>: $('#' + idForm).find('input[type="email"]').val(),
                            <?=InterFaceRequest::MODE;?>: "<?=ajaxController::MODE_STRICT;?>",
                            <?=InterFaceRequest::EXCLUDE;?>: <?=Auth::$data['id'];?>
                        },
                        dataType: 'json',
                        cache: false,
                        success: function(response)
                        {
                            if(response.code == 200)
                            {
                                $.toastShow('<?=lang('errors', 'email_exists');?>', {
                                    type: 'error',
                                    timeout: 3000
                                });
                                return;
                            }
                            $('#' + idForm).submit();
                        },
                        error: function(response)
                        {
                            $('#' + idForm).submit();
                        }
                    });
                    return false;
                }
            });
        });

        $(document).on('click', '[role=change-username]', function() {
            var idForm = 'dialogForm';

            $.dialogShow({
                title: '<?=lang('profile', 'change_username');?>',
                content: '\
                    <form id="'+idForm+'" method="post">\
                        <?=Security::insertHiddenToken();?>\
                        <?=profileController::insertHiddenAction(Interface_controller::ACTION_CHANGE_USERNAME);?>\
                        <div class="dialog-label"><?=lang('profile', 'new_username');?>:</div>\
                        <div class="form-group">\
                            <div class="form-control">\
                                <input type="text" class="form-input" name="<?=Interface_controller::INPUT_FORM_USERNAME;?>" placeholder="<?=lang('profile', 'new_username');?>" value="'+$('#username').val()+'">\
                            </div>\
                        </div>\
                        <div class="dialog-label margin-t-2"><?=lang('label', 'password');?>:</div>\
                        <div class="form-group">\
                            <div class="form-control">\
                                <input type="password" class="form-input" name="password" placeholder="<?=lang('label', 'password');?>">\
                            </div>\
                        </div>\
                    </form>',
                button: {
                    confirm: '<?=lang('button', 'change');?>',
                    cancel: '<?=lang('button', 'cancel');?>'
                },
                isCenter: false,
                bgHide: false,
                onInit: function() {
                    Validator({
                        form: '#'+idForm,
                        selector: '.form-control',
                        class_error: 'error',
                        class_message: null,
                        rules: {
                            '[type=text]': [
                                Validator.isRequired(),
                                Validator.minLength(<?=Auth::USERNAME_MIN_LENGTH;?>, '<?=lang('errors', 'username_length', ['min' => Auth::USERNAME_MIN_LENGTH, 'max' => Auth::USERNAME_MAX_LENGTH]);?>'),
                                Validator.maxLength(<?=Auth::USERNAME_MAX_LENGTH;?>, '<?=lang('errors', 'username_length', ['min' => Auth::USERNAME_MIN_LENGTH, 'max' => Auth::USERNAME_MAX_LENGTH]);?>'),
                                Validator.isUsername()
                            ],
                            '[type=password]': Validator.isRequired()
                        }
                    });

                },
                onBeforeConfirm: function() {

                    $.ajax({
                        type: "GET",
                        url: "<?=RouteMap::get('ajax', ['name' => ajaxController::SEARCH_USER]);?>",
                        data: {
                            <?=InterFaceRequest::KEYWORD;?>: $('#' + idForm).find('input[type="text"]').val(),
                            <?=InterFaceRequest::MODE;?>: "<?=ajaxController::MODE_STRICT;?>",
                            <?=InterFaceRequest::EXCLUDE;?>: <?=Auth::$data['id'];?>
                        },
                        dataType: 'json',
                        cache: false,
                        success: function(response)
                        {
                            if(response.code == 200)
                            {
                                $.toastShow('<?=lang('errors', 'username_exists');?>', {
                                    type: 'error',
                                    timeout: 3000
                                });
                                return;
                            }
                            $('#' + idForm).submit();
                        },
                        error: function(response)
                        {
                            $('#' + idForm).submit();
                        }
                    });

                    return false;
                }
            });
        });

});
</script>
<?php endif; ?>