
    <form id="form-filter" class="filter-bar">
        <input type="hidden" name="<?=InterFaceRequest::START_DATE;?>" id="startDate" value="<?=_echo($startDate);?>" />
        <input type="hidden" name="<?=InterFaceRequest::END_DATE;?>" id="endDate" value="<?=_echo($endDate);?>" />

    <?php if($team['type'] == Team::TYPE_FULLSTACK): ?>
        <div class="form-control">
            <select id="filter-type" class="js-custom-select" name="<?=orderController::FILTER_TYPE;?>">
            <?php if($is_caller): ?>
                <option <?=($filter_type == orderController::TYPE_CALL ? 'selected' : null);?> value="<?=orderController::TYPE_CALL;?>"><?=Role::DEFAULT_NAME_CALLER;?></option>
            <?php endif; ?>
            <?php if($is_shipper): ?>
                <option <?=($filter_type == orderController::TYPE_SHIP ? 'selected' : null);?> value="<?=orderController::TYPE_SHIP;?>"><?=Role::DEFAULT_NAME_SHIPPER;?></option>
            <?php endif; ?>
            <?php if($is_advertiser): ?>
                <option <?=($filter_type == orderController::TYPE_ADS ? 'selected' : null);?> value="<?=orderController::TYPE_ADS;?>"><?=Role::DEFAULT_NAME_ADVERTISER;?></option>
            <?php endif; ?>
            </select>
        </div>
    <?php endif; ?>

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
            <select id="filter-view" class="js-custom-select" name="<?=orderController::FILTER_VIEW;?>" >
                <option value="<?=orderController::VIEW_DAY;?>"><?=lang('status', 'view_day');?></option>
                <option <?=($filter_view == orderController::VIEW_TIME ? 'selected' : null);?> value="<?=orderController::VIEW_TIME;?>"><?=lang('status', 'view_time');?></option>
                <option <?=($filter_view == orderController::VIEW_MONTH ? 'selected' : null);?> value="<?=orderController::VIEW_MONTH;?>"><?=lang('status', 'view_month');?></option>
                <option <?=($filter_view == orderController::VIEW_YEAR ? 'selected' : null);?> value="<?=orderController::VIEW_YEAR;?>"><?=lang('status', 'view_year');?></option>
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

    <div class="filter-waybill">
        <div class="filter-waybill__title">
            <i class="fas fa-sort-down"></i>
            <span><?=lang('system', 'txt_custom_view');?> </span>
        </div>
        <div class="form-control">
            <ul class="custom-control-group" id="filter-status">
                <li class="form-check">
                    <input data-role="agree-buy" type="checkbox" value="<?=Order::STATUS_AGREE_BUY;?>" id="label_3" <?=(in_array(Order::STATUS_AGREE_BUY, $filter_status) ? 'checked' : null);?>>
                    <label for="label_3">Agree Buy</label>
                </li>
                <li class="form-check">
                    <input data-role="delivery-date" type="checkbox" value="<?=Order::STATUS_DELIVERY_DATE;?>" id="label_7" <?=(in_array(Order::STATUS_DELIVERY_DATE, $filter_status) ? 'checked' : null);?>>
                    <label for="label_7">Delivery Date</label>
                </li>
                <li class="form-check">
                    <input data-role="delivering" type="checkbox" value="<?=Order::STATUS_DELIVERING;?>" id="label_8" <?=(in_array(Order::STATUS_DELIVERING, $filter_status) ? 'checked' : null);?>>
                    <label for="label_8">Delivering</label>
                </li>
            <?php if($filter_type != orderController::TYPE_SHIP): ?>
                <li class="form-check">
                    <input data-role="pending-confirm" type="checkbox" value="<?=Order::STATUS_PENDING_CONFIRM;?>" id="label_1" <?=(in_array(Order::STATUS_PENDING_CONFIRM, $filter_status) ? 'checked' : null);?>>
                    <label for="label_1">Pending Confirm</label>
                </li>
                <li class="form-check">
                    <input data-role="calling" type="checkbox" value="<?=Order::STATUS_CALLING;?>" id="label_2" <?=(in_array(Order::STATUS_CALLING, $filter_status) ? 'checked' : null);?>>
                    <label for="label_2">Calling</label>
                </li>
                <li class="form-check">
                    <input data-role="busy-callback" type="checkbox" value="<?=Order::STATUS_BUSY_CALLBACK;?>" id="label_4" <?=(in_array(Order::STATUS_BUSY_CALLBACK, $filter_status) ? 'checked' : null);?>>
                    <label for="label_4">Busy - Callback</label>
                </li>
                <li class="form-check">
                    <input data-role="can-not-call" type="checkbox" value="<?=Order::STATUS_CAN_NOT_CALL;?>" id="label_5" <?=(in_array(Order::STATUS_CAN_NOT_CALL, $filter_status) ? 'checked' : null);?>>
                    <label for="label_5">Can Not Call</label>
                </li>
                <li class="form-check">
                    <input data-role="wrong-number" type="checkbox" value="<?=Order::STATUS_WRONG_NUMBER;?>" id="label_6" <?=(in_array(Order::STATUS_WRONG_NUMBER, $filter_status) ? 'checked' : null);?>>
                    <label for="label_6">Wrong Number</label>
                </li>
                <li class="form-check">
                    <input data-role="refuse-buy" type="checkbox" value="<?=Order::STATUS_REFUSE_BUY;?>" id="label_12" <?=(in_array(Order::STATUS_REFUSE_BUY, $filter_status) ? 'checked' : null);?>>
                    <label for="label_12">Refuse Buy</label>
                </li>
                <li class="form-check">
                    <input data-role="duplicate" type="checkbox" value="<?=Order::STATUS_DUPLICATE;?>" id="label_13" <?=(in_array(Order::STATUS_DUPLICATE, $filter_status) ? 'checked' : null);?>>
                    <label for="label_13">Duplicate</label>
                </li>
                <li class="form-check">
                    <input data-role="trash" type="checkbox" value="<?=Order::STATUS_TRASH;?>" id="label_14" <?=(in_array(Order::STATUS_TRASH, $filter_status) ? 'checked' : null);?>>
                    <label for="label_14">Trash</label>
                </li>
            <?php endif; ?>
            </ul>
        </div>
    </div>

