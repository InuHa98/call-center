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
				<label class="control-label"><?=lang('label', 'name_province');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="<?=lang('label', 'name_province');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'area');?></label>
                <div class="form-control">
                    <select class="js-custom-select" name="<?=adminPanelController::INPUT_AREA;?>" data-placeholder="<?=lang('placeholder', 'select_area');?>" data-max-width="300px">
					<?php foreach($list_area as $area): ?>
						<option <?=($area['id'] == $area_id ? 'selected' : null);?> value="<?=$area['id'];?>"><?=_echo($area['name']);?></option>
					<?php endforeach;?>
					</select>
                </div>
			</div>

		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::build_query([InterFaceRequest::ID => $country['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PROVINCE]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>