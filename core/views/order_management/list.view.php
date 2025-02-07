<?php View::render('layout.header', compact('title')); ?>



<div class="box">

    <div class="box__header"><i class="fas fa-tasks"></i><span class="padding-x-2"><?=lang('system', 'order_management');?></span></div>
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
                    <select id="filter-team" class="js-custom-select" name="<?=orderController::FILTER_TEAM;?>" data-placeholder="<?=lang('placeholder', 'select_team');?>" enable-search="true">
                        <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_team');?></option>
                    <?php foreach($list_team as $team):
                        $data_team = Team::get_data($team['type']);
                        $html_option = '
                        <div class="user-infomation">
                            <span class="user-avatar avatar--small">
                                <span style="background: '.$data_team['color'].'">'.$data_team['icon'].'</span>
                            </span>
                            <span class="user-display-name">'.$team['name'].'</span>
                        </div>';
                    ?>
                        <option <?=($team['id'] == $filter_team ? 'selected' : null);?> value="<?=$team['id'];?>" data-html="<?=_echo($html_option);?>"></option>
                    <?php endforeach;?>
                    </select>
                </div>
                        
                <div class="form-control">
                    <select id="filter-status" class="js-custom-select" name="<?=orderController::FILTER_STATUS;?>" data-placeholder="<?=lang('placeholder', 'select_status');?>">
                        <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_status');?></option>
                    <?php foreach($list_status as $status): ?>
                        <option <?=($status == $filter_status  ? 'selected' : null);?> value="<?=$status;?>" data-html="<?=_echo(orderController::render_status($status));?>"></option>
                    <?php endforeach; ?>
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
            <span>
            <?=($order_duplicate ? lang('order', 'txt_count_order_duplicate', [
                'count' => $count,
                'phone' => _echo($order_duplicate['order_phone'])
            ]) : lang('order', 'txt_count_order', [
                'count' => $count
            ]));?>
            </span>
        </div>
        <div class="table-scroll">
            <table class="table-statistics table-sort">
                <thead>
                    <tr>
                        <th></th>
                        <th class="sort-btn">ID</th>
                        <th></th>
                        <th class="sort-btn"><?=lang('label', 'date');?></th>
                        <th></th>
                        <th><?=lang('label', 'product');?></th>
                        <th class="align-center sort-btn"></th>
                        <th class="align-center sort-btn"><?=lang('label', 'price');?></th>
                        <th class="align-center"></th>
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
            ?>
                    <tr>
                        <td>
                            <div class="drop-menu">
                                <div class="drop-menu__button">
                                    <i class="fa fa-ellipsis-v"></i>
                                </div>
                                <ul class="drop-menu__content" data-id="<?=$order['id'];?>">
                                    <li>
                                        <a href="<?=RouteMap::get('order_management', ['block' => orderManagementController::BLOCK_DELTAI, 'action' => $order['id']]);?>">
                                            <i class="fas fa-eye"></i> <?=lang('system', 'detail');?>
                                        </a>
                                    </li>
                                <?php if(!in_array($order['status'], [Order::STATUS_PENDING_CONFIRM])): ?>
                                    <li>
                                        <a href="<?=RouteMap::get('order_management', ['block' => orderManagementController::BLOCK_EDIT, 'action' => $order['id']]);?>">
                                            <i class="fas fa-edit"></i> <?=lang('button', 'edit');?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if($order['status'] == Order::STATUS_DELIVERING): ?>
                                    <li role="mark-delivered">
                                        <i class="fas fa-box-check"></i> <?=lang('button', 'mark_delivered');?>
                                    </li>
                                    <li role="mark-unreceived">
                                        <i class="fas fa-times"></i> <?=lang('button', 'mark_unreceived');?>
                                    </li>
                                <?php endif; ?>
                                <?php if($order['status'] == Order::STATUS_DELIVERED): ?>
                                    <li role="mark-returned">
                                        <i class="fas fa-undo-alt"></i> <?=lang('button', 'mark_returned');?>
                                    </li>
                                <?php endif; ?>
                                <?php if($order['status'] == Order::STATUS_TRASH): ?>
                                    <li role="delete-order" class="border-top text-danger">
                                        <i class="fa fa-trash"></i> <?=lang('button', 'delete');?>
                                    </li>
                                <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                        <td class="nowrap">
                            <span class="id">#<?=$order['id'];?></span>
                        </td>
                        <td class="nowrap">
                        <?php if(!$order_duplicate && $order['duplicate']): ?>
                            <a class="badge rounded-pill bg-pink" href="<?=RouteMap::get('order_management', ['block' => orderManagementController::BLOCK_DUPLICATE, 'action' => $order['id']]);?>"><?=lang('order', 'txt_duplicate');?>  (<?=$order['duplicate'];?>)</a>
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
                        <td class="align-left nowrap">
                            <span class="time"><?=Currency::format(round($order['price'] * round(1 / $order['currency_exchange_rate'], 2)));?> <?=Currency::DEFAULT_CURRENCY;?></span>
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
                            <?php if($order['note_ads']): ?>
                                <p><strong><?=lang('label', 'advertiser');?>:</strong> <?=_echo($order['note_ads']);?></p>
                            <?php endif; ?>
                            <?php if($order['note_call']): ?>
                                <p><strong><?=lang('label', 'caller');?>:</strong> <?=_echo($order['note_call']);?></p>
                            <?php endif; ?>
                            <?php if($order['note_ship']): ?>
                                <p><strong><?=lang('label', 'shipper');?>:</strong> <?=_echo($order['note_ship']);?></p>
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
                        <td class="nowrap"><?=_echo($order['order_province']);?></td>
                        <td class="nowrap"><?=_echo($order['order_district']);?></td>
                        <td class="nowrap"><?=_echo($order['order_ward']);?></td>
                        <td class="nowrap"><?=_echo($order['order_address']);?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button role="export-excel" class="btn btn--round btn-outline-gray margin-t-2"><i class="fas fa-file-excel"></i> <?=lang('button', 'export_excel');?></button>

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
        const filter_team = $('#filter-team');
        const filter_status = $('#filter-status');
        const filter_product = $('#filter-product');
        const filter_time = $('#filter-time');
        const date_picker = $('#date-picker');


        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_id.val('');
            filter_keyword.val('');
            filter_product.val(filter_product.find('option:first').val()).change();
            filter_team.val(filter_team.find('option:first').val()).change();
            filter_status.val(filter_status.find('option:first').val()).change();
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
				title: '<?=lang('button', 'mark_returned');?>',
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

        role_click('mark-returned', function(self) {

            const id = self.parent().data('id');

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_MARK_RETURNED;?>">\
                <input type="hidden" name="<?=orderController::INPUT_ID;?>" value="' + id + '">\
                <div class="dialog-label"><?=lang('label', 'note');?>:</div>\
                <div class="form-group">\
                    <div class="form-control">\
                        <textarea class="form-textarea" name="<?=orderController::INPUT_SHIP_NOTE;?>" placeholder="<?=lang('placeholder', 'can_be_left_blank');?>"></textarea>\
                    </div>\
                </div>\
            </form>');


            $.dialogShow({
                title: '<?=lang('button', 'mark_returned');?> #' + id,
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

        role_click('delete-order', function(self) {
            const form = $('\
            <form method="POST">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_DELETE_ORDER;?>" />\
                <input type="hidden" name="<?=orderController::INPUT_ID;?>" value="' + self.parent().data('id') + '" />\
                <div class="dialog-message"><?=lang('system', 'txt_can_undo');?></div>\
            </form>');
            $.dialogShow({
				title: 'Xoá đơn hàng',
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

        role_click('export-excel', function(self) {
            const form = $('\
            <form method="POST">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=orderController::INPUT_ACTION;?>" value="<?=orderController::ACTION_EXPORT_EXCEL;?>" />\
            </form>');
            $('body').append(form);
			form.submit();
        });

	});
</script>

<?php View::render('layout.footer'); ?>