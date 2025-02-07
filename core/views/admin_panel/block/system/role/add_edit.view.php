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
				<label class="control-label"><?=lang('system_role', 'role_name');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="<?=lang('system_role', 'role_name');?>" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
            <div class="form-group limit--width">
				<label class="control-label"><?=lang('system_role', 'role_level');?></label>
                <div class="form-control">
                    <select class="form-select js-custom-select" name="<?=adminPanelController::INPUT_LEVEL;?>" data-max-width="300px">
                        <?php for($i = Role::MAX_LEVEL; $i >= Role::MIN_LEVEL; $i--): ?>
                            <option <?=($i == $level ? 'selected': null);?> value="<?=$i;?>"><?=$i;?></option>
                        <?php endfor; ?>
                    </select>
                    <div class="label-desc"><?=lang('system_role', 'desc_level');?></div>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('system_role', 'role_color');?></label>
                <div class="form-control">
                    <input type="color" class="form-input" name="<?=adminPanelController::INPUT_COLOR;?>" value="<?=_echo($color);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label"><?=lang('system_role', 'role_permission');?></label>
                <div class="form-control">
                    <div class="genre-list">
                    <?php foreach(UserPermission::list() as $key => $value):
                        $is_selected = in_array($key, $perms) ? true : false;
                    ?>
                        <div class="state-btn <?=($is_selected ? 'include' : null);?>" title="<?=_echo($value);?>">
                            <select name="<?=adminPanelController::INPUT_PERMISSION;?>[<?=$key;?>]">
                                <option value="0"></option>
                                <option <?=($is_selected ? 'selected' : null);?> value="1"></option>
                            </select>
                            <label><?=$key;?></label>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
            <a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>

<link rel="stylesheet" href="<?=assets('css/colorPicker.css');?>">
<script type="text/javascript" src="<?=assets('js/colorPicker.js');?>"></script>

<script type="text/javascript">
    (function() {
        $(document).ready(function() {
            $('.state-btn').on('click', function() {
                var selectedGenre = $(this).children('select');
                if ($(this).hasClass('include')) {
                    $(this).removeClass('include');
                    selectedGenre.val(0).change();
                } else {
                    $(this).addClass('include');
                    selectedGenre.val(1).change();
                }
            });
        });
    })();
</script>