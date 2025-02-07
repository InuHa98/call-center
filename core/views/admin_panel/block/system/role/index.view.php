<div class="box">
    <div class="box__header">
        <span><?=lang('admin_panel', 'system_role');?></span>
    </div>

    <div class="box__body">

    <?php if($role_list): ?>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th></th>
                        <th><?=lang('label', 'role_name');?></th>
                        <th><?=lang('label', 'level');?></th>
                        <th><?=lang('label', 'permission');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($role_list as $role):
            $role_perms = json_decode($role['perms'], true);
        ?>
            <tr>
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $role['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> <?=lang('system', 'txt_edit');?></a>
                            </li>
                        <?php endif; ?>
                        <?php if($role['is_default'] != Role::IS_DEFAULT && $is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-role" href="<?=RouteMap::build_query([InterFaceRequest::ID => $role['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> <?=lang('system', 'txt_delete');?></a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td>
                    <span class="user-role" style="background: <?=_echo($role['color']);?>"><?=_echo($role['name']);?></span>
                </td>
                <td>
                    <?=_echo($role['level']);?>
                </td>
                <td class="nowrap">
                    <?php if($role_perms): ?>
                    <?php foreach($role_perms as $perm): ?>
                        <span class="role-perm"><?=$perm;?></span>
                    <?php endforeach; ?>
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
        <div class="alert"><?=lang('system_role', 'empry_role');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-role"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('system_role', 'txt_delete_role');?>', '<?=lang('system_role', 'txt_desc_delete_role');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>