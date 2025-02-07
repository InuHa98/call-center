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
		<div class="box__body">
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('system_configuration', 'app_name');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=DotEnv::APP_NAME;?>" placeholder="<?=lang('system_configuration', 'app_name');?>" type="text" value="<?=_echo($app_name);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('system_configuration', 'app_title');?></label>
                <div class="form-control">
				    <input class="form-input" name="<?=DotEnv::APP_TITLE;?>" placeholder="<?=lang('system_configuration', 'app_title');?>" type="text" value="<?=_echo($app_title);?>">
                </div>
            </div>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('system_configuration', 'limit_page');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
                        </div>
                        <input type="text" id="limit-page" class="form-input" name="<?=DotEnv::APP_LIMIT_ITEM_PAGE;?>" placeholder="<?=lang('system_configuration', 'limit_page');?>" value="<?=_echo($limit_item_page);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('system_configuration', 'email');?></label>
                <div class="form-control">
				    <input class="form-input" name="<?=DotEnv::APP_EMAIL;?>" placeholder="<?=lang('system_configuration', 'email');?>" type="text" value="<?=_echo($app_email);?>">
                </div>
            </div>

			<div class="form-group">
				<label class="control-label"><?=lang('system_configuration', 'profile_upload_mode');?></label>
                
                <div class="form-control">
                    <div class="form-radio">
                        <input type="radio" id="profile_upload_mode_localhost" name="<?=DotEnv::PROFILE_UPLOAD_MODE;?>" value="<?=App::PROFILE_UPLOAD_MODE_LOCALHOST;?>" checked>
                        <label for="profile_upload_mode_localhost">Localhost</label>
                    </div>
                    <div class="form-radio">
                        <input type="radio" id="profile_upload_mode_imgur" name="<?=DotEnv::PROFILE_UPLOAD_MODE;?>" value="<?=App::PROFILE_UPLOAD_MODE_IMGUR;?>" <?=($profile_upload_mode == App::PROFILE_UPLOAD_MODE_IMGUR ? 'checked' : null);?>>
                        <label for="profile_upload_mode_imgur">Imgur</label>
                    </div>
                    <div class="label-desc"><div class="label-desc"><?=lang('system_configuration', 'desc_profile_upload_mode');?></div></div>
				</div>
			</div>

            <div class="form-group">
				<label class="control-label"><?=lang('system_configuration', 'limit_login');?></label>
                
                <div class="form-control">
                    <div class="form-switch">
                        <input type="checkbox" id="limit_login" name="<?=DotEnv::LIMIT_LOGIN;?>" value="1" <?=($limit_login ? 'checked' : null);?>>
                        <label for="limit_login"><?=lang('system_configuration', 'txt_disable');?> / <?=lang('system_configuration', 'txt_enable');?></label>
                    </div>
                    <div class="label-desc"><?=lang('system_configuration', 'desc_limit_login');?></div>
				</div>
			</div>

            <div class="form-group limit--width">
				<label class="control-label"><?=lang('system_configuration', 'language');?></label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=DotEnv::DEFAULT_LANGUAGE;?>" data-placeholder="<?=lang('placeholder', 'select_language');?>" data-max-width="300px">
                    <?php 

                        foreach(Language::list() as $lang => $name)
                        {
                            echo '<option value="'.$lang.'" '.($default_language == $lang ? 'selected' : null).'>'.$name.'</option>';
                        }

                    ?>
						
                    </select>
				</div>
			</div>

            <div class="form-group">
				<label class="control-label"><?=lang('system_configuration', 'imgur_api');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <input class="form-input" name="<?=DotEnv::IMGUR_CLIENT_ID;?>[]" placeholder="<?=lang('system_configuration', 'txt_client_id');?>" type="text" value="<?=(isset($imgur_client_id[0]) ? _echo($imgur_client_id[0]) : null);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-add-input" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                <?php if(isset($imgur_client_id[1])): ?>
                <?php
                    array_shift($imgur_client_id); foreach($imgur_client_id as $key): ?>
                    <div class="input-group">
                        <input class="form-input" name="<?=DotEnv::IMGUR_CLIENT_ID;?>[]" placeholder="<?=lang('system_configuration', 'txt_client_id');?>" type="text" value="<?=_echo($key);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>

		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
		</div>
	</form>
</div>

<script type="text/javascript">
    (function() {
        $(document).ready(function() {
            const input_limit_page = $('#limit-page');

            input_limit_page.on('input', function(event) {
                var value = event.target.value;
                var validValue = value.replace(/\D/g, '');
                
                if (validValue.startsWith('0') && validValue !== '0' && validValue !== '0.') {
                    validValue = validValue.substring(1);
                }

                event.target.value = validValue;
            }).on('change', function(event) {
                if (event.target.value === '') {
                    event.target.value = 1;
                    $(this).change();
                }
            });

            $('[role="btn-plus"]').on('click', function() {
                input_limit_page.val(parseInt(input_limit_page.val()) + 1).change();
            });

            $('[role="btn-minus"]').on('click', function() {
                const limit_page = parseInt(input_limit_page.val()) - 1;
                input_limit_page.val(limit_page > 0 ? limit_page : 1).change();
            });

            $('[role="btn-add-input"]').on('click', function() {
                let input_group = $(this).parents('.input-group');
                let new_input = input_group.clone();
                new_input.find('input').val('');
                new_input.find('.input-group-append').html('<button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>');
                input_group.parent().append(new_input);
            });

            $(document).on('click', '[role="btn-remove-input"]', function() {
                let input_group = $(this).parents('.input-group');
                input_group.remove();
            });
        });
    })();
</script>