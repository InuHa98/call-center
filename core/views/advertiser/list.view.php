<?php View::render('layout.header', compact('title', 'current_route')); ?>


<?php
if($error)
{
    echo '<div class="alert alert--error">'.$error.'</div>';
}
else if($success)
{
    echo '<div class="alert alert--success">'.$success.'</div>';
}

$is_edit = isset($is_edit) ? $is_edit : false;


?>


<div class="box">

    <div class="box__header"><i class="fas fa-list"></i><span class="padding-x-2"><?=lang('system', 'list_conversion');?></span></div>
    <div class="box__body">

        <form id="form-filter" method="GET" class="filter-bar">
            <input type="hidden" name="<?=InterFaceRequest::START_DATE;?>" id="startDate" value="<?=_echo($startDate);?>" />
            <input type="hidden" name="<?=InterFaceRequest::END_DATE;?>" id="endDate" value="<?=_echo($endDate);?>" />

            <div class="form-control">
                <div class="input-group">
                    <input id="filter-keyword" type="text" class="form-input" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="<?=lang('placeholder', 'id_name_phone');?>" value="<?=_echo($filter_keyword);?>">
                    <div class="input-group-append">
                    <?php if($filter_keyword != ''): ?>
                        <button type="button" id="clear-keyword" class="btn btn-outline-default btn-dim"><i class="fas fa-times"></i></button>
                    <?php else: ?>
                        <button type="button" id="submit-keyword" class="btn btn-outline-default btn-dim"><i class="fas fa-search"></i></button>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-control">
                <select id="filter-product" class="js-custom-select" name="<?=orderController::FILTER_PRODUCT;?>" data-placeholder="<?=lang('placeholder', 'select_product');?>" enable-search="true">
                    <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_product');?></option>
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
                <select id="filter-time" class="js-custom-select" name="<?=orderController::FILTER_TIME;?>" data-placeholder="<?=lang('system', 'custom_time');?>">
                    <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_time');?></option>
                    <option <?=($filter_time == 'custom' ? 'selected' : null);?> value="custom"><?=lang('system', 'custom_time');?></option>
                </select>
            </div>

            <div class="form-control <?=($filter_time == InterFaceRequest::OPTION_ALL ? 'disabled' : null);?>" id="picker-time">
                <div class="form-icon">
                    <span class="form-control-feedback"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" id="date-picker" class="form-input">
                </div>
            </div>

            <div class="form-control">
                <button type="submit" class="btn"><i class="fas fa-filter"></i> <?=lang('system', 'filter');?></button>
                <button type="button" class="btn btn-outline-gray" id="clear-filter"><?=lang('system', 'clear_filter');?></button>
            </div>

        </form>


    <?php if($list_order): ?>
        <div class="margin-b-2">
            <?=lang('order', 'txt_count_conversion', [
                'count' => $count
            ]);?>
        </div>
        <div class="table-scroll">
    <table class="table-statistics table-sort">
        <thead>
            <tr>

                <th></th>
                <th class="sort-btn">ID</th>
                <th></th>
                <th class="sort-btn"><?=lang('label', 'date');?></th>
                <th><?=lang('label', 'status');?>/<?=lang('label', 'caller');?></th>
                <th></th>
                <th></th>
                <th><?=lang('label', 'product');?></th>
                <th class="align-center sort-btn"></th>
                <th><?=lang('label', 'order_name');?></th>
                <th><?=lang('label', 'order_phone');?></th>
                <th width="100%"><?=lang('label', 'note');?></th>
            </tr>
        </thead>
        <tbody>

    <?php foreach($list_order as $order):
        
        $user_call = [
            'id' => $order['call_id'],
            'username' => $order['call_username'],
            'avatar' => $order['call_avatar'],
            'is_ban' => $order['call_is_ban']
        ];


    ?>
            <tr>

                <td>
                <?php if($order['can_edit'] == Order::IS_CAN_EDIT && Auth::$data['is_ban_team'] == Team::IS_NOT_BAN): ?>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content" data-id="<?=$order['id'];?>">
                            <li>
                                <a href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_DELTAI, 'action' => $order['id']]);?>">
                                    <i class="fa fa-eye"></i> <?=lang('order', 'detail_order');?>
                                </a>
                            </li>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $order['id']], 'advertiser', ['action' => orderController::ACTION_EDIT]);?>">
                                    <i class="fa fa-edit"></i> <?=lang('order', 'edit_order');?>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
                </td>
                <td class="nowrap">
                    <span class="id">#<?=$order['id'];?></span>
                </td>
                <td class="nowrap">
                <?php if($order['duplicate']): ?>
                    <a class="badge rounded-pill bg-pink" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_DUPLICATE, 'action' => $order['id']]);?>"><?=lang('order', 'txt_duplicate');?>  (<?=$order['duplicate'];?>)</a>
                <?php endif; ?>
                </td>
                <td class="nowrap">
                    <span class="time"><?=_time($order['created_at']);?></span>
                </td>

                <td class="nowrap">
                    <?=orderController::render_status($order['status']);?>
                </td>
                <td>
                <?php if($user_call['id']): ?>
                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_call['id']]);?>">
                        <span class="user-avatar avatar--small">
                            <img src="<?=User::get_avatar($user_call);?>" />
                            <?=no_avatar($user_call);?>
                        </span>
                        <span class="user-display-name"><?=User::get_username($user_call);?></span>
                    </a>
                <?php endif; ?>
                </td>

                <td>
                    <img class="product-image" src="<?=Product::get_image(['image' => $order['product_image']]);?>" />
                </td>

                <td class="nowrap">
                    <span class="product-name"><?=_echo($order['product_name']);?></span>
                </td>

                <td class="align-center nowrap">
                <?php if($order['quantity']): ?>
                    <?=($order['quantity'] ? 'x<strong>'.$order['quantity'].'</strong>' : '<span class="time">x'.$order['quantity'].'</span>');?>
                <?php endif; ?>
                </td>


                <td class="nowrap">
                    <span class="badge btn-outline-primary btn-dim"><i class="fas fa-user"></i> <?=_echo($order['order_first_name']);?> <?=_echo($order['order_last_name']);?></span>
                </td>

                <td class="nowrap">
                <?php foreach(json_decode($order['order_phone'], true) as $phone): ?>
                    <span class="badge btn-outline-gray btn-dim"><i class="fas fa-phone"></i> <?=_echo($phone);?></span>
                <?php endforeach; ?>
                </td>

                <td class="nowrap">
                    <?=_echo($order['note_ads']);?>
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
        <div class="alert"><?=lang('order', 'empty_conversion');?></div>
    <?php endif; ?>
    </div>

