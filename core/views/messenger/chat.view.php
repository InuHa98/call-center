<?php View::render('layout.header', ['title' => $title]); ?>

<?php

echo assetController::load_css('messenger.css');

?>

<div class="section-sub-header">
    <span><?=lang('messenger', 'title');?></span>
</div>


<div class="row">
    <div class="col-xs-12 col-lg-12 col-xl-3">
        <div class="box box--list">
            <div class="box__body <?=($is_spam == true ? 'reverse' : null);?>">
                <a class="box__body-item <?=($is_spam != true ? 'active' : null);?>" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_INBOX]);?>">
                    <span class="item-icon">
                        <i class="fab fa-facebook-messenger"></i>
                    </span>
                    <div>
                        <span class="item-title"><?=lang('messenger', 'txt_list');?></span>
                    </div>
                <?php if($_count_message > 0) : ?>
                    <span class="count-new-item"><?=$_count_message;?></span>
                <?php endif; ?>
                </a>
                <a class="box__body-item <?=($is_spam == true ? 'active' : null);?>" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_SPAM]);?>">
                    <span class="item-icon">
                        <i class="fas fa-ban"></i>
                    </span>
                    <div>
                        <span class="item-title"><?=lang('messenger', 'txt_spam');?></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-lg-12 col-xl-9">
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
        <div class="messenger-chat">
            <div class="messenger-chat__user">
                <div class="messenger-chat__user-avatar user-avatar">
                    <img src="<?=User::get_avatar($user_to);?>" />
                    <?=no_avatar($user_to);?>
                </div>
                <a class="messenger-chat__user-name" href="<?=RouteMap::get('profile', ['id' => $user_to['id']]);?>">
                    <div class="margin-b-1 user-display-name"><?=User::get_username($user_to);?></div>
                    <div><?=User::get_role($user_to);?></div>
                </a>
                <div class="messenger-chat__user-actions">
                    <div class="drop-menu">
                        <button class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>



                        <ul class="drop-menu__content">
                            <?=($is_spam === true ? '
                                <li role="make-inbox">
                                    <form method="POST" id="form-make-inbox">
                                        '.Security::insertHiddenToken().'
                                        '.messengerController::insertHiddenAction(messengerController::ACTION_MAKE_INBOX).'
                                    </form>
                                    <i class="fas fa-undo"></i> '.lang('messenger', 'not_spam').'
                                </li>
                            ' : '
                                <li role="make-spam">
                                    <form method="POST" id="form-make-spam">
                                        '.Security::insertHiddenToken().'
                                        '.messengerController::insertHiddenAction(messengerController::ACTION_MAKE_SPAM).'
                                    </form>
                                    <i class="fas fa-ban"></i> '.lang('messenger', 'make_spam').'
                                </li>
                            ');?>
                        <?php if($count > 0): ?>
                            <li role="delete" class="text-danger">
                                <form method="POST" id="form-delete">
                                    <?=Security::insertHiddenToken();?>
                                    <?=messengerController::insertHiddenAction(messengerController::ACTION_DELETE_MESSAGE);?>
                                </form>
                                <i class="fa fa-trash"></i> <?=lang('messenger', 'delete');?>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="loading-message" class="messenger-chat__loading">
                <div class="messenger-chat__loading-icon animation-spinner">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="messenger-chat__loading-text"><?=lang('messenger', 'loading');?></div>
            </div>

            <div id="messages" class="messenger-chat__messages"></div>

            <form id="form-validate" class="messenger-chat__editor" method="POST">
                <?=Security::insertHiddenToken();?>
                <?=messengerController::insertHiddenAction(messengerController::ACTION_SEND_MESSAGE);?>
                <div class="form-group">
                    <div class="form-control">
                        <textarea id="editor" class="form-textarea" name="<?=messengerController::INPUT_MESSAGE;?>" placeholder="Nhập tin nhắn..." rows="1"></textarea>
                    </div>
                </div>
                <input type="submit" class="btn" value="<?=lang('messenger', 'send');?>">
            </form>
        </div>

    </div>
</div>



<script type="text/javascript" src="<?=APP_URL;?>/assets/js/tinymce/tinymce.min.js?v=<?=$_version;?>"></script>
<script type="text/javascript" src="<?=APP_URL;?>/assets/js/form-validator.js?v=<?=$_version;?>"></script>

<script type="text/javascript">


	$(document).ready(function() {

    <?php if($count > 0): ?>
        (function() {

            var current_page = 1;
            var isLoading = false;
            var isComplete = false;

            var container_message = $('#messages');
            var element_loading = $('#loading-message');
            var class_show_loading = 'show';

            var user_avatar = '<?='<img src="'.User::get_avatar($user_to).'" />'.no_avatar($user_to); ?>';

            var load_messages = function() {
                isLoading = true;

                if(isComplete == true)
                {
                    element_loading.removeClass(class_show_loading);
                    return;
                }

                element_loading.removeClass(class_show_loading).addClass(class_show_loading);


                $.ajax({
                    type: "POST",
                    url: "<?=current_url();?>",
                    data: {page: current_page},
                    dataType: 'json',
                    cache: false,
                    success: function(response) {
                        if(response.code == 200)
                        {
                            var data_messages = response.data || [];

                            var i = 0;
                            var html_message_item = '';
                            data_messages.forEach(function(message) {
                                
                                var is_break_time = message.break_time !== false,
                                    is_break_line = message.break_line == true;

                                if(is_break_time || is_break_line)
                                {
                                    if(i > 0)
                                    {
                                        html_message_item += '</div>';
                                    }

                                    if(is_break_time)
                                    {
                                        html_message_item += '<div class="message-time">' + message.time + '</div>';
                                    }

                                    if(is_break_line)
                                    {
                                        html_message_item += '<div class="message-break"></div>';
                                    }
                                    html_message_item += '<div class="message-group">';
                                }
                                
                                if(message.is_reply == true)
                                {
                                    html_message_item += '\
                                    <div class="message-item message-item--reply">\
                                        <div class="message-item__msg">\
                                            <div class="message-item__msg-time">' + message.time + '</div>\
                                            <div class="message-item__msg-text">' + message.message + '</div>\
                                            <div class="message-item__msg-seen">' + (message.seen ? '<strong><?=lang('messenger', 'seen');?></strong> ' + message.seen : '<strong><?=lang('messenger', 'sended');?></strong>') + '</div>\
                                        </div>';
                                    if(message.is_last_seen == true)
                                    {
                                        html_message_item += '<div class="message-item__last-seen user-avatar">' + user_avatar + '</div>';
                                    }
                                    else
                                    {
                                        html_message_item += '<div class="message-item__last-seen">' + (message.seen ? '' : '<i class="fas fa-check-circle"></i>') + '</div>';
                                    }
                                    html_message_item += '</div>';
                                }
                                else
                                {
                                    html_message_item += '\
                                    <div class="message-item">\
                                        <div class="message-item__avatar user-avatar">' + user_avatar + '</div>\
                                        <div class="message-item__msg">\
                                            <div class="message-item__msg-time">' + message.time + '</div>\
                                            <div class="message-item__msg-text">' + message.message + '</div>\
                                            <div class="message-item__msg-seen"><strong><?=lang('messenger', 'seen');?></strong></div>\
                                        </div>\
                                    </div>';
                                }
                                i++;
                            });

                            container_message.prepend(html_message_item);
                            if(current_page <= 1)
                            {

                                container_message.scrollTop(container_message[0].scrollHeight); 

                                container_message.on('scroll', function(e){
                                    var scrollTop = $(this).scrollTop();
                                    if(isComplete === false && scrollTop === 0 && isLoading === false)
                                    {
                                        load_messages();
                                    }
                                });
                            }
                            current_page++;
                            return;
                        }
                        else
                        {
                            isComplete = true;
                        }
                    },
                    error: function() {
                        $.toastShow('<?=lang('messenger', 'can_not_loading');?>', {
						    type: 'error',
						    timeout: 5000
					    });	
                    },
                    complete: function() {
                        isLoading = false;
                        element_loading.removeClass(class_show_loading);
                    }
                });
            };
            load_messages();

            $('#messages').on("click", ".message-item__msg-text", function() {
                var class_show = "show-info";
                var selector_item = ".message-item";
                var parent_this = $(this).parents(selector_item);
                var container = $('#messages');
                
                if(parent_this.hasClass(class_show))
                {
                    parent_this.removeClass(class_show);
                }
                else{
                    $('#messages').find(selector_item).removeClass(class_show);
                    parent_this.addClass(class_show);

                    
                    var position_y = parent_this.offset().top - container.offset().top + parent_this.outerHeight(true) + 48;
                    var diff_scroll = position_y - container.height();
                    if(diff_scroll > 0)
                    {
                        container.animate({
                            scrollTop: container.scrollTop() + diff_scroll
                        });  
                    }
                }


            });

        })();

    <?php endif; ?>

        textarea_editor({
            id: '#editor',
            theme: 'ttmanga',
            meme_sources: [],
        });

        role_click('make-spam', function() {
            $('#form-make-spam').submit();
        });

        role_click('make-inbox', function() {
            $('#form-make-inbox').submit();
        });

        role_click('delete', function() {
            $('#form-delete').submit();
        });

        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            class_message: null,
            rules: {
                '#editor': Validator.isRequired('<?=lang('messenger', 'enter_message');?>')
            }
        });
    });
</script>
<?php View::render('layout.footer'); ?>