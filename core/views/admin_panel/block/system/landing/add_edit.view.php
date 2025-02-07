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
	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <div class="box__header"><?=$txt_description;?></div>
		<div class="box__body">
			<div class="form-group">
				<label class="control-label"><?=lang('label', 'domain');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_DOMAIN;?>" placeholder="https://" type="text" value="<?=_echo($domain);?>">
                </div>
			</div>

			<div class="form-group">
				<label class="control-label"><?=lang('label', 'postback');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_POSTBACK;?>" placeholder="?advertiser=<?=postbackController::POSTBACK_ADVERTISER;?>" type="text" value="<?=_echo($postback);?>">
					<div class="label-desc"><strong><?=postbackController::POSTBACK_PRODUCT;?></strong>: <?=lang('system_landing_page', 'desc_postback_product');?></div>
					<div class="label-desc"><strong><?=postbackController::POSTBACK_LANDING;?></strong>: <?=lang('system_landing_page', 'desc_postback_landing');?></div>
					<div class="label-desc"><strong><?=postbackController::POSTBACK_ADVERTISER;?></strong>: <?=lang('system_landing_page', 'desc_postback_advertiser');?></div>
                </div>
			</div>

			<div class="form-group">
				<label class="control-label"><?=lang('label', 'product');?></label>
                <div class="form-control">
                    <select class="js-custom-select" name="<?=adminPanelController::INPUT_PRODUCT;?>" data-placeholder="<?=lang('placeholder', 'select_product');?>" enable-search="true">
					<?php foreach($list_product as $product):
						$html_option = '
						<div class="user-infomation">
							<span class="user-avatar avatar--small">
								<img src="'.Product::get_image($product).'" />
							</span>
							<span class="user-display-name">#'.$product['id'].' - '.$product['name'].' - '.$product['country_name'].'</span>
						</div>';
					?>
						<option <?=($product['id'] == $product_id ? 'selected' : null);?> value="<?=$product['id'];?>" data-html="<?=_echo($html_option);?>"></option>
					<?php endforeach;?>
					</select>
                </div>
			</div>

			<div class="form-group">
				<label class="control-label"><?=lang('label', 'key');?></label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_KEY;?>" placeholder="<?=lang('system_landing_page', 'desc_key');?>" type="text" value="<?=_echo($key);?>">
                </div>
			</div>

		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
			<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_LANDING]);?>" class="btn btn--round btn--gray pull-right"><?=lang('system', 'txt_back');?></a>
		</div>
	</form>
</div>