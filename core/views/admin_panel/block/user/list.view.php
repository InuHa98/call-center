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

            <div class="form-control">
                <select class="js-custom-select" name="<?=adminPanelController::INPUT_TEAM;?>" onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_ALL;?>"><?=lang('system', 'all_team');?></option>
                    <option <?=($team_type == 1 ? 'selected' : null);?> value="1"><?=lang('status', 'has_team');?></option>
                    <option <?=($team_type == 2 ? 'selected' : null);?> value="2"><?=lang('status', 'no_team');?></option>
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
                        <th width="100%"><?=lang('label', 'team');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($user_list as $user):
            $data_team = Team::get_data($user['team_type']);
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
                        <strong><?=$user['username'];?></strong>
                    </a>
                </td>
                <td>
                <?php if($user['id'] != Auth::$data['id'] && (UserPermission::isAdmin() || $user['role_level'] > Auth::$data['role_level'])): ?>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content" data-id="<?=$user['id'];?>">
                        <?php if(UserPermission::has('admin_user_edit')): ?>
                        <?php if(!$user['team_id']): ?>
                            <li role="set-team" class="border-bottom">
                                <i class="fas fa-users-class"></i> <?=lang('user_management', 'set_team');?>
                            </li>
                        <?php endif; ?>
                            <li role="change-username">
                                <i class="fa fa-user"></i> <?=lang('user_management', 'change_username');?>
                            </li>
                            <li role="change-password">
                                <i class="fa fa-lock"></i> <?=lang('user_management', 'change_password');?>
                            </li>
                            <li role="change-email">
                                <i class="fas fa-envelope"></i> <?=lang('user_management', 'change_email');?>
                            </li>
                            <li class="border-bottom" role="change-permission">
                                <i class="fas fa-user-cog"></i> <?=lang('user_management', 'change_permission');?>
                            </li>
                        <?php endif; ?>
                        <?php if(UserPermission::has('admin_user_ban')): ?>
                            <li role="ban-user" class="text-danger">
                                <i class="fas fa-ban"></i> <?=lang('user_management', 'ban');?>
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
                <td>
                    <?php if($user['team_type']): ?>

                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('team', ['id' => $user['team_id']]);?>">
                        <span class="user-avatar avatar--small">
                            <span style="background: <?=$data_team['color'];?>"><?=$data_team['icon'];?></span>
                        </span>
                        <span class="user-display-name"><?=_echo($user['team_name']);?></span>
                    </a>
                    <?php endif; ?>
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
        DATA_USERS.map(o => {
            try {
                o.perms = JSON.parse(o.perms);
                o.role_perms = JSON.parse(o.role_perms);
            } catch(error) {

            }
            return o;
        });

    <?php if(UserPermission::has('admin_user_edit')): ?>
		$(document).on('click', '[role=change-username]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('<?=lang('errors', 'user_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_USERNAME;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + user.id + '">\
                <div class="dialog-label"><?=lang('user_management', 'new_username');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <input type="text" class="form-input" name="<?=adminPanelController::INPUT_USERNAME;?>" placeholder="<?=lang('user_management', 'new_username');?>" value="' + user.username + '">\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('user_management', 'change_username');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'change');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
                    Validator({
                        form: form,
                        selector: '.form-control',
                        class_error: 'error',
                        rules: {
                            '.form-input': [
                                Validator.isRequired(),
                                Validator.isUsername('<?=lang('errors', 'username');?>'),
                                Validator.minLength(<?=Auth::USERNAME_MIN_LENGTH;?>),
                                Validator.maxLength(<?=Auth::USERNAME_MAX_LENGTH;?>)
                            ]
                        }
                    });
                },
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
		});

        $(document).on('click', '[role=change-password]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));
            const min = <?=Auth::PASSWORD_MIN_LENGTH;?>;
            const max = <?=Auth::PASSWORD_MAX_LENGTH;?>;
            if(!user) {
                return $.toastShow('<?=lang('errors', 'user_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_PASSWORD;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + user.id + '">\
                <div class="dialog-label"><?=lang('user_management', 'new_password');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <input type="password" class="form-input" id="new-password" name="<?=adminPanelController::INPUT_PASSWORD;?>" placeholder="<?=lang('user_management', 'new_password');?>">\
                    </div>\
                </div>\
                <div class="dialog-label margin-t-4"><?=lang('user_management', 'confirm_new_password');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <input type="password" class="form-input" id="new-password-confirm" name="<?=adminPanelController::INPUT_PASSWORD_CONFIRM;?>" placeholder="<?=lang('user_management', 'confirm_new_password');?>">\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('user_management', 'change_password');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'change');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
                    Validator({
                        form: form,
                        selector: '.form-control',
                        class_error: 'error',
                        rules: {
                            '#new-password': [
                                Validator.isRequired(),
                                Validator.minLength(min, '<?=lang('errors', 'min_password', ['min' => Auth::PASSWORD_MIN_LENGTH]);?>'),
                                Validator.maxLength(max, '<?=lang('errors', 'max_password', ['max' => Auth::PASSWORD_MAX_LENGTH]);?>')
                            ],
                            '#new-password-confirm': [
                                Validator.isRequired(),
                                Validator.isConfirmed(document.querySelector('#new-password'), '<?=lang('errors', 'confirm_password');?>')
                            ]
                        }
                    });
                },
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
		});

        $(document).on('click', '[role=change-email]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('<?=lang('errors', 'user_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_EMAIL;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + user.id + '">\
                <div class="dialog-label"><?=lang('user_management', 'new_email');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <input type="text" class="form-input" name="<?=adminPanelController::INPUT_EMAIL;?>" placeholder="<?=lang('user_management', 'new_email');?>" value="' + user.email + '">\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('user_management', 'change_email');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'change');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
                    Validator({
                        form: form,
                        selector: '.form-control',
                        class_error: 'error',
                        rules: {
                            '.form-input': [
                                Validator.isRequired(),
                                Validator.isEmail('<?=lang('errors', 'email');?>')
                            ]
                        }
                    });
                },
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
		});

        $(document).on('click', '[role=change-permission]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('<?=lang('errors', 'user_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_PERMISSION;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + user.id + '">\
                <div class="form-group">\
                    <div class="form-control">\
                        <div class="genre-list">\
                        <?php foreach($list_permission as $key => $value): ?>\
                            <div class="state-btn" title="<?=_echo($value);?>">\
                                <select name="<?=adminPanelController::INPUT_PERMISSION;?>[<?=$key;?>]" data-perm="<?=$key;?>">\
                                    <option value="0"></option>\
                                    <option value="1"></option>\
                                </select>\
                                <label><?=$key;?></label>\
                            </div>\
                        <?php endforeach; ?>\
                        </div>\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('user_management', 'change_permission');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'change');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {

                    user.role_perms.forEach(function(perm) {
                        const select = form.find('select[data-perm="' + perm + '"]');
                        select.val(1);
                        select.parents('.state-btn').addClass('include');
                    });

                    for(const key in user.perms) {
                        const value = user.perms[key];
                        const select = form.find('select[data-perm="' + key + '"]');
                        if(value == true || value == 'true' || value == 1 || value == '1') {
                            select.val(1);
                            select.parents('.state-btn').addClass('include');
                        } else {
                            select.val(0);
                            select.parents('.state-btn').removeClass('include');
                        }
                    }


                    $(document).off('click.permission').on('click.permission', '.state-btn', function() {
                        var selectedGenre = $(this).children('select');
                        if ($(this).hasClass('include')) {
                            $(this).removeClass('include');
                            selectedGenre.val(0).change();
                        } else {
                            $(this).addClass('include');
                            selectedGenre.val(1).change();
                        }
                    });
                },
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
		});

        $(document).on('click', '[role=set-team]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('<?=lang('errors', 'user_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_SET_TEAM;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + user.id + '">\
                <div class="dialog-label"><?=lang('user_management', 'select_team');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <select class="js-custom-select" name="<?=adminPanelController::INPUT_TEAM;?>" enable-search="true" data-placeholder="<?=lang('user_management', 'select_team');?>">\
                    <?php foreach($list_team as $team):
                        $data_team = Team::get_data($team['type']);
                        $html_option = '<div class="user-infomation"><div class="user-avatar avatar--small"><span style="background: '.$data_team['color'].'">'.$data_team['icon'].'</span></div><div class="user-display-name">'.$team['name'].' - '.$team['country_name'].'</div></div>';
                    ?>\
                        <option value="<?=$team['id'];?>" data-html="<?=_echo($html_option);?>"></option>\
                    <?php endforeach; ?>\
                        </select>\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('user_management', 'set_team');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'continue');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
                    JSCustomSelect();
                },
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
		});
    <?php endif; ?>

    <?php if(UserPermission::has('admin_user_ban')): ?>
        $(document).on('click', '[role="ban-user"]', function(e) {
            e.preventDefault();

            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('<?=lang('errors', 'user_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }


            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_BAN;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + user.id + '">\
                <div class="dialog-label"><?=lang('user_management', 'reason_ban');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <textarea class="form-textarea" name="<?=adminPanelController::INPUT_REASON;?>" placeholder="<?=lang('placeholder', 'can_be_left_blank');?>"></textarea>\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('user_management', 'ban');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'continue');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});

        });
    <?php endif; ?>


    });
</script>