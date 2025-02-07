<?php View::render('layout.header', compact('title', 'current_route')); ?>



<div class="box">
    <div class="box__header"><i class="far fa-edit text-primary"></i><span class="padding-x-2"><?=lang('order', 'detail_order');?> - #<?=$order['id'];?></span></div>
    <div class="box__body only-read">
    <?php if($order['duplicate']): ?>
        <div class="alert alert--warning margin-t-0">
        <?=lang('order', 'txt_calling_duplicate', ['count' => $order['duplicate'], 'link' => RouteMap::get('order', ['block' => orderController::BLOCK_DUPLICATE, 'action' => $order['id']])]);?>
        </div>
    <?php endif; ?>
        <div class="card-product">
            <img class="card-product__image small-image" src="<?=Product::get_image(['image' => $order['product_image']]);?>" />
            <div class="card-info-order">
                <div class="card-product__id">
                    <span class="label"><?=lang('label', 'order');?>:</span> #<?=_echo($order['id']);?>
                </div>
                <div class="card-product__name">
                    <span class="label"><?=lang('label', 'product');?>:</span> <?=_echo($order['product_name']);?>
                </div>
                <div class="card-product__price">
                    <span class="label"><?=lang('label', 'price');?>:</span> <?=Currency::format($order['product_price']);?> <?=_echo($order['currency']);?>
                </div>

                <div class="card-product__time">
                    <span class="card-product__created-at">
                        <span class="label"><?=lang('label', 'created_at');?>:</span> <span class="time"><?=_time($order['created_at']);?></span>
                        <?php if($user_ads['id']): ?>
                            <a target="_blank" class="user-infomation bg--white" href="<?=RouteMap::get('profile', ['id' => $user_ads['id']]);?>">
                                <span class="user-avatar avatar--small">
                                    <img src="<?=User::get_avatar($user_ads);?>" />
                                    <?=no_avatar($user_ads);?>
                                </span>
                                <span class="user-display-name"><?=User::get_username($user_ads);?></span>
                            </a>
                        <?php endif; ?>
                    </span>

                <?php if($order['call_at']): ?>
                    <span class="card-product__call-at">
                        <span class="label"><?=lang('label', 'call_at');?>:</span> <span class="time"><?=_time($order['call_at']);?></span>
                        <a target="_blank" class="user-infomation bg--white" href="<?=RouteMap::get('profile', ['id' => $user_call['id']]);?>">
                            <span class="user-avatar avatar--small">
                                <img src="<?=User::get_avatar($user_call);?>" />
                                <?=no_avatar($user_call);?>
                            </span>
                            <span class="user-display-name"><?=User::get_username($user_call);?></span>
                        </a>
                    </span>
                <?php endif; ?>
                <?php if($order['delivery_at']): ?>
                    <span class="card-product__delivery-at">
                        <span class="label"><?=lang('label', 'delivery_at');?>:</span> <span class="time"><?=_time($order['delivery_at']);?></span>
                        <a target="_blank" class="user-infomation bg--white" href="<?=RouteMap::get('profile', ['id' => $user_ship['id']]);?>">
                            <span class="user-avatar avatar--small">
                                <img src="<?=User::get_avatar($user_ship);?>" />
                                <?=no_avatar($user_ship);?>
                            </span>
                            <span class="user-display-name"><?=User::get_username($user_ship);?></span>
                        </a>
                    </span>
                <?php endif; ?>  
                </div>   
            </div>

        </div>
        <div class="row label-ver">
            <div class="col-xs-12 col-lg-12 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'status');?></label>
                    <div class="form-control">
                        <select class="js-custom-select" data-placeholder="<?=lang('placeholder', 'enter_status');?>">
                            <option data-html="<?=_echo(orderController::render_status($order['status']));?>"></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-6 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'quantity');?></label>
                    <div class="form-control">
                        <input type="text" class="form-input" placeholder="<?=lang('placeholder', 'enter_quantity');?>" value="<?=_echo($order['quantity']);?>">
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-6 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('order', 'txt_total_price');?> (<?=_echo($order['currency']);?>)</label>
                    <div class="form-control">
                        <input type="text" class="form-input" placeholder="<?=lang('placeholder', 'enter_price');?>" value="<?=_echo($order['price']);?>">
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-6 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'first_name');?></label>
                    <div class="form-control">
                        <input type="text" class="form-input" placeholder="<?=lang('placeholder', 'enter_first_name');?>" value="<?=_echo($order['order_first_name']);?>">
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-6 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'last_name');?></label>
                    <div class="form-control">
                        <input type="text" class="form-input" placeholder="<?=lang('placeholder', 'enter_last_name');?>" value="<?=_echo($order['order_last_name']);?>">
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-12 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('order', 'txt_order_phone');?></label>
                    <div class="form-control">
                        <div class="input-group">
                            <input type="text" class="form-input order-phone" placeholder="<?=lang('placeholder', 'enter_phone');?>" value="<?=(isset($order_phone[0]) ? _echo($order_phone[0]) : null);?>">
                        </div>
                        <?php if(isset($order_phone[1])): ?>
                        <?php
                            array_shift($order_phone); foreach($order_phone as $key): ?>
                            <div class="input-group">
                                <input class="form-input" placeholder="<?=lang('placeholder', 'enter_phone');?>" type="text" value="<?=_echo($key);?>">
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-lg-12 col-xl-12">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'address');?></label>
                    <div class="form-control">
                        <textarea class="form-textarea" placeholder="<?=lang('placeholder', 'enter_address');?>"><?=_echo($order['order_address']);?></textarea>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-4 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'province');?></label>
                    <div class="form-control">
                        <select class="js-custom-select" data-placeholder="<?=lang('placeholder', 'enter_province');?>">
                            <option><?=_echo($order['order_province']);?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-4 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'district');?></label>
                    <div class="form-control">
                        <select class="js-custom-select" data-placeholder="<?=lang('placeholder', 'enter_district');?>">
                            <option><?=_echo($order['order_district']);?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-4 col-xl-4">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'ward');?></label>
                    <div class="form-control">
                        <select class="js-custom-select" data-placeholder="<?=lang('placeholder', 'enter_ward');?>">
                            <option><?=_echo($order['order_ward']);?></option>
                        </select>
                    </div>
                </div>
            </div>


            <div class="col-xs-12 col-md-12 col-xl-12">
                <div class="form-group">
                    <label class="control-label"><?=lang('label', 'area');?></label>
                    <div class="form-control">
                        <select class="js-custom-select" data-placeholder="<?=lang('placeholder', 'enter_area');?>">
                            <?php if($order['order_area']): ?>
                            <option data-html="<?=_echo('<i class="fas fa-map-marker-alt"></i> '.$order['order_area']);?>"></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <?php if($order['note_ads'] != ''): ?>
                <div class="col-xs-12 col-lg-12 col-xl-12">
                    <div class="form-group">
                        <label class="control-label"><?=lang('order', 'txt_ads_note');?></label>
                        <div class="form-control">
                            <textarea class="form-textarea" placeholder="<?=lang('placeholder', 'enter_note');?>"><?=_echo($order['note_ads']);?></textarea>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($order['note_call'] != ''): ?>
                <div class="col-xs-12 col-lg-12 col-xl-12">
                    <div class="form-group">
                        <label class="control-label"><?=lang('order', 'txt_call_note');?></label>
                        <div class="form-control">
                            <textarea class="form-textarea" placeholder="<?=lang('placeholder', 'enter_note');?>"><?=_echo($order['note_call']);?></textarea>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($order['note_ship'] != ''): ?>
                <div class="col-xs-12 col-lg-12 col-xl-12">
                    <div class="form-group">
                        <label class="control-label"><?=lang('order', 'txt_ship_note');?></label>
                        <div class="form-control">
                            <textarea class="form-textarea" placeholder="<?=lang('placeholder', 'enter_note');?>"><?=_echo($order['note_ship']);?></textarea>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="box__footer">
    <?php if($view_in_list): ?>
        <a href="<?=$view_in_list;?>" class="btn btn--round pull-right"><i class="fas fa-eye"></i> <?=lang('system', 'txt_view_in_list');?></a>
    <?php endif; ?>
        <a href="<?=$referer;?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
    </div>
</div>


<?php View::render('layout.footer'); ?>