<?php if($list_statistics): ?>

    <div class="table-scroll">
        <table class="table-statistics table-sort">
            <thead>
                <tr>
                    <th class="sort-btn"><?=lang('label', 'date');?></th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'apo');?>">APO (%)</th>
                    <th></th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'earning');?>">Earning</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'holding');?>">Holding</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'deduct');?>">Deduct</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'pre_sale');?>">Pre Sales</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'sale');?>">Sales</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'total');?>">Total Order</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'pending_delivery');?>">Pending Delivery</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'delivered');?>" role="status-delivered">Delivered</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'unreceived');?>" role="status-unreceived">Unreceived</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'returned');?>" role="status-returned">Returned</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'agree_buy');?>" role="status-agree-buy">Agree Buy</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'delivery_date');?>" role="status-delivery-date">Delivery Date</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'delivering');?>" role="status-delivering">Delivering</th>
                <?php if($filter_type != orderController::TYPE_SHIP): ?>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'pending_confirm');?>" role="status-pending-confirm">Pending Confirm</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'calling');?>" role="status-calling">Calling</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'busy_callback');?>" role="status-busy-callback">Busy Callback</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'can_not_call');?>" role="status-can-not-call">Can Not Call</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'wrong_number');?>" role="status-wrong-number">Wrong Number</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'refuse_buy');?>" role="status-refuse-buy">Refuse Buy</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'duplicate');?>" role="status-duplicate">Duplicate</th>
                    <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'trash');?>" role="status-trash">Trash</th>
                <?php endif; ?>
                </tr>
            </thead>
            <tbody>


            <?php

            $total_earning = 0;
            $total_holding = 0;
            $total_deduct = 0;
            $total_pre_sales = 0;
            $total_sales = 0;
            $total_order = 0;
            $total_pending_delivery = 0;
            $total_pending_confirm = 0;
            $total_calling = 0;
            $total_agree_buy = 0;
            $total_busy_callback = 0;
            $total_can_not_call = 0;
            $total_wrong_number = 0;
            $total_delivery_date = 0;
            $total_delivered = 0;
            $total_delivering = 0;
            $total_returned = 0;
            $total_unreceived = 0;
            $total_refuse_buy = 0;
            $total_duplicate = 0;
            $total_trash = 0;

            $total_count_apo = 0;

            foreach($list_statistics as $statistics):
                if($filter_type == orderController::TYPE_SHIP) {
                    $count_apo = $statistics['total_delivered'];
                } else {
                    $count_apo = $statistics['total_delivered'] + $statistics['total_delivering'] + $statistics['total_returned'] + $statistics['total_unreceived'] + $statistics['total_delivery_date'] + $statistics['total_agree_buy'];
                }

                $apo_rate = $statistics['total_order'] > 0 ? round($count_apo / $statistics['total_order'] * 100, 2) : 0;

                $total_earning += $statistics['total_earning'];
                $total_holding += $statistics['total_holding'];
                $total_deduct += $statistics['total_deduct'];
                $total_pre_sales += $statistics['pre_sales'];
                $total_sales += $statistics['sales'];
                $total_order += $statistics['total_order'];
                $total_pending_delivery += $statistics['total_pending_delivery'];
                $total_pending_confirm += $statistics['total_pending_confirm'];
                $total_calling += $statistics['total_calling'];
                $total_agree_buy += $statistics['total_agree_buy'];
                $total_busy_callback += $statistics['total_busy_callback'];
                $total_can_not_call += $statistics['total_can_not_call'];
                $total_wrong_number += $statistics['total_wrong_number'];
                $total_delivery_date += $statistics['total_delivery_date'];
                $total_delivered += $statistics['total_delivered'];
                $total_delivering += $statistics['total_delivering'];
                $total_returned += $statistics['total_returned'];
                $total_unreceived += $statistics['total_unreceived'];
                $total_refuse_buy += $statistics['total_refuse_buy'];
                $total_duplicate += $statistics['total_duplicate'];
                $total_trash += $statistics['total_trash'];

                $total_count_apo += $count_apo;
            ?>
                <tr>

                    <td class="nowrap">
                        <span class="time"><?=$statistics['order_range'];?></span>
                    </td>
                    <td class="nowrap align-center"><?=render_count($apo_rate);?>%</td>
                    <td class="nowrap align-center"><?=($count_apo > 0 ? '('.render_count($count_apo).')' : null);?></td>
                    <td class="nowrap align-center"><?=render_count($statistics['total_earning'], 'badge rounded-pill bg-success');?></td>
                    <td class="nowrap align-center"><?=render_count($statistics['total_holding'], 'badge rounded-pill bg-warning');?></td>
                    <td class="nowrap align-center"><?=render_count(-$statistics['total_deduct'], 'badge rounded-pill bg-danger');?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>"><?=render_count($statistics['pre_sales']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>"><?=render_count($statistics['sales'], 'text-primary');?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>"><?=render_count($statistics['total_order']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>"><?=render_count($statistics['total_pending_delivery'], 'text-warning');?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-delivered"><?=render_count($statistics['total_delivered'], 'text-success');?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-unreceived"><?=render_count($statistics['total_unreceived']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-returned"><?=render_count($statistics['total_returned'], 'text-danger');?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-agree-buy"><?=render_count($statistics['total_agree_buy']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-delivery-date"><?=render_count($statistics['total_delivery_date']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-delivering"><?=render_count($statistics['total_delivering']);?></td>
                <?php if($filter_type != orderController::TYPE_SHIP): ?>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-pending-confirm"><?=render_count($statistics['total_pending_confirm']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-calling"><?=render_count($statistics['total_calling']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-busy-callback"><?=render_count($statistics['total_busy_callback']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-can-not-call"><?=render_count($statistics['total_can_not_call']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-wrong-number"><?=render_count($statistics['total_wrong_number']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-refuse-buy"><?=render_count($statistics['total_refuse_buy']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-duplicate"><?=render_count($statistics['total_duplicate']);?></td>
                    <td class="nowrap align-center tooltip" title="<?=$statistics['order_range'];?>" role="status-trash"><?=render_count($statistics['total_trash']);?></td>
                <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="align-left"><?=lang('system', 'txt_total');?></th>
                    <th><?=render_count($total_order > 0 ? round($total_count_apo / $total_order * 100, 2) : 0);?>%</th>
                    <th>(<?=render_count($total_count_apo);?>)</th>
                    <th><?=render_count($total_earning, 'text-success');?></th>
                    <th><?=render_count($total_holding, 'text-warning');?></th>
                    <th><?=render_count(-$total_deduct, 'text-danger');?></th>
                    <th><?=render_count($total_pre_sales);?></th>
                    <th><?=render_count($total_sales, 'text-primary');?></th>
                    <th><?=render_count($total_order);?></th>
                    <th><?=render_count($total_pending_delivery, 'text-warning');?></th>
                    <th role="status-delivered"><?=render_count($total_delivered, 'text-success');?></th>
                    <th role="status-unreceived"><?=render_count($total_unreceived);?></th>
                    <th role="status-returned"><?=render_count($total_returned, 'text-danger');?></th>
                    <th role="status-agree-buy"><?=render_count($total_agree_buy);?></th>
                    <th role="status-delivery-date"><?=render_count($total_delivery_date);?></th>
                    <th role="status-delivering"><?=render_count($total_delivering);?></th>
                <?php if($filter_type != orderController::TYPE_SHIP): ?>
                    <th role="status-pending-confirm"><?=render_count($total_pending_confirm);?></th>
                    <th role="status-calling"><?=render_count($total_calling);?></th>
                    <th role="status-busy-callback"><?=render_count($total_busy_callback);?></th>
                    <th role="status-can-not-call"><?=render_count($total_can_not_call);?></th>
                    <th role="status-wrong-number"><?=render_count($total_wrong_number);?></th>
                    <th role="status-refuse-buy"><?=render_count($total_refuse_buy);?></th>
                    <th role="status-duplicate"><?=render_count($total_duplicate);?></th>
                    <th role="status-trash"><?=render_count($total_trash);?></th>
                <?php endif; ?>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="pagination">
        <?=html_pagination($pagination);?>
    </div>

<?php else: ?>
    <div class="alert"><?=lang('statistic', 'empty');?></div>
<?php endif; ?>


<?=assetController::load_js('moment.min.js');?>
<?=assetController::load_css('daterangepicker.css'); ?>
<?=assetController::load_js('daterangepicker.js');?>

<?=assetController::load_css('tooltipster.css'); ?>
<?=assetController::load_js('tooltipster.js');?>

<script type="text/javascript">
$(document).ready(function() {

    $('.tooltip').tooltipster({
        animation: 'fade',
        delay: 100,
        touchDevices: false,
        trigger: 'hover'
    });

    const startDate = '<?=_echo($startDate);?>';
    const endDate = '<?=_echo($endDate);?>';
    const date_format = 'DD-MM-YYYY';

    const form_filter = $('#form-filter');
    const clear_filter = $('#clear-filter');
    const filter_type = $('#filter-type');
    const filter_product = $('#filter-product');
    const filter_view = $('#filter-view');
    const filter_time = $('#filter-time');
    const date_picker = $('#date-picker');


    clear_filter.on('click', function(e) {
        e.preventDefault();
        filter_type.val(filter_type.find('option:first').val()).change();
        filter_product.val(filter_product.find('option:first').val()).change();
        filter_view.val(filter_view.find('option:first').val()).change();
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

    $('.filter-waybill').on('click', '.filter-waybill__title', function() {
        $(this).parent().toggleClass('show');
    });

    $('#filter-status').on('click', 'input[type="checkbox"]', function() {
        const data_role = $(this).data('role');
        if($(this).is(':checked')) {
            $('[role="status-' + data_role + '"]').removeClass('hidden');
        } else {
            $('[role="status-' + data_role + '"]').addClass('hidden');
        }

        const date = new Date();
        date.setTime(date.getTime() + (365 *24*60*60*1000));  
        const expires = "; expires=" + date.toUTCString();
        let value = [];

        $('#filter-status').find('input[type="checkbox"]').each(function() {
            if($(this).is(':checked')) {
                value.push($(this).val());
            }
        });

        document.cookie = "<?=orderController::COOKIE_FILTER_STATUS;?>=" + JSON.stringify(value)  + expires + "; path=/";
    });

    $('#filter-status').find('input[type="checkbox"]').each(function() {
        const data_role = $(this).data('role');
        if($(this).is(':checked')) {
            $('[role="status-' + data_role + '"]').removeClass('hidden');
        } else {
            $('[role="status-' + data_role + '"]').addClass('hidden');
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