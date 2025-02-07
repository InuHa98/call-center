<div class="box">
    <div class="box__header">
        <span><?=lang('admin_panel', 'system_currency');?> (<b><?=$count;?></b>)</span>
    <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CURRENCY, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    <?php endif; ?>
    </div>

    <div class="box__body">

    <?php if($currency_list): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th></th>
                        <th width="40%"><?=lang('label', 'currency');?></th>
                        <th width="30%"><?=lang('label', 'exchange_rate');?></th>
                        <th width="30%"></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($currency_list as $currency): ?>
            <tr class="valign-center">
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $currency['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CURRENCY, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> <?=lang('button', 'edit');?></a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-country" href="<?=RouteMap::build_query([InterFaceRequest::ID => $currency['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CURRENCY, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> <?=lang('button', 'delete');?></a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td class="nowrap">
                    <strong><?=_echo($currency['name']);?></strong>
                </td>
                <td>
                    <span class="badge rounded-pill bg-dark"><?=_echo($currency['exchange_rate']);?></span>
                </td>
                <td class="nowrap">
                    1 <?=_echo($currency['name']);?></strong> = <strong><?=Currency::format($currency['to_default_currency']);?> <?=Currency::DEFAULT_CURRENCY;?>
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
        <div class="alert"><?=lang('system_currency', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-country"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('system_currency', 'txt_delete');?>', '<?=lang('system_currency', 'txt_desc_delete');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>