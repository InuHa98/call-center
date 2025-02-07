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
        <span><?=lang('admin_panel', 'team_ban_list');?> (<b><?=$count;?></b>)</span>
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
                        <th><?=lang('label', 'reason');?></th>
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
            $color = $data_team['color'];
            $type = $data_team['text'];
            

            $products = Product::select([
                'id',
                'name'
            ])::list([
                '[RAW] FIND_IN_SET(<{table}.id>, :ids)' => [
                    'ids' => $team['product_id']
                ]
            ]);

        ?>
            <tr>
                <td>
                <?php if($is_access_unban): ?>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content" data-id="<?=$team['id'];?>">
                            <li role="unban-team">
                                <i class="far fa-unlock"></i> <?=lang('team_management', 'unban');?>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
                </td>
                <td>
                    <span class="user-avatar">
                        <?=no_avatar($team);?>
                    </span>
                </td>
                <td class="nowrap">
                    <a target="_blank" href="<?=RouteMap::get('team', ['id' => $team['id']]);?>">
                        <strong class="btn btn--small btn-outline-danger"><?=_echo($team['name']);?></strong>
                    </a>
                </td>
                <td class="nowrap">
                    <span class="user-role" style="background: <?=$color;?>"><?=$type;?></span>
                </td>
                <td class="nowrap">
                    <i class="fas fa-globe-americas"></i> <?=_echo($team['country_name']);?>
                </td>
                <td>
                    <?php if($products): ?>
                        <?php foreach($products as $product): ?>
                            <a  target="_blank" class="product-item" href="<?=RouteMap::build_query([InterFaceRequest::ID => $product['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PRODUCT, 'action' => adminPanelController::ACTION_EDIT]);?>">
                                <i class="fas fa-shopping-cart"></i> <?=_echo($product['name']);?>
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
                <td>
                    <?=_echo($team['reason_ban'], true);?>
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
        <div class="alert"><?=lang('team_management', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_TEAMS = <?=json_encode($team_list);?>;


    <?php if($is_access_unban): ?>
        $(document).on('click', '[role="unban-team"]', async function(e) {
            e.preventDefault();

            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('<?=lang('errors', 'team_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            if(await comfirm_dialog('<?=lang('team_management', 'unban');?>', '<?=lang('team_management', 'unban');?>: <b>' + team.name + '</b>?') == true) {
                const form = $('\
                <form method="post" style="display: none">\
                    <?=$insertHiddenToken;?>\
                    <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_UNBAN;?>">\
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + team.id + '">\
                </form>');
                $('body').append(form);
                form.submit();
            }
        });
    <?php endif; ?>


    });
</script>