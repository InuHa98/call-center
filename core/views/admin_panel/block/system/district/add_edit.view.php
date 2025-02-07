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
				<label class="control-label"><?=lang('label', 'name_district');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="<?=lang('label', 'name_district');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::build_query([InterFaceRequest::ID => $province['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_DISTRICT]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>