<?php

interface Interface_controller {
    const INPUT_FORM_ACTION = 'input_action';
    const INPUT_FORM_DATA_IMAGE = 'input_data_image';
    const INPUT_FORM_NAME = 'input_name';
    const INPUT_FORM_DATE_OF_BIRTH = 'input_date_of_birth';
    const INPUT_FORM_SEX = 'input_sex';
    const INPUT_FORM_EMAIL = 'input_email';
    const INPUT_FORM_USERNAME = 'input_username';
    const INPUT_FORM_FACEBOOK = 'input_facebook';
    const INPUT_FORM_BIO = 'input_bio';

    const INPUT_FORM_AUTH_SESSION = 'input_auth_session';

    const INPUT_BILLING_ACCOUNT_NAME = 'account_name';
    const INPUT_BILLING_ACCOUNT_NUMBER = 'account_number';
    const INPUT_BILLING_BANK_NAME = 'bank_name';
    const INPUT_BILLING_BANK_BRANCH = 'bank_branch';

    const INPUT_FORM_LANGUAGE = 'input_language';
    const INPUT_FORM_PAGE = 'input_page';

    const INPUT_FORM_PASSWORD = 'input_password';
    const INPUT_FORM_NEW_PASSWORD = 'input_new_password';
    const INPUT_FORM_CONFIRM_PASSWORD = 'input_confirm_password';

    const ACTION_UPLOAD_IMAGE = 'action_upload_image';
    const ACTION_INFOMATION = 'action_infomation';
    const ACTION_BILLING = 'action_billing';
    const ACTION_CHANGEPASSWORD = 'action_changepassword';
    const ACTION_LOGINDEVICE = 'action_logindevice';
    const ACTION_SETTINGS = 'action_settings';

    const ACTION_CHANGE_EMAIL = 'action_change_email';
    const ACTION_CHANGE_USERNAME = 'action_change_username';
    const ACTION_LOGOUT_DEVICE = 'action_logout_device';
    const ACTION_LOGOUT_ALL = 'action_logout_all';
}



?>