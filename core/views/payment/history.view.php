<?php View::render('layout.header', compact('title')); ?>


<div class="row margin-b-4">
    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('payment', 'total_paid');?></div>
            <div class="card-content">
                <h2 class="text-primary"><?=Currency::format($total['paid']);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon text-primary">
                    <i class="far fa-money-bill-alt"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('payment', 'txt_paid');?></div>
        </div>
    </div>

    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('payment', 'total_wait');?></div>
            <div class="card-content">
                <h2 class="text-warning"><?=Currency::format($total['unpaid']);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon text-warning">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('payment', 'txt_wait');?></div>
        </div>
    </div>

    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('payment', 'total_earning');?></div>
            <div class="card-content">
                <h2 class="text-success"><?=Currency::format($total['earning']);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon text-success">
                    <i class="fas fa-usd-circle"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('payment', 'txt_earning');?></div>
        </div>
    </div>

    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('payment', 'total_deduct');?></div>
            <div class="card-content">
                <h2 class="text-danger">-<?=Currency::format($total['deduct']);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon text-danger">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('payment', 'txt_deduct');?></div>
        </div>
    </div>


</div>

<div class="box">

    <div class="box__header">
        <span>
            <i class="far fa-money-bill-alt"></i>
            <span class="padding-x-2"><?=lang('system', 'payment_history');?></span>
        </span>
    </div>
    <div class="box__body">

    <form id="form-filter" method="GET" class="filter-bar">
            <input type="hidden" name="<?=InterFaceRequest::START_DATE;?>" id="startDate" value="<?=_echo($startDate);?>" />
            <input type="hidden" name="<?=InterFaceRequest::END_DATE;?>" id="endDate" value="<?=_echo($endDate);?>" />

            <div class="form-control">
                <select id="filter-time" class="js-custom-select" name="<?=InterFaceRequest::TIME;?>" data-placeholder="<?=lang('system', 'custom_time');?>">
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

    <?php if($list_invoice): ?>
        <div class="margin-b-2">
            <?=lang('payment', 'txt_count', [
                'count' => $count
            ]);?>
        </div>
        <div class="table-scroll">
    <table class="table-statistics table-sort">
        <thead>
            <tr>
                <th class="sort-btn">ID</th>
                <th class="sort-btn"><?=lang('label', 'date');?></th>
                <th width="50%"><?=lang('label', 'status');?></th>
                <th class="align-center sort-btn"><?=lang('label', 'amount');?></th>
                <th class="align-center sort-btn"><?=lang('label', 'earning');?></th>
                <th class="align-center sort-btn"><?=lang('label', 'deduct');?></th>
                <th><?=lang('label', 'paid_by');?></th>
            </tr>
        </thead>
        <tbody>

    <?php foreach($list_invoice as $invoice):
        
        $user_paid = [
            'id' => $invoice['user_id'],
            'username' => $invoice['user_username'],
            'avatar' => $invoice['user_avatar'],
            'is_ban' => $invoice['user_is_ban']
        ];

    ?>
            <tr>
                <td class="nowrap">
                    <span class="id">#<?=$invoice['id'];?></span>
                </td>
                <td class="nowrap">
                    <span class="time"><?=date('d-m-Y', $invoice['created_at']);?></span>
                </td>
                <td class="nowrap">
                <?php if($invoice['status'] != Invoice::STATUS_PAID): ?>
                    <span class="badge rounded-pill bg-warning"><?=lang('status', 'waiting_payment');?></span>
                <?php else :?>
                    <span class="badge rounded-pill bg-success"><?=lang('status', 'paid');?></span>
                <?php endif; ?>
                </td>
                <td class="nowrap align-center">
                    <?=render_count($invoice['amount']);?> <?=$currency;?>
                </td>

                <td class="nowrap align-center">
                    <?=render_count($invoice['earning']);?> <?=$currency;?>
                </td>

                <td class="nowrap align-center">
                    -<?=render_count($invoice['deduct'], 'text-danger');?> <?=$currency;?>
                </td>
                <td>
                <?php if($user_paid['id']): ?>
                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_paid['id']]);?>">
                        <span class="user-avatar avatar--small">
                            <img src="<?=User::get_avatar($user_paid);?>" />
                            <?=no_avatar($user_paid);?>
                        </span>
                        <span class="user-display-name"><?=User::get_username($user_paid);?></span>
                    </a>
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
        <div class="alert"><?=lang('payment', 'empty_invoice');?></div>
    <?php endif; ?>
    </div>

</div>

<?=assetController::load_css('daterangepicker.css'); ?>
<?=assetController::load_js('moment.min.js');?>
<?=assetController::load_js('daterangepicker.js');?>

<script type="text/javascript">
	$(document).ready(function() {

        const startDate = '<?=_echo($startDate);?>';
        const endDate = '<?=_echo($endDate);?>';
        const date_format = 'DD-MM-YYYY';

        const form_filter = $('#form-filter');
        const clear_filter = $('#clear-filter');
        const filter_time = $('#filter-time');
        const date_picker = $('#date-picker');


        clear_filter.on('click', function(e) {
            e.preventDefault();
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


	});
</script>

<?php View::render('layout.footer'); ?>