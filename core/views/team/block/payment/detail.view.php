
<div class="box">
    <div class="box__header">
        <span><strong><?=lang('payment', 'payment_no');?>:</strong> #<?=$payment['id'];?></span>
        <span class="action">
        <?php if($payment['status'] != Payment::STATUS_PAID): ?>
            <span class="badge rounded-pill bg-pink"><?=lang('status', 'unpaid');?></span>
        <?php else :?>
            <span class="badge rounded-pill bg-success"><?=lang('status', 'paid');?></span>
        <?php endif; ?>
        </span>
    </div>
    <div class="box__body">
        <div class="margin-b-2"><?=lang('team', 'desc_payment');?></div>
        <div class="table-scroll">
            <table class="table-statistics table-sort">
                <thead>
                    <tr>
                        <th></th>
                        <th class="sort-btn"><?=lang('label', 'invoice');?></th>
                        <th class="sort-btn"><?=lang('label', 'status');?>
                        <th></th>
                        <th class="align-left" width="100%"><?=lang('label', 'member');?></th>
                        <th class="sort-btn align-center"><?=lang('label', 'earning');?></th>
                        <th class="sort-btn align-center"><?=lang('label', 'deduct');?></th>
                        <th class="sort-btn align-center"><?=lang('payment', 'leader_profit');?></th>
                        <th class="sort-btn align-center"><?=lang('label', 'amount');?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>


                <?php

                $total_earning = 0;
                $total_deduct = 0;
                $total_paid = 0;
                $i = 0;
                foreach($list_invoices as $invoice):


                    $total_earning += $invoice['earning_member'];
                    $total_deduct += $invoice['deduct_member'];

                    $amount = $invoice['earning_member'] - $invoice['deduct_member'];
                    if($amount < 0) {
                        $amount = 0;
                    }
                    $leader_amount = $invoice['earning'] - $invoice['deduct'];
                    if($leader_amount < 0) {
                        $leader_amount = 0;
                    }
                    $total_paid += $amount;

                    $user = [
                        'id' => $invoice['user_id'],
                        'username' => $invoice['user_username'],
                        'avatar' => $invoice['user_avatar'],
                        'is_ban' => $invoice['user_is_ban'],
                        'billing' => $invoice['user_billing']
                    ];
                ?>
                    <tr>
                        <td class="nowrap">
                        <?php if($invoice['status'] != Invoice::STATUS_PAID): ?>
                            <button role="btn-paid" class="btn btn--small btn--warning" data-id="<?=$invoice['id'];?>">
                                <i class="fas fa-dollar-sign"></i> <?=lang('button', 'mark_paid');?>
                            </button>
                        <?php endif; ?>
                        </td>

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
                        <td class="nowrap align-center"><?=render_count($invoice['earning_member']);?></td>
                        <td class="nowrap align-center"><?=render_count(-$invoice['deduct_member']);?></td>
                        <td class="nowrap align-center"><?=render_count($leader_amount);?></td>
                        <td class="nowrap align-center"><?=render_count($amount, 'text-danger');?></td>
                        <td class="nowrap align-center">
                        <?php if($is_admin):?>
                            <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * $amount)).' '.Currency::DEFAULT_CURRENCY;?>)</span>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    $list_invoices[$i]['amount_text'] = '<span class="text-danger">'.Currency::format($amount).'</span> '.$currency.($is_admin ? ' <span style="opacity: .5">('.Currency::format(round($payment['to_default_currency'] * $amount)).' '.Currency::DEFAULT_CURRENCY.')</span>' : null);
                    $list_invoices[$i]['user_received'] = '
                    <a target="_blank" class="user-infomation" href="'.RouteMap::get('profile', ['id' => $user['id']]).'">
                        <span class="user-avatar avatar--small">
                            <img src="'.User::get_avatar($user).'" />
                            '.no_avatar($user).'
                        </span>
                        <span class="user-display-name">'.User::get_username($user).'</span>
                    </a>';
                    $billing = User::get_billing($user);
                    $list_invoices[$i]['user_billing'] = '
                    <br/>
                    <div class="form-group limit--width">
                        <div class="form-control">
                            <label class="control-label">'.lang('label', 'bank_name').':</label>
                            <div class="input-group">
                                <input class="form-input" type="text" value="'._echo($billing['bank_name']).'">
                                <div class="input-group-append">
                                    <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group limit--width">
                        <div class="form-control">
                            <label class="control-label">'.lang('label', 'bank_branch').':</label>
                            <div class="input-group">
                                <input class="form-input" type="text" value="'._echo($billing['bank_branch']).'">
                                <div class="input-group-append">
                                    <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group limit--width">
                        <div class="form-control">
                            <label class="control-label">'.lang('label', 'account_name').':</label>
                            <div class="input-group">
                                <input class="form-input" type="text" value="'._echo($billing['account_name']).'">
                                <div class="input-group-append">
                                    <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group limit--width">
                        <div class="form-control">
                            <label class="control-label">'.lang('label', 'account_number').':</label>
                            <div class="input-group">
                                <input class="form-input" type="text" value="'._echo($billing['account_number']).'">
                                <div class="input-group-append">
                                    <button type="button" role="copy" class="btn btn-outline-gray btn-dim" style="pointer-events: auto;"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>';
                    $i++;
                    endforeach;
                ?>
                </tbody>
                <tfoot>
                    <tr class="invoice-bg">
                        <th class="nowrap align-left" colspan="5"><strong><?=lang('label', 'date');?>:</strong> <?=date('d/m/Y', $payment['created_at']);?></th>
                        <th class="nowrap align-center"><?=render_count($total_earning);?></th>
                        <th class="nowrap align-center">-<?=render_count($total_deduct);?></th>
                        <th class="nowrap align-center"><?=render_count($payment['leader_profit']);?></th>
                        <th class="nowrap align-center"><?=render_count($total_paid, 'text-danger');?></th>
                        <th class="nowrap align-center">
                        <?php if($is_admin):?>
                            <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * $total_paid)).' '.Currency::DEFAULT_CURRENCY;?>)</span>
                        <?php endif; ?>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="8" class="align-right"><?=lang('payment', 'amount_received');?>:</th>
                        <th><?=render_count($payment['amount']);?> <?=$currency;?></th>
                        <th>
                        <?php if($is_admin):?>
                            <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * $payment['amount'])).' '.Currency::DEFAULT_CURRENCY;?>)</span>
                        <?php endif; ?>
                        </th>
                    </tr>

                <?php if($payment['leader_deduct']): ?>
                    <tr>
                        <th colspan="8" class="align-right"><?=lang('payment', 'leader_deduct');?>:</th>
                        <th class="align-left">-<?=render_count($payment['leader_deduct']);?> <?=$currency;?></th>
                        <th>
                        <?php if($is_admin):?>
                            <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * $payment['leader_deduct'])).' '.Currency::DEFAULT_CURRENCY;?>)</span>
                        <?php endif; ?>
                        </th>
                    </tr>
                <?php endif; ?>

                    <tr>
                        <th colspan="8" class="align-right"><?=lang('payment', 'leader_profit');?>:</th>
                        <th><?=render_count($payment['leader_profit'] - $payment['leader_deduct'], 'text-success');?> <?=$currency;?></th>
                        <th>
                        <?php if($is_admin):?>
                            <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * ($payment['leader_profit'] - $payment['leader_deduct']))).' '.Currency::DEFAULT_CURRENCY;?>)</span>
                        <?php endif; ?>
                        </th>
                    </tr>

                    <tr>
                        <th colspan="8" class="align-right"><?=lang('payment', 'total_amount_paid');?>:</th>
                        <th class="align-left"><?=render_count($total_paid, 'text-danger');?> <?=$currency;?> </th>
                        <th>
                        <?php if($is_admin):?>
                            <span class="time">(<?=Currency::format(round($payment['to_default_currency'] * $total_paid)).' '.Currency::DEFAULT_CURRENCY;?>)</span>
                        <?php endif; ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
    <div class="box__footer">
        <a href="<?=RouteMap::get('team', ['id' => $team['id'], 'block' => teamController::BLOCK_PAYMENT]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
    </div>

</div>


<script type="text/javascript">
	$(document).ready(function() {

        const DATA_INVOICES = <?=json_encode($list_invoices);?>;

        $(document).on('click', '[role="btn-paid"]', function() {
            const invoice = DATA_INVOICES.find(o => o.id == $(this).attr('data-id'));

            if(!invoice) {
                return $.toastShow('<?=lang('payment', 'error_invoice_not_found');?>', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $('\
            <form method="post" class="margin-b-4">\
                <?=Security::insertHiddenToken();?>\
                <input type="hidden" name="<?=InterFaceRequest::ID;?>" value="' + invoice.id + '">\
                <div class="dialog-label"><?=lang('payment', 'pay_to');?>: '+ invoice.user_received +'</div>\
                <div class="dialog-label"><?=lang('payment', 'total_amount_paid');?>: '+ invoice.amount_text +'</div>\
                <div class="label-ver only-read">' + invoice.user_billing + '</div>\
            </form>');


			$.dialogShow({
				title: '<?=lang('payment', 'mark_paid');?> #' + invoice.id,
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

	});
</script>