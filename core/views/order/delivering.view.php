<?php View::render('layout.header', compact('title', 'current_route')); ?>



<div class="box">

    <div class="box__header"><i class="fas fa-shipping-fast text-primary"></i><span class="padding-x-2"><?=lang('system', 'order_delivering');?></span></div>
    <div class="box__body">

        <form id="form-filter" method="GET">
            <input type="hidden" name="<?=InterFaceRequest::START_DATE;?>" id="startDate" value="<?=_echo($startDate);?>" />
            <input type="hidden" name="<?=InterFaceRequest::END_DATE;?>" id="endDate" value="<?=_echo($endDate);?>" />

            <div class="filter-waybill <?=($filter_id != '' ? 'show' : null);?>">
                <div class="filter-waybill__title">
                    <i class="fas fa-sort-down"></i>
                    <span><?=lang('order', 'filter_list_id');?> <?=($count_id > 0 ? '('.$count_id.')' : null);?></span>
                </div>
                <textarea id="filter-id" class="form-textarea" name="<?=orderController::FILTER_ID;?>" placeholder="<?=lang('placeholder', 'filter_list_id');?>"><?=_echo($filter_id);?></textarea>
            </div>

            <div class="filter-bar">

                <div class="form-control">
                    <div class="input-group">
                        <input id="filter-keyword" type="text" class="form-input" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="<?=lang('placeholder', 'name_phone');?>" value="<?=_echo($filter_keyword);?>">
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
            </div>

        </form>

    <?php if($error): ?>
        <div class="alert alert--error"><?=$error;?></div>
    <?php elseif($success): ?>
        <div class="alert alert--success"><?=$success;?></div>
    <?php endif; ?>


    <?php if($list_order): ?>
        <div class="margin-b-2">
            <span>            <?=lang('order', 'txt_count_order', [
                'count' => $count
            ]);?></span>
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
                        <th></th>
                        <th width="100%"><?=lang('label', 'note');?></th>
                        <th><?=lang('label', 'order_name');?></th>
                        <th><?=lang('label', 'order_phone');?></th>
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
                $user_ship = [
                    'id' => $order['ship_id'],
                    'username' => $order['ship_username'],
                    'avatar' => $order['ship_avatar'],
                    'is_ban' => $order['ship_is_ban']
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
                        <?php if($order['ship_user_id'] == Auth::$data['id']): ?>
                            <span class="form-check">
                                <input type="checkbox" role="multiple_selected" name="<?=orderController::INPUT_ID;?>[]" value="<?=$order['id'];?>" id="label_<?=$order['id'];?>">
                                <label for="label_<?=$order['id'];?>"></label>
                            </span>
                        <?php endif; ?>
                        </td>
                        <td>
                        <?php if($order['ship_user_id'] == Auth::$data['id'] && Auth::$data['is_ban_team'] == Team::IS_NOT_BAN): ?>
                            <div class="drop-menu">
                                <div class="drop-menu__button">
                                    <i class="fa fa-ellipsis-v"></i>
                                </div>
                                <ul class="drop-menu__content" data-id="<?=$order['id'];?>">
                                    <li role="mark-delivered" class="text-success">
                                        <i class="fas fa-box-check"></i> <?=lang('button', 'mark_delivered');?>
                                    </li>
                                    <li role="mark-unreceived" class="text-danger">
                                        <i class="fas fa-times"></i> <?=lang('button', 'mark_unreceived');?>
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
                        <?php if($user_ship['id']): ?>
                            <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_ship['id']]);?>">
                                <span class="user-avatar avatar--small">
                                    <img src="<?=User::get_avatar($user_ship);?>" />
                                    <?=no_avatar($user_ship);?>
                                </span>
                                <span class="user-display-name"><?=User::get_username($user_ship);?></span>
                            </a>
                        <?php endif; ?>
                        </td>
                        
                        <td class="nowrap">
                            <?=($order['ads_user_id'] == $order['call_user_id'] ? _echo($order['note_ads']) : _echo($order['note_call']));?>
                        </td>


                        <td class="nowrap">
                            <span class="badge btn-outline-primary btn-dim"><i class="fas fa-user"></i> <?=_echo($order['order_first_name']);?> <?=_echo($order['order_last_name']);?></span>
                        </td>

                        <td class="nowrap">
                        <?php foreach(json_decode($order['order_phone'], true) as $phone): ?>
                            <span class="badge btn-outline-gray btn-dim"><i class="fas fa-phone"></i> <?=_echo($phone);?></span>
                        <?php endforeach; ?>
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

    <?php if(UserPermission::is_shipper() && Auth::$data['is_ban_team'] == Team::IS_NOT_BAN): ?>
        <div class="margin-y-2">
            <span class="drop-menu disabled" role="action-order">
                <span class="btn btn--small btn--round">
                    <?=lang('system', 'txt_option');?> <span role="multiple_selected_count">(0)</span> <i class="fas fa-ellipsis-h"></i>
                </span>
                <ul class="drop-menu__content">
                    <li role="mark-delivered-selected" class="text-success">
                        <i class="fas fa-box-check"></i> <?=lang('button', 'mark_delivered');?> <span role="multiple_selected_count">(0)</span>
                    </li>
                    <li role="mark-unreceived-selected" class="text-danger">
                        <i class="fas fa-times"></i> <?=lang('button', 'mark_unreceived');?> <span role="multiple_selected_count">(0)</span>
                    </li>
                </ul>
            </span>
        </div>
    <?php endif; ?>

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
        const clear_keyword = $('#clear-keyword');
        const submit_keyword = $('#submit-keyword');
        const filter_id = $('#filter-id');
        const filter_keyword = $('#filter-keyword');
        const filter_product = $('#filter-product');
        const filter_time = $('#filter-time');
        const date_picker = $('#date-picker');


        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_id.val('');
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

        $('.filter-waybill').on('click', '.filter-waybill__title', function() {
            $(this).parent().toggleClass('show');
        });



        const role_multiple_selected = '[role="multiple_selected"]';

        multiple_selected({
			role_select_all: "#multiple_selected_all",
			role_select: role_multiple_selected,
			onSelected: function(total_selected, config){
                $('[role="action-order"]').removeClass('disabled');
				$('[role="multiple_selected_count"]').html('('+total_selected+')');
				$('[role="multiple_selected_count"]').parents('li').removeClass("disabled");
			},
			onNoSelected: function(total_selected, config){
                $('[role="action-order"]').addClass('disabled');
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
	    

    <?php if(UserPermission::is_shipper()): ?>
        role_click('mark-delivered', async function(self) {
			if(await comfirm_dialog('<?=lang('button', 'mark_delivered');?>', '<?=lang('system', 'txt_can_undo');?>') !== true)
			{
				return;
			}

            const form = $('\
            <form method="POST">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_MARK_DELIVERED;?>" />\
                <input type="hidden" name="<?=orderController::INPUT_ID;?>" value="' + self.parent().data('id') + '" />\
            </form>');
            $('body').append(form);
			form.submit();
        });

        role_click('mark-unreceived', function(self) {
            const form = $('\
            <form method="POST">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_MARK_UNRECEIVED;?>" />\
                <input type="hidden" name="<?=orderController::INPUT_ID;?>" value="' + self.parent().data('id') + '" />\
                <div class="dialog-label"><?=lang('label', 'reason');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <textarea class="form-textarea" name="<?=orderController::INPUT_SHIP_NOTE;?>" placeholder="<?=lang('placeholder', 'can_be_left_blank');?>"></textarea>\
                    </div>\
                </div>\
            </form>');
            $.dialogShow({
				title: '<?=lang('button', 'mark_unreceived');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'continue');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
        });

        role_click('mark-delivered-selected', async function(self) {
			if(await comfirm_dialog('<?=lang('button', 'mark_delivered');?>', '<?=lang('system', 'txt_can_undo');?>') !== true)
			{
				return;
			}
			request_action('<?=orderController::ACTION_MARK_DELIVERED;?>');
        });

        role_click('mark-unreceived-selected', function(self) {
            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_MARK_UNRECEIVED;?>">\
                <div class="dialog-label"><?=lang('label', 'reason');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <textarea class="form-textarea" name="<?=orderController::INPUT_SHIP_NOTE;?>" placeholder="<?=lang('placeholder', 'can_be_left_blank');?>"></textarea>\
                    </div>\
                </div>\
            </form>');
            $(role_multiple_selected+":checked").each(function(){
                form.append('<input type="hidden" name="<?=orderController::INPUT_ID;?>[]" value="'+$(this).val()+'">');
            });

			$.dialogShow({
				title: '<?=lang('button', 'mark_unreceived');?>',
				content: form,
				button: {
					confirm: '<?=lang('button', 'continue');?>',
					cancel: '<?=lang('button', 'cancel');?>'
				},
				bgHide: false,
                isCenter: true,
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
        });
    <?php endif; ?>


	});
</script>

<?php View::render('layout.footer'); ?>