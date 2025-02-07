<?php View::render('layout.header', compact('title', 'current_route')); ?>


<?php
if($error)
{
    echo '<div class="alert alert--error">'.$error.'</div>';
}
else if($success)
{
    echo '<div class="alert alert--success">'.$success.'</div>';
}

$user_ads = [
    'id' => $order['ads_id'],
    'username' => $order['ads_username'],
    'avatar' => $order['ads_avatar'],
    'is_ban' => $order['ads_is_ban']
];
$user_call = [
    'id' => $order['call_id'],
    'username' => $order['call_username'],
    'avatar' => $order['call_avatar'],
    'is_ban' => $order['call_is_ban']
];
$user_ship = [
    'id' => $order['ship_id'],
    'username' => $order['ship_username'],
    'avatar' => $order['ship_avatar'],
    'is_ban' => $order['ship_is_ban']
];

?>


<div class="box">
	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <input type="hidden" name="<?=InterFaceRequest::REFERER;?>" value="<?=$referer;?>">
        <div class="box__header"><i class="far fa-edit text-primary"></i><span class="padding-x-2"><?=lang('order', 'txt_edit');?> - #<?=$order['id'];?></span></div>
		<div class="box__body">
        <?php if($order['duplicate']): ?>
            <div class="alert alert--warning margin-t-0">
            <?=lang('order', 'txt_calling_duplicate', ['count' => $order['duplicate'], 'link' => RouteMap::get('order', ['block' => orderController::BLOCK_DUPLICATE, 'action' => $order['id']])]);?>
            </div>
        <?php endif; ?>
            <div class="card-product">
                <img class="card-product__image small-image" src="<?=Product::get_image(['image' => $order['product_image']]);?>" />
                <div class="card-info-order">
                    <div class="card-product__id">
                        <span class="label"><?=lang('label', 'order');?>:</span> #<?=_echo($order['id']);?>
                    </div>
                    <div class="card-product__name">
                        <span class="label"><?=lang('label', 'product');?>:</span> <?=_echo($order['product_name']);?>
                    </div>
                    <div class="card-product__price">
                        <span class="label"><?=lang('label', 'price');?>:</span> <?=Currency::format($order['product_price']);?> <?=_echo($order['currency']);?>
                    </div>

                    <?php if($order['note_ads'] != ''): ?>
                        <div class="card-product__note">
                            <span class="label"><?=lang('order', 'txt_ads_note');?>:</span> <?=_echo($order['note_ads']);?>
                        </div>
                    <?php endif; ?>

                    <?php if($order['note_ship'] != ''): ?>
                        <div class="card-product__note">
                            <span class="label"><?=lang('order', 'txt_ship_note');?>:</span> <?=_echo($order['note_ship']);?>
                        </div>
                    <?php endif; ?>

                    <?php if($order['updated_at']): ?>
                    <div class="card-product__updated-at">
                        <span class="label"><?=lang('label', 'last_update');?>:</span> <span class="time"><?=_time($order['updated_at']);?></span>
                    </div>
                    <?php endif; ?>

                    <div class="card-product__time">
                        <span class="card-product__created-at">
                            <span class="label"><?=lang('label', 'created_at');?>:</span> <span class="time"><?=_time($order['created_at']);?></span>
                            <?php if($user_ads['id']): ?>
                                <a target="_blank" class="user-infomation bg--white" href="<?=RouteMap::get('profile', ['id' => $user_ads['id']]);?>">
                                    <span class="user-avatar avatar--small">
                                        <img src="<?=User::get_avatar($user_ads);?>" />
                                        <?=no_avatar($user_ads);?>
                                    </span>
                                    <span class="user-display-name"><?=User::get_username($user_ads);?></span>
                                </a>
                            <?php endif; ?>
                        </span>

                    <?php if($order['call_at']): ?>
                        <span class="card-product__call-at">
                            <span class="label"><?=lang('label', 'call_at');?>:</span> <span class="time"><?=_time($order['call_at']);?></span>
                            <a target="_blank" class="user-infomation bg--white" href="<?=RouteMap::get('profile', ['id' => $user_call['id']]);?>">
                                <span class="user-avatar avatar--small">
                                    <img src="<?=User::get_avatar($user_call);?>" />
                                    <?=no_avatar($user_call);?>
                                </span>
                                <span class="user-display-name"><?=User::get_username($user_call);?></span>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if($order['delivery_at']): ?>
                        <span class="card-product__delivery-at">
                            <span class="label"><?=lang('label', 'delivery_at');?>:</span> <span class="time"><?=_time($order['delivery_at']);?></span>
                            <a target="_blank" class="user-infomation bg--white" href="<?=RouteMap::get('profile', ['id' => $user_ship['id']]);?>">
                                <span class="user-avatar avatar--small">
                                    <img src="<?=User::get_avatar($user_ship);?>" />
                                    <?=no_avatar($user_ship);?>
                                </span>
                                <span class="user-display-name"><?=User::get_username($user_ship);?></span>
                            </a>
                        </span>
                    <?php endif; ?>  
                    </div>
                </div>

            </div>
            <div class="row label-ver">
                <div class="col-xs-12 col-lg-12 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'status');?></label>
                        <div class="form-control">
                            <select id="status" class="js-custom-select" name="<?=orderController::INPUT_STATUS;?>" data-placeholder="<?=lang('placeholder', 'enter_status');?>">
                                <option></option>
                            <?php foreach($list_status as $key): ?>
                                <option <?=($key == $status ? 'selected' : null);?> value="<?=$key;?>" data-html="<?=_echo(orderController::render_status($key));?>"></option>
                            <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'quantity');?></label>
                        <div class="form-control">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" role="btn-minus-quantity" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
                                </div>
                                <input type="text" id="quantity" class="form-input" name="<?=orderController::INPUT_QUANTITY;?>" placeholder="<?=lang('placeholder', 'enter_quantity');?>" value="<?=_echo($quantity);?>">
                                <div class="input-group-append">
                                    <button type="button" role="btn-plus-quantity" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('order', 'txt_total_price');?> (<?=_echo($order['currency']);?>)</label>
                        <div class="form-control">
                            <div class="input-group">
                                <input type="text" id="price" class="form-input only-read" name="<?=orderController::INPUT_PRICE;?>" placeholder="<?=lang('placeholder', 'enter_price');?>" value="<?=_echo($price);?>">
                                <div class="input-group-append">
                                    <button type="button" role="btn-edit-price" class="btn btn-outline-gray btn-dim"><i class="fas fa-edit"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'first_name');?></label>
                        <div class="form-control">
                            <input type="text" id="first_name" class="form-input" name="<?=orderController::INPUT_FIRST_NAME;?>" placeholder="<?=lang('placeholder', 'enter_first_name');?>" value="<?=_echo($order_first_name);?>">
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><?=lang('label', 'last_name');?></label>
                        <div class="form-control">
                            <input type="text" class="form-input" name="<?=orderController::INPUT_LAST_NAME;?>" placeholder="<?=lang('placeholder', 'enter_last_name');?>" value="<?=_echo($order_last_name);?>">
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-12 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('order', 'txt_order_phone');?></label>
                        <div class="form-control">
                            <div class="input-group">
                                <input type="text" role="number_phone" class="form-input order-phone" name="<?=orderController::INPUT_ORDER_PHONE;?>[]" placeholder="<?=lang('placeholder', 'enter_phone');?>" value="<?=(isset($order_phone[0]) ? _echo($order_phone[0]) : null);?>">
                                <div class="input-group-append">
                                    <button type="button" role="btn-add-phone" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <?php if(isset($order_phone[1])): ?>
                            <?php
                                array_shift($order_phone); foreach($order_phone as $key): ?>
                                <div class="input-group">
                                    <input class="form-input order-phone" role="number_phone" name="<?=orderController::INPUT_ORDER_PHONE;?>[]" placeholder="<?=lang('placeholder', 'enter_phone');?>" type="text" value="<?=_echo($key);?>">
                                    <div class="input-group-append">
                                        <button type="button" role="btn-remove-phone" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-lg-12 col-xl-12">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'address');?></label>
                        <div class="form-control">
                            <textarea class="form-textarea" id="address" name="<?=orderController::INPUT_ADDRESS;?>" placeholder="<?=lang('placeholder', 'enter_address');?>"><?=_echo($address);?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-4 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'province');?></label>
                        <div class="form-control">
                            <select id="province" class="js-custom-select" name="<?=orderController::INPUT_PROVINCE;?>" data-placeholder="<?=lang('placeholder', 'enter_province');?>" enable-search="true">
                                <option></option>
                            <?php foreach($list_province as $province): ?>
                                <option <?=($province['id'] == $province_id ? 'selected' : null);?> value="<?=$province['id'];?>"><?=_echo($province['name']);?></option>
                            <?php endforeach;?>

                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'district');?></label>
                        <div class="form-control">
                            <select id="district" class="js-custom-select" name="<?=orderController::INPUT_DISTRICT;?>" data-placeholder="<?=lang('placeholder', 'enter_district');?>" enable-search="true">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-4 col-xl-4">
                    <div class="form-group">
                        <label class="control-label"><?=lang('label', 'ward');?></label>
                        <div class="form-control">
                            <select id="ward" class="js-custom-select" name="<?=orderController::INPUT_WARD;?>" data-placeholder="<?=lang('placeholder', 'enter_ward');?>" enable-search="true">
                            </select>
                        </div>
                    </div>
                </div>

                <?php if($list_area): ?>
                    <div class="col-xs-12 col-md-12 col-xl-12">
                        <div class="form-group">
                            <label class="control-label"><?=lang('label', 'area');?></label>
                            <div class="form-control">
                                <select id="area" class="js-custom-select" name="<?=orderController::INPUT_AREA;?>" data-placeholder="<?=lang('placeholder', 'select_area');?>">
                                    <option value="0"><?=lang('placeholder', 'enter_area');?></option>
                                <?php foreach($list_area as $area): ?>
                                    <option <?=($area['id'] == $area_id ? 'selected' : null);?> value="<?=$area['id'];?>" data-html="<?=_echo('<i class="fas fa-map-marker-alt"></i> '.$area['name']);?>"></option>
                                <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(isset($note_ads)): ?>
                    <div class="col-xs-12 col-lg-12 col-xl-12">
                        <div class="form-group">
                            <label class="control-label"><?=lang('order', 'txt_ads_note');?></label>
                            <div class="form-control">
                                <textarea class="form-textarea" name="<?=orderController::INPUT_ADS_NOTE;?>" placeholder="<?=lang('placeholder', 'enter_note');?>"><?=_echo($note_ads);?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(isset($note_call)): ?>
                    <div class="col-xs-12 col-lg-12 col-xl-12">
                        <div class="form-group">
                            <label class="control-label"><?=lang('order', 'txt_call_note');?></label>
                            <div class="form-control">
                                <textarea class="form-textarea" name="<?=orderController::INPUT_CALL_NOTE;?>" placeholder="<?=lang('placeholder', 'enter_note');?>"><?=_echo($note_call);?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(isset($note_ship)): ?>
                    <div class="col-xs-12 col-lg-12 col-xl-12">
                        <div class="form-group">
                            <label class="control-label"><?=lang('order', 'txt_ship_note');?></label>
                            <div class="form-control">
                                <textarea class="form-textarea" name="<?=orderController::INPUT_SHIP_NOTE;?>" placeholder="<?=lang('placeholder', 'enter_note');?>"><?=_echo($note_ship);?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
		</div>

		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
            <a href="<?=$referer;?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>

	</form>
