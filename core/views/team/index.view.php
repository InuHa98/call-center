<?php View::render('layout.header', ['title' => $title]); ?>

<?php

echo assetController::load_css('team.css');



$html_profit = '';
$html_deduct = '';

switch($team['type']) {
	case Team::TYPE_ONLY_CALL:
		$html_profit = '<span>Profit: </span><span class="text-success">+'.Currency::format($team['profit_call']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
		$html_deduct = '<span>Deduct: </span><span class="text-danger">-'.Currency::format($team['deduct_call']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
		break;

	case Team::TYPE_ONLY_ADS:
		$html_profit = '<span>Profit: </span><span class="text-success">+'.Currency::format($team['profit_ads']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
		$html_deduct = '<span>Deduct: </span><span class="text-danger">-'.Currency::format($team['deduct_ads']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
		break;

	case Team::TYPE_ONLY_SHIP:
		$html_profit = '<span>Profit: </span><span class="text-success">+'.Currency::format($team['profit_ship']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
		$html_deduct = '<span>Deduct: </span><span class="text-danger">-'.Currency::format($team['deduct_ship']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
		break;

	case Team::TYPE_FULLSTACK:
		$is_caller = true;
		$is_shipper = true;
		$is_advertisers = true;

		$perms = json_decode($team['perms'], true);
		foreach ($perms as $key => $value)
		{
			$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
			if($value != true)
			{
				if($key == 'access_caller') {
					$is_caller = false;
				}
				else if($key == 'access_advertisers') {
					$is_advertisers = false;
				}
				else if($key == 'access_shipper') {
					$is_shipper = false;
				}
			}
		}
		if($is_caller) {
			$html_profit .= '
			<span>
				<i class="fas fa-headset"></i> <span class="text-success">+'.Currency::format($team['profit_call']).' '.$team['currency'].'</span>
			</span>';
			$html_deduct .= '
			<span>
				<i class="fas fa-headset"></i> <span class="text-danger">-'.Currency::format($team['deduct_call']).' '.$team['currency'].'</span>
			</span>';
		}

		if($is_advertisers) {
			$html_profit .= '
			<span>
				<i class="fas fa-ad"></i> <span class="text-success">+'.Currency::format($team['profit_ads']).' '.$team['currency'].'</span>
			</span>';
			$html_deduct .= '
			<span>
				<i class="fas fa-ad"></i> <span class="text-danger">-'.Currency::format($team['deduct_ads']).' '.$team['currency'].'</span>
			</span>';      
		}

		if($is_shipper) {
			$html_profit .= '
			<span>
				<i class="fas fa-shipping-fast"></i> <span class="text-success">+'.Currency::format($team['profit_ship']).' '.$team['currency'].'</span>
			</span>';
			$html_deduct .= '
			<span>
				<i class="fas fa-shipping-fast"></i><span class="text-danger">-'.Currency::format($team['deduct_ship']).' '.$team['currency'].'</span>
			</span>';         
		}

		break;
}

?>


<div class="box">
	<div class="box__header">
		<?=$data_team['icon'];?> <span class="padding-x-2"><?=_echo($team['name']);?></span>
		<span class="action">
			<span class="user-role" style="background: <?=$data_team['color'];?>"><?=$data_team['text'];?></span>
		</span>
	</div>
	<div class="box__body">

		<div class="box__body-item">
			<div>
				<span class="item-title">
					<?=$html_profit;?>
				</span>
				<span class="item-text desc-text"><?=lang('team', 'desc_profit');?></span>
			</div>
		</div>

		<div class="box__body-item">
			<div>
				<span class="item-title">
					<?=$html_deduct;?>
				</span>
				<span class="item-text desc-text"><?=lang('team', 'desc_deduct');?></span>
			</div>
		</div>

	</div>
</div>



<div class="tabmenu-horizontal margin-b-2">

	<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_DASHBOARD ? 'active' : null);?>">
		<a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_DASHBOARD]);?>"><?=lang('team', 'dashboard');?></a>
	</div>

	<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_STATISTICS ? 'active' : null);?>">
		<a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_STATISTICS]);?>"><?=lang('team', 'statistic');?></a>
	</div>

	<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_MEMBER ? 'active' : null);?>">
		<a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER]);?>"><?=lang('team', 'member');?></a>
	</div>

	<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_ORDERS ? 'active' : null);?>">
		<a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_ORDERS]);?>"><?=lang('team', 'order');?></a>
	</div>

	<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_PAYMENT ? 'active' : null);?>">
		<a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_PAYMENT]);?>">
			<span><?=lang('team', 'payment');?></span>
		<?php if($new_payment > 0): ?>
			<span class="count-new-item bg-pink"><?=$new_payment;?></span>
		<?php endif; ?>
		</a>
	</div>

</div>
<?php 
if(isset($block_view['view']))
{
	View::render($block_view['view'], $block_view['data']);
}
?>




<?=assetController::load_js('form-validator.js');?>

<?php View::render('layout.footer'); ?>