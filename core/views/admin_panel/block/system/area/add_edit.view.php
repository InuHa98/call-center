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
				<label class="control-label"><?=lang('label', 'name_area');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="<?=lang('label', 'name_area');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'country');?></label>
                <div class="form-control">
                    <select class="js-custom-select" name="<?=adminPanelController::INPUT_COUNTRY;?>" data-placeholder="<?=lang('placeholder', 'select_country');?>" data-max-width="300px">
					<?php foreach($list_country as $country): ?>
						<option <?=($country['id'] == $country_id ? 'selected' : null);?> value="<?=$country['id'];?>"><?=_echo($country['name']);?></option>
					<?php endforeach;?>
					</select>
                </div>
			</div>

		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_AREA]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>