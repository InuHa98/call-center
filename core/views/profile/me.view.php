<?php View::render('layout.header', ['title' => $block_view['title']]); ?>

<?=assetController::load_css('profile.css');?>

<div class="section-profile-cover">
	<div id="preview_cover" class="section-profile-cover__bg-cover" >
	</div>
	<div class="section-profile-cover__bg-alpha"></div>
</div>
<div class="section-profile-infomation">
	<div class="container">
		<div class="user-avatar section-profile-infomation__avatar">
			<img id="preview_avatar" class="img-responsive" src="<?=User::get_avatar();?>">
			<?=no_avatar(Auth::$data);?>
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
					<?=User::get_role(Auth::$data);?>
				</div>
			</div>
			<div class="section-profile-infomation__info-right">

				<form method="POST">
					<input type="hidden" name="<?=profileController::INPUT_FORM_ACTION;?>" value="<?=profileController::ACTION_UPLOAD_IMAGE;?>">
					<input type="hidden" id="data-upload" name="<?=profileController::INPUT_FORM_DATA_IMAGE;?>">
					<input type="submit" class="save-upload" role="btn-save-upload">
				</form>
			</div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-xs-12 col-lg-12 col-xl-4">
		<div class="box">
			<div class="box__body">
				<div class="statistics">
					<div class="statistics-item">
						<div class="label"><?=lang('system', 'earning');?></div>
						<div class="text"><?=Currency::format(User::get_earnings());?></div>
					</div>
					<div class="statistics-item">
						<div class="label"><?=lang('system', 'holding');?></div>
						<div class="text"><?=Currency::format(User::get_holdings());?></div>
					</div>
					<div class="statistics-item">
						<div class="label"><?=lang('system', 'deduct');?></div>
						<div class="text"><?=Currency::format(User::get_deduct());?></div>
					</div>
				</div>

				<div class="box__body-item">
					<span class="item-icon">
						<i class="far fa-calendar-alt"></i>
					</span>
					<div>
						<span class="item-title"><?=lang('profile', 'date_join');?>:</span>
						<span class="item-text"><?=date('d/m/Y', Auth::$data['created_at']);?></span>
					</div>
				</div>
				<div class="box__body-item">
					<span class="item-icon">
						<i class="fas fa-mobile-alt"></i>
					</span>
					<div>
						<span class="item-title"><?=lang('profile', 'limit_login');?>:</span>
						<span class="item-text"><?=User::count_limit_device();?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-lg-12 col-xl-8">
		<div class="box">
			<div class="box__header">
				<div class="tabmenu-horizontal">
					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_INFOMATION ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_INFOMATION, 'profile', ['id' => 'me']);?>"><?=lang('profile', 'infomation');?></a>
					</div>
					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_BILLING ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_BILLING, 'profile', ['id' => 'me']);?>"><?=lang('profile', 'billing');?></a>
					</div>
					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_LOGINDEVICE ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_LOGINDEVICE, 'profile', ['id' => 'me']);?>"><?=lang('profile', 'devices');?></a>
					</div>
					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_CHANGEPASSWORD ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_CHANGEPASSWORD, 'profile', ['id' => 'me']);?>"><?=lang('profile', 'change_password');?></a>
					</div>
					<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_SETTINGS ? 'active' : null);?>">
						<a href="<?=RouteMap::join('/'.profileController::BLOCK_SETTINGS, 'profile', ['id' => 'me']);?>"><?=lang('profile', 'setting');?></a>
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




<input id="input-upload-image" type="file" style="display: none;">

<?=assetController::load_css('cropper.css');?>
<?=assetController::load_js('cropper.js');?>

<?=assetController::load_js('avatar-cover-upload.js');?>
<?=assetController::load_js('form-validator.js');?>


<?php View::render('layout.footer'); ?>