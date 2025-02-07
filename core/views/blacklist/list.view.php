<?php View::render('layout.header', compact('title')); ?>


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
            <i class="fas fa-ban"></i>
            <span class="padding-x-2"><?=lang('system', 'blacklist');?></span>
        </span>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('blacklist', ['action' => blacklistController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> <?=lang('button', 'add');?></a>
        </div>
    </div>
    <div class="box__body">

        <form id="form-filter" method="GET" class="filter-bar">

            <div class="form-control">
                <div class="input-group">
                    <input id="filter-keyword" type="text" class="form-input" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="<?=lang('label', 'order_phone');?>" value="<?=_echo($filter_keyword);?>">
                    <div class="input-group-append">
                    <?php if($filter_keyword != ''): ?>
                        <button type="button" id="clear-keyword" class="btn btn-outline-default btn-dim"><i class="fas fa-times"></i></button>
                    <?php else: ?>
                        <button type="button" id="submit-keyword" class="btn btn-outline-default btn-dim"><i class="fas fa-search"></i></button>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-control">
                <select id="filter-country" class="js-custom-select" name="<?=InterFaceRequest::COUNTRY;?>" data-placeholder="<?=lang('system', 'select_country');?>" enable-search="true">
                    <option value="<?=InterFaceRequest::OPTION_ALL;?>"><?=lang('system', 'all_country');?></option>
                <?php foreach($list_country as $country): ?>
                    <option <?=($country['id'] == $filter_country ? 'selected' : null);?> value="<?=$country['id'];?>"><?=_echo($country['name']);?></option>
                <?php endforeach;?>
                </select>
            </div>


            <div class="form-control">
                <button type="submit" class="btn"><i class="fas fa-filter"></i> <?=lang('system', 'filter');?></button>
                <button type="button" class="btn btn-outline-gray" id="clear-filter"><?=lang('system', 'clear_filter');?></button>
            </div>

        </form>


    <?php if($list_blacklist): ?>
        <div class="margin-b-2">
        <?=lang('blacklist', 'txt_count', ['count' => $count]);?>
        </div>
        <div class="table-scroll">
    <table class="table-statistics table-sort">
        <thead>
            <tr>
                <th></th>
                <th><?=lang('label', 'created_at');?></th>
                <th><?=lang('label', 'order_phone');?></th>
                <th><?=lang('label', 'country');?></th>
                <th width="100%"><?=lang('label', 'reason');?></th>
                <th><?=lang('label', 'ban_by');?></th>
            </tr>
        </thead>
        <tbody>

    <?php foreach($list_blacklist as $blacklist):
        
        $user_ban = [
            'id' => $blacklist['user_id'],
            'username' => $blacklist['user_username'],
            'avatar' => $blacklist['user_avatar'],
            'is_ban' => $blacklist['user_is_ban']
        ];


    ?>
            <tr>

                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content" data-id="<?=$blacklist['id'];?>">
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $blacklist['id']], 'blacklist', ['action' => blacklistController::ACTION_EDIT]);?>">
                                    <i class="fa fa-edit"></i> <?=lang('button', 'edit');?>
                                </a>
                            </li>
                            <li class="border-top text-danger">
                                <a role="delete" href="<?=RouteMap::build_query([InterFaceRequest::ID => $blacklist['id']], 'blacklist', ['action' => blacklistController::ACTION_DELETE]);?>">
                                    <i class="fa fa-trash"></i> <?=lang('button', 'delete');?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>

                <td class="nowrap">
                    <span class="time"><?=_time($blacklist['created_at']);?></span>
                </td>

                <td class="nowrap">
                <?php foreach(json_decode($blacklist['number_phone'], true) as $phone): ?>
                    <span class="badge btn-outline-gray btn-dim"><i class="fas fa-phone"></i> <?=_echo($phone);?></span>
                <?php endforeach; ?>
                </td>
                
                <td class="nowrap">
                    <i class="fas fa-globe-americas"></i> <?=_echo($blacklist['country_name']);?>
                </td>

                <td class="nowrap">
                    <?=_echo($blacklist['reason']);?>
                </td>

                <td>
                <?php if($user_ban['id']): ?>
                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_ban['id']]);?>">
                        <span class="user-avatar avatar--small">
                            <img src="<?=User::get_avatar($user_ban);?>" />
                            <?=no_avatar($user_ban);?>
                        </span>
                        <span class="user-display-name"><?=User::get_username($user_ban);?></span>
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
        <div class="alert"><?=lang('blacklist', 'empty');?></div>
    <?php endif; ?>
    </div>

</div>


<script type="text/javascript">
	$(document).ready(function() {


        const form_filter = $('#form-filter');
        const clear_filter = $('#clear-filter');
        const clear_keyword = $('#clear-keyword');
        const submit_keyword = $('#submit-keyword');
        const filter_keyword = $('#filter-keyword');
        const filter_country = $('#filter-country');



        clear_filter.on('click', function(e) {
            e.preventDefault();
            filter_keyword.val('');
            filter_country.val(filter_country.find('option:first').val()).change();
            form_filter.submit();
        });

        clear_keyword.on('click', function() {
            filter_keyword.val('');
            form_filter.submit();
        });

        submit_keyword.on('click', function() {
            form_filter.submit();
        });
        
        $('[role="delete"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('Xoá khỏi danh sách đen', 'Bạn thực sự muốn xoá mục này khỏi danh sách đen?') == true) {
                window.location.href = $(this).attr('href');
            }
        });
	});
</script>

<?php View::render('layout.footer'); ?>