<?php View::render('layout.header', compact('title')); ?>


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
        <div class="box__header"><?=($is_edit ? '<i class="fa fa-edit"></i><span class="padding-x-2">'.lang('blacklist', 'txt_edit').'</span>' : '<i class="fa fa-plus"></i><span class="padding-x-2">'.lang('blacklist', 'txt_add').'</span>');?></div>
		<div class="box__body">

            <div class="row label-ver">
                <div class="col-xs-12 col-lg-12 col-xl-12">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'country');?></label>
                        <div class="form-control">
                            <select id="country" class="js-custom-select" name="<?=blacklistController::INPUT_COUNTRY;?>" data-placeholder="<?=lang('system', 'select_country');?>" enable-search="true">
                            <?php foreach($list_country as $country): ?>
                                <option <?=($country['id'] == $country_id ? 'selected' : null);?> value="<?=$country['id'];?>"><?=_echo($country['name']);?></option>
                            <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="col-xs-12 col-md-12 col-xl-12">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'order_phone');?></label>
                        <div class="form-control">
                            <div class="input-group">
                                <input type="text" role="number_phone" class="form-input order-phone" name="<?=blacklistController::INPUT_NUMBER_PHONE;?>[]" placeholder="<?=lang('placeholder', 'enter_phone');?>" value="<?=(isset($number_phone[0]) ? _echo($number_phone[0]) : null);?>">
                                <div class="input-group-append">
                                    <button type="button" role="btn-add-phone" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <?php if(isset($number_phone[1])): ?>
                            <?php
                                array_shift($number_phone); foreach($number_phone as $key): ?>
                                <div class="input-group">
                                    <input class="form-input order-phone" role="number_phone" name="<?=blacklistController::INPUT_NUMBER_PHONE;?>[]" placeholder="<?=lang('placeholder', 'enter_phone');?>" type="text" value="<?=_echo($key);?>">
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
                        <label class="control-label"> <?=lang('label', 'reason');?></label>
                        <div class="form-control">
                            <textarea class="form-textarea" name="<?=blacklistController::INPUT_REASON;?>" placeholder="<?=lang('placeholder', 'can_be_left_blank');?>"><?=_echo($reason);?></textarea>
                        </div>
                    </div>
                </div>
            </div>
		</div>
		<div class="box__footer">

			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>

        <?php if($is_edit): ?>
            <a href="<?=RouteMap::get('blacklist');?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
        <?php endif; ?>
		</div>
	</form>
</div>

<?=assetController::load_js('form-validator.js');?>

<script type="text/javascript">
	$(document).ready(function() {


        $(document).on('input', '.order-phone', function(event) {
			var value = event.target.value;
			var validValue = value.replace(/[^0-9+]/g, '');

			event.target.value = validValue;
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
                '#country': [
                    Validator.isRequired('<?=lang('system', 'select_country');?>'),
                ],
                '[role="number_phone"]': [
                    Validator.isRequired(),
                    Validator.isPhone(),
                ]
            }
        });
	});
</script>

<?php View::render('layout.footer'); ?>