<div class="box">
    <div class="box__header">
        <span><?=lang('admin_panel', 'system_country');?> (<b><?=$count;?></b>)</span>
    <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    <?php endif; ?>
    </div>
    <div class="box__body">

    <?php if($country_list): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th></th>
                        <th><?=lang('label', 'name_country');?></th>
                        <th><?=lang('label', 'country_code');?></th>
                        <th><?=lang('label', 'currency');?></th>
                        <th><?=lang('label', 'phone_code');?></th>
                        <th class="align-center" width="40%"><?=lang('label', 'province');?>/th>
                        <th class="align-center" width="30%"><?=lang('label', 'district');?></th>
                        <th class="align-center" width="30%"><?=lang('label', 'ward');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($country_list as $country): ?>
            <tr class="valign-center">
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $country['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> <?=lang('button', 'edit');?></a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-country" href="<?=RouteMap::build_query([InterFaceRequest::ID => $country['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_COUNTRY, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> <?=lang('button', 'delete');?></a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td class="nowrap">
                    <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $country['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PROVINCE]);?>">
                        <strong><?=_echo($country['name']);?></strong>
                    </a>
                </td>
                <td>
                    <?=_echo($country['code']);?>
                </td>
                <td>
                    <span class="badge rounded-pill bg-dark"><?=_echo($country['currency']);?></span>
                </td>
                <td>
                    <?=_echo($country['phone_code']);?>
                </td>
                <td class="align-center"><?=_echo($country['total_provinces']);?></td>
                <td class="align-center"><?=_echo($country['total_districts']);?></td>
                <td class="align-center"><?=_echo($country['total_wards']);?></td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>

    <?php else: ?>
        <div class="alert"><?=lang('system_country', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-country"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('system_country', 'txt_delete');?>', '<?=lang('system_country', 'txt_desc_delete');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>