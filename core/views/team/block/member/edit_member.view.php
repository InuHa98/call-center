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
        <div class="box__header">
            <span><?=lang('team', 'txt_edit');?></span>
            <span class="action">
                <a target="_blank" class="user-infomation bg--white" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
                    <span class="user-avatar avatar--small">
                        <img src="<?=User::get_avatar($user);?>" />
                        <?=no_avatar($user);?>
                    </span>
                    <span class="user-display-name"><?=User::get_username($user);?></span>
                </a>
            </span>
        </div>
		<div class="box__body">

        <?php if($is_caller): ?>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('team', 'profit_call');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="call-profit" class="form-input only-number" name="<?=teamController::INPUT_PROFIT_CALL;?>" placeholder="<?=lang('team', 'profit_call');?>" type="text" value="<?=_echo($profit_call);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
				<?php if($is_admin): ?>
                    <div class="label-desc">= <strong id="text-profit-call"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                <?php endif; ?>
                    <div class="label-desc"><?=lang('team', 'txt_limit');?> <?=Currency::format($team['profit_call']);?></div>
                </div>
			</div>
        <?php endif; ?>

        <?php if($is_advertiser): ?>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team', 'profit_ads');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ads-profit" class="form-input only-number" name="<?=teamController::INPUT_PROFIT_ADS;?>" placeholder="<?=lang('team', 'profit_ads');?>" type="text" value="<?=_echo($profit_ads);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
				<?php if($is_admin): ?>
                    <div class="label-desc">= <strong id="text-profit-ads"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                <?php endif; ?>
                    <div class="label-desc"><?=lang('team', 'txt_limit');?> <?=Currency::format($team['profit_ads']);?></div>
                </div>
			</div>
        <?php endif; ?>
        <?php if($is_shipper): ?>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team', 'profit_ship');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ship-profit" class="form-input only-number" name="<?=teamController::INPUT_PROFIT_SHIP;?>" placeholder="<?=lang('team', 'profit_ship');?>" type="text" value="<?=_echo($profit_ship);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
				<?php if($is_admin): ?>
                    <div class="label-desc">= <strong id="text-profit-ship"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                <?php endif; ?>
                    <div class="label-desc"><?=lang('team', 'txt_limit');?> <?=Currency::format($team['profit_ship']);?></div>
                </div>
			</div>
        <?php endif; ?>

		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>

<?=assetController::load_js('form-validator.js');?>

<script type="text/javascript">
	$(document).ready(function() {

    <?php if($is_admin): ?>
		const CALL_PROFIT = $('#call-profit');
		const ADS_PROFIT = $('#ads-profit');
		const SHIP_PROFIT = $('#ship-profit');
		const TEXT_PROFIT_CALL = $('#text-profit-call');
		const TEXT_PROFIT_ADS = $('#text-profit-ads');
		const TEXT_PROFIT_SHIP = $('#text-profit-ship');

		let current_country = <?=json_encode($country);?>;

		function update_profit() {
			if(!current_country) {
				return;
			}
			const profit_call = (parseFloat(current_country.to_default_currency) * (CALL_PROFIT.val() || 0)).toFixed(0);
			const profit_ads = (parseFloat(current_country.to_default_currency) * (ADS_PROFIT.val() || 0)).toFixed(0);
			const profit_ship = (parseFloat(current_country.to_default_currency) * (SHIP_PROFIT.val() || 0)).toFixed(0);

			TEXT_PROFIT_CALL.html(parseFloat(profit_call).toLocaleString());
			TEXT_PROFIT_ADS.html(parseFloat(profit_ads).toLocaleString());
			TEXT_PROFIT_SHIP.html(parseFloat(profit_ship).toLocaleString());
		}

        update_profit();
		CALL_PROFIT.on('keyup', function() {
			update_profit();
		});

		ADS_PROFIT.on('keyup', function() {
			update_profit();
		});

		SHIP_PROFIT.on('keyup', function() {
			update_profit();
		});
    <?php endif; ?>

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

		$('[role="btn-plus"]').on('click', function() {
			const input_form = $(this).parents('.input-group').find('input');
            input_form.val(parseFloat(input_form.val()) + 1).change();
			update_profit();
        });

        $('[role="btn-minus"]').on('click', function() {
			const input_form = $(this).parents('.input-group').find('input');
            const quantity = parseFloat(input_form.val()) - 1;
            input_form.val(quantity > 0 ? quantity : 0).change();
			update_profit();
        });

	});
</script>