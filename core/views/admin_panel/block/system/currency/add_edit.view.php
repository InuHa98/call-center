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
        <div class="box__header"><?=$txt_description;?></div>
		<div class="box__body">
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'currency');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="<?=lang('label', 'currency');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'exchange_rate');?></label>
                <div class="form-control">
                    <input class="form-input only-number" name="<?=adminPanelController::INPUT_RATE;?>" placeholder="<?=lang('label', 'exchange_rate');?>" type="text" value="<?=_echo($exchange_rate);?>">
                </div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CURRENCY]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		
		$('.only-number').on('input', function(event) {
			var value = event.target.value;
			var validValue = value.replace(/[^0-9.]/g, '');
			if (validValue.indexOf('.') !== validValue.lastIndexOf('.')) {
				validValue = validValue.slice(0, validValue.lastIndexOf('.'));
			}
			if(validValue.startsWith('.')) {
                validValue = '0' + validValue;
            }

            if (validValue.length > 0 && validValue[0] === '0' && validValue !== '0' && validValue[1] !== '.') {
                validValue = validValue.substring(1);
            }
			event.target.value = validValue;
		});

	});
</script>