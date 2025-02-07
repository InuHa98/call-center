<?php View::render('layout.header', ['title' => $title]); ?>

<?php

echo assetController::load_css('messenger.css');

$block = ($is_spam == true ? 'spam' : 'inbox');

?>

<div class="section-sub-header">
    <span><?=lang('messenger', 'title');?></span>
</div>


<div class="row">
	<div class="col-xs-12 col-lg-12 col-xl-3">
		<div class="box box--list">
			<div class="box__body">
				<a class="box__body-item <?=($is_spam != true ? 'active' : null);?>" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_INBOX]);?>">
					<span class="item-icon">
						<i class="fab fa-facebook-messenger"></i>
					</span>
					<div>
						<span class="item-title"><?=lang('messenger', 'txt_list');?></span>
					</div>
				<?php if($_count_message > 0) : ?>
					<span class="count-new-item"><?=$_count_message;?></span>
				<?php endif; ?>
				</a>
				<a class="box__body-item <?=($is_spam == true ? 'active' : null);?>" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_SPAM]);?>">
					<span class="item-icon">
						<i class="fas fa-ban"></i>
					</span>
					<div>
						<span class="item-title"><?=lang('messenger', 'txt_spam');?></span>
					</div>
				</a>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-lg-12 col-xl-9">
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

	
	<div class="messenger-type">
		<div class="inbox-status">
			<a class="inbox-status__button <?=(!$type ? 'active' : null);?>" href="<?=RouteMap::get('messenger', ['block' => $block]);?>"><?=lang('messenger', 'all');?></a>
			<a class="inbox-status__button <?=($type == Messenger::UNSEEN ? 'active' : null);?>" href="<?=RouteMap::get('messenger', ['block' => $block, 'id' => Messenger::UNSEEN]);?>"><?=lang('messenger', 'unread');?></a>
			<a class="inbox-status__button <?=($type == Messenger::SEEN ? 'active' : null);?>" href="<?=RouteMap::get('messenger', ['block' => $block, 'id' => Messenger::SEEN]);?>"><?=lang('messenger', 'read');?></a>
		</div>
		<form class="inbox-search" method="GET">
			<input type="text" class="inbox-search__input" name="<?=messengerController::INPUT_SEARCH_KEYWORD;?>" value="<?=_echo($keyword);?>" placeholder="<?=lang('placeholder', 'search');?>">
			<span class="inbox-search__icon <?=($keyword != "" ? 'icon-cancel': null);?>">
				<i class="fas fa-search"></i>
			</span>
		</form>
	</div>
	

	<?php if($inbox_data): ?>
		<div class="messenger-inbox">
		<?php foreach($inbox_data as $inbox): ?>
			<?php
				$prefix = '';
				$isSeen = false;
				$isNew = false;
				$status = '';

				if($inbox['user_to_id'] == Auth::$id)
				{
					$user = [
						'id' => $inbox['user_from_id'],
						'name' => $inbox['user_from_name'],
						'username' => $inbox['user_from_username'],
						'avatar' => $inbox['user_from_avatar'],
						'is_ban' => $inbox['user_from_user_ban'],
						'role_color' => $inbox['user_from_role_color']
					];
					
					if(!$inbox['seen'])
					{
						$isNew = true;
					}

				}
				else
				{
					$user = [
						'id' => $inbox['user_to_id'],
						'name' => $inbox['user_to_name'],
						'username' => $inbox['user_to_username'],
						'avatar' => $inbox['user_to_avatar'],
						'is_ban' => $inbox['user_to_user_ban'],
						'role_color' => $inbox['user_to_role_color']
					];
	
					$prefix = lang('messenger', 'you').':';
	
					if($inbox['seen'] && $inbox['seen'] > 0)
					{
						$isSeen = true;
					}
				}

				$avatar = '
				<div class="user-avatar">
					<img src="'.User::get_avatar($user).'" />
					'.no_avatar($user).'
				</div>';

				if($isNew == true)
				{
					$status = '<span class="new-message"></span>';
				}
				else if($prefix)
				{
					if($isSeen == true)
					{
						$status = '<span class="seen">'.$avatar.'</span>';
					}
					else
					{
						$status = '<span class="unseen"><i class="fas fa-check-circle"></i></span>';
					}
				}
			?>
			<a class="messenger-inbox__item <?=($isNew == true ? 'new' : null);?>" href="<?=RouteMap::get('messenger', ['block' => $block, 'id' => $inbox['id']]);?>">
				<div class="drop-menu">
					<button class="drop-menu__button">
						<i class="fa fa-ellipsis-v"></i>
					</button>
					<ul class="drop-menu__content">
						<?=($is_spam === true ? '
							<li role="make-inbox">
								<form method="POST">
									'.Security::insertHiddenToken().'
									'.messengerController::insertHiddenAction(messengerController::ACTION_MAKE_INBOX).'
									'.messengerController::insertHiddenID($inbox['id']).'
								</form>
								<i class="fas fa-undo"></i> '.lang('messenger', 'not_spam').'
							</li>
						' : '
							<li role="make-spam">
								<form method="POST">
									'.Security::insertHiddenToken().'
									'.messengerController::insertHiddenAction(messengerController::ACTION_MAKE_SPAM).'
									'.messengerController::insertHiddenID($inbox['id']).'
								</form>
								<i class="fas fa-ban"></i> '.lang('messenger', 'make_spam').'
							</li>
						');?>
						<li role="delete" class="text-danger">
							<form method="POST">
								<?=Security::insertHiddenToken();?>
								<?=messengerController::insertHiddenAction(messengerController::ACTION_DELETE_MESSAGE);?>
								<?=messengerController::insertHiddenID($inbox['id']);?>
							</form>
							<i class="fa fa-trash"></i> <?=lang('messenger', 'delete');?>
						</li>
					</ul>
				</div>

				<div class="messenger-inbox__item-avatar"><?=$avatar;?></div>
				<div class="messenger-inbox__item-preview">
					<div class="name"><?=ucwords(User::get_username($user));?></div>
					<div class="message">
						<div class="text"><?=$prefix.' '._echo(BBcode::hide($inbox['text']));?></div>
						<div class="">&nbsp;·&nbsp;</div>
						<div class="time"><?=_time($inbox['time']);?></div>
					</div>
				</div>
				<div class="messenger-inbox__item-status"><?=$status;?></div>
			</a>
		<?php endforeach; ?>

		</div>

		<div class="pagination">
			<?=html_pagination($pagination);?>
		</div>

	<?php else: ?>
		<div class="alert"><?=lang('messenger', 'empty');?></div>
	<?php endif; ?>
	</div>
</div>



<script type="text/javascript" src="<?=APP_URL;?>/assets/js/form-validator.js?v=<?=$_version;?>"></script>

<script type="text/javascript">
	$(document).ready(function() {

		$('.drop-menu').on('click', function(e) {
			e.preventDefault();
		});

        role_click('make-spam', function(self) {
            self.find('form').submit();
        });

        role_click('make-inbox', function(self) {
            self.find('form').submit();
        });

        role_click('delete', function(self) {
            self.find('form').submit();
        });

		$('form .icon-cancel').on('click', function() {
			$('form.inbox-search input').val('');
			$('form.inbox-search').submit();
		});
	});
</script>
<?php View::render('layout.footer'); ?>