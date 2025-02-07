<div class="box">
    <div class="box__header">
        <span><?=lang('admin_panel', 'system_district');?> (<b><?=$count;?></b>)</span>
    <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::build_query([InterFaceRequest::ID => $province['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_DISTRICT, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    <?php endif; ?>
    </div>

    <div class="box__body">


    <?php if($district_list): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th><?=lang('label', 'name_district');?></th>
                        <th><?=lang('label', 'country');?></th>
                        <th width="100%"><?=lang('label', 'province');?>/th>
                        <th><?=lang('label', 'ward');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($district_list as $district):
            $ward = Wards::count(['district_id' => $district['id']]);
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
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $district['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_DISTRICT, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> <?=lang('button', 'edit');?></a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-country" href="<?=RouteMap::build_query([InterFaceRequest::ID => $district['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_DISTRICT, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> <?=lang('button', 'delete');?></a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td>
                    <?=$district['id'];?>
                </td>
                <td class="nowrap">
                    <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $district['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_WARD]);?>">
                        <strong><?=_echo($district['name']);?></strong>
                    </a>
                </td>
                <td class="nowrap">
                    <i class="fas fa-globe-americas"></i> <?=_echo($district['country_name']);?>
                </td>
                <td class="nowrap">
                    <?=_echo($district['province_name']);?>
                </td>
                <td class="align-center nowrap"><?=Currency::format($ward);?></td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>

    <?php else: ?>
        <div class="alert"><?=lang('system_district', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-country"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('system_district', 'txt_delete');?>', '<?=lang('system_district', 'txt_desc_delete');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>