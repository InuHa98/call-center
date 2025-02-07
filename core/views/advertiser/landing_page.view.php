<?php View::render('layout.header', compact('title', 'current_route')); ?>



<div class="box">

    <div class="box__header"><i class="fas fa-link"></i><span class="padding-x-2"><?=lang('system', 'landing_page');?></span></div>
    <div class="box__body">

        <form id="form-filter" method="GET">

            <div class="filter-bar">

                <div class="form-control">
                    <select id="filter-product" class="js-custom-select" name="<?=orderController::FILTER_PRODUCT;?>" data-placeholder="<?=lang('placeholder', 'select_product');?>" enable-search="true">
                    <?php foreach($list_product as $product):
                        $html_option = '
                        <div class="user-infomation">
                            <span class="user-avatar avatar--small">
                                <img src="'.Product::get_image($product).'" />
                            </span>
                            <span class="user-display-name">'.$product['name'].' ('.Currency::format($product['price']).' '.$product['currency'].')</span>
                        </div>';
                    ?>
                        <option <?=($product['id'] == $filter_product ? 'selected' : null);?> value="<?=$product['id'];?>" data-html="<?=_echo($html_option);?>"></option>
                    <?php endforeach;?>
                    </select>
                </div>

                <div class="form-control">
                    <button type="submit" class="btn"><i class="fas fa-filter"></i> <?=lang('system', 'filter');?></button>
                    <button type="button" class="btn btn-outline-gray" id="clear-filter"><?=lang('system', 'clear_filter');?></button>
                </div>
            </div>

        </form>


    <?php if($list_landing): ?>
        <div class="margin-b-2">
            <?=lang('order', 'txt_count_landing_page', [
                'count' => $count
            ]);?>
        </div>
        <div class="table-scroll">
            <table class="table-statistics">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?=lang('label', 'domain');?></th>
                        <th><?=lang('label', 'product');?></th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($list_landing as $landing):
            $product = Product::get($landing['product_id']);
        ?>
            <tr class="valign-center">
                <td class="id">#<?=$landing['id'];?></td>
                <td class="nowrap">
                    <div class="form-control" style="min-width: 300px">
                        <div class="input-group">
                            <input type="text" class="form-input" value="<?=_echo(postbackController::build_postback($landing));?>">
                            <div class="input-group-append">
                                <button type="button" role="copy" class="btn btn-outline-gray btn-dim"><i class="far fa-copy"></i></button>
                            </div>
                        </div>
                    </div>
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
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>


    <?php else: ?>
        <div class="alert"><?=lang('order', 'empty_landing_page');?></div>
    <?php endif; ?>
    </div>

</div>


<script type="text/javascript">
	$(document).ready(function() {

        const form_filter = $('#form-filter');
        const clear_filter = $('#clear-filter');
        const filter_product = $('#filter-product');


        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_product.val(filter_product.find('option:first').val()).change();
            form_filter.submit();
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

<?php View::render('layout.footer'); ?>