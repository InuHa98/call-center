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
				<label class="control-label"><?=lang('label', 'status');?></label>
                <div class="form-control">
                    <div class="form-switch">
                        <input type="checkbox" id="status" name="<?=DotEnv::AUTO_BAN;?>" value="1" <?=($status ? 'checked' : null);?>>
                        <label for="status"><?=lang('status', 'disable');?> / <?=lang('status', 'enable');?></label>
                    </div>
                    <div class="label-desc"><?=lang('system_auto_ban', 'desc_status');?></div>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('system_auto_ban', 'order');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
                        </div>
                        <input type="text" id="order" class="form-input" name="<?=DotEnv::AUTO_BAN_ORDER_CHECK;?>" placeholder="<?=lang('system_auto_ban', 'order');?>" value="<?=_echo($order);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="label-desc"><?=lang('system_auto_ban', 'desc_order', ['min' => DotEnv::AUTO_BAN_MIN_ORDER]);?></div>
                </div>
            </div>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('system_auto_ban', 'rpo');?></label>
                <div class="form-control">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
                        </div>
                        <input type="text" id="rpo" class="form-input" name="<?=DotEnv::AUTO_BAN_RPO;?>" placeholder="<?=lang('system_auto_ban', 'rpo');?>" value="<?=_echo($rpo);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="label-desc"><?=lang('system_auto_ban', 'desc_rpo');?></div>
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
            const input_order = $('#order');
            const input_rpo = $('#rpo');

            input_order.on('input', function(event) {
                var value = event.target.value;
                var validValue = value.replace(/\D/g, '');
                
                if (validValue.startsWith('0') && validValue !== '0' && validValue !== '0.') {
                    validValue = validValue.substring(1);
                }

                event.target.value = validValue;
            }).on('change', function(event) {
                if (event.target.value === '' || event.target.value < <?=DotEnv::AUTO_BAN_MIN_ORDER;?>) {
                    event.target.value = <?=DotEnv::AUTO_BAN_MIN_ORDER;?>;
                    $(this).change();
                }
            });

            input_rpo.on('input', function(event) {
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

            $('[role="btn-plus"]').on('click', function() {
                const input = $(this).parents('.input-group').find('input');
                input.val(parseInt(input.val()) + 1).change();
            });

            $('[role="btn-minus"]').on('click', function() {
                const input = $(this).parents('.input-group').find('input');
                const value = parseInt(input.val()) - 1;
                input.val(value > 0 ? value : 1).change();
            });


        });
    })();
</script>