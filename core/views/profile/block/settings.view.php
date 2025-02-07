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



    <form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <?=profileController::insertHiddenAction(Interface_controller::ACTION_SETTINGS);?>


            <div class="form-group limit--width">
				<label class="control-label"><?=lang('profile', 'limit_page');?>:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=Interface_controller::INPUT_FORM_PAGE;?>" data-max-width="500px">
                        <option <?=($limit_page == 5 ? 'selected' : null);?> value="5">5</option>
                        <option <?=($limit_page == 25 ? 'selected' : null);?> value="25">25</option>
                        <option <?=($limit_page == 50 ? 'selected' : null);?> value="50">50</option>
                        <option <?=($limit_page == 100 ? 'selected' : null);?> value="100">100</option>
                        <option <?=($limit_page == 250 ? 'selected' : null);?> value="250">250</option>
                        <option <?=($limit_page == 500 ? 'selected' : null);?> value="500">500</option>
                    </select>
				</div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label"><?=lang('label', 'language');?>:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=Interface_controller::INPUT_FORM_LANGUAGE;?>" data-max-width="500px">
                    <?php 

                        foreach(Language::list() as $lang => $name)
                        {
                            echo '<option value="'.$lang.'" '.($language == $lang ? 'selected' : null).'>'.$name.'</option>';
                        }

                    ?>
                    </select>
				</div>
			</div>


		<div class="margin-y-2">
			<button type="submit" class="btn btn--round pull-right"><?=lang('system', 'txt_save');?></button>
		</div>
	</form>



<script type="text/javascript">
	$(document).ready(function() {
        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            rules: {
                'input[type=radio]': Validator.isRequired(),
                'select': Validator.isRequired()
            }
        });
    });
</script>