</div>

<?=assetController::load_js('form-validator.js');?>

<script type="text/javascript">
	$(document).ready(function() {

        const PROVINCE = <?=json_encode($list_province);?>;
        const DISTRICT = <?=json_encode($list_district);?>;
        const WARD = <?=json_encode($list_ward);?>;

        const select_product = $('#product');
        const select_province = $('#province');
        const select_district = $('#district');
        const select_ward = $('#ward');
        const select_area = $('#area');

        const input_quantity = $('#quantity');
        const input_price = $('#price');
        const input_status = $('#status');


        let current_product = <?=json_encode([
            'id' => $order['product_id'],
            'price' => $order['product_price']
        ]);?>;
        let current_province = PROVINCE.find(o => o.id == select_province.val());
        let current_district = DISTRICT.find(o => o.id == select_district.val());
        let current_ward = WARD.find(o => o.id == select_ward.val());


        function update_price() {
			if(!current_product) {
				return;
			}
            input_price.val(current_product.price * parseInt(input_quantity.val()));
		}

        function update_district() {
			current_province = PROVINCE.find(o => o.id == select_province.val());
			if(!current_province) {
				return;
			}

            let option_district = '';

            DISTRICT.filter(o => o.province_id == current_province.id).forEach(district => {
                option_district += '<option value="' + district.id + '">' + district.name + '</option>';
            });

			select_district.html(option_district).change();
		}

        function update_ward() {
			current_district = DISTRICT.find(o => o.id == select_district.val());
			if(!current_district) {
				return;
			}

            let option_ward = '';

            WARD.filter(o => o.district_id == current_district.id).forEach(ward => {
                option_ward += '<option value="' + ward.id + '">' + ward.name + '</option>';
            });

			select_ward.html(option_ward);
		}

        function update_area() {
			current_province = PROVINCE.find(o => o.id == select_province.val());
			if(!current_province) {
				return;
			}

            if(current_province.area_id && select_area.length) {
                select_area.val(current_province.area_id).change();
            }
		}

        function check_address(message) {
            return function (value) {
                return (input_status.val() != <?=Order::STATUS_AGREE_BUY;?> && input_status.val() != <?=Order::STATUS_DELIVERY_DATE;?>) || value ? undefined : (message || '<?=lang('placeholder', 'enter');?>');
            };
        }

        update_price();
        update_area();
        update_district();
        update_ward();
        
        select_district.val(<?=$district_id;?>).change();
        select_ward.val(<?=$ward_id;?>).change();



        select_product.on('change', function() {
			update_price();
		});

        input_quantity.on('change', function() {
			update_price();
		});

		select_province.on('change', function() {
			update_district();
			update_area();
		});

        select_district.on('change', function() {
			update_ward();
		});




        input_quantity.on('input', function(event) {
			var value = event.target.value;
			var validValue = value.replace(/\D/g, '');
            
            if (validValue.startsWith('0') && validValue !== '0' && validValue !== '0.') {
                validValue = validValue.substring(1);
            }

			event.target.value = validValue;
		}).on('change', function(event) {
            if (event.target.value === '') {
                event.target.value = 0;
                $(this).change();
            }
        });

        input_price.on('input', function(event) {
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



        $(document).on('input', '.order-phone', function(event) {
			var value = event.target.value;
			var validValue = value.replace(/[^0-9+]/g, '');

			event.target.value = validValue;
		});

        $('[role="btn-plus-quantity"]').on('click', function() {
            input_quantity.val(parseInt(input_quantity.val()) + 1).change();
        });

        $('[role="btn-minus-quantity"]').on('click', function() {
            const quantity = parseInt(input_quantity.val()) - 1;
            input_quantity.val(quantity > 0 ? quantity : 0).change();
        });

        $('[role="btn-edit-price"]').on('click', function() {
            input_price.toggleClass('only-read');
            if(input_price.hasClass('only-read')) {
                $(this).addClass('btn-outline-gray').removeClass('btn-outline-info');
            } else {
                $(this).removeClass('btn-outline-gray').addClass('btn-outline-info');
            }
            
        });

        $('[role="btn-add-phone"]').on('click', function() {
            let input_group = $(this).parents('.input-group');
            let new_input = input_group.clone();
            new_input.find('input').val('');
            new_input.find('.input-group-append').html('<button type="button" role="btn-remove-phone" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>');
            input_group.parent().append(new_input);
        });

        $(document).on('click', '[role="btn-remove-phone"]', function() {
            let input_group = $(this).parents('.input-group');
            input_group.remove();
        });

        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            rules: {

                '#quantity': [
                    Validator.isRequired('<?=lang('placeholder', 'enter_quantity');?>')
                ],
                '#price': [
                    Validator.isRequired('<?=lang('placeholder', 'enter_price');?>')
                ],
                '#first_name': [
                    Validator.isRequired('<?=lang('placeholder', 'enter_first_name');?>')
                ],
                '[role="number_phone"]': [
                    Validator.isRequired('<?=lang('placeholder', 'enter_phone');?>'),
                    Validator.isPhone(),
                ],

                '#province': [
                    check_address('<?=lang('placeholder', 'enter_province');?>') 
                ],
                '#district': [
                    check_address('<?=lang('placeholder', 'enter_district');?>')   
                ],
                '#address': [
                    check_address('<?=lang('placeholder', 'enter_address');?>')  
                ],

                '#status': [
                    Validator.isRequired('<?=lang('placeholder', 'enter_status');?>')
                ]
            }
        });
	});
</script>

<?php View::render('layout.footer'); ?>