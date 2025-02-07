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
				<label class="control-label"><?=lang('system_mailer', 'default_mode');?>:</label>
				<div class="form-control">
					<select role="change-mode-mailer" class="form-select js-custom-select" name="<?=DotEnv::MAILER_MODE;?>" data-placeholder="<?=lang('placeholder', 'select_mode');?>" data-max-width="300px">
                        <option value="<?=Mailer::MODE_SMTP;?>"><?=Mailer::MODE_SMTP;?></option>
                        <option value="<?=Mailer::MODE_API;?>" <?=($mailer_mode == Mailer::MODE_API ? 'selected' : null);?>><?=Mailer::MODE_API;?></option>
                    </select>
				</div>
			</div>

            <div class="form-group limit--width">
				<label class="control-label"><?=lang('system_mailer', 'default_template');?>:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=DotEnv::MAILER_TEMPLATE;?>" data-placeholder="<?=lang('placeholder', 'select_template');?>" data-max-width="300px">
                    <?php
                        if($list_template) {
                            foreach($list_template as $v) {
                                echo '<option value="'._echo($v).'" '.($mailer_tempalte == $v ? 'selected' : null).'>'._echo($v).'</option>';
                            }
                        }   
                    ?>
                    </select>
				</div>
			</div>

            <div class="form-group limit--width">
				<label class="control-label">Mailer Name</label>
                <div class="form-control">
				    <input class="form-input" name="<?=DotEnv::MAILER_NAME;?>" placeholder="No-reply" type="text" value="<?=_echo($mailer_name);?>">
                </div>
            </div>

            <div class="form-group limit--width">
				<label class="control-label">Mailer From</label>
                <div class="form-control">
				    <input class="form-input" name="<?=DotEnv::MAILER_FROM;?>" placeholder="No-reply@gmail.com" type="text" value="<?=_echo($mailer_from);?>">
                </div>
            </div>

            <div id="mailer-panel-smtp" class="mailer-panel <?=($mailer_mode == Mailer::MODE_SMTP ? 'show' : null);?>">
                <div class="form-group">
                    <label class="control-label">SMTP Authentication</label>
                    
                    <div class="form-control">
                        <div class="form-radio">
                            <input type="radio" id="smtp_authentication_enable" name="<?=adminPanelController::INPUT_MAILER_SMTP_AUTHENTICATION;?>" value="true" checked>
                            <label for="smtp_authentication_enable"><?=lang('system_mailer', 'txt_enable');?></label>
                        </div>
                        <div class="form-radio">
                            <input type="radio" id="smtp_authentication_disable" name="<?=adminPanelController::INPUT_MAILER_SMTP_AUTHENTICATION;?>" value="false" <?=($smtp_authencation != true ? 'checked' : null);?>>
                            <label for="smtp_authentication_disable"><?=lang('system_mailer', 'txt_disable');?></label>
                        </div>
                        <div class="label-desc"><?=lang('system_mailer', 'desc_authentication');?></div>
                    </div>
                </div>

                <div class="form-group limit--width">
                    <label class="control-label">SMTP Host</label>
                    <div class="form-control">
                        <input class="form-input" name="<?=adminPanelController::INPUT_MAILER_SMTP_HOST;?>" placeholder="smtp.gmail.com" type="text" value="<?=_echo($smtp_host);?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">SMTP Secure</label>
                    
                    <div class="form-control">
                        <div class="form-radio">
                            <input type="radio" id="smtp_secure_none" name="<?=adminPanelController::INPUT_MAILER_SMTP_SECURE;?>" value="" checked>
                            <label for="smtp_secure_none">None</label>
                        </div>
                        <div class="form-radio">
                            <input type="radio" id="smtp_secure_ssl" name="<?=adminPanelController::INPUT_MAILER_SMTP_SECURE;?>" value="ssl" <?=($smtp_secure == 'ssl' ? 'checked' : null);?>>
                            <label for="smtp_secure_ssl">SSL</label>
                        </div>
                        <div class="form-radio">
                            <input type="radio" id="smtp_secure_tls" name="<?=adminPanelController::INPUT_MAILER_SMTP_SECURE;?>" value="tls" <?=($smtp_secure == 'tls' ? 'checked' : null);?>>
                            <label for="smtp_secure_tls">TLS</label>
                        </div>
                    </div>
                </div>

                <div class="form-group limit--width">
                    <label class="control-label">SMTP Port</label>
                    <div class="form-control">
                        <input class="form-input" name="<?=adminPanelController::INPUT_MAILER_SMTP_PORT;?>" placeholder="465" type="text" value="<?=_echo($smtp_port);?>">
                        <div class="label-desc"><?=lang('system_mailer', 'desc_port');?></div>
                    </div>
                </div>

                <div class="form-group limit--width">
                    <label class="control-label">SMTP Username</label>
                    <div class="form-control">
                        <input class="form-input" name="<?=adminPanelController::INPUT_MAILER_SMTP_USERNAME;?>" placeholder="SMTP Username" type="text" value="<?=_echo($smtp_username);?>">
                    </div>
                </div>

                <div class="form-group limit--width">
                    <label class="control-label">SMTP Password</label>
                    <div class="form-control">
                        <input class="form-input" name="<?=adminPanelController::INPUT_MAILER_SMTP_PASSWORD;?>" placeholder="SMTP Password" type="text" value="<?=_echo($smtp_password);?>">
                    </div>
                </div>

            </div>

            <div id="mailer-panel-api" class="mailer-panel <?=($mailer_mode == Mailer::MODE_API ? 'show' : null);?>">
                <div class="form-group">
                    <label class="control-label">API Server</label>
                    
                    <div class="form-control">
                    <?php
                        if(Mailer::API_LIST) {
                            foreach(Mailer::API_LIST as $v) {
                                echo '<div class="form-radio">
                                        <input type="radio" id="api_server_'.$v.'" name="'.adminPanelController::INPUT_MAILER_API_SERVER.'" value="'.$v.'" '.($api_server == $v ? 'checked' : null).'>
                                        <label for="api_server_'.$v.'">'.$v.'</label>
                                    </div>';
                            }
                        }
                    ?>
                    </div>
                </div>

                <div class="form-group limit--width">
                    <label class="control-label">API Key</label>
                    <div class="form-control">
                        <input class="form-input" name="<?=adminPanelController::INPUT_MAILER_API_KEY;?>" placeholder="API key" type="text" value="<?=_echo($api_key);?>">
                    </div>
                </div>

                <div class="form-group limit--width">
                    <label class="control-label">API Secret</label>
                    <div class="form-control">
                        <input class="form-input" name="<?=adminPanelController::INPUT_MAILER_API_SECRET;?>" placeholder="API secret" type="text" value="<?=_echo($api_secret);?>">
                    </div>
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
            role_event('change', 'change-mode-mailer', function() {
                if($(this).val() == '<?=Mailer::MODE_API;?>') {
                    $('#mailer-panel-smtp').removeClass('show');
                    $('#mailer-panel-api').addClass('show');
                } else {
                    $('#mailer-panel-api').removeClass('show');
                    $('#mailer-panel-smtp').addClass('show');
                }
            });
        });
    })();
</script>