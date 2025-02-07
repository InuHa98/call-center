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
    <div class="box__header">
        <span><?=lang('admin_panel', 'team_list');?> (<b><?=$count;?></b>)</span>
    <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_TEAM, 'block' => adminPanelController::BLOCK_TEAM_LIST, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    <?php endif; ?>        
    </div>
    <div class="box__body">

        <form method="GET" class="filter-bar">
            <div class="form-control">
                <div class="form-icon">
                    <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-input border-radius-left" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="<?=lang('placeholder', 'search');?>" value="<?=_echo($keyword);?>">
                </div>
            </div>

            <div class="form-control">
                <select class="js-custom-select" name="<?=InterFaceRequest::COUNTRY;?>" data-placeholder="<?=lang('placeholder', 'select_country');?>" data-max-width="180px" onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_ALL;?>"><?=lang('system', 'all_country');?></option>
                <?php foreach($list_country as $country): ?>
                    <option <?=($country['id'] == $country_id ? 'selected' : null);?> value="<?=$country['id'];?>"><?=_echo($country['name']);?></option>
                <?php endforeach;?> 
                </select>
            </div>

            <div class="form-control">
                <select class="js-custom-select" name="<?=InterFaceRequest::TYPE;?>" data-placeholder="<?=lang('placeholder', 'select_team');?>" data-max-width="180px"  onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_ALL;?>"><?=lang('system', 'all_team');?></option>
                    <option <?=($type_id == Team::TYPE_ONLY_CALL ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_CALL;?>"><?=Role::DEFAULT_NAME_CALLER;?></option>
                    <option <?=($type_id == Team::TYPE_ONLY_SHIP ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_SHIP;?>"><?=Role::DEFAULT_NAME_SHIPPER;?></option>
                    <option <?=($type_id == Team::TYPE_ONLY_ADS ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_ADS;?>"><?=Role::DEFAULT_NAME_ADVERTISER;?></option>
                    <option <?=($type_id == Team::TYPE_FULLSTACK ? 'selected' : null);?> value="<?=Team::TYPE_FULLSTACK;?>"><?=Role::DEFAULT_NAME_FULLSTACK;?></option>
                </select>
            </div>

        </form>

    <?php if($team_list): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><?=lang('label', 'name_team');?></th>
                        <th><?=lang('label', 'type');?></th>
                        <th><?=lang('label', 'country');?></th>
                        <th><?=lang('label', 'product');?></th>
                        <th><?=lang('label', 'leader');?></th>
                        <th class="align-center"><?=lang('label', 'member');?></th>
                        <th class="align-center"><?=lang('label', 'profit');?></th>
                        <th class="align-center"><?=lang('label', 'deduct');?></th>
                        <th class="align-center"><?=lang('label', 'currency');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($team_list as $team):
            $user_leader = [
                'id' => $team['leader_id'],
                'username' => $team['leader_username'],
                'avatar' => $team['leader_avatar'],
                'is_ban' => $team['leader_is_ban'],
                'role_color' => $team['leader_role_color']
            ];

            $data_team = Team::get_data($team['type']);

            $products = Team::get_product($team);

            $html_profit = '';
            $html_deduct = '';

            switch($team['type']) {
                case Team::TYPE_ONLY_CALL:
                    $html_profit = '<span class="badge rounded-pill bg-success">+'.Currency::format($team['profit_call']).'</span>';
                    $html_deduct = '<span class="badge rounded-pill bg-danger">-'.Currency::format($team['deduct_call']).'</span>';
                    break;

                case Team::TYPE_ONLY_ADS:
                    $html_profit = '<span class="badge rounded-pill bg-success">+'.Currency::format($team['profit_ads']).'</span>';
                    $html_deduct = '<span class="badge rounded-pill bg-danger">-'.Currency::format($team['deduct_ads']).'</span>';
                    break;

                case Team::TYPE_ONLY_SHIP:
                    $html_profit = '<span class="badge rounded-pill bg-success">+'.Currency::format($team['profit_ship']).'</span>';
                    $html_deduct = '<span class="badge rounded-pill bg-danger">-'.Currency::format($team['deduct_ship']).'</span>';
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
                            <i class="fas fa-headset"></i> <span class="badge rounded-pill bg-success">+'.Currency::format($team['profit_call']).'</span>
                        </span>';   
                        $html_deduct .= '
                        <span>
                            <i class="fas fa-headset"></i> <span class="badge rounded-pill bg-danger">-'.Currency::format($team['deduct_call']).'</span>
                        </span>';
                    
                    }

                    if($is_advertisers) {
                        $html_profit .= '
                        <span>
                            <i class="fas fa-ad"></i> <span class="badge rounded-pill bg-success">+'.Currency::format($team['profit_ads']).'</span>
                        </span>';
                        $html_deduct .= '
                        <span>
                            <i class="fas fa-ad"></i> <span class="badge rounded-pill bg-danger">-'.Currency::format($team['deduct_ads']).'</span>
                        </span>';                 
                    }

                    if($is_shipper) {
                        $html_profit .= '
                        <span>
                            <i class="fas fa-shipping-fast"></i> <span class="badge rounded-pill bg-success">+'.Currency::format($team['profit_ship']).'</span>
                        </span>';
                        $html_deduct .= '
                        <span>
                            <i class="fas fa-shipping-fast"></i> <span class="badge rounded-pill bg-danger">-'.Currency::format($team['deduct_ship']).'</span>
                        </span>';                 
                    }

                    break;
            }
        ?>
            <tr>
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content" data-id="<?=$team['id'];?>">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $team['id']], 'admin_panel', ['group' => adminPanelController::GROUP_TEAM, 'block' => adminPanelController::BLOCK_TEAM_LIST, 'action' => adminPanelController::ACTION_EDIT]);?>">
                                    <i class="fa fa-edit"></i> <?=lang('button', 'edit');?>
                                </a>
                            </li>
                            <?php if($team['type'] == Team::TYPE_FULLSTACK): ?>
                            <li role="change-permission">
                                <i class="fas fa-user-cog"></i> <?=lang('team_management', 'change_permission');?>
                            </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if($is_access_ban): ?>
                            <li role="ban-team" class="border-top text-danger">
                                <i class="fas fa-ban"></i> <?=lang('team_management', 'ban');?>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td>
                    <span class="user-avatar">
                        <span style="background: <?=$data_team['color'];?>"><?=$data_team['icon'];?></span>
                    </span>
                </td>
                <td class="nowrap">
                    <a target="_blank" href="<?=RouteMap::get('team', ['id' => $team['id']]);?>">
                        <strong class="btn btn--small btn-outline-gray"><?=_echo($team['name']);?></strong>
                    </a>
                </td>
                <td class="nowrap">
                    <span class="user-role" style="background: <?=$data_team['color'];?>"><?=$data_team['text'];?></span>
                </td>
                <td class="nowrap">
                    <i class="fas fa-globe-americas"></i> <?=_echo($team['country_name']);?>
                </td>
                <td class="nowrap">
                    <?php if($products): ?>
                        <?php foreach($products as $product): ?>
                            <a target="_blank" class="product-infomation" href="<?=RouteMap::build_query([InterFaceRequest::ID => $product['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PRODUCT, 'action' => adminPanelController::ACTION_EDIT]);?>">
                                <img class="product-image" src="<?=Product::get_image($product);?>" />
                                <span class="product-name"><?=_echo($product['name']);?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_leader['id']]);?>">
                        <span class="user-avatar avatar--small">
                            <img src="<?=User::get_avatar($user_leader);?>" />
                            <?=no_avatar($user_leader);?>
                        </span>
                        <span class="user-display-name"><?=User::get_username($user_leader);?></span>
                    </a>
                </td>
                <td class="nowrap align-center"><i class="fad fa-users"></i> <?=$team['total_members'];?></td>
                <td class="nowrap"><?=$html_profit;?></td>
                <td class="nowrap"><?=$html_deduct;?></td>
                <td class="nowrap align-center"><span class="badge rounded-pill bg-dark"><?=_echo($team['currency']);?></span></td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>

    <?php else: ?>
        <div class="alert"><?=lang('team_management', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_TEAMS = <?=json_encode($team_list);?>;
        DATA_TEAMS.map(o => {
            o.perms = JSON.parse(o.perms);
            return o;
        });

    <?php if($is_access_edit): ?>
        $(document).on('click', '[role=change-permission]', function() {
            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('<?=lang('errors', 'team_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_PERMISSION;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + team.id + '">\
                <div class="form-group">\
                    <div class="form-control">\
                        <div class="genre-list">\
                        <?php foreach($list_permission as $key => $value): ?>\
                            <div class="state-btn include" title="<?=_echo($value);?>">\
                                <select name="<?=adminPanelController::INPUT_PERMISSION;?>[<?=$key;?>]" data-perm="<?=$key;?>">\
                                    <option value="1"></option>\
                                    <option value="0"></option>\
                                </select>\
                                <label><?=$key;?></label>\
                            </div>\
                        <?php endforeach; ?>\
                        </div>\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('team_management', 'change_permission');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'change');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {

                    for(const key in team.perms) {
                        const value = team.perms[key];
                        const select = form.find('select[data-perm="' + key + '"]');
                        if(value == false || value == 'false' || value != 1 || value == '0') {
                            select.val(0);
                            select.parents('.state-btn').removeClass('include');
                        } else {
                            select.val(1);
                            select.parents('.state-btn').addClass('include');
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
    <?php endif; ?>

    <?php if($is_access_ban): ?>
        $(document).on('click', '[role="ban-team"]', async function(e) {
            e.preventDefault();

            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('<?=lang('errors', 'team_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=$insertHiddenToken;?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_BAN;?>">\
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + team.id + '">\
                <div class="dialog-label"><?=lang('team_management', 'reason_ban');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <textarea class="form-textarea" name="<?=adminPanelController::INPUT_REASON;?>" placeholder="<?=lang('placeholder', 'can_be_left_blank');?>"></textarea>\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('team_management', 'ban');?>',
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