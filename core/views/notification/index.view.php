<?php View::render('layout.header', ['title' => $title]); ?>

<?php

echo assetController::load_css('notification.css');

$insertHiddenToken = Security::insertHiddenToken();
?>

<div class="section-sub-header">
    <span><?=lang('notification', 'title');?></span>
</div>



<div class="flex-panel">
	<div class="flex-panel__box">
		<span><?=lang('notification', 'all_notifi');?> (<strong><?=$count;?></strong>)</span>
	</div>
	<div class="flex-panel__box flex--right">
		<div class="drop-menu">
			<span class="btn btn--small btn--round">
				<?=lang('notification', 'option');?> <i class="fas fa-ellipsis-h"></i>
			</span>
			<ul class="drop-menu__content">
			<?php if($type == null || $type == notificationController::TYPE_UNSEEN): ?>
				<li role="make-seen-all">
					<i class="fas fa-check-double"></i> <?=lang('notification', 'mark_all_seen');?>
				</li>
			<?php endif; ?>
			<?php if($type == null || $type == notificationController::TYPE_SEEN): ?>
				<li role="make-unseen-all">
					<i class="fas fa-undo"></i> <?=lang('notification', 'mark_all_seen');?>
				</li>
			<?php endif; ?>
				<li role="delete-all" class="border-bottom text-danger">
					<i class="fas fa-trash"></i> <?=lang('notification', 'delete_all');?>
				</li>
			<?php if($type == null || $type == notificationController::TYPE_UNSEEN): ?>
				<li role="make-seen-selected" class="disabled">
				<?=lang('notification', 'mark_seen');?> <span role="multiple_selected_count">(0)</span>
				</li>
			<?php endif; ?>
			<?php if($type == null || $type == notificationController::TYPE_SEEN): ?>
				<li role="make-unseen-selected" class="disabled">
				<?=lang('notification', 'mark_unseen');?> <span role="multiple_selected_count">(0)</span>
				</li>
			<?php endif; ?>
				<li role="delete-selected" class="disabled text-danger">
				<?=lang('notification', 'delete_select');?> <span role="multiple_selected_count">(0)</span>
				</li>
			</ul>
		</div>
	</div>
</div>


<div class="notification-type">
	<div class="notification-status">
		<div class="notification-select">
			<span class="form-check">
				<input type="checkbox"id="multiple_selected_all">
				<label for="multiple_selected_all"></label>
			</span>
		</div>
		<a class="notification-status__button <?=($type == null ? 'active' : null);?>" href="<?=RouteMap::get('notification');?>"><?=lang('notification', 'all');?></a>
		<a class="notification-status__button <?=($type == notificationController::TYPE_UNSEEN ? 'active' : null);?>" href="<?=RouteMap::get('notification', ['id' => notificationController::TYPE_UNSEEN]);?>"><?=lang('notification', 'unseen');?></a>
		<a class="notification-status__button <?=($type == notificationController::TYPE_SEEN ? 'active' : null);?>" href="<?=RouteMap::get('notification', ['id' => notificationController::TYPE_SEEN]);?>"><?=lang('notification', 'seen');?></a>
	</div>
</div>


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

