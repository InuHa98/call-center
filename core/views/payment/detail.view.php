<?php View::render('layout.header', compact('title')); ?>



<div class="box">
	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <div class="box__header">
            <span><i class="fa fa-plus"></i><span class="padding-x-2"><?=lang('payment', 'detail');?></span></span>
            <span class="action">
        <?php if($payment['status'] != Payment::STATUS_PAID): ?>
            <span class="badge rounded-pill bg-pink"><?=lang('status', 'unpaid');?></span>
        <?php else :?>
            <span class="badge rounded-pill bg-success"><?=lang('status', 'paid');?></span>
        <?php endif; ?>
        </span>
        </div>
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



            <div class="invoice-title">
                <div class="invoice-title__left">
                    <strong><?=lang('label', 'date');?>:</strong> <?=date('d/m/Y', $payment['created_at']);?>
                </div>
                <div class="invoice-title__right">
                    <strong><?=lang('payment', 'payment_no');?>:</strong> #<?=$payment['id'];?>
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
                            <th class="sort-btn"><?=lang('label', 'invoice');?></th>
                            <th class="sort-btn"><?=lang('label', 'status');?>
                            <th></th>
                            <th class="align-left" width="100%"><?=lang('label', 'member');?></th>
                            <th class="sort-btn align-center"><?=lang('label', 'earning');?></th>
                            <th class="sort-btn align-center"><?=lang('label', 'deduct');?></th>
                            <th class="sort-btn align-center"><?=lang('label', 'amount');?></th>
                        </tr>
                    </thead>
                    <tbody>


                    <?php

                    $total_earning = 0;
                    $total_deduct = 0;
                    $total_amount = 0;
                    $total_leader_profit = 0;
                    $leader_deduct = 0;
                    foreach($list_invoices as $invoice):
                        $total_earning += $invoice['earning'];
                        $total_deduct += $invoice['deduct'];
                        $amount = $invoice['earning'] - $invoice['deduct'];

                        if($invoice['user_id'] == $leader['id']) {
                            $leader_deduct = $amount < 0 ? abs($amount) : 0;
                        }

                        if($amount < 0) {
                            $amount = 0;
                        }
  
                        $member_amount = $invoice['earning_member'] - $invoice['deduct_member'];
                        if($member_amount < 0) {
                            $member_amount = 0;
                        }
            
                        $total_leader_profit += $amount - $member_amount;

                        $total_amount += $amount;

                        $user = [
                            'id' => $invoice['user_id'],
                            'username' => $invoice['user_username'],
                            'avatar' => $invoice['user_avatar'],
                            'is_ban' => $invoice['user_is_ban']
                        ];
                    ?>
                        <tr>
                            <td>
                                <span class="id">#<?=$invoice['id'];?></span>
                            </td>
                            <td class="nowrap">
                            <?php if($invoice['status'] != Invoice::STATUS_PAID): ?>
                                <span class="badge rounded-pill bg-pink"><?=lang('status', 'unpaid');?></span>
                            <?php else :?>
                                <span class="badge rounded-pill bg-success"><?=lang('status', 'paid');?></span>
                            <?php endif; ?>
                            </td>
                            <td>
                                <span class="user-avatar">
                                    <img src="<?=User::get_avatar($user);?>" />
                                    <?=no_avatar($user);?>
                                </span>
                            </td>
                            <td class="align-left">
                                <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
                                    <span class="user-display-name"><?=User::get_username($user);?></span>
                                </a>
                            </td>
                            <td class="nowrap align-center"><?=Currency::format($invoice['earning']);?></td>
                            <td class="nowrap align-center"><?=Currency::format(-$invoice['deduct']);?></td>
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
                            <th></th>
                            <th></th>
                            <th class="nowrap align-center"><?=Currency::format($total_earning);?></th>
                            <th class="nowrap align-center">-<?=Currency::format($total_deduct);?></th>
                            <th class="nowrap align-center"><?=Currency::format($total_amount);?></th>
                        </tr>

			        <?php if($leader_deduct): ?>
				        <tr class="invoice-bg">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th colspan="3" class="align-right"><?=lang('payment', 'leader_deduct');?></th>
                            <th class="align-left">-<?=Currency::format($leader_deduct);?> <?=$currency;?> <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * $leader_deduct)).' '.Currency::DEFAULT_CURRENCY;?>)</span></th>
                        </tr>
			        <?php endif; ?>

                        <tr class="invoice-bg">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th colspan="3" class="align-right"><?=lang('payment', 'total_pay');?></th>
                            <th class="align-left"><?=Currency::format($total_amount);?> <?=$currency;?> <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * $total_amount)).' '.Currency::DEFAULT_CURRENCY;?>)</span></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
		<div class="box__footer">
            <a href="<?=RouteMap::get('payment');?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>


<script type="text/javascript">
	$(document).ready(function() {


	});
</script>

<?php View::render('layout.footer'); ?>