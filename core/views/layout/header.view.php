<?php

$title = isset($title) ? _echo($title).' - '. env(DotEnv::APP_TITLE) : env(DotEnv::APP_TITLE);
$current_route = isset($current_route) ? $current_route : Router::$current_route;

$contact_support = User::get([
	'adm' => UserPermission::IS_ADMIN,
	'ORDER' => [
		'id' => 'ASC'
	]
]);

if(Auth::$isLogin == true)
{
	$is_advertiser = UserPermission::is_advertisers();
	$is_caller = UserPermission::is_caller();
	$is_shipper = UserPermission::is_shipper();

	$display_name = ucwords(Auth::$data['username']);
	$role_color = Auth::$data['role_color'];
	$role_name = Auth::$data['role_name'];
	$avatar = User::get_avatar();
	$no_avatar = no_avatar(Auth::$data);
	$team = Team::get(Auth::$data['team_id']);

	$currency = isset($team['currency']) ? _echo($team['currency']) : null;

	$is_ban_team = (Auth::$data['is_ban_team'] == Team::IS_BAN);
	$html_profit = '';
	$html_deduct = '';
	$count_payment_team = $team && $team['leader_id'] == Auth::$data['id'] ? Payment::count([
		'team_id' => $team['id'],
		'status' => Payment::STATUS_NOT_PAID
	]) : 0;

	$earning = Currency::format(User::get_earnings());
	$holding = Currency::format(User::get_holdings());
	$deduct = Currency::format(User::get_deduct());

	switch(isset($team['type']) ? $team['type'] : null) {
		case Team::TYPE_ONLY_CALL:
			$html_profit = '<span>'.lang('system', 'profit').': </span><span class="text-success">+'.Currency::format(Auth::$data['profit_call']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
			$html_deduct = '<span>'.lang('system', 'deduct').': </span><span class="text-danger">-'.Currency::format(Auth::$data['deduct_call']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
			break;

		case Team::TYPE_ONLY_ADS:
			$html_profit = '<span>'.lang('system', 'profit').': </span><span class="text-success">+'.Currency::format(Auth::$data['profit_ads']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
			$html_deduct = '<span>'.lang('system', 'deduct').': </span><span class="text-danger">-'.Currency::format(Auth::$data['deduct_ads']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
			break;

		case Team::TYPE_ONLY_SHIP:
			$html_profit = '<span>'.lang('system', 'profit').': </span><span class="text-success">+'.Currency::format(Auth::$data['profit_ship']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
			$html_deduct = '<span>'.lang('system', 'deduct').': </span><span class="text-danger">-'.Currency::format(Auth::$data['deduct_ship']).'</span> <span class="text-gray">'.$team['currency'].'</span>';
			break;

		case Team::TYPE_FULLSTACK:

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
						$is_advertiser = false;
					}
					else if($key == 'access_shipper') {
						$is_shipper = false;
					}
				}
			}

			$html_profit .= '<div>'.lang('system', 'profit').' / '.lang('system', 'deduct').':</div>';
			if($is_caller) {
				$html_profit .= '
				<div>
					<i class="fas fa-headset"></i> <span class="text-success">+'.Currency::format(Auth::$data['profit_call']).'</span> '.$team['currency'].' / <span class="text-danger">-'.Currency::format(Auth::$data['deduct_call']).'</span> '.$team['currency'].'
				</div>';
			}

			if($is_advertiser) {
				$html_profit .= '
				<div>
					<i class="fas fa-ad"></i> <span class="text-success">+'.Currency::format(Auth::$data['profit_ads']).'</span> '.$team['currency'].' / <span class="text-danger">-'.Currency::format(Auth::$data['deduct_ads']).'</span> '.$team['currency'].'
				</div>';            
			}

			if($is_shipper) {
				$html_profit .= '
				<div>
					<i class="fas fa-shipping-fast"></i> <span class="text-success">+'.Currency::format(Auth::$data['profit_ship']).'</span> '.$team['currency'].' / <span class="text-danger">-'.Currency::format(Auth::$data['deduct_ship']).'</span> '.$team['currency'].'
				</div>';             
			}

			break;
	}
}

?>
<!DOCTYPE html>
	<html lang="vi" xmlns="http://www.w3.org/1999/xhtml" prefix="fb: http://www.facebook.com/2008/fbml">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Language" content="vi" />
		<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<!--meta name="referrer" content="no-referrer" -->

		<title><?=$title;?></title>
		
		<meta property="og:title" content="<?=$title;?>" />
		<meta property="og:locale" content="vi_VN" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="<?=APP_URL.'/'.trim($_SERVER['REQUEST_URI'], "/");?>" />
		<meta property="og:site_name" content="<?=env(DotEnv::APP_NAME);?>" />


		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="icon" type="image/x-icon" href="<?=APP_URL;?>/assets/images/favico.ico">
		<link rel="shortcut icon" type="image/x-icon" href="<?=APP_URL;?>/assets/images/favico.ico">

		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/font-awesome/css/all.css?v=<?=$_version;?>" />
		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/app.css?v=<?=$_version;?>" />

		<script type="text/javascript" src="<?=APP_URL;?>/assets/js/jquery-3.4.1.min.js?v=<?=$_version;?>"></script>
	</head>
	<body>

		<div class="side-nav-menu">

			<a class="logo" href="<?=APP_URL;?>">Call<span>Center</span></a>

			<div class="nav__statistic">
				<div class="nav__statistic-item">
					<div class="label"><?=lang('system', 'earning');?>:</div>
					<div class="text"><?=$earning;?> <?=$currency;?></div>
				</div>
				<div class="nav__statistic-item">
					<div class="label"><?=lang('system', 'holding');?>:</div>
					<div class="text"><?=$holding;?> <?=$currency;?></div>
				</div>
				<div class="nav__statistic-item">
					<div class="label"><?=lang('system', 'deduct');?>:</div>
					<div class="text"><?=$deduct;?> <?=$currency;?></div>
				</div>
			</div>

			<ul class="side-nav-menu__items">

				<li class="side-nav-menu__items-link <?=(Router::$current_route == 'dashboard' ? 'active' : null);?>">
					<a class="color-green" href="<?=RouteMap::get('dashboard');?>">
						<i class="fas fa-chart-line"></i>
						<?=lang('system', 'dashboard');?>
					</a>
				</li>

			<?php if(Team::is_leader(Auth::$data['team_id'])): ?>
				<li class="side-nav-menu__items-link <?=($current_route == 'team' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('team');?>">
						<span><i class="fas fa-users-class"></i> <?=lang('system', 'my_team');?></span>
					<?php if($count_payment_team): ?>
						<span class="count-new-item bg-pink"><?=$count_payment_team;?></span>
					<?php endif; ?>
					</a>
				</li>
			<?php endif; ?>

			<?php if(UserPermission::isAccessAdminPanel()): ?>
				<li class="side-nav-menu__items-title">
					Administrator:
				</li>
				<li class="side-nav-menu__items-link <?=($current_route == 'admin_panel' ? 'active' : null);?>">
					<a class="color-green" href="<?=RouteMap::get('admin_panel');?>">
						<i class="fas fa-cogs"></i>
						<?=lang('system', 'admin_panel');?>
					</a>
				</li>
			<?php endif; ?>

			<?php if(UserPermission::access_order_statistics()): ?>
				<li class="side-nav-menu__items-link <?=($current_route == 'statistics_order' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('statistics', ['block' => statisticsController::BLOCK_ORDER]);?>">
						<i class="far fa-chart-bar"></i>
						<?=lang('system', 'order_statistics');?>
					</a>
				</li>
			<?php endif; ?>

			<?php if(UserPermission::access_landing_statistics()): ?>
				<li class="side-nav-menu__items-link <?=($current_route == 'statistics_landing' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('statistics', ['block' => statisticsController::BLOCK_LANDING]);?>">
						<i class="far fa-chart-bar"></i>
						<?=lang('system', 'landing_statistics');?>
					</a>
				</li>
			<?php endif; ?>
			
			<?php if(UserPermission::access_order_management()): ?>
				<li class="side-nav-menu__items-link <?=(Router::$current_route == 'order_management' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('order_management');?>">
						<i class="fas fa-tasks"></i>
						<?=lang('system', 'order_management');?>
					</a>
				</li>
			<?php endif; ?>

			<?php if(UserPermission::access_payment_team()): ?>
				<li class="side-nav-menu__items-link <?=(Router::$current_route == 'payment' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('payment');?>">
						<i class="far fa-money-bill-alt"></i>
						<?=lang('system', 'payment');?>
					</a>
				</li>
			<?php endif; ?>

			<?php if(UserPermission::access_blacklist()): ?>
				<li class="side-nav-menu__items-link <?=(Router::$current_route == 'blacklist' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('blacklist');?>">
						<i class="fas fa-ban"></i>
						<?=lang('system', 'blacklist');?>
					</a>
				</li>
			<?php endif; ?>

			<?php if($is_advertiser): ?>
				<li class="side-nav-menu__items-title">
					Ads Group:
				</li>
			<?php if(!$is_ban_team): ?>
				<li class="side-nav-menu__items-link <?=($current_route == 'landing_page' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('advertiser', ['action' => advertiserController::ACTION_LANDING_PAGE]);?>">
						<i class="fas fa-link"></i>
						<?=lang('system', 'landing_page');?>
					</a>
				</li>
				<li class="side-nav-menu__items-link <?=($current_route == 'add_conversion' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('advertiser', ['action' => advertiserController::ACTION_ADD]);?>">
						<i class="fa fa-plus"></i>
						<?=lang('system', 'add_conversion');?>
					</a>
				</li>
			<?php endif; ?>
				<li class="side-nav-menu__items-link <?=($current_route == 'my_conversion' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('advertiser');?>">
						<i class="fas fa-list"></i>
						<?=lang('system', 'list_conversion');?>
					</a>
				</li>
			<?php endif; ?>

			<?php if($is_caller):
				$count_pending = Order::count([
                    'status' => [
						Order::STATUS_PENDING_CONFIRM,
						Order::STATUS_BUSY_CALLBACK,
						Order::STATUS_CAN_NOT_CALL
					],
					'country_id' => $team['country_id'],
					'call_team_id' => [0, Auth::$data['team_id']]
				]);
			?>
				<li class="side-nav-menu__items-title">
					Call Group:
				</li>
			
				<li class="side-nav-menu__items-link <?=($current_route == 'calling' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('caller', ['block' => callerController::BLOCK_CALLING]);?>">
						<i class="far fa-phone-volume"></i>
						<?=lang('system', 'order_calling');?>
					</a>
				</li>
			<?php if(!$is_ban_team): ?>
				<li class="side-nav-menu__items-link  <?=($current_route == 'pending_confirm' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('caller', ['block' => callerController::BLOCK_PENDING_CONFIRM]);?>">
						<i class="fas fa-hourglass-half"></i>
						<span><?=lang('system', 'order_pending_confirm');?></span>
					<?php if($count_pending): ?>
						<span class="count-new-item"><?=$count_pending;?></span>
					<?php endif; ?>
					</a>
				</li>
			<?php endif; ?>
			<?php endif; ?>

			<?php if($is_shipper):
				$count_order = Order::count([
					'status' => [
						Order::STATUS_AGREE_BUY,
						Order::STATUS_DELIVERY_DATE
					],
					'country_id' => $team['country_id'],
					'ship_user_id' => 0
				]);
				$count_pending = Order::count([
					'status' => [
						Order::STATUS_AGREE_BUY,
						Order::STATUS_DELIVERY_DATE
					],
					'country_id' => $team['country_id'],
					'ship_user_id' => Auth::$data['id']
				]);
			?>
				<li class="side-nav-menu__items-title">
					Ship Group:
				</li>
				<?php if(!$is_ban_team): ?>
				<li class="side-nav-menu__items-link  <?=($current_route == 'new_order' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('shipper', ['block' => shipperController::BLOCK_NEW_ORDER]);?>">
						<i class="fas fa-shopping-cart"></i>
						<span><?=lang('system', 'new_order');?></span>
					<?php if($count_order): ?>
						<span class="count-new-item"><?=$count_order;?></span>
					<?php endif; ?>
					</a>
				</li>
				<?php endif; ?>

				<li class="side-nav-menu__items-link  <?=($current_route == 'pending_delivery' ? 'active' : null);?>">
					<a href="<?=RouteMap::get('shipper', ['block' => shipperController::BLOCK_PENDING_DELIVERY]);?>">
						<i class="fas fa-truck-loading"></i>
						<span><?=lang('system', 'pending_delivery');?></span>
					<?php if($count_pending): ?>
						<span class="count-new-item"><?=$count_pending;?></span>
					<?php endif; ?>
					</a>
				</li>
			<?php endif; ?>

			<?php if($is_caller || $is_shipper):
				
				$count_delivering = Order::count([
					'status' => Order::STATUS_DELIVERING,
					'country_id' => $team['country_id'],
					'OR' => [
						'call_user_id' => Auth::$data['id'],
						'ship_user_id' => Auth::$data['id']
					]
				]);

			?>
				<li class="side-nav-menu__items-group show__group">
					<div class="side-nav-menu__items-group__title">
						<span class="group_text">
							<i class="fas fa-list"></i>
							<?=lang('system', 'order');?>
						</span>
						<span class="group_arrow">
							<i class="fas fa-chevron-right"></i>
						</span>
					</div>
					<ul class="side-nav-menu__items-group__items">
					<?php if($is_caller):

						$count_busy_callback = Order::count([
							'status' => Order::STATUS_BUSY_CALLBACK,
							'country_id' => $team['country_id'],
							'call_user_id' => Auth::$data['id']
						]);

						$count_can_not_call = Order::count([
							'status' => Order::STATUS_CAN_NOT_CALL,
							'country_id' => $team['country_id'],
							'call_user_id' => Auth::$data['id']
						]);

						$count_pending_delivery = Order::count([
							'status' => [
								Order::STATUS_AGREE_BUY,
								Order::STATUS_DELIVERY_DATE
							],
							'country_id' => $team['country_id'],
							'call_user_id' => Auth::$data['id']
						]);
					?>
						<li>
							<a class="<?=($current_route == 'order_busy_callback' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_BUSY_CALLBACK]);?>">
								<span><?=lang('system', 'order_busy_callback');?></span>
							<?php if($count_busy_callback): ?>
								<span class="count-new-item bg-warning"><?=$count_busy_callback;?></span>
							<?php endif; ?>
							</a>
						</li>
						<li>
							<a class="<?=($current_route == 'order_can_not_call' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_CAN_NOT_CALL]);?>">
								<span><?=lang('system', 'order_can_not_call');?></span>
							<?php if($count_can_not_call): ?>
								<span class="count-new-item bg-danger"><?=$count_can_not_call;?></span>
							<?php endif; ?>
							</a>
						</li>

						<li>
							<a class="<?=($current_route == 'order_pending_delivery' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_PENDING_DELIVERY]);?>">
								<?=lang('system', 'order_pending_delivery');?>
							<?php if($count_pending_delivery): ?>
								(<span class="text-warning"><?=$count_pending_delivery;?></span>)
							<?php endif; ?>
							</a>
						</li>



					<?php endif;?>


						<li>
							<a class="<?=($current_route == 'order_delivering' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_DELIVERING]);?>">
							<?=lang('system', 'order_delivering');?> 
							<?php if($count_delivering): ?>
								(<span class="text-primary"><?=$count_delivering;?></span>)
							<?php endif; ?>
							</a>
						</li>
						<li>
							<a class="<?=($current_route == 'order_delivered' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_DELIVERED]);?>">
							<?=lang('system', 'order_delivered');?> 
							</a>
						</li>
						<li>
							<a class="<?=($current_route == 'order_unreceived' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_UNRECEIVED]);?>">
							<?=lang('system', 'order_unreceived');?> 
							</a>
						</li>
						<li>
							<a class="<?=($current_route == 'order_returned' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_RETURNED]);?>">
							<?=lang('system', 'order_returned');?> 
							</a>
						</li>


					<?php if($is_caller):?>
						<li>
							<a class="<?=($current_route == 'order_refuse_buy' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_REFUSE_BUY]);?>">
							<?=lang('system', 'order_refuse_buy');?>
							</a>
						</li>
						<li>
							<a class="<?=($current_route == 'order_wrong_number' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_WRONG_NUMBER]);?>">
							<?=lang('system', 'order_wrong_number');?>
							</a>
						</li>

						<li>
							<a class="<?=($current_route == 'order_duplicate' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_DUPLICATE]);?>">
							<?=lang('system', 'order_duplicate');?> 
							</a>
						</li>
						<li>
							<a class="<?=($current_route == 'order_trash' ? 'active' : null);?>" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_TRASH]);?>">
							<?=lang('system', 'order_trash');?> 
							</a>
						</li>
					<?php endif;?>
					</ul>
				</li>
			<?php endif; ?>

			</ul>
		</div>

		<div class="side-nav-main">
			<div id="section-header" class="section-header">
				<div class="section-header__wrapper">
					<div id="btn_sidenav-menu" class="section-header__button">
						<i class="fas fa-bars"></i>
					</div>
					<a class="section-header__logo" href="<?=APP_URL;?>">Call<span>Center</span></a>
					<div class="section-header__statistic">
						<div class="section-header__statistic-item">
							<div class="label"><?=lang('system', 'earning');?>:</div>
							<div class="text"><?=$earning;?></div>
						</div>
						<div class="section-header__statistic-item">
							<div class="label"><?=lang('system', 'holding');?>:</div>
							<div class="text"><?=$holding;?></div>
						</div>
						<div class="section-header__statistic-item">
							<div class="label"><?=lang('system', 'deduct');?>:</div>
							<div class="text"><?=$deduct;?></div>
						</div>
					</div>
					<div class="section-header__auth">
						<div class="section-header__auth-action">


						<?php if(Auth::$isLogin == true): ?>
							<a class="action__message" href="<?=RouteMap::get('messenger');?>">
								<i class="fas fa-envelope"></i>
							<?php
								if($_count_message > 0) { 
									echo '<span class="count">'.$_count_message.'</span>';
								}
							?>
							</a>
							<div class="action__notification" id="btn_notification">
								<span class="icon">
									<i class="fa fa-bell"></i>
								<?php if($_count_notification > 0): ?>
									<span class="count"><?=$_count_notification;?></span>
								<?php endif; ?>
								</span>
								<span class="text"><?=lang('system', 'notification');?></span>
								<span class="arrow">
									<i class="fas fa-sort-down"></i>
								</span>
							<?php
							

								$notification_content = '';

								if(isset($_notification) && $_notification)
								{ 
									$notification_content = '<ul class="notification-list">';

									foreach($_notification as $item)
									{
										$user_from = [
											'id' => $item['user_from_id'],
											'avatar' => $item['user_from_avatar'],
											'username' => $item['user_from_username']
										];
										$notification_content .= '
										<li>
											<a class="notification-list__item" href="'.RouteMap::get('notification', ['id' => $item['id']]).'">
												<span class="user-avatar">
													<img src="'.User::get_avatar($user_from).'">
													'.no_avatar($user_from).'
												</span>
												<div>
													<div class="notification-list__item-text">'.notificationController::renderHTML($item, true).'</div>
													<div class="notification-list__item-time">'._time($item['created_at']).'</div>
												</div>
											</a>
										</li>';								
									}

									$notification_content .= '</ul>';
								}

							?>
								<div class="notification-header" id="notification-content">
									<div class="notification-header__title"><?=lang('system', 'notification_new');?></div>
									<div class="notification-header__content">

									<?php if(!$_count_notification): ?>
										<span class="empty__notification">
											<div class="empty__notification-icon">
												<i class="fas fa-bell-slash"></i>
											</div>
											<div class="empty__notification-text"><?=lang('system', 'notification_empty');?></div>
										</span>
									<?php else: ?>
										<?=$notification_content;?>
									<?php endif; ?>
									</div>
									<div class="notification-header__footer">
										<a href="<?=RouteMap::get('notification');?>"><?=lang('system', 'view_all');?></a>
									</div>
								</div>
							</div>
						<?php endif; ?>
						</div>
						<div class="section-header__auth-infomation" id="btn_auth">

							<div class="auth-infomation">
								<span class="auth-infomation__avatar">
									<span class="user-avatar">
										<img src="<?=$avatar;?>" />
										<?=$no_avatar;?>
									</span>
								</span>
								<span class="text"><?=$display_name;?></span>
								<span class="arrow">
									<i class="fas fa-sort-down"></i>
								</span>
							</div>
							<ul class="auth-menu">

							<?php if($team): ?>
								<li class="infomation">
									<div class="text-header">
										<?=$html_profit;?>
									</div>
									<div class="text-header">
										<?=$html_deduct;?>
									</div>
								</li>
							<?php endif; ?>
								

								<li>
									<a href="<?=RouteMap::get('profile', ['id' => 'me']);?>">
										<i class="fa fa-user"></i>
										<?=lang('system', 'my_account');?>
									</a>							
								</li>
								<li>
									<a href="<?=RouteMap::get('payment', ['block' => paymentController::BLOCK_HISTORY]);?>">
										<i class="far fa-money-bill-alt"></i>
										<?=lang('system', 'payment_history');?>
									</a>							
								</li>
							<?php if($contact_support): ?>
								<li>
									<a href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_NEW, 'id' => $contact_support['id']]);?>">
										<i class="fas fa-user-headset"></i>
										<?=lang('system', 'contact_support');?>
									</a>							
								</li>
							<?php endif; ?>
								<li>
									<a href="<?=RouteMap::get('logout');?>">
										<i class="fa fa-power-off"></i>
										<?=lang('system', 'logout');?>
									</a>							
								</li>
							</ul>

						</div>
					</div>
				</div>
			</div>
			<div class="section-content">
				<div class="container-fluid">