<?php View::render('layout.header', ['title' => $title]); ?>

<?=assetController::load_css('profile.css');?>

<div class="section-profile-cover">
	<div id="preview_cover" class="section-profile-cover__bg-cover" >
	</div>
	<div class="section-profile-cover__bg-alpha"></div>
</div>
<div class="section-profile-infomation">
	<div class="container">
		<div class="user-avatar section-profile-infomation__avatar">
			<img id="preview_avatar" class="img-responsive" src="<?=User::get_avatar($user);?>">
			<?=no_avatar($user);?>
			<div role="btn-change-avatar" class="container__avatar-btn-change">
				<i class="fas fa-camera"></i>
			</div>
		</div>
		<div class="section-profile-infomation__info">
			<div class="section-profile-infomation__info-left">
				<div class="name-box">
					<div class="display-name"><?=ucwords($display_name);?></div>
				</div>
				<div>
					<?=User::get_role($user);?>
				</div>
			</div>
			<div class="section-profile-infomation__info-right">
				<a class="send-message" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_NEW, 'id' => $user['id']]);?>">
					<i class="far fa-envelope"></i>
					<span><?=lang('profile', 'send_message');?></span>
				</a>
			</div>
		</div>
	</div>
</div>



<div class="row">
	<div class="col-xs-12 col-lg-12 col-xl-3">
		<div class="box">
			<div class="box__body">
				<div class="statistics">
					<div class="statistics-item">
						<div class="label"><?=lang('system', 'earning');?></div>
						<div class="text"><?=Currency::format(User::get_earnings($user));?></div>
					</div>
					<div class="statistics-item">
						<div class="label"><?=lang('system', 'holding');?></div>
						<div class="text"><?=Currency::format(User::get_holdings($user));?></div>
					</div>
					<div class="statistics-item">
						<div class="label"><?=lang('system', 'deduct');?></div>
						<div class="text"><?=Currency::format(User::get_deduct($user));?></div>
					</div>
				</div>


				<div class="box__body-item">
					<span class="item-icon">
						<i class="far fa-calendar-alt"></i>
					</span>
					<div>
						<span class="item-title"><?=lang('profile', 'date_join');?>:</span>
						<span class="item-text"><?=date('d/m/Y', $user['created_at']);?></span>
					</div>
				</div>
				<div class="box__body-item">
					<span class="item-icon">
						<i class="fas fa-at"></i>
					</span>
					<div>
						<span class="item-title"><?=lang('label', 'email');?>:</span>
						<span class="item-text"><?=_echo($user['email']);?></span>
					</div>
				</div>
				<div class="box__body-item">
					<span class="item-icon">
						<i class="fas fa-users-class"></i>
					</span>
					<div>
						<span class="item-title"><?=lang('label', 'team');?>:</span>
						<span class="item-text">
							<a href="<?=RouteMap::get('team', ['id' => $user['team_id']]);?>">
								<?=_echo($user['team_name']);?>
							</a>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-lg-12 col-xl-9">
		<div class="box">
			<div class="box__header">
				<div class="tabmenu-horizontal">
					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_INFOMATION ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_INFOMATION, 'profile', ['id' => $user['id']]);?>"><?=lang('profile', 'infomation');?></a>
					</div>
					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_BILLING ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_BILLING, 'profile', ['id' => $user['id']]);?>"><?=lang('profile', 'billing');?></a>
					</div>

					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_STATISTICS ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_STATISTICS, 'profile', ['id' => $user['id']]);?>"><?=lang('profile', 'statistic');?></a>
					</div>
					
				</div>
			</div>
			<div class="box__body">
		<?php 
			if($block_view)
			{
				View::render($block_view['view'], $block_view['data']);
			}
		?>
			</div>
		</div>
	</div>
</div>


<?php View::render('layout.footer'); ?>