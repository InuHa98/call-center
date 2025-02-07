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
	<form id="form-validate" method="POST" enctype="multipart/form-data">
        <?=Security::insertHiddenToken();?>
        <div class="box__header"><?=$txt_description;?></div>
		<div class="box__body">
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'name_team');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="<?=lang('label', 'name_team');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'type');?></label>
                <div class="form-control">
                    <select id="change-type" class="js-custom-select" name="<?=adminPanelController::INPUT_TYPE;?>" data-placeholder="<?=lang('placeholder', 'select_type');?>" data-max-width="300px">
						<option <?=($type == Team::TYPE_ONLY_CALL ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_CALL;?>"><?=Role::DEFAULT_NAME_CALLER;?></option>
						<option <?=($type == Team::TYPE_ONLY_SHIP ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_SHIP;?>"><?=Role::DEFAULT_NAME_SHIPPER;?></option>
						<option <?=($type == Team::TYPE_ONLY_ADS ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_ADS;?>"><?=Role::DEFAULT_NAME_ADVERTISER;?></option>
						<option <?=($type == Team::TYPE_FULLSTACK ? 'selected' : null);?> value="<?=Team::TYPE_FULLSTACK;?>"><?=Role::DEFAULT_NAME_FULLSTACK;?></option>
					</select>
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'leader');?></label>
                <div class="form-control">
                    <select class="js-custom-select" name="<?=adminPanelController::INPUT_LEADER;?>" data-max-width="300px" data-placeholder="<?=lang('placeholder', 'select_leader');?>" enable-search="true">
					<?php foreach($list_member as $member):
						$html_option = '
						<div class="user-infomation">
							<span class="user-avatar avatar--small">
								<img src="'.User::get_avatar($member).'" />
								'.no_avatar($member).'
							</span>
							<span class="user-display-name">'.User::get_username($member).'</span>
						</div>';
					?>
						<option <?=($member['id'] == $leader_id ? 'selected' : null);?> value="<?=$member['id'];?>" data-html="<?=_echo($html_option);?>"><?=_echo($member['username']);?></option>
					<?php endforeach;?>
					</select>
                </div>
			</div>
			
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'product');?></label>
                <div class="form-control">
                    <select class="js-custom-select" name="<?=adminPanelController::INPUT_PRODUCT;?>[]" data-placeholder="<?=lang('placeholder', 'select_product');?>" enable-search="true" multiple>
					<?php foreach($list_product as $product):
						$html_option = '
						<div class="user-infomation">
							<span class="user-avatar avatar--small">
								<img src="'.Product::get_image($product).'" />
							</span>
							<span class="user-display-name">#'.$product['id'].' - '.$product['name'].' - '.$product['country_name'].' - '.$product['currency'].'</span>
						</div>';
					?>
						<option <?=(in_array($product['id'], $product_id) ? 'selected' : null);?> value="<?=$product['id'];?>" data-html="<?=_echo($html_option);?>"></option>
					<?php endforeach;?>
					</select>
					<div class="label-desc"><?=lang('team_management', 'desc_product');?></div>
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'country');?>/<?=lang('label', 'currency');?></label>
                <div class="form-control">
                    <select id="select-country" class="js-custom-select" name="<?=adminPanelController::INPUT_COUNTRY;?>" data-placeholder="<?=lang('placeholder', 'select_country');?>" data-max-width="300px" enable-search="true">
					<?php foreach($list_country as $country): ?>
						<option <?=($country['id'] == $country_id ? 'selected' : null);?> value="<?=$country['id'];?>"><?=_echo($country['name']);?> - <?=_echo($country['currency']);?></option>
					<?php endforeach;?>
					</select>
					<div class="label-desc"><strong>1</strong> <span id="text-currency"></span> = <strong id="text-to-default-currency"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                </div>
			</div>
			
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team_management', 'caller_profit');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="call-profit" class="form-input only-number" name="<?=adminPanelController::INPUT_PROFIT_CALL;?>" placeholder="Lợi nhuận caller" type="text" value="<?=_echo($profit_call);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-profit-call"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team_management', 'caller_deduct');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="call-deduct" class="form-input only-number" name="<?=adminPanelController::INPUT_DEDUCT_CALL;?>" placeholder="Khấu trừ caller" type="text" value="<?=_echo($deduct_call);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-deduct-call"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team_management', 'advertiser_profit');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ads-profit" class="form-input only-number" name="<?=adminPanelController::INPUT_PROFIT_ADS;?>" placeholder="Lợi nhuận advertisers" type="text" value="<?=_echo($profit_ads);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-profit-ads"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team_management', 'advertiser_deduct');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ads-deduct" class="form-input only-number" name="<?=adminPanelController::INPUT_DEDUCT_ADS;?>" placeholder="Khấu trừ advertisers" type="text" value="<?=_echo($deduct_ads);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-deduct-ads"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team_management', 'shipper_profit');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ship-profit" class="form-input only-number" name="<?=adminPanelController::INPUT_PROFIT_SHIP;?>" placeholder="Lợi nhuận shipper" type="text" value="<?=_echo($profit_ship);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-profit-ship"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('team_management', 'shipper_deduct');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ship-deduct" class="form-input only-number" name="<?=adminPanelController::INPUT_DEDUCT_SHIP;?>" placeholder="Khấu trừ shipper" type="text" value="<?=_echo($deduct_ship);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-deduct-ship"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
                </div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_TEAM, 'block' => adminPanelController::BLOCK_TEAM_LIST]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>



