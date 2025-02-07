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
        <span><?=lang('admin_panel', 'system_landing_page');?> (<b><?=$count;?></b>)</span>
    <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_LANDING, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
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
                <select class="js-custom-select" name="<?=InterFaceRequest::PRODUCT;?>" data-placeholder="<?=lang('placeholder', 'select_product');?>" enable-search="true" onchange="this.form.submit();">
                        <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_product');?></option>
                    <?php foreach($list_product as $product):
                        $html_option = '
                        <div class="user-infomation">
                            <span class="user-avatar avatar--small">
                                <img src="'.Product::get_image($product).'" />
                            </span>
                            <span class="user-display-name">#'.$product['id'].' - '.$product['name'].' - '.$product['country_name'].'</span>
                        </div>';
                    ?>
                        <option <?=($product['id'] == $product_id ? 'selected' : null);?> value="<?=$product['id'];?>" data-html="<?=_echo($html_option);?>"></option>
                    <?php endforeach;?>
                    </select>
            </div>

        </form>



    <?php if($landing_list): ?>

        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th></th>
                        <th><?=lang('label', 'domain');?></th>
                        <th><?=lang('label', 'postback');?></th>
                        <th><?=lang('label', 'product');?></th>
                        <th><?=lang('label', 'tracking_page_view');?></th>
                        <th width="100%"><?=lang('label', 'tracking_conversion');?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($landing_list as $landing):
            $product = Product::get($landing['product_id']);
            
        ?>
            <tr class="valign-center">
                <td class="id">#<?=$landing['id'];?></td>
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $landing['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_LANDING, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> <?=lang('button', 'edit');?></a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-landing" href="<?=RouteMap::build_query([InterFaceRequest::ID => $landing['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_LANDING, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> <?=lang('button', 'delete');?></a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>

                <td class="nowrap">
                    <strong><?=_echo($landing['domain']);?></strong>
                </td>
                <td class="nowrap">
                    <?=_echo($landing['postback']);?>
                </td>
                <td class="nowrap">
                <?php if($product): ?>
                    <div class="user-infomation">
                        <span class="user-avatar avatar--small">
                            <img src="<?=Product::get_image($product);?>" />
                        </span>
                        <span class="user-display-name">#<?=$product['id'];?> - <?=$product['name'];?> - <?=$product['country_name'];?></span>
                    </div>
                <?php endif; ?>
                </td>
                <td class="nowrap">
                    <div class="form-control" style="min-width: 300px">
                        <div class="input-group">
                            <input type="text" class="form-input" value="<?=_echo(RouteMap::build_query_api([postbackController::PARAM_ID => $landing['id'], postbackController::PARAM_KEY => $landing['key']], 'postback', ['name' => postbackController::BLOCK_LANDING, 'action' => postbackController::ACTION_VIEW]));?>">
                            <div class="input-group-append">
                                <button type="button" role="copy" class="btn btn-outline-gray btn-dim"><i class="far fa-copy"></i></button>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="nowrap">
                    <div class="form-control" style="min-width: 300px">
                        <div class="input-group">
                            <input type="text" class="form-input" value="<?=_echo(RouteMap::get('postback', ['name' => postbackController::BLOCK_PURCHASE, 'action' => $landing['key']]));?>">
                            <div class="input-group-append">
                                <button type="button" role="copy" class="btn btn-outline-gray btn-dim"><i class="far fa-copy"></i></button>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="nowrap">
                    <?=postbackController::PARAM_FIRST_NAME;?>, <?=postbackController::PARAM_LAST_NAME;?>, <?=postbackController::PARAM_PHONE;?>, <?=postbackController::PARAM_ADDRESS;?>
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
        <div class="alert"><?=lang('system_landing_page', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-landing"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('system_landing_page', 'txt_delete');?>', '<?=lang('system_landing_page', 'txt_desc_delete');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });

        $(document).on('click', '[role="copy"]', function() {
            const input = $(this).parents('.input-group').find('input');
            input.select();
            document.execCommand('copy');
            $.toastShow('<?=lang('system', 'txt_copy_success');?>', {
                type: 'success',
                timeout: 3000
            });
        });
        
    });
</script>