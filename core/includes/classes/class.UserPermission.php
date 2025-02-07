<?php

class UserPermission {
    public const IS_ADMIN = 98;
    public const ALL_PERMS = '*';

    private static $permissions = [
        'admin_configuration' => null,
        'admin_mailer_setting' => null,
        'admin_role_edit' => null,
        'admin_role_delete' => null,
        'admin_country_add' => null,
        'admin_country_edit' => null,
        'admin_country_delete' => null,
        'admin_area_add' => null,
        'admin_area_edit' => null,
        'admin_area_delete' => null,
        'admin_product_add' => null,
        'admin_product_edit' => null,
        'admin_product_delete' => null,
        'admin_currency_add' => null,
        'admin_currency_edit' => null,
        'admin_currency_delete' => null,
        'admin_landing_add' => null,
        'admin_landing_edit' => null,
        'admin_landing_delete' => null,
        'admin_auto_ban' => null,

        'admin_user_edit' => null,
        'admin_user_ban' => null,
        'admin_user_unban' => null,

        'admin_team_add' => null,
        'admin_team_edit' => null,
        'admin_team_delete' => null,
        'admin_team_ban' => null,
        'admin_team_unban' => null,

        'order_statistics' => null,
        'landing_statistics' => null,
        'payment_team' => null,
        'order_management' => null,
        'blacklist' => null,

        'access_advertisers' => null,
        'access_caller' => null,
        'access_shipper' => null
    ];

    public static function member_default() { // quyền mặc định khi người dùng đăng kí tài khoản mới
        return [];
    }

    public static function caller_default() { // quyền mặc định khi người dùng đăng kí tài khoản mới
        return ['access_caller'];
    }

    public static function shipper_default() { // quyền mặc định khi người dùng đăng kí tài khoản mới
        return ['access_shipper'];
    }

    public static function advertiser_default() { // quyền mặc định khi người dùng đăng kí tài khoản mới
        return ['access_advertisers'];
    }

    public static function fullstack_default() { // quyền mặc định khi người dùng đăng kí tài khoản mới
        return [
            'access_advertisers',
            'access_caller',
            'access_shipper'
        ];
    }

    private static function get_user($user_id = null) {
        return $user_id == "" ? Auth::$data : (is_array($user_id) ? $user_id : User::get($user_id));
    }

    public static function isAdmin($user_id = null) {
        $user = self::get_user($user_id);

        if(!$user) {
            return false;
        }

        return $user['adm'] == self::IS_ADMIN ? true : false;
    }

    public static function list() {
        $permissions = [];
        foreach(self::$permissions as $key => $value) {
            $permissions[$key] = lang('permissions', $key);
        }
        return $permissions;
    }

    public static function has($permission, $user_id = null) {
        $user = self::get_user($user_id);
        if(!$user)
        {
            return false;
        }

        if(self::isAdmin($user))
        {
            return true;
        }

        $permissions = self::get($user);

        if(!is_array($permission))
        {
            $permission = [$permission];
        }

        foreach($permission as $perm) {
            if(array_key_exists($perm, self::$permissions) && in_array($perm, $permissions)) {
                return true;
            }            
        }

        return false;
    }

    public static function get($user_id = null) {
        $user = self::get_user($user_id);
        if(!$user)
        {
            return [];
        }

        if($user['id'] === Auth::$data['id'] && is_array(Auth::$permissions)) {
            return Auth::$permissions;
        }

        
        if(self::isAdmin($user))
        {
            return array_keys(self::$permissions);
        }



        $permissions_role = isset($user['role_perms']) ? json_decode($user['role_perms'], true) : [];


        if(in_array(self::ALL_PERMS, $permissions_role)) {
            return array_keys(self::$permissions);
        }


        $permissions = [];
		if($permissions_role)
		{
			try {
				$permissions = $permissions_role;
				if(!is_array($permissions))
				{
					$permissions = [];
				}
			} catch(Error $error){
				$permissions = [];
			}
		}

		if($user['perms'])
		{

			$permissions_user = json_decode($user['perms'], true);

			foreach ($permissions_user as $key => $value)
			{
				$check = array_search($key, $permissions);
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
				if($check === false)
				{
					if($value == true)
					{
						$permissions[] = $key;
					}
				} else
				{
					if($value != true && isset($permissions[$check]))
					{
						unset($permissions[$check]);
					}
				}
			}
		}

        $permissions = array_values($permissions);

        if($user['id'] === Auth::$data['id'] && Auth::$permissions === null) {
            Auth::$permissions = $permissions;
        }

        return $permissions;
    }

