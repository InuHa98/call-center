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
				<label class="control-label"><?=lang('label', 'name_product');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="<?=lang('label', 'name_product');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'stock');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ads-cost" class="form-input only-number" name="<?=adminPanelController::INPUT_STOCK;?>" placeholder="<?=lang('label', 'stock');?>" type="text" value="<?=_echo($stock);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'product_image');?></label>
                <div class="form-control">
					<div class="form-file">
                    	<input name="<?=adminPanelController::INPUT_IMAGE;?>" id="select-image" type="file">
					</div>
					<img class="preview-product-image" id="preview-product-image" src="<?=$image_preview;?>" />
                </div>
			</div>
			<div class="form-group">
				<label class="control-label"><?=lang('label', 'description');?></label>
                <div class="form-control">
					<div class="input-group">
						<input type="text" class="form-input" name="<?=adminPanelController::INPUT_DESC;?>[]" placeholder="<?=lang('label', 'description');?>" value="<?=(isset($description[0]) ? _echo($description[0]) : null);?>">
						<div class="input-group-append">
							<button type="button" role="btn-add-desc" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<?php if(isset($description[1])): ?>
					<?php
						array_shift($description); foreach($description as $key): ?>
						<div class="input-group">
							<input class="form-input" name="<?=adminPanelController::INPUT_DESC;?>[]" placeholder="<?=lang('label', 'description');?>" type="text" value="<?=_echo($key);?>">
							<div class="input-group-append">
								<button type="button" role="btn-remove-desc" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'country');?></label>
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
				<label class="control-label"><?=lang('label', 'price_sell');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="price" class="form-input only-number" name="<?=adminPanelController::INPUT_PRICE;?>" placeholder="<?=lang('label', 'price_sell');?>" type="text" value="<?=_echo($price);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-price"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
				</div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'ads_cost');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="ads-cost" class="form-input only-number" name="<?=adminPanelController::INPUT_ADS_COST;?>" placeholder="<?=lang('label', 'ads_cost');?>" type="text" value="<?=_echo($ads_cost);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-ads-cost"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
				</div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'ship_cost');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="delivery-cost" class="form-input only-number" name="<?=adminPanelController::INPUT_DELIVERY_COST;?>" placeholder="<?=lang('label', 'ship_cost');?>" type="text" value="<?=_echo($delivery_cost);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-delivery-cost"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
				</div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'import_cost');?></label>
                <div class="form-control">
					<div class="input-group">
						<div class="input-group-prepend">
							<button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
						</div>
						<input id="import-cost" class="form-input only-number" name="<?=adminPanelController::INPUT_IMPORT_COST;?>" placeholder="<?=lang('label', 'import_cost');?>" type="text" value="<?=_echo($import_cost);?>">
						<div class="input-group-append">
							<button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="label-desc">= <strong id="text-import-cost"></strong> <?=Currency::DEFAULT_CURRENCY;?></div>
				</div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PRODUCT]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>



<script type="text/javascript">
	$(document).ready(function() {
		
		const COUNTRYS = <?=json_encode($list_country);?>;
		

		const SELECT_COUNTRY = $('#select-country');
		const INPUT_PRICE = $('#price');
		const INPUT_ADS_COST = $('#ads-cost');
		const INPUT_DELIVERY_COST = $('#delivery-cost');
		const INPUT_IMPORT_COST = $('#import-cost');
		const TEXT_CURRENCY = $('#text-currency');
		const TEXT_EXCHANGE_RATE = $('#text-to-default-currency');
		const TEXT_PRICE = $('#text-price');
		const TEXT_ADS_COST = $('#text-ads-cost');
		const TEXT_DELIVERY_COST = $('#text-delivery-cost');
		const TEXT_IMPORT_COST = $('#text-import-cost');

		let current_country = COUNTRYS.find(o => o.id == SELECT_COUNTRY.val());

		function update_currency() {
			current_country = COUNTRYS.find(o => o.id == SELECT_COUNTRY.val());
			if(!current_country) {
				return;
			}
			TEXT_CURRENCY.html(current_country.currency);
			TEXT_EXCHANGE_RATE.html(current_country.to_default_currency);
			update_cost();
		}

		function update_cost() {
			if(!current_country) {
				return;
			}
			const price = (parseFloat(current_country.to_default_currency) * (INPUT_PRICE.val() || 0)).toFixed(0);
			const ads_cost = (parseFloat(current_country.to_default_currency) * (INPUT_ADS_COST.val() || 0)).toFixed(0);
			const delivery_cost = (parseFloat(current_country.to_default_currency) * (INPUT_DELIVERY_COST.val() || 0)).toFixed(0);
			const import_cost = (parseFloat(current_country.to_default_currency) * (INPUT_IMPORT_COST.val() || 0)).toFixed(0);

			TEXT_PRICE.html(parseFloat(price).toLocaleString());
			TEXT_ADS_COST.html(parseFloat(ads_cost).toLocaleString());
			TEXT_DELIVERY_COST.html(parseFloat(delivery_cost).toLocaleString());
			TEXT_IMPORT_COST.html(parseFloat(import_cost).toLocaleString());
		}

		update_currency();
		SELECT_COUNTRY.on('change', function() {
			update_currency();
		});

		INPUT_PRICE.on('keyup', function() {
			update_cost();
		});

		INPUT_ADS_COST.on('keyup', function() {
			update_cost();
		});

		INPUT_DELIVERY_COST.on('keyup', function() {
			update_cost();
		});

		INPUT_IMPORT_COST.on('keyup', function() {
			update_cost();
		});

		$('.only-number').on('input', function(event) {
			var value = event.target.value;
			var validValue = value.replace(/[^0-9.]/g, '');
			if (validValue.indexOf('.') !== validValue.lastIndexOf('.')) {
				validValue = validValue.slice(0, validValue.lastIndexOf('.'));
			}
			event.target.value = validValue;
		});

		$('[role="btn-plus"]').on('click', function() {
			const input_form = $(this).parents('.input-group').find('input');
            input_form.val(parseInt(input_form.val()) + 1).change();
			update_cost();
        });

        $('[role="btn-minus"]').on('click', function() {
			const input_form = $(this).parents('.input-group').find('input');
            const quantity = parseInt(input_form.val()) - 1;
            input_form.val(quantity > 0 ? quantity : 0).change();
			update_cost();
        });

		$('[role="btn-add-desc"]').on('click', function() {
            let input_group = $(this).parents('.input-group');
            let new_input = input_group.clone();
            new_input.find('input').val('');
            new_input.find('.input-group-append').html('<button type="button" role="btn-remove-desc" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>');
            input_group.parent().append(new_input);
        });

        $(document).on('click', '[role="btn-remove-desc"]', function() {
            let input_group = $(this).parents('.input-group');
            input_group.remove();
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