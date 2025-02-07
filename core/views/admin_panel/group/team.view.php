<div class="box box--list">
	<div class="box__body">
	<?php if(UserPermission::is_access_team_list()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_TEAM_LIST ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_TEAM, 'block' => adminPanelController::BLOCK_TEAM_LIST]);?>">
			<span class="item-icon">
				<i class="fas fa-users"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'team_list');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_team_ban_list()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_TEAM_BAN_LIST ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_TEAM, 'block' => adminPanelController::BLOCK_TEAM_BAN_LIST]);?>">
			<span class="item-icon">
				<i class="fas fa-ban"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'team_ban_list');?></span>
			</div>
		</a>
	<?php endif; ?>

	</div>
</div>