    public static function access_order_statistics($user_id = null) {
        return self::has([
            'order_statistics'
        ], $user_id);
    }

    public static function access_landing_statistics($user_id = null) {
        return self::has([
            'landing_statistics'
        ], $user_id);
    }

    public static function access_payment_team($user_id = null) {
        return self::has([
            'payment_team'
        ], $user_id);
    }

    public static function access_blacklist($user_id = null) {
        return self::has([
            'blacklist'
        ], $user_id);
    }

    public static function access_order_management($user_id = null) {
        return self::has([
            'order_management'
        ], $user_id);
    }

    public static function is_caller($user_id = null) {
        return self::has([
            'access_caller'
        ], $user_id);
    }

    public static function is_shipper($user_id = null) {
        return self::has([
            'access_shipper'
        ], $user_id);
    }

    public static function is_advertisers($user_id = null) {
        return self::has([
            'access_advertisers'
        ], $user_id);
    }

    public static function is_access_configuration($user_id = null) {
        return self::has([
            'admin_configuration'
        ], $user_id);
    }


    public static function is_access_mailer_setting($user_id = null) {
        return self::has([
            'admin_mailer_setting'
        ], $user_id);
    }

    public static function is_access_role($user_id = null) {
        return self::has([
            'admin_role_edit',
            'admin_role_delete'
        ], $user_id);
    }

    public static function is_access_country($user_id = null) {
        return self::has([
            'admin_country_add',
            'admin_country_edit',
            'admin_country_delete'
        ], $user_id);
    }

    public static function is_access_landing($user_id = null) {
        return self::has([
            'admin_landing_add',
            'admin_landing_edit',
            'admin_landing_delete'
        ], $user_id);
    }

    public static function is_access_auto_ban($user_id = null) {
        return self::has([
            'admin_auto_ban'
        ], $user_id);
    }

    public static function is_access_area($user_id = null) {
        return self::has([
            'admin_area_add',
            'admin_area_edit',
            'admin_area_delete'
        ], $user_id);
    }

    public static function is_access_product($user_id = null) {
        return self::has([
            'admin_product_add',
            'admin_product_edit',
            'admin_product_delete'
        ], $user_id);
    }

    public static function is_access_currency($user_id = null) {
        return self::has([
            'admin_currency_add',
            'admin_currency_edit',
            'admin_currency_delete'
        ], $user_id);
    }

    public static function is_access_user_list($user_id = null) {
        return self::has([
            'admin_user_edit',
            'admin_user_ban'
        ], $user_id);
    }

    public static function is_access_user_ban_list($user_id = null) {
        return self::has([
            'admin_user_unban'
        ], $user_id);
    }

    public static function is_access_team_list($user_id = null) {
        return self::has([
            'admin_team_add',
            'admin_team_delete',
            'admin_team_edit',
            'admin_team_ban'
        ], $user_id);
    }


    public static function is_access_team_ban_list($user_id = null) {
        return self::has([
            'admin_team_unban'
        ], $user_id);
    }
    

    public static function is_access_group_system($user_id = null) {
        return self::has([
            'admin_configuration',
            'admin_mailer_setting',
            'admin_role_edit',
            'admin_role_delete',
            'admin_country_add',
            'admin_country_edit',
            'admin_country_delete',
            'admin_area_add',
            'admin_area_edit',
            'admin_area_delete',
            'admin_product_add',
            'admin_product_edit',
            'admin_product_delete',
            'admin_currency_add',
            'admin_currency_edit',
            'admin_currency_delete',
            'admin_landing_add',
            'admin_landing_edit',
            'admin_landing_delete',
            'admin_auto_ban'
        ], $user_id);
    }
   
    public static function is_access_group_user($user_id = null) {
        return self::has([
            'admin_user_edit',
            'admin_user_ban',
            'admin_user_unban'
        ], $user_id);
    }

    public static function is_access_group_team($user_id = null) {
        return self::has([
            'admin_team_edit',
            'admin_team_ban',
            'admin_team_unban'
        ], $user_id);
    }
    
    public static function isAccessAdminPanel($user_id = null) {
        $user = self::get_user($user_id);

        if(self::is_access_group_system($user) == true) {
            return true;
        }
        if(self::is_access_group_user($user) == true) {
            return true;
        }
        if(self::is_access_group_team($user) == true) {
            return true;
        }
        return false;
    }

}

?>