<script type="text/javascript">
	$(document).ready(function() {

		const COUNTRYS = <?=json_encode($list_country);?>;
		

		const SELECT_COUNTRY = $('#select-country');
		const CALL_PROFIT = $('#call-profit');
		const CALL_DEDUCT = $('#call-deduct');
		const ADS_PROFIT = $('#ads-profit');
		const ADS_DEDUCT = $('#ads-deduct');
		const SHIP_PROFIT = $('#ship-profit');
		const SHIP_DEDUCT = $('#ship-deduct');
		const TEXT_CURRENCY = $('#text-currency');
		const TEXT_EXCHANGE_RATE = $('#text-to-default-currency');
		const TEXT_PROFIT_CALL = $('#text-profit-call');
		const TEXT_DEDUCT_CALL = $('#text-deduct-call');
		const TEXT_PROFIT_ADS = $('#text-profit-ads');
		const TEXT_DEDUCT_ADS = $('#text-deduct-ads');
		const TEXT_PROFIT_SHIP = $('#text-profit-ship');
		const TEXT_DEDUCT_SHIP = $('#text-deduct-ship');

		let current_country = COUNTRYS.find(o => o.id == SELECT_COUNTRY.val());

		function update_currency() {
			current_country = COUNTRYS.find(o => o.id == SELECT_COUNTRY.val());
			if(!current_country) {
				return;
			}
			TEXT_CURRENCY.html(current_country.currency);
			TEXT_EXCHANGE_RATE.html(current_country.to_default_currency);
			update_profit_deduct();
		}

		function update_profit_deduct() {
			if(!current_country) {
				return;
			}
			const profit_call = (parseFloat(current_country.to_default_currency) * (CALL_PROFIT.val() || 0)).toFixed(0);
			const deduct_call = (parseFloat(current_country.to_default_currency) * (CALL_DEDUCT.val() || 0)).toFixed(0);
			const profit_ads = (parseFloat(current_country.to_default_currency) * (ADS_PROFIT.val() || 0)).toFixed(0);
			const deduct_ads = (parseFloat(current_country.to_default_currency) * (ADS_DEDUCT.val() || 0)).toFixed(0);
			const profit_ship = (parseFloat(current_country.to_default_currency) * (SHIP_PROFIT.val() || 0)).toFixed(0);
			const deduct_ship = (parseFloat(current_country.to_default_currency) * (SHIP_DEDUCT.val() || 0)).toFixed(0);

			TEXT_PROFIT_CALL.html(parseFloat(profit_call).toLocaleString());
			TEXT_DEDUCT_CALL.html(parseFloat(deduct_call).toLocaleString());
			TEXT_PROFIT_ADS.html(parseFloat(profit_ads).toLocaleString());
			TEXT_DEDUCT_ADS.html(parseFloat(deduct_ads).toLocaleString());
			TEXT_PROFIT_SHIP.html(parseFloat(profit_ship).toLocaleString());
			TEXT_DEDUCT_SHIP.html(parseFloat(deduct_ship).toLocaleString());
		}

		update_currency();
		SELECT_COUNTRY.on('change', function() {
			update_currency();
		});

		CALL_PROFIT.on('keyup', function() {
			update_profit_deduct();
		});
		CALL_DEDUCT.on('keyup', function() {
			update_profit_deduct();
		});

		ADS_PROFIT.on('keyup', function() {
			update_profit_deduct();
		});
		ADS_DEDUCT.on('keyup', function() {
			update_profit_deduct();
		});

		SHIP_PROFIT.on('keyup', function() {
			update_profit_deduct();
		});
		SHIP_DEDUCT.on('keyup', function() {
			update_profit_deduct();
		});


		function update_view(type) {
			const form_profit_call = CALL_PROFIT.parents('.form-group');
			const form_profit_ads = ADS_PROFIT.parents('.form-group');
			const form_profit_ship = SHIP_PROFIT.parents('.form-group');
			const form_deduct_call = CALL_DEDUCT.parents('.form-group');
			const form_deduct_ads = ADS_DEDUCT.parents('.form-group');
			const form_deduct_ship = SHIP_DEDUCT.parents('.form-group');

			switch(type) {
				case '<?=Team::TYPE_ONLY_CALL;?>':
					form_profit_call.show();
					form_deduct_call.show();
					form_profit_ads.hide();
					form_deduct_ads.hide();
					form_profit_ship.hide();
					form_deduct_ship.hide();
					break;

				case '<?=Team::TYPE_ONLY_ADS;?>':
					form_profit_call.hide();
					form_deduct_call.hide();
					form_profit_ads.show();
					form_deduct_ads.show();
					form_profit_ship.hide();
					form_deduct_ship.hide();
					break;

				case '<?=Team::TYPE_ONLY_SHIP;?>':
					form_profit_call.hide();
					form_deduct_call.hide();
					form_profit_ads.hide();
					form_deduct_ads.hide();
					form_profit_ship.show();
					form_deduct_ship.show();
					break;

				case '<?=Team::TYPE_FULLSTACK;?>':
					form_profit_call.show();
					form_deduct_call.show();
					form_profit_ads.show();
					form_deduct_ads.show();
					form_profit_ship.show();
					form_deduct_ship.show();
					break;
			}
		}

		update_view('<?=$type;?>');

		$('#change-type').on('change', function() {
			update_view($(this).val());
		});

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
			update_profit_deduct();
        });

        $('[role="btn-minus"]').on('click', function() {
			const input_form = $(this).parents('.input-group').find('input');
            const quantity = parseFloat(input_form.val()) - 1;
            input_form.val(quantity > 0 ? quantity : 0).change();
			update_profit_deduct();
        });

		$('#select-image').on('change', function(event) {
			const file = event.target.files && event.target.files[0];
			if(!file) {
				return;
			}
			if (<?=json_encode(Product::$allow_image_extensions);?>.indexOf(file.type) < 0) {
				$.toastShow('<?=lang('errors', 'image_format');?>', {
					type: 'error',
					timeout: 3000
				});
				return false;
			}
			$('#preview-product-image').attr('src', URL.createObjectURL(file));
		});
	});
</script>