<?php if($notification_data): ?>
	<div class="notifications-list">
	<?php foreach($notification_data as $notifi): ?>
		<?php

			$user = [
				'id' => $notifi['user_from_id'],
				'name' => $notifi['user_from_name'],
				'username' => $notifi['user_from_username'],
				'avatar' => $notifi['user_from_avatar']
			];

			$isSeen = $notifi['seen'] == Notification::SEEN ? true : false;

		?>
		<a class="notifications-list__item <?=($isSeen == true ? null : 'unseen');?>" href="<?=RouteMap::get('notification', ['id' => $notifi['id']]);?>">
			<div class="notification-select">
				<span class="form-check">
					<input type="checkbox" role="multiple_selected" name="id[]" value="<?=$notifi['id'];?>" id="label_<?=$notifi['id'];?>">
					<label for="label_<?=$notifi['id'];?>"></label>
				</span>
			</div>

			<div class="drop-menu">
				<button class="drop-menu__button">
					<i class="fa fa-ellipsis-v"></i>
				</button>
				<ul class="drop-menu__content">
					<?=($notifi['seen'] == Notification::UNSEEN ? '
						<li role="make-seen">
							<form method="POST">
								'.$insertHiddenToken.'
								'.notificationController::insertHiddenAction(notificationController::ACTION_MAKE_SEEN).'
								'.notificationController::insertHiddenID($notifi['id']).'
							</form>
							<i class="fas fa-check"></i> '.lang('notification', 'mark_seen').'
						</li>
					' : '
						<li role="make-unseen">
							<form method="POST">
								'.$insertHiddenToken.'
								'.notificationController::insertHiddenAction(notificationController::ACTION_MAKE_UNSEEN).'
								'.notificationController::insertHiddenID($notifi['id']).'
							</form>
							<i class="fas fa-undo"></i> '.lang('notification', 'mark_unseen').'
						</li>
					');?>
					<li role="delete" class="text-danger">
						<form method="POST">
							<?=$insertHiddenToken;?>
							<?=notificationController::insertHiddenAction(notificationController::ACTION_DELETE);?>
							<?=notificationController::insertHiddenID($notifi['id']);?>
						</form>
						<i class="fa fa-trash"></i> <?=lang('notification', 'delete');?>
					</li>
				</ul>
			</div>

			<div class="notifications-list__item-avatar">
				<div class="user-avatar">
					<img src="<?=User::get_avatar($user);?>" />
					<?=no_avatar($user);?>
				</div>
			</div>
			<div class="notifications-list__item-preview">
				<div class="text"><?=notificationController::renderHTML($notifi, true);?></div>
				<div class="time"><?=_time($notifi['created_at']);?></div>
			</div>
			<div class="notifications-list__item-status"></div>
		</a>
	<?php endforeach; ?>

	</div>

	<div class="pagination">
		<?=html_pagination($pagination);?>
	</div>

<?php else: ?>
	<div class="alert"><?=lang('notification', 'empty');?></div>
<?php endif; ?>
</div>


<form method="POST" id="form-action">
	<?=$insertHiddenToken;?>
	<input id="_action" type="hidden" name="<?=notificationController::NAME_FORM_ACTION;?>" value="" />
</form>

<?=assetController::load_js('form-validator.js');?>


<script type="text/javascript">


	function request_action(type)
	{
		var form = $('#form-action');
		var action = $('#_action');
		action.val(type);
		$(role_multiple_selected+":checked").each(function(){
			form.append('<input type="hidden" name="<?=notificationController::INPUT_ID;?>[]" value="'+$(this).val()+'">');
		});
		form.submit();
	}

	var role_multiple_selected = '[role=multiple_selected]';

	$(document).ready(function() {

		$('.drop-menu').on('click', function(e) {
			e.preventDefault();
		});

		multiple_selected({
			role_select_all: "#multiple_selected_all",
			role_select: role_multiple_selected,
			onSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('('+total_selected+')');
				$("[role=multiple_selected_count]").parents('li').removeClass("disabled");
			},
			onNoSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('(0)');
				$("[role=multiple_selected_count]").parents('li').removeClass("disabled").addClass("disabled");
			}
		});

		role_click('make-seen-all', function(self) {
            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=notificationController::ACTION_MAKE_SEEN;?>');
			form.submit();
        });

		role_click('make-unseen-all', function(self) {
            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=notificationController::ACTION_MAKE_UNSEEN;?>');
			form.submit();
        });

		role_click('delete-all', async function(self) {
			if(await comfirm_dialog('<?=lang('notification', 'txt_delete_all');?>', '<?=lang('notification', 'txt_desc_delete_all');?>') !== true)
			{
				return;
			}

            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=notificationController::ACTION_DELETE;?>');
			form.submit();
        });

        role_click('make-seen', function(self) {
            self.find('form').submit();
        });

        role_click('make-unseen', function(self) {
            self.find('form').submit();
        });

        role_click('delete', async function(self) {
			if(await comfirm_dialog('<?=lang('notification', 'txt_delete');?>', '<?=lang('notification', 'txt_desc_delete');?>') !== true)
			{
				return;
			}
            self.find('form').submit();
        });


		role_click('make-seen-selected', function(self) {
			request_action('<?=notificationController::ACTION_MAKE_SEEN;?>');
        });

		role_click('make-unseen-selected', function(self) {
			request_action('<?=notificationController::ACTION_MAKE_UNSEEN;?>');
        });

		role_click('delete-selected', async function(self) {
			if(await comfirm_dialog('<?=lang('notification', 'txt_delete_select');?>', '<?=lang('notification', 'txt_desc_delete_select');?>') !== true)
			{
				return;
			}
			request_action('<?=notificationController::ACTION_DELETE;?>');
        });

	});
</script>
<?php View::render('layout.footer'); ?>