</div>

<?=assetController::load_css('daterangepicker.css'); ?>
<?=assetController::load_js('moment.min.js');?>
<?=assetController::load_js('daterangepicker.js');?>
<?=assetController::load_js('form-validator.js');?>

<script type="text/javascript">
	$(document).ready(function() {
        const startDate = '<?=_echo($startDate);?>';
        const endDate = '<?=_echo($endDate);?>';
        const date_format = 'DD-MM-YYYY';

        const form_filter = $('#form-filter');
        const clear_filter = $('#clear-filter');
        const clear_keyword = $('#clear-keyword');
        const submit_keyword = $('#submit-keyword');
        const filter_keyword = $('#filter-keyword');
        const filter_product = $('#filter-product');
        const filter_time = $('#filter-time');
        const date_picker = $('#date-picker');



        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_keyword.val('');
            filter_product.val(filter_product.find('option:first').val()).change();
            filter_time.val(filter_time.find('option:first').val()).change();
            date_picker.data('daterangepicker').setStartDate(startDate);
            date_picker.data('daterangepicker').setEndDate(endDate);
            form_filter.submit();
        });

        clear_keyword.on('click', function() {
            filter_keyword.val('');
            form_filter.submit();
        });

        submit_keyword.on('click', function() {
            form_filter.submit();
        });
        
        filter_time.on('change', function() {
            if($(this).val() == '<?=InterFaceRequest::OPTION_ALL;?>') {
                $('#picker-time').addClass('disabled');
            } else {
                $('#picker-time').removeClass('disabled');
            }
        });

        date_picker.daterangepicker({
            startDate: startDate,
            endDate: endDate,
            ranges: {
                '<?=lang('system', 'daterange_today');?>': [
                    moment(),
                    moment()
                ],
                '<?=lang('system', 'daterange_yesterday');?>': [
                    moment().subtract(1, 'days'),
                    moment().subtract(1, 'days')
                ],
                '<?=lang('system', 'daterange_last_7_day');?>': [
                    moment().subtract(6, 'days'),
                    moment()
                ],
                '<?=lang('system', 'daterange_last_30_day');?>': [
                    moment().subtract(29, 'days'),
                    moment()
                ],
                '<?=lang('system', 'daterange_this_month');?>': [
                    moment().startOf('month'),
                    moment().endOf('month')
                ],
                '<?=lang('system', 'daterange_last_month');?>': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month')
                ]
            },
            locale: {
                "format": date_format,
                "separator": " / ",
                "applyLabel": "<?=lang('system', 'daterange_apply');?>",
                "cancelLabel": "<?=lang('system', 'daterange_cancel');?>",
                "fromLabel": "<?=lang('system', 'daterange_from');?>",
                "toLabel": "<?=lang('system', 'daterange_to');?>",
                "customRangeLabel": "<?=lang('system', 'daterange_custom_date');?>",
                "weekLabel": "<?=lang('system', 'week');?>",
                "daysOfWeek": [
                    "<?=lang('system', 'daterange_sunday');?>",
                    "<?=lang('system', 'daterange_monday');?>",
                    "<?=lang('system', 'daterange_tuesday');?>",
                    "<?=lang('system', 'daterange_wednesday');?>",
                    "<?=lang('system', 'daterange_thursday');?>",
                    "<?=lang('system', 'daterange_friday');?>",
                    "<?=lang('system', 'daterange_saturday');?>"
                ],
                "monthNames": [
                    "<?=lang('system', 'month');?> 1",
                    "<?=lang('system', 'month');?> 2",
                    "<?=lang('system', 'month');?> 3",
                    "<?=lang('system', 'month');?> 4",
                    "<?=lang('system', 'month');?> 5",
                    "<?=lang('system', 'month');?> 6",
                    "<?=lang('system', 'month');?> 7",
                    "<?=lang('system', 'month');?> 8",
                    "<?=lang('system', 'month');?> 9",
                    "<?=lang('system', 'month');?> 10",
                    "<?=lang('system', 'month');?> 11",
                    "<?=lang('system', 'month');?> 12"
                ],
                "firstDay": 1
            },
            showDropdowns: true,
            alwaysShowCalendars: true,
            linkedCalendars: false,
            autoApply: true,
            autoUpdateInput: true
        }, function(start, end) {
            $('#startDate').val(start.format('DD-MM-YYYY'));
            $('#endDate').val(end.format('DD-MM-YYYY'));
        });
	});
</script>

<?php View::render('layout.footer'); ?>