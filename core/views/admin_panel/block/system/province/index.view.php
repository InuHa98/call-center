<div class="box">
    <div class="box__header">
        <span><?=lang('admin_panel', 'system_province');?> (<b><?=$count;?></b>)</span>
    <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::build_query([InterFaceRequest::ID => $country['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PROVINCE, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    <?php endif; ?>
    </div>
    <div class="box__body">

    <?php if($province_list): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th><?=lang('label', 'province');?>/th>
                        <th><?=lang('label', 'country');?></th>
                        <th width="100%"><?=lang('label', 'area');?></th>
                        <th><?=lang('label', 'district');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($province_list as $province):
            $district = Districts::count(['province_id' => $province['id']]);
        ?>
            <tr class="valign-center">
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $province['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PROVINCE, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> <?=lang('button', 'edit');?></a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-country" href="<?=RouteMap::build_query([InterFaceRequest::ID => $province['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PROVINCE, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> <?=lang('button', 'delete');?></a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td>
                    <?=$province['id'];?>
                </td>
                <td class="nowrap">
                    <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $province['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_DISTRICT]);?>">
                        <strong><?=_echo($province['name']);?></strong>
                    </a>
                </td>
                <td class="nowrap">
                    <i class="fas fa-globe-americas"></i> <?=_echo($province['country_name']);?>
                </td>
                <td class="nowrap">
                    <?php if($province['area_name']): ?>
                    <i class="fas fa-map-marker-alt"></i> <?=_echo($province['area_name']);?>
                    <?php endif; ?>
                </td>
                <td class="align-center nowrap"><?=Currency::format($district);?></td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>

    <?php else: ?>
        <div class="alert"><?=lang('system_province', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-country"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('system_province', 'txt_delete');?>', '<?=lang('system_province', 'txt_desc_delete');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>