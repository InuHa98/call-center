<div class="box box--list">
	<div class="box__body">
	<?php if(UserPermission::is_access_configuration()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_CONFIGURATION ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIGURATION]);?>">
			<span class="item-icon">
				<i class="fas fa-cog"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_configuration');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_mailer_setting()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_MAILER ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_MAILER]);?>">
			<span class="item-icon">
				<i class="fas fa-envelope"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_mailer');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_role()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_ROLE ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE]);?>">
			<span class="item-icon">
				<i class="fab fa-joomla"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_role');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_currency()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_CURRENCY ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CURRENCY]);?>">
			<span class="item-icon">
				<i class="fas fa-dollar-sign"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_currency');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_country()): ?>
		<a class="box__body-item <?=(in_array($block_name, [adminPanelController::BLOCK_COUNTRY, adminPanelController::BLOCK_PROVINCE, adminPanelController::BLOCK_DISTRICT, adminPanelController::BLOCK_WARD]) ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY]);?>">
			<span class="item-icon">
				<i class="fas fa-globe-americas"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_country');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_area()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_AREA ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_AREA]);?>">
			<span class="item-icon">
				<i class="fas fa-map-marker-alt"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_area');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_product()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_PRODUCT ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PRODUCT]);?>">
			<span class="item-icon">
				<i class="fas fa-shopping-cart"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_product');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_landing()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_LANDING ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_LANDING]);?>">
			<span class="item-icon">
				<i class="fas fa-link"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_landing_page');?></span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_auto_ban()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_AUTO_BAN ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_AUTO_BAN]);?>">
			<span class="item-icon">
				<i class="fas fa-engine-warning"></i>
			</span>
			<div>
				<span class="item-title"><?=lang('admin_panel', 'system_auto_ban');?></span>
			</div>
		</a>
	<?php endif; ?>

	</div>
</div>