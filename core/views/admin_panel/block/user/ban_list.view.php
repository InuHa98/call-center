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

<div class="box">
    <div class="box__body">
        <form method="GET" class="filter-bar margin-b-2">

            <div class="form-control">
                <div class="input-group">
                    <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-input border-radius-left" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="<?=lang('placeholder', 'search');?>" value="<?=_echo($keyword);?>"/>
                    <div class="input-group-append">
                        <select class="js-custom-select" name="<?=InterFaceRequest::TYPE;?>">
                            <option value="<?=adminPanelController::INPUT_USERNAME;?>"><?=lang('label', 'username');?></option>
                            <option <?=($type == adminPanelController::INPUT_EMAIL ? 'selected' : null);?> value="<?=adminPanelController::INPUT_EMAIL;?>"><?=lang('label', 'email');?></option>
                        </select>               
                    </div>
                </div>
            </div>

            <div class="form-control">
                <select class="js-custom-select" name="<?=adminPanelController::INPUT_ROLE;?>" onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_ALL;?>"><?=lang('system', 'all_role');?></option>
                <?php foreach($list_role as $rl): ?>
                    <option <?=($role == $rl['id'] ? 'selected' : null);?> value="<?=$rl['id'];?>"><?=_echo($rl['name']);?></option>
                <?php endforeach; ?>
                </select>
            </div>

        </form>




    <?php if($user_list): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th></th>
                        <th><?=lang('label', 'username');?></th>
                        <th></th>
                        <th><?=lang('label', 'role');?></th>
                        <th><?=lang('label', 'email');?></th>
                        <th><?=lang('label', 'reason');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($user_list as $user):

        ?>
            <tr>
                <td><?=$user['id'];?></td>
                <td>
                    <span class="user-avatar">
                        <img src="<?=User::get_avatar($user);?>" />
                        <?=no_avatar($user);?>
                    </span>
                </td>
                <td>
                    <a target="_blank" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
                        <strong><?=User::get_username($user);?></strong>
                    </a>
                </td>
                <td>
                <?php if($user['id'] != Auth::$data['id'] && (UserPermission::isAdmin() || $user['role_level'] > Auth::$data['role_level'])): ?>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content" data-id="<?=$user['id'];?>">
                        <?php if(UserPermission::has('admin_user_unban')): ?>
                            <li role="unban-user">
                                <i class="far fa-unlock"></i> <?=lang('user_management', 'unban');?>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                </td>
                <td>
                    <span class="user-role" style="background: <?=_echo($user['role_color']);?>"><?=_echo($user['role_name']);?></span>
                </td>
                <td class="nowrap">
                    <i class="fas fa-envelope"></i>
                    <span><?=_echo($user['email']);?></span>
                </td>
                <td width="100%">
                    <?=_echo($user['reason_ban'], true);?>
                </td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>

    <?php else: ?>
        <div class="alert"><?=lang('user_management', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_USERS = <?=json_encode($user_list);?>;

    <?php if(UserPermission::has('admin_user_unban')): ?>
        $(document).on('click', '[role="unban-user"]', async function(e) {
            e.preventDefault();

            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('<?=lang('errors', 'user_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            if(await comfirm_dialog('<?=lang('user_management', 'unban');?>', '<?=lang('user_management', 'unban');?>: <b>' + user.username + '</b>?') == true) {
                const form = $('\
                <form method="post" style="display: none">\
                    <?=$insertHiddenToken;?>\
                    <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_UNBAN;?>">\
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + user.id + '">\
                </form>');
                $('body').append(form);
                form.submit();
            }
        });
    <?php endif; ?>


    });
</script>