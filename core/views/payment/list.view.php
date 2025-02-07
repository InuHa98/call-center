<?php


 View::render('layout.header', compact('title')); ?>


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
        <span>
            <i class="far fa-money-bill-alt"></i>
            <span class="padding-x-2"><?=lang('system', 'payment');?></span>
        </span>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('payment', ['block' => paymentController::BLOCK_CREATE]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    </div>
    <div class="box__body">

        <form id="form-filter" method="GET" class="filter-bar">
            <input type="hidden" name="<?=InterFaceRequest::START_DATE;?>" id="startDate" value="<?=_echo($startDate);?>" />
            <input type="hidden" name="<?=InterFaceRequest::END_DATE;?>" id="endDate" value="<?=_echo($endDate);?>" />

            <div class="form-control">
                <select id="filter-type" class="js-custom-select" name="<?=InterFaceRequest::TYPE;?>" data-placeholder="<?=lang('placeholder', 'select_team');?>" data-max-width="180px">
                    <option value="<?=adminPanelController::INPUT_ALL;?>"><?=lang('system', 'all_team');?></option>
                    <option <?=($filter_type === Team::TYPE_ONLY_CALL ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_CALL;?>"><?=Role::DEFAULT_NAME_CALLER;?></option>
                    <option <?=($filter_type === Team::TYPE_ONLY_SHIP ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_SHIP;?>"><?=Role::DEFAULT_NAME_SHIPPER;?></option>
                    <option <?=($filter_type === Team::TYPE_ONLY_ADS ? 'selected' : null);?> value="<?=Team::TYPE_ONLY_ADS;?>"><?=Role::DEFAULT_NAME_ADVERTISER;?></option>
                    <option <?=($filter_type === Team::TYPE_FULLSTACK ? 'selected' : null);?> value="<?=Team::TYPE_FULLSTACK;?>"><?=Role::DEFAULT_NAME_FULLSTACK;?></option>
                </select>
            </div>

            <div class="form-control">
                <select id="filter-status" class="js-custom-select" name="<?=InterFaceRequest::STATUS;?>" data-placeholder="Please choose a status" enable-search="true">
                    <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_status');?></option>
                    <option <?=($filter_status === Payment::STATUS_PAID ? 'selected' : null);?> value="<?=Payment::STATUS_PAID;?>">Paid</option>
                    <option <?=($filter_status === Payment::STATUS_NOT_PAID ? 'selected' : null);?> value="<?=Payment::STATUS_NOT_PAID;?>">UnPaid</option>
                </select>
            </div>

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


    <?php if($list_payment): ?>
        <div class="margin-b-2">
            <?=lang('payment', 'txt_count', [
                'count' => $count
            ]);?>
        </div>
        <div class="table-scroll">
    <table class="table-statistics table-sort">
        <thead>
            <tr>
                <th></th>
                <th class="sort-btn">ID</th>
                <th class="sort-btn"><?=lang('label', 'date');?></th>
                <th><?=lang('label', 'status');?></th>
                <th></th>
                <th width="100%"><?=lang('label', 'team');?></th>
                <th class="sort-btn"><?=lang('payment', 'leader_profit');?></th>
                <th class="sort-btn"><?=lang('payment', 'leader_deduct');?></th>
                <th class="sort-btn"><?=lang('label', 'amount');?></th>
                <th><?=lang('label', 'paid_by');?></th>
            </tr>
        </thead>
        <tbody>

    <?php foreach($list_payment as $payment):
        
        $user_paid = [
            'id' => $payment['user_id'],
            'username' => $payment['user_username'],
            'avatar' => $payment['user_avatar'],
            'is_ban' => $payment['user_is_ban']
        ];

        $data_team = Team::get_data($payment['team_type']);

    ?>
            <tr>

                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                            <li>
                                <a href="<?=RouteMap::get('payment', ['block' => paymentController::BLOCK_DETAIL, 'action' => $payment['id']]);?>">
                                    <i class="fas fa-eye"></i> <?=lang('system', 'detail');?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
                <td class="nowrap">
                    <span class="id">#<?=$payment['id'];?></span>
                </td>
                <td class="nowrap">
                    <span class="time"><?=date('d-m-Y', $payment['created_at']);?></span>
                </td>

                <td class="nowrap">
                <?php if($payment['status'] != Payment::STATUS_PAID): ?>
                    <span class="badge rounded-pill bg-pink"><?=lang('status', 'unpaid');?></span>
                <?php else :?>
                    <span class="badge rounded-pill bg-success"><?=lang('status', 'paid');?></span>
                <?php endif; ?>
                </td>


                <td class="nowrap">
                    <span class="user-avatar avatar--small">
                        <span style="background: <?=$data_team['color'];?>"><?=$data_team['icon'];?></span>
                    </span>
                </td>
                
                <td class="nowrap">
                    <a target="_blank" class="user-display-name" href="<?=RouteMap::get('team', ['id' => $payment['team_id']]);?>"><?=_echo($payment['team_name']);?></a>
                </td>

                <td class="nowrap">
                    <span><?=render_count($payment['leader_profit']);?> <?=_echo($payment['currency']);?>
                    <span class="time"> (<?=Currency::format(round($payment['leader_profit'] * $payment['to_default_currency']));?> <?=Currency::DEFAULT_CURRENCY;?>)</span>
                </td>

                <td class="nowrap">
                    <span>-<?=render_count($payment['leader_deduct'], 'text-danger');?> <?=_echo($payment['currency']);?>
                    <span class="time"> (<?=Currency::format(round($payment['leader_deduct'] * $payment['to_default_currency']));?> <?=Currency::DEFAULT_CURRENCY;?>)</span>
                </td>

                <td class="nowrap">
                    <?=render_count($payment['amount'], 'text-primary');?> <?=_echo($payment['currency']);?> <span class="time">(<?=Currency::format(round($payment['amount'] * $payment['to_default_currency']));?> <?=Currency::DEFAULT_CURRENCY;?>)</span>
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
        const filter_type = $('#filter-type');
        const filter_status = $('#filter-status');
        const filter_time = $('#filter-time');
        const date_picker = $('#date-picker');


        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_type.val(filter_type.find('option:first').val()).change();
            filter_status.val(filter_status.find('option:first').val()).change();
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