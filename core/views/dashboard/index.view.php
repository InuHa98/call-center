<?php if(isset($title)) {
    View::render('layout.header', compact('title'));
} ?>

<form id="form-filter" class="filter-bar">
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

<div class="row">

    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('system', 'earning');?></div>
            <div class="card-content">
                <h2><?=Currency::format($earning);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon text-success">
                    <i class="fas fa-usd-circle"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('system', 'desc_earning');?></div>
        </div>
    </div>

    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('system', 'holding');?></div>
            <div class="card-content">
                <h2><?=Currency::format($holding);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon text-warning">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('system', 'desc_holding');?></div>
        </div>
    </div>

    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('system', 'deduct');?></div>
            <div class="card-content">
                <h2><?=Currency::format($deduct);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon text-danger">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('system', 'desc_deduct');?></div>
        </div>
    </div>

    <div class="col-xs-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-title"><?=lang('system', 'estimated_profit');?></div>
            <div class="card-content">
                <h2><?=Currency::format($holding + $earning + $deduct);?></h2>&nbsp;<span><?=$currency;?></span>
                <div class="icon">
                    <i class="fas fa-calculator-alt"></i>
                </div>
            </div>
            <div class="card-desc"><?=lang('system', 'desc_estimated_profit');?></div>
        </div>
    </div>
</div>

<div class="row margin-t-2">
    <div class="col-xs-12 col-xl-12 col-xxl-7">
        <div class="card">
            <div class="card-title padding-b-3"><?=lang('system', 'detailed_chart');?></div>
            <canvas id="chart-statistics"></canvas>
        </div>
    </div>
    <div class="col-xs-12 col-xl-8 col-xxl-3">
        <div class="card">
            <div class="card-title padding-b-3"><?=lang('system', 'order_chart');?></div>
            <canvas id="chart-status"></canvas>
        </div>
    </div>
    <div class="col-xs-12 col-xl-4 col-xxl-2">
        <div class="card">
            <div class="card-title padding-b-3"><?=lang('system', 'statistics_order');?></div>

            <div class="card-content">
                <div>
                    <span class="text-light"><?=lang('system', 'order_total');?></span>
                    <h2><?=Currency::format($total_order);?></h2>
                </div>
                <div class="bg-icon bg-gray">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>

            <div class="card-content">
                <div>
                    <span class="text-light"><?=lang('system', 'order_unpaid');?></span>
                    <h2><?=Currency::format($unpaid_order);?></h2>
                </div>
                <div class="bg-icon bg-primary">
                    <i class="fad fa-money-check-alt"></i>
                </div>
            </div>
 
            <div class="card-content">
                <div>
                    <span class="text-light"><?=lang('system', 'order_earning');?></span>
                    <h2><?=Currency::format($earning_order);?></h2>
                </div>
                <div class="bg-icon bg-success">
                    <i class="fas fa-usd-circle"></i>
                </div>
            </div>

            <div class="card-content">
                <div>
                    <span class="text-light"><?=lang('system', 'order_holding');?></span>
                    <h2><?=Currency::format($holding_order);?></h2>
                </div>
                <div class="bg-icon bg-warning">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>

            <div class="card-content">
                <div>
                    <span class="text-light"><?=lang('system', 'order_deduct');?></span>
                    <h2><?=Currency::format($deduct_order);?></h2>
                </div>
                <div class="bg-icon bg-pink">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>

        </div>
    </div>
</div>

<?=assetController::load_css('daterangepicker.css'); ?>
<?=assetController::load_js('moment.min.js');?>
<?=assetController::load_js('daterangepicker.js');?>
<?=assetController::load_js('chart.js'); ?>

<script type="text/javascript">
    $(document).ready(function() {

        const startDate = '<?=_echo($startDate);?>';
        const endDate = '<?=_echo($endDate);?>';
        const date_format = 'DD-MM-YYYY';

        const form_filter = $('#form-filter');
        const clear_filter = $('#clear-filter');
        const filter_product = $('#filter-product');
        const filter_time = $('#filter-time');
        const date_picker = $('#date-picker');


        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_product.val(filter_product.find('option:first').val()).change();
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

        const Utils = ChartUtils.init();
        const skipped = (ctx, value) =>  ctx.p0.parsed.y === 0 && ctx.p1.parsed.y === 0 ? value : undefined;
        const data_chart_statistics = <?=json_encode($data_chart_statistics, JSON_PRETTY_PRINT);?>;

        data_chart_statistics.datasets.forEach(function(data) {
            data.segment = {
                borderColor: ctx => skipped(ctx, 'rgb(255, 255, 255, 0.1)'),
                borderDash: ctx => skipped(ctx, [6, 6])
            };
            data.spanGaps = true;
        });

        new Chart(document.getElementById('chart-statistics'), {
            type: 'line',
            data: data_chart_statistics,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: false
                    },
                },
                interaction: {
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Order'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(document.getElementById('chart-status'), {
            type: 'doughnut',
            data: <?=json_encode($data_chart_status);?>,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    }              
                }
            }
        });
    });
</script>
<?php if(isset($title)) {
    View::render('layout.footer');
} ?>