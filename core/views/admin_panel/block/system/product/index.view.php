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
        <span><?=lang('admin_panel', 'system_product');?> (<b><?=$count;?></b>)</span>
    <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PRODUCT, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
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
                <select class="js-custom-select" name="<?=InterFaceRequest::STATUS;?>" onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_ALL;?>"><?=lang('system', 'all_status');?></option>
                    <option <?=($status == Product::STATUS_ACTIVE ? 'selected' : null);?> value="<?=Product::STATUS_ACTIVE;?>"><?=lang('status', 'available');?></option>
                    <option <?=($status == Product::STATUS_INACTIVE ? 'selected' : null);?> value="<?=Product::STATUS_INACTIVE;?>"><?=lang('status', 'unavailable');?></option>
                </select>      
            </div>

            <div class="form-control">   
                <select class="js-custom-select" name="<?=InterFaceRequest::COUNTRY;?>" onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_ALL;?>"><?=lang('system', 'all_country');?></option>
                <?php foreach($list_country as $country): ?>
                    <option <?=($country_id == $country['id'] ? 'selected' : null);?> value="<?=$country['id'];?>"><?=_echo($country['name']);?></option>
                <?php endforeach; ?>
                </select> 
            </div>

        </form>



    <?php if($product_list): ?>

        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th></th>
                        <th></th>
                        <th><?=lang('label', 'name_product');?></th>
                        <th><?=lang('label', 'status');?></th>
                        <th width="100%"><?=lang('label', 'country');?></th>
                        <th class="align-center"><?=lang('label', 'stock');?></th>
                        <th class="align-center"><?=lang('label', 'price_sell');?></th>
                        <th class="align-center"><?=lang('label', 'ads_cost');?></th>
                        <th class="align-center"><?=lang('label', 'ship_cost');?></th>
                        <th class="align-center"><?=lang('label', 'import_cost');?></th>
                        <th class="align-center"><?=lang('label', 'currency');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($product_list as $product): ?>
            <tr class="valign-center">
                <td class="id">#<?=$product['id'];?></td>
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $product['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PRODUCT, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> <?=lang('button', 'edit');?></a>
                            </li>
                            <li role="change-status" data-id="<?=$product['id'];?>">
                                <i class="fas fa-exchange"></i> <?=lang('button', 'change_status');?>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-country" href="<?=RouteMap::build_query([InterFaceRequest::ID => $product['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_PRODUCT, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> <?=lang('button', 'delete');?></a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td>
                    <img class="preview-product" src="<?=Product::get_image($product['image']);?>" />
                </td>
                <td class="nowrap">
                    <strong><?=_echo($product['name']);?></strong>
                </td>
                <td>
                <?php if($product['status'] == Product::STATUS_ACTIVE): ?>
                    <span class="dot dot-success"><?=lang('status', 'available');?></span>
                <?php else: ?>
                    <span class="dot dot-gray"><?=lang('status', 'unavailable');?></span>
                <?php endif; ?>
                </td>
                <td class="nowrap">
                    <i class="fas fa-globe-americas"></i> <?=_echo($product['country_name']);?>
                </td>
                <td class="align-center nowrap"><span class="number text-bold"><?=Currency::format($product['stock']);?></span></td>
                <td class="align-center nowrap"><span class="badge rounded-pill bg-success"><?=Currency::format($product['price']);?></span></td>
                <td class="align-center nowrap"><span class="badge rounded-pill bg-primary"><?=Currency::format($product['ads_cost']);?></span></td>
                <td class="align-center nowrap"><span class="badge rounded-pill bg-info"><?=Currency::format($product['delivery_cost']);?></span></td>
                <td class="align-center nowrap"><span class="badge rounded-pill bg-danger"><?=Currency::format($product['import_cost']);?></span></td>
                <td class="align-center nowrap"><span class="badge rounded-pill bg-dark"><?=_echo($product['currency']);?></span></td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>

    <?php else: ?>
        <div class="alert"><?=lang('system_product', 'empty');?></div>
    <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-country"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('<?=lang('system_product', 'txt_delete');?>', '<?=lang('system_product', 'txt_desc_delete');?>') == true) {
                window.location.href = $(this).attr('href');
            }
        });

        $('[role="change-status"]').on('click', function() {
            const id = $(this).attr('data-id');
            if(!id) {
                return;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_STATUS;?>">\
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="' + id + '">\
            </form>');
            form.hide();
            $('body').append(form);
            form.submit();
        });
    });
</script>