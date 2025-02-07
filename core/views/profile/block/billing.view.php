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
        <?=profileController::insertHiddenAction(Interface_controller::ACTION_BILLING);?>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'account_name');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <input class="form-input" name="<?=Interface_controller::INPUT_BILLING_ACCOUNT_NAME;?>" placeholder="<?=lang('label', 'account_name');?>" type="text" value="<?=_echo($account_name);?>">
                        <div class="input-group-append">
                            <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>
			</div>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'account_number');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <input class="form-input" name="<?=Interface_controller::INPUT_BILLING_ACCOUNT_NUMBER;?>" placeholder="<?=lang('label', 'account_number');?>" type="text" value="<?=_echo($account_number);?>">
                        <div class="input-group-append">
                            <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'bank_name');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <input class="form-input" name="<?=Interface_controller::INPUT_BILLING_BANK_NAME;?>" placeholder="<?=lang('label', 'bank_name');?>" type="text" value="<?=_echo($bank_name);?>">
                        <div class="input-group-append">
                            <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>
			</div>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'bank_branch');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <input class="form-input" name="<?=Interface_controller::INPUT_BILLING_BANK_BRANCH;?>" placeholder="<?=lang('label', 'bank_branch');?>" type="text" value="<?=_echo($bank_branch);?>">
                        <div class="input-group-append">
                            <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>
			</div>

        <?php if($is_edit): ?>
		<div class="margin-y-2">
			<button type="submit" class="btn btn--round pull-right"><?=lang('button', 'update');?></button>
		</div>
        <?php endif; ?>
	</form>



<script type="text/javascript">
	$(document).ready(function(){

        $(document).on('click', '[role="copy"]', function() {
            const input = $(this).parents('.input-group').find('input');
            input.select();
            document.execCommand('copy');
            $.toastShow('<?=lang('system', 'txt_copy_success');?>', {
                type: 'success',
                timeout: 3000
            });
        });

    });
</script>
