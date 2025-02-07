<?php View::render('layout.header', compact('title', 'current_route')); ?>


<div class="box margin-t-4">
    <div class="box__header"><i class="far fa-chart-bar"></i> <span class="padding-x-2"><?=lang('system', 'landing_statistics');?></span></div>
    <div class="box__body">
        <form id="form-filter" class="filter-bar">
            <input type="hidden" name="<?=InterFaceRequest::START_DATE;?>" id="startDate" value="<?=_echo($startDate);?>" />
            <input type="hidden" name="<?=InterFaceRequest::END_DATE;?>" id="endDate" value="<?=_echo($endDate);?>" />


            <div class="form-control">
                <select id="filter-product" class="js-custom-select" name="<?=orderController::FILTER_PRODUCT;?>" data-placeholder="<?=lang('placeholder', 'select_product');?>" enable-search="true">
                <?php foreach($list_product as $product):
                    $html_option = '
                    <div class="user-infomation">
                        <span class="user-avatar avatar--small">
                            <img src="'.Product::get_image($product).'" />
                        </span>
                        <span class="user-display-name">#'.$product['id'].' - '.$product['name'].' ('.Currency::format($product['price']).' '.$product['currency'].')</span>
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


    <?php if($list_statistics): ?>

        <div class="table-scroll">
            <table class="table-statistics table-sort">
                <thead>
                    <tr>
                        <th class="align-center tooltip">Landing Page</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'apo');?>">APO (%)</th>
                        <th class="align-center"></th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'page_view');?>">PageView</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'conversion');?>">Conversion</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'pre_sale');?>">Pre Sales</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'sale');?>">Sales</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'agree_buy');?>">Agree Buy</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'refuse_buy');?>">Refuse Buy</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'duplicate');?>">Duplicate</th>
                        <th class="sort-btn align-center tooltip" title="<?=lang('statistic', 'trash');?>">Trash</th>
                    </tr>
                </thead>
                <tbody>


                <?php
                $total_page_view = 0;
                $total_conversion = 0;
                $total_count_apo = 0;
                $total_pre_sales = 0;
                $total_sales = 0;
                $total_agree_buy = 0;
                $total_refuse_buy = 0;
                $total_duplicate = 0;
                $total_trash = 0;


                foreach($list_statistics as $statistics):

                    $count_apo = $statistics['total_agree_buy'];
                    $apo_rate = $statistics['total_conversion'] > 0 ? round($count_apo / $statistics['total_conversion'] * 100, 2) : 0;
                    $total_pre_sales += $statistics['pre_sales'];
                    $total_sales += $statistics['sales'];
                    $total_conversion += $statistics['total_conversion'];
                    $total_agree_buy += $statistics['total_agree_buy'];
                    $total_refuse_buy += $statistics['total_refuse_buy'];
                    $total_duplicate += $statistics['total_duplicate'];
                    $total_trash += $statistics['total_trash'];

                    $total_count_apo += $count_apo;
                ?>
                    <tr>
                        <td class="nowrap align-center"><span class="time"><?=_echo($statistics['domain']);?></span></td>
                        <td class="nowrap align-center"><?=render_count($apo_rate);?>%</td>
                        <td class="nowrap align-center"><?=($count_apo > 0 ? '('.render_count($count_apo).')' : null);?></td>
                        <td class="nowrap align-center"><?=render_count($statistics['total_page_view']);?></td>
                        <td class="nowrap align-center"><?=render_count($statistics['total_conversion']);?></td>
                        <td class="nowrap align-center"><?=render_count($statistics['pre_sales']);?></td>
                        <td class="nowrap align-center"><?=render_count($statistics['sales'], 'text-primary');?></td>
                        <td class="nowrap align-center" role="status-agree-buy"><?=render_count($statistics['total_agree_buy'], 'text-success');?></td>
                        <td class="nowrap align-center" role="status-refuse-buy"><?=render_count($statistics['total_refuse_buy'], 'text-danger');?></td>
                        <td class="nowrap align-center" role="status-duplicate"><?=render_count($statistics['total_duplicate']);?></td>
                        <td class="nowrap align-center" role="status-trash"><?=render_count($statistics['total_trash']);?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="align-left"><?=lang('system', 'txt_total');?></th>
                        <th><?=render_count($total_conversion > 0 ? round($total_count_apo / $total_conversion * 100, 2) : 0);?>%</th>
                        <th>(<?=render_count($total_count_apo);?>)</th>
                        <th><?=render_count($total_page_view);?></th>
                        <th><?=render_count($total_conversion);?></th>
                        <th><?=render_count($total_pre_sales);?></th>
                        <th><?=render_count($total_sales, 'text-primary');?></th>
                        <th role="status-agree-buy"><?=render_count($total_agree_buy, 'text-success');?></th>
                        <th role="status-refuse-buy"><?=render_count($total_refuse_buy, 'text-danger');?></th>
                        <th role="status-duplicate"><?=render_count($total_duplicate);?></th>
                        <th role="status-trash"><?=render_count($total_trash);?></th>
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

    </div>

</div>

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

<?php View::render('layout.footer'); ?>