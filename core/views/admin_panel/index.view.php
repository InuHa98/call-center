<?php View::render('layout.header', ['title' => $title]); ?>

<?php

echo assetController::load_css('admin_panel.css');

?>

<div class="section-sub-header">
    <span><?=lang('system', 'admin_panel');?></span>
</div>


<div class="row">
	<div class="col-xs-12">
		<div class="tabmenu-horizontal margin-b-2">
		<?php if(UserPermission::is_access_group_system()): ?>
			<div class="tabmenu-horizontal__item <?=($group_name == adminPanelController::GROUP_SYSTEM ? 'active' : null);?>">
				<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM]);?>"><?=lang('admin_panel', 'system_setting');?></a>
			</div>
		<?php endif;?>

		<?php if(UserPermission::is_access_group_user()): ?>
			<div class="tabmenu-horizontal__item <?=($group_name == adminPanelController::GROUP_USER ? 'active' : null);?>">
				<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_USER]);?>"><?=lang('admin_panel', 'user_management');?></a>
			</div>
		<?php endif;?>

		<?php if(UserPermission::is_access_group_team()): ?>
			<div class="tabmenu-horizontal__item <?=($group_name == adminPanelController::GROUP_TEAM ? 'active' : null);?>">
				<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_TEAM]);?>">
					<span><?=lang('admin_panel', 'team_management');?></span>
				<?php if($group_name != adminPanelController::GROUP_TEAM && $notification_approval_team > 0): ?>
					<span class="count-new-item"><?=$notification_approval_team;?></span>
				<?php endif; ?>
				</a>
			</div>
		<?php endif;?>
		</div>
	</div>

	<div class="col-xs-12 col-lg-12 col-xl-3">
	<?php 
		if(isset($block_view['view_group']))
		{
			View::render($block_view['view_group'], compact('block_name', 'notification_approval_team'));
		}
	?>
	</div>

	<div class="col-xs-12 col-lg-12 col-xl-9">
	<?php 
		if(isset($block_view['view_block']))
		{
			View::render($block_view['view_block'], $block_view['data']);
		}
	?>
	</div>
</div>


<?=assetController::load_js('form-validator.js');?>

<?php View::render('layout.footer'); ?>