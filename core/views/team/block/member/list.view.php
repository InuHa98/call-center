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
        <span><?=lang('team', 'member');?> (<b><?=$count;?></b>)</span>

        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER, 'action' => teamController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    </div>
    <div class="box__body">

        <form class="filter-bar">

            <div class="form-control">
                <div class="input-group">
                    <input id="filter-keyword" type="text" class="form-input" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="<?=lang('label', 'username');?>" value="<?=_echo($keyword);?>">
                    <div class="input-group-append">
                    <?php if($keyword != ''): ?>
                        <button type="button" id="clear-keyword" class="btn btn-outline-default btn-dim"><i class="fas fa-times"></i></button>
                    <?php else: ?>
                        <button type="button" id="submit-keyword" class="btn btn-outline-default btn-dim"><i class="fas fa-search"></i></button>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-control">
                <select class="js-custom-select" name="<?=InterFaceRequest::STATUS;?>" data-placeholder="Trạng thái" onchange="this.form.submit();">
                    <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_status');?></option>
                    <option <?=($status == Team::IS_NOT_BAN ? 'selected' : null);?> value="<?=Team::IS_NOT_BAN;?>"><?=lang('status', 'available');?></option>
                    <option <?=($status == Team::IS_BAN ? 'selected' : null);?> value="<?=Team::IS_BAN;?>"><?=lang('status', 'unavailable');?></option>
                </select>
            </div>
        </form>

    <?php if($list_user): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><?=lang('label', 'username');?></th>
                        <th></th>
                        <th><?=lang('label', 'profit');?> / <?=lang('label', 'deduct');?></th>
                        <th class="align-center"><?=lang('label', 'total_order');?></th>
                        <th class="align-center"><?=lang('label', 'order_unpaid');?></th>
                        <th class="align-center"><?=lang('label', 'earning');?></th>
                        <th class="align-center"><?=lang('label', 'holding');?></th>
                        <th class="align-center"><?=lang('label', 'deduct');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($list_user as $user):


        $html_profit = '';

        switch($team['type']) {
            case Team::TYPE_ONLY_CALL:
                $html_profit = '<span class="text-success">+'.Currency::format($user['profit_call']).'</span> / <span class="text-danger">-'.Currency::format($user['deduct_call']).'</span>';
                break;

            case Team::TYPE_ONLY_ADS:
                $html_profit = '<span class="text-success">+'.Currency::format($user['profit_ads']).'</span> / <span class="text-danger">-'.Currency::format($user['deduct_ads']).'</span>';
                break;

            case Team::TYPE_ONLY_SHIP:
                $html_profit = '<span class="text-success">+'.Currency::format($user['profit_ship']).'</span> / <span class="text-danger">-'.Currency::format($user['deduct_ship']).'</span>';
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
                        <i class="fas fa-headset"></i> <span class="text-success">+'.Currency::format($user['profit_call']).'</span> / <span class="text-danger">-'.Currency::format($user['deduct_call']).'</span>
                    </span>';   
                }

                if($is_advertisers) {
                    $html_profit .= '
                    <span>
                        <i class="fas fa-ad"></i> <span class="text-success">+'.Currency::format($user['profit_ads']).'</span> / <span class="text-danger">-'.Currency::format($user['deduct_ads']).'</span>
                    </span>';               
                }

                if($is_shipper) {
                    $html_profit .= '
                    <span>
                        <i class="fas fa-shipping-fast"></i> <span class="text-success">+'.Currency::format($user['profit_ship']).'</span> / <span class="text-danger">-'.Currency::format($user['deduct_ship']).'</span>
                    </span>';               
                }

                break;
        }

        ?>
            <tr>
                <td>
                <?php if($user['id'] != Auth::$data['id']): ?>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content" data-id="<?=$user['id'];?>">
                        <?php if($team['leader_id'] != $user['id']): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $user['id']], 'team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER, 'action' => teamController::ACTION_EDIT]);?>">
                                    <i class="fa fa-edit"></i> <?=lang('button', 'edit');?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if($user['is_ban_team'] == Team::IS_NOT_BAN): ?>
                            <li role="ban-user">
                                <i class="fas fa-minus-circle"></i> <?=lang('button', 'ban');?>
                            </li>
                        <?php else: ?>
                            <li role="unban-user">
                                <i class="fas fa-check-double"></i> <?=lang('button', 'unban');?>
                            </li>
                        <?php endif; ?>
                        
                        <?php if($team['leader_id'] != $user['id']): ?>
                            <li class="border-top">
                                <a  role="delete-member" class=" text-danger" href="<?=RouteMap::build_query([InterFaceRequest::ID => $user['id']], 'team', ['id' => $team['id'], 'block' => teamController::BLOCK_MEMBER, 'action' => teamController::ACTION_DELETE]);?>">
                                    <i class="fas fa-ban"></i> <?=lang('button', 'delete');?>
                                </a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                </td>
                <td class="nowrap">
                    <div class="user-avatar">
                        <img src="<?=User::get_avatar($user);?>" />
                        <?=no_avatar($user);?>
                    </div>
                </td>
                <td class="nowrap">
                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
                        <div class="user-display-name">
                            <?=User::get_username($user);?>
                        </div>
                    </a>
                    <?php if(Team::is_leader($team['id'], $user)): ?>
                        <strong class="badge rounded-pill bg-danger"><?=lang('label', 'leader');?></strong>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($user['is_ban_team'] == User::IS_NOT_BAN): ?>
                        <span class="dot dot-success"><?=lang('status', 'available');?></span>
                    <?php else: ?>
                        <span class="dot dot-gray"><?=lang('status', 'unavailable');?></span>
                    <?php endif; ?>
                </td>
                <td class="nowrap">
                    <?=$html_profit;?>
                </td>
                    
                <td class="align-center">
                    <span><?=Currency::format($user['total_orders']);?></span>
                </td>

                <td class="align-center">
                    <span class="text-primary"><?=Currency::format($user['total_unpaid_orders']);?></span>
                </td>
                
                <td class="align-center">
                    <span class="badge rounded-pill bg-success"><?=Currency::format(User::get_earnings($user));?></span>
                </td>
                <td class="align-center">
                    <span class="badge rounded-pill bg-warning"><?=Currency::format(User::get_holdings($user));?></span>
                </td>
                <td class="align-center">
                    <span class="badge rounded-pill bg-danger"><?=Currency::format(User::get_deduct($user));?></span>
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
        <div class="alert"><?=lang('errors', 'empty_user');?></div>
    <?php endif; ?>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        const filter_keyword = $('#filter-keyword');
        const clear_keyword = $('#clear-keyword');
        const submit_keyword = $('#submit-keyword');

        clear_keyword.on('click', function() {
            filter_keyword.val('');
            filter_keyword[0].form.submit();
        });

        submit_keyword.on('click', function() {
            filter_keyword[0].form.submit();
        });

        $(document).on('click', '[role="ban-user"]', async function(e) {
            e.preventDefault();

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_BAN;?>">\
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="' + $(this).parent().data('id') + '">\
                <div class="dialog-label"><?=lang('label', 'reason');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <textarea class="form-textarea" name="<?=teamController::INPUT_REASON;?>" placeholder="<?=lang('placeholder', 'can_be_left_blank');?>"></textarea>\
                    </div>\
                </div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('button', 'ban');?>',
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

        $(document).on('click', '[role="unban-user"]', async function(e) {
            e.preventDefault();

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_UNBAN;?>">\
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="' + $(this).parent().data('id') + '">\
                <div class="dialog-message"><?=lang('button', 'unban');?>?</div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('button', 'unban');?>',
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

        $('[role="delete-member"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('button', 'delete');?>', '<?=lang('system', 'txt_can_undo');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>