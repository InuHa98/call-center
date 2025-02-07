<?php View::render('layout.header', compact('title', 'current_route')); ?>


<?php

$is_edit = isset($is_edit) ? $is_edit : false;


?>


<div class="box">

    <div class="box__header"><i class="fas fa-shopping-cart text-info"></i><span class="padding-x-2"><?=lang('system', 'new_order');?></span></div>
    <div class="box__body">

        <form id="form-filter" method="GET" class="filter-bar">
            <input type="hidden" name="<?=InterFaceRequest::START_DATE;?>" id="startDate" value="<?=_echo($startDate);?>" />
            <input type="hidden" name="<?=InterFaceRequest::END_DATE;?>" id="endDate" value="<?=_echo($endDate);?>" />

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
                <select id="filter-status" class="js-custom-select" name="<?=orderController::FILTER_STATUS;?>[]" data-placeholder="<?=lang('placeholder', 'select_status');?>" multiple>
                    <option <?=(in_array(InterFaceRequest::OPTION_ALL, $filter_status) ? 'selected' : null);?> value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_status');?></option>
                <?php foreach($list_status as $status): ?>
                    <option <?=(in_array($status, $filter_status)  ? 'selected' : null);?> value="<?=$status;?>" data-html="<?=_echo(orderController::render_status($status));?>"></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="form-control">
                <select id="filter-area" class="js-custom-select" name="<?=orderController::FILTER_AREA;?>">
                    <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_area');?></option>
                <?php foreach($list_area as $area): ?>
                    <option <?=($area['id'] == $filter_area ? 'selected' : null);?> value="<?=$area['id'];?>" data-html="<?=_echo('<i class="fas fa-map-marker-alt"></i> '.$area['name']);?>"></option>
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

    <?php if($error): ?>
        <div class="alert alert--error"><?=$error;?></div>
    <?php elseif($success): ?>
        <div class="alert alert--success"><?=$success;?></div>
    <?php endif; ?>


    <?php if($list_order): ?>
        <div class="margin-b-2">
            <span>
            <?=lang('order', 'txt_count_order', [
                'count' => $count
            ]);?>
            </span>
        </div>
        <div class="table-scroll">
            <table class="table-statistics table-sort">
                <thead>
                    <tr>
                        <th>
                            <span class="form-check">
                                <input type="checkbox" id="multiple_selected_all">
                                <label for="multiple_selected_all"></label>
                            </span>
                        </th>
                        <th></th>
                        <th class="sort-btn">ID</th>
                        <th></th>
                        <th class="sort-btn"><?=lang('label', 'holding');?></th>
                        <th class="sort-btn"><?=lang('label', 'date');?></th>
                        <th></th>
                        <th><?=lang('label', 'product');?></th>
                        <th class="align-center sort-btn"></th>
                        <th class="align-center sort-btn"><?=lang('label', 'price');?></th>
                        <th><?=lang('label', 'status');?>/<?=lang('label', 'caller');?>/<?=lang('label', 'shipper');?></th>
                        <th></th>
                        <th width="100%"><?=lang('label', 'note');?></th>
                        <th><?=lang('label', 'order_name');?></th>
                        <th><?=lang('label', 'order_phone');?></th>
                        <th><?=lang('label', 'area');?></th>
                        <th><?=lang('label', 'province');?></th>
                        <th><?=lang('label', 'district');?></th>
                        <th><?=lang('label', 'ward');?></th>
                        <th><?=lang('label', 'address');?></th>
                    </tr>
                </thead>
                <tbody>

            <?php
            $total_amount = 0;
            foreach($list_order as $order):
                $user_call = [
                    'id' => $order['call_id'],
                    'username' => $order['call_username'],
                    'avatar' => $order['call_avatar'],
                    'is_ban' => $order['call_is_ban']
                ];
                $amount = 0;
                if($order['payment_ads'] == Order::IS_NOT_PAYMENT && $order['ads_user_id'] == Auth::$data['id']) {
                    $amount += $order['profit_member_ads'];
                }
                if($order['payment_call'] == Order::IS_NOT_PAYMENT && $order['call_user_id'] == Auth::$data['id']) {
                    $amount += $order['profit_member_call'] * $order['quantity'];
                }
                if($order['payment_ship'] == Order::IS_NOT_PAYMENT && $order['ship_user_id'] == Auth::$data['id']) {
                    $amount += $order['profit_member_ship'];
                }
                $total_amount += $amount;
            ?>
                    <tr>
                        <td>
                            <span class="form-check">
                                <input type="checkbox" role="multiple_selected" name="<?=orderController::INPUT_ID;?>[]" value="<?=$order['id'];?>" id="label_<?=$order['id'];?>">
                                <label for="label_<?=$order['id'];?>"></label>
                            </span>
                        </td>
                        <td>
                            <button role="btn-get-order" class="btn btn--round btn--small btn--primary" data-id="<?=$order['id'];?>">
                                <i class="fas fa-truck-loading"></i> <?=lang('order', 'txt_receive');?>
                            </button>
                        </td>

                        <td class="nowrap">
                            <span class="id">#<?=$order['id'];?></span>
                        </td>
                        <td class="nowrap">
                        <?php if($order['duplicate']): ?>
                            <a class="badge rounded-pill bg-pink" href="<?=RouteMap::get('order', ['block' => orderController::BLOCK_DUPLICATE, 'action' => $order['id']]);?>"><?=lang('order', 'txt_duplicate');?>  (<?=$order['duplicate'];?>)</a>
                        <?php endif; ?>
                        </td>
                        <td>
                            <?php if($amount): ?>
                                <span class="badge rounded-pill bg-warning">+<?=Currency::format($amount);?></span>
                            <?php endif; ?>
                        </td>
                        <td class="nowrap">
                            <span class="time"><?=_time($order['created_at']);?></span>
                        </td>

                        <td>
                            <img class="product-image" src="<?=Product::get_image(['image' => $order['product_image']]);?>" />
                        </td>

                        <td class="nowrap">
                            <span class="product-name"><?=_echo($order['product_name']);?></span>
                        </td>
                        <td class="align-center nowrap">
                            <?=($order['quantity'] ? 'x<strong>'.$order['quantity'].'</strong>' : '<span class="time">x'.$order['quantity'].'</span>');?>
                        </td>
                        <td class="align-center nowrap">
                            <strong><?=Currency::format($order['price']);?></strong> <?=$order['currency'];?>
                        </td>
                        <td class="nowrap">
                            <?=orderController::render_status($order['status']);?>
                        </td>

                        <td class="nowrap">
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
                        
                        <td class="nowrap">
                            <?=($order['ads_user_id'] == $order['call_user_id'] ? _echo($order['note_ads']) : _echo($order['note_call']));?>
                        </td>



                        <td class="nowrap">
                            <span class="badge btn-outline-info btn-dim"><i class="fas fa-user"></i> <?=_echo($order['order_first_name']);?> <?=_echo($order['order_last_name']);?></span>
                        </td>

                        <td class="nowrap">
                        <?php foreach(json_decode($order['order_phone'], true) as $phone): ?>
                            <span class="badge btn-outline-gray btn-dim"><i class="fas fa-phone"></i> <?=_echo($phone);?></span>
                        <?php endforeach; ?>
                        </td>
                        <td class="nowrap">
                            <?php if($order['order_area']): ?>
                                <i class="fas fa-map-marker-alt"></i> <span><?=_echo($order['order_area']);?></span>
                            <?php endif; ?>
                        </td>
                        <td class="nowrap"><?=_echo($order['order_province']);?></td>
                        <td class="nowrap"><?=_echo($order['order_district']);?></td>
                        <td class="nowrap"><?=_echo($order['order_ward']);?></td>
                        <td class="nowrap"><?=_echo($order['order_address']);?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            <?php if($total_amount): ?>
                <tfoot>
                    <tr>
                        <th colspan="4" class="align-left"><?=lang('system', 'txt_total');?>:</th>
                        <th colspan="16" class="align-left"><span class="badge rounded-pill bg-warning">+<?=Currency::format($total_amount);?></span></th>
                    </tr>
                </tfoot>
            <?php endif; ?>
            </table>
        </div>

        <div class="margin-y-2">
            <span class="drop-menu">
                <span class="btn btn--small btn--round">
                    <?=lang('system', 'txt_option');?> <i class="fas fa-ellipsis-h"></i>
                </span>
                <ul class="drop-menu__content">
                    <li role="get-all-order">
                        <i class="fas fa-check-double"></i> <?=lang('order', 'txt_receive_all');?>
                    </li>
                    <li role="get-selected-order" class="disabled">
                        <i class="fas fa-truck-loading"></i> <?=lang('order', 'txt_receive_select');?> <span role="multiple_selected_count">(0)</span>
                    </li>
                </ul>
            </span>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>


    <?php else: ?>
        <div class="alert"><?=lang('order', 'empty_order');?></div>
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
        const filter_product = $('#filter-product');
        const filter_status = $('#filter-status');
        const filter_area = $('#filter-area');
        const filter_time = $('#filter-time');
        const date_picker = $('#date-picker');


        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_product.val(filter_product.find('option:first').val()).change();
            filter_status.val(filter_status.find('option:first').val()).change();
            filter_area.val(filter_area.find('option:first').val()).change();
            filter_time.val(filter_time.find('option:first').val()).change();
            date_picker.data('daterangepicker').setStartDate(startDate);
            date_picker.data('daterangepicker').setEndDate(endDate);
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

        $(document).on('click', '[role="btn-get-order"]', function() {


            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_GET_ORDER;?>">\
                <input type="hidden" name="<?=orderController::INPUT_ID;?>" value="' + $(this).data('id') + '">\
            </form>');
            form.hide();
            $('body').append(form);
            form.submit();
		});


        const role_multiple_selected = '[role="multiple_selected"]';

        multiple_selected({
			role_select_all: "#multiple_selected_all",
			role_select: role_multiple_selected,
			onSelected: function(total_selected, config){
				$('[role="multiple_selected_count"]').html('('+total_selected+')');
				$('[role="multiple_selected_count"]').parents('li').removeClass("disabled");
			},
			onNoSelected: function(total_selected, config){
				$('[role="multiple_selected_count"]').html('(0)');
				$('[role="multiple_selected_count"]').parents('li').removeClass("disabled").addClass("disabled");
			}
		});

        function request_action(type) {
            const form = $('\
            <form method="POST">\
                <?=Security::insertHiddenToken();?>\
                <input role="action" type="hidden" name="<?=orderController::INPUT_ACTION;?>" />\
            </form>');
            form.find('[role="action"]').val(type);
            $(role_multiple_selected+":checked").each(function(){
                form.append('<input type="hidden" name="<?=orderController::INPUT_ID;?>[]" value="'+$(this).val()+'">');
            });
            $('body').append(form);
            form.submit();
        }

        role_click('get-all-order', async function(self) {
			if(await comfirm_dialog('<?=lang('order', 'txt_receive_select');?>', '<?=lang('order', 'desc_receive_select');?>') !== true)
			{
				return;
			}
            const form = $('\
            <form method="POST">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_GET_ORDER;?>" />\
            </form>');
            $('body').append(form);
			form.submit();
        });
	    
        role_click('get-selected-order', function(self) {
			request_action('<?=orderController::ACTION_GET_ORDER;?>');
        });

	});
</script>

<?php View::render('layout.footer'); ?>