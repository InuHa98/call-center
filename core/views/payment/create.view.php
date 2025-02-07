<?php View::render('layout.header', compact('title')); ?>



<div class="box">
	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <div class="box__header"><i class="fa fa-plus"></i><span class="padding-x-2"><?=lang('payment', 'new');?></span></div>
		<div class="box__body">

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

    <?php if(!$check_payment): ?>
        <?php if($list_team): ?>
            <div class="row label-ver">
                <div class="col-xs-12 col-lg-12 col-xl-12">
                    <div class="form-group">
                        <label class="control-label"><span class="text-danger">*</span> <?=lang('label', 'team');?></label>
                        <div class="form-control">
                            <select id="team" class="js-custom-select" name="<?=paymentController::INPUT_TEAM;?>" data-placeholder="<?=lang('placeholder', 'select_team');?>" enable-search="true">
                            <?php foreach($list_team as $team):
                                $data_team = Team::get_data($team['type']);
                                $html_option = '
                                <div class="user-infomation">
                                    <span class="user-avatar avatar--small">
                                        <span style="background: '.$data_team['color'].'">'.$data_team['icon'].'</span>
                                    </span>
                                    <span class="user-display-name">'.$team['name'].' - '.Currency::format($team['earning']).' '.$team['currency'].' '.($team['last_pay'] ? '(Last Pay: '.date('d-m-Y', $team['last_pay']).')' : null).'</span>
                                </div>';
                            ?>
                                <option <?=($team['id'] == $team_id ? 'selected' : null);?> value="<?=$team['id'];?>" data-html="<?=_echo($html_option);?>"></option>
                            <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert"><?=lang('payment', 'empty');?></div>
        <?php endif; ?>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('payment', 'btn_create');?></button>
            <a href="<?=RouteMap::get('payment');?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
    <?php else: ?>
        
            <input type="hidden" name="<?=paymentController::INPUT_ACTION;?>" value="<?=paymentController::ACTION_CREATE;?>" />
            <input type="hidden" name="<?=paymentController::INPUT_TEAM;?>" value="<?=$team['id'];?>" />
            <input type="hidden" name="<?=paymentController::INPUT_DATE;?>" value="<?=$invoice_date;?>" />

            <div class="invoice-title">
                <div class="invoice-title__left">
                    <strong><?=lang('label', 'date');?>:</strong> <?=date('d/m/Y', $invoice_date);?>
                </div>
                <div class="invoice-title__right">

                </div>
            </div>
            <hr>
            <div class="invoice-title">
                <div class="invoice-title__left  margin-b-4">
                    <div><strong><?=lang('payment', 'invoiced_to');?>:</strong></div>
                    <div><?=lang('label', 'team');?>: <a target="_blank" href="<?=RouteMap::get('team', ['id' => $team['id']]);?>"><?=_echo($team['name']);?></a></div>
                    <div><?=lang('label', 'leader');?>: <a target="_blank" href="<?=RouteMap::get('profile', ['id' => $leader['id']]);?>"><?=_echo($leader['username']);?></a></div>
                    <div><?=lang('label', 'currency');?>: <?=$currency;?></div>
                </div>
                <div class="invoice-title__right  margin-b-4">
                <div><strong><?=lang('payment', 'pay_to');?>:</strong></div>
                <div><span><?=lang('label', 'account_name');?>:</span> <?=_echo($pay_to['account_name']);?></div>
                <div><span><?=lang('label', 'account_number');?>:</span> <?=_echo($pay_to['account_number']);?></div>
                <div><span><?=lang('label', 'bank_name');?>:</span> <?=_echo($pay_to['bank_name']);?></div>
                <div><span><?=lang('label', 'bank_branch');?>:</span> <?=_echo($pay_to['bank_branch']);?></div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="table-statistics table-sort">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="align-left" width="100%"><?=lang('label', 'member');?></th>
                            <th class="sort-btn align-center"><?=lang('label', 'sale');?></th>
                            <th class="sort-btn align-center"><?=lang('label', 'earning');?></th>
                            <th class="sort-btn align-center"><?=lang('label', 'deduct');?></th>
                            <th class="sort-btn align-center"><?=lang('label', 'amount');?></th>
                        </tr>
                    </thead>
                    <tbody>


                    <?php

                    $total_earning = 0;
                    $total_deduct = 0;
                    $total_sales = 0;
                    $total_amount = 0;
                    $total_leader_profit = 0;
                    $leader_deduct = 0;
                    foreach($list_members as $member):
                        if($member['sales'] < 1) {
                            continue;
                        }
                        
                        $total_earning += $member['earning'];
                        $total_deduct += $member['deduct'];
                        $total_sales += $member['sales'];
                        $amount = $member['earning'] - $member['deduct'];

                        if($member['id'] == $leader['id']) {
                            $leader_deduct = $amount < 0 ? abs($amount) : 0;
                        }

                        if($amount < 0) {
                            $amount = 0;
                        }

                        $member_amount = $member['earning_member'] - $member['deduct_member'];
                        if($member_amount < 0) {
                            $member_amount = 0;
                        }
            
                        $total_leader_profit += $amount - $member_amount;

                        $total_amount += $amount;

                    ?>
                        <tr>
                            <td>
                                <span class="user-avatar">
                                    <img src="<?=User::get_avatar($member);?>" />
                                    <?=no_avatar($member);?>
                                </span>
                            </td>
                            <td class="align-left">
                                <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $member['id']]);?>">
                                    <span class="user-display-name"><?=User::get_username($member);?></span>
                                </a>
                            </td>
                            <td class="nowrap align-center"><?=Currency::format($member['sales']);?></td>
                            <td class="nowrap align-center"><?=Currency::format($member['earning']);?></td>
                            <td class="nowrap align-center"><?=Currency::format(-$member['deduct']);?></td>
                            <td class="nowrap align-center"><?=Currency::format($amount);?></td>
                        </tr>
                    <?php
                        endforeach;

                        if($leader_deduct > 0) {
                            $leader_deduct = $leader_deduct >= $total_leader_profit ? $total_leader_profit : $total_leader_profit - ($total_leader_profit - $leader_deduct);
                        }
                        $total_amount = $total_amount - $leader_deduct; 
                    ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="nowrap align-center"><?=Currency::format($total_sales);?></th>
                            <th class="nowrap align-center"><?=Currency::format($total_earning);?></th>
                            <th class="nowrap align-center">-<?=Currency::format($total_deduct);?></th>
                            <th class="nowrap align-center"><?=Currency::format($total_amount);?></th>
                        </tr>

			        <?php if($leader_deduct): ?>
				        <tr class="invoice-bg">
                            <th></th>
                            <th></th>
                            <th colspan="3" class="align-right"><?=lang('payment', 'leader_deduct');?></th>
                            <th class="align-left">-<?=Currency::format($leader_deduct);?> <?=$currency;?> <span class="time">(<?=Currency::format(round($team['to_default_currency'] * $leader_deduct)).' '.Currency::DEFAULT_CURRENCY;?>)</span></th>
                        </tr>
			        <?php endif; ?>

                        <tr class="invoice-bg">
                            <th></th>
                            <th></th>
                            <th colspan="3" class="align-right"><?=lang('payment', 'total_pay');?></th>
                            <th class="align-left"><?=Currency::format($total_amount);?> <?=$currency;?> <span class="time">(<?=Currency::format(round($team['to_default_currency'] * $total_amount)).' '.Currency::DEFAULT_CURRENCY;?>)</span></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
		<div class="box__footer">
			<input type="submit" role="create" class="btn btn--primary btn--round pull-right" value="<?=lang('payment', 'btn_pay');?>">
            <a href="<?=RouteMap::get('payment', ['block' => paymentController::BLOCK_CREATE]);?>" class="btn btn--round btn--gray pull-right"><?=lang('button', 'cancel');?></a>
		</div>
    <?php endif; ?>
	</form>
</div>

<?=assetController::load_js('form-validator.js');?>

<script type="text/javascript">
	$(document).ready(function() {

        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            rules: {
                '#team': [
                    Validator.isRequired('<?=lang('placeholder', 'select_team');?>'),
                ]
            }
        });

        $('[role="create"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('Xác nhận tạo thanh toán', '<?=lang('system', 'txt_can_undo');?>') == true) {
                return this.form.submit();
            }
        });

	});
</script>

<?php View::render('layout.footer'); ?>