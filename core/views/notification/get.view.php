<?php View::render('layout.header', ['title' => $title]); ?>

<?php

echo assetController::load_css('notification.css');

$insertHiddenToken = Security::insertHiddenToken();
?>

<div class="section-sub-header">
    <span><?=lang('notification', 'title');?></span>
</div>


	<div class="row">

		<div class="col-xs-12 col-md-12 col-lg-12">
			<div class="notification-nav">
				<a class="notification-nav__back" href="<?=$referrer;?>">
					<i class="fa fa-arrow-left"></i> <?=lang('system', 'txt_back');?>
				</a>
				<button role="delete" class="notification-nav__delete"><?=lang('notification', 'delete');?></button>
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
			<div class="notifications-get">
				<div class="notifications-get__info">
					<div class="notifications-get__info-avatar">
						<div class="user-avatar">
							<img src="<?=User::get_avatar($user_from);?>" />
							<?=no_avatar($user_from);?>
						</div>
					</div>
					<div class="notifications-get__info-user">
						<a class="username" target="_blank" href="<?=RouteMap::get('profile', ['id' => $user_from['id']]);?>"><?=ucwords(User::get_username($user_from));?></a>
						<div class="time"><?=_time($notification['created_at']);?></div>
					</div>
				</div>
				<div class="notifications-get__content"><?=notificationController::renderHTML($notification);?></div>
			</div>

		</div>
	</div>


<form method="POST" id="form-action">
	<?=$insertHiddenToken;?>
	<input type="hidden" name="<?=notificationController::NAME_FORM_ACTION;?>" value="<?=notificationController::ACTION_DELETE;?>" />
	<input type="hidden" name="<?=notificationController::INPUT_ID;?>" value="<?=$notification['id'];?>" />
</form>

<script type="text/javascript" src="<?=APP_URL;?>/assets/js/form-validator.js?v=<?=$_version;?>"></script>

<script type="text/javascript">

	function comfirm_dialog(title, text)
	{
		return new Promise(function(resolve, reject) {
			$.dialogShow({
				title: title,
				content: '<div class="dialog-message">'+text+'</div>',
				button: {
					confirm: '<?=lang('button', 'continue');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
				onConfirm: function(){
					resolve(true);
				},
				onCancel: function(){
					resolve(false);
				}
			});
		});
	}


	$(document).ready(function() {

		$('.drop-menu').on('click', function(e) {
			e.preventDefault();
		});


		role_click('delete', async function(self) {
			if(await comfirm_dialog('<?=lang('notification', 'txt_delete_select');?>', '<?=lang('notification', 'txt_desc_delete_select');?>') !== true)
			{
				return;
			}
			var form = $('#form-action');
			form.submit();
        });

	});
</script>
<?php View::render('layout.footer'); ?>