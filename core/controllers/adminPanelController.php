<?php


class adminPanelController {
	use Block_configuration;
	use Block_mailer;
	use Block_role;
	use Block_country;
	use Block_area;
	use Block_currency;
	use Block_province;
	use Block_district;
	use Block_ward;
	use Block_product;
	use Block_landing;
	use Block_auto_ban;
	use Block_user_list;
	use Block_user_ban_list;
	use Block_team_list;
	use Block_team_ban_list;


	const GROUP_SYSTEM = 'System';
	const GROUP_USER = 'Users';
	const GROUP_TEAM = 'Team';

	const BLOCK_CONFIGURATION = 'Configuration';
	const BLOCK_MAILER = 'Mailer';
	const BLOCK_ROLE = 'Role';
	const BLOCK_CURRENCY = 'Currency';
	const BLOCK_COUNTRY = 'Country';
	const BLOCK_AREA = 'Area';
	const BLOCK_PROVINCE = 'Province';
	const BLOCK_DISTRICT = 'District';
	const BLOCK_WARD = 'Ward';
	const BLOCK_PRODUCT = 'Product';
	const BLOCK_LANDING = 'Landing';
	const BLOCK_AUTO_BAN = 'Auto-ban';
	const BLOCK_USER_LIST = 'List';
	const BLOCK_USER_BAN_LIST = 'Ban-List';
	const BLOCK_TEAM_LIST = 'List';
	const BLOCK_TEAM_BAN_LIST = 'Ban-List';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';


	public const INPUT_MAILER_SMTP_AUTHENTICATION = 'smpt_authencation';
	public const INPUT_MAILER_SMTP_USERNAME = 'smpt_username';
	public const INPUT_MAILER_SMTP_PASSWORD = 'smpt_apassword';
	public const INPUT_MAILER_SMTP_HOST = 'smpt_host';
	public const INPUT_MAILER_SMTP_SECURE = 'smpt_secure';
	public const INPUT_MAILER_SMTP_PORT = 'smpt_port';
	public const INPUT_MAILER_API_SERVER = 'api_server';
	public const INPUT_MAILER_API_KEY = 'api_key';
	public const INPUT_MAILER_API_SECRET = 'api_secret';


	public const INPUT_ID = 'id';
	public const INPUT_KEY = 'key';
	public const INPUT_NOTE = 'note';
	public const INPUT_STATUS = 'status';
	public const INPUT_DOMAIN = 'domain';
	public const INPUT_POSTBACK = 'postback';
	public const INPUT_NAME = 'name';
	public const INPUT_DESC = 'desc';
	public const INPUT_TEXT = 'text';
	public const INPUT_IMAGE = 'image';
	public const INPUT_CODE = 'code';
	public const INPUT_PHONE_CODE = 'phone_code';
	public const INPUT_AREA = 'area';
	public const INPUT_COOKIE = 'cookie';
	public const INPUT_COLOR = 'color';
	public const INPUT_LEVEL = 'level';
	public const INPUT_PERMISSION = 'perms';
	public const INPUT_ACTION = '_action';
	public const INPUT_USERNAME = 'username';
	public const INPUT_PASSWORD = 'password';
	public const INPUT_LEADER = 'own';
	public const INPUT_CONFIG = 'config';
	public const INPUT_REASON = 'reason';
	public const INPUT_PASSWORD_CONFIRM = 'password_confirm';
	public const INPUT_EMAIL = 'email';
	public const INPUT_ROLE = 'role';
	public const INPUT_ALL = 'all';
	public const INPUT_COUNTRY = 'country';
	public const INPUT_PRICE = 'price';
	public const INPUT_STOCK = 'stock';
	public const INPUT_ADS_COST = 'ads_cost';
	public const INPUT_DELIVERY_COST = 'delivery_cost';
	public const INPUT_IMPORT_COST = 'import_cost';
	public const INPUT_CURRENCY = 'currency';
	public const INPUT_RATE = 'rate';
	public const INPUT_TYPE = 'type';
	public const INPUT_PRODUCT = 'product';
	public const INPUT_PROFIT_ADS = 'profit_ads';
	public const INPUT_DEDUCT_ADS = 'deduct_ads';
	public const INPUT_PROFIT_CALL = 'profit_call';
	public const INPUT_DEDUCT_CALL = 'deduct_call';
	public const INPUT_PROFIT_SHIP = 'profit_ship';
	public const INPUT_DEDUCT_SHIP = 'deduct_ship';
	public const INPUT_TEAM = 'team';

	public const ACTION_CHANGE_STATUS = 'change_status';
	public const ACTION_CHANGE_NAME = 'change_name';
	public const ACTION_CHANGE_USERNAME = 'change_username';
	public const ACTION_CHANGE_PASSWORD = 'change_password';
	public const ACTION_CHANGE_EMAIL = 'change_email';
	public const ACTION_CHANGE_ROLE = 'change_role';
	public const ACTION_CHANGE_LEADER = 'change_leader';
	public const ACTION_CHANGE_PERMISSION = 'change_permission';
	public const ACTION_BAN = 'ban';
	public const ACTION_UNBAN = 'unban';
	public const ACTION_CHECK_CONFIG = 'check_config';
	public const ACTION_CHANGE_CONFIG = 'change_config';
	public const ACTION_ACCEPT = 'accept';
	public const ACTION_REJECT = 'reject';
	public const ACTION_SET_TEAM = 'set_team';


	public function index($group, $block = null, $action = null)
	{
		Language::load('admin_panel.lng');
		$re_check_permission = true;

		$group_name = null;
		$block_name = null;
		$block_view = null;

		$notification_approval_team = 0;

		switch($group) {
			case self::GROUP_SYSTEM:
			case self::GROUP_USER:
			case self::GROUP_TEAM:
				$group_name = $group;
				break;
			default:
				if(UserPermission::is_access_group_system()) {
					$group_name = self::GROUP_SYSTEM;
				} else if(UserPermission::is_access_group_user()) {
					$group_name = self::GROUP_USER;
				} else if(UserPermission::is_access_group_team()) {
					$group_name = self::GROUP_TEAM;
				}
				break;
		}

		if($group_name == null) {
			return ServerErrorHandler::error_404();
		}

		switch($block) {
			case self::BLOCK_CONFIGURATION:
			case self::BLOCK_MAILER:
			case self::BLOCK_ROLE:
			case self::BLOCK_COUNTRY:
			case self::BLOCK_AREA:
			case self::BLOCK_CURRENCY:
			case self::BLOCK_PROVINCE:
			case self::BLOCK_DISTRICT:
			case self::BLOCK_WARD:
			case self::BLOCK_PRODUCT:
			case self::BLOCK_LANDING:
			case self::BLOCK_AUTO_BAN:
			case self::BLOCK_USER_LIST:
			case self::BLOCK_USER_BAN_LIST:		
			case self::BLOCK_TEAM_LIST:
			case self::BLOCK_TEAM_BAN_LIST:

				$block_name = $block;
				break;
							
			default:
				switch($group_name) {
					case self::GROUP_SYSTEM:
						if(UserPermission::is_access_configuration()) {
							$block_name = self::BLOCK_CONFIGURATION;
						}
						else if(UserPermission::is_access_mailer_setting()) {
							$block_name = self::BLOCK_MAILER;
						}
						else if(UserPermission::is_access_role()) {
							$block_name = self::BLOCK_ROLE;
						}
						else if(UserPermission::is_access_currency()) {
							$block_name = self::BLOCK_CURRENCY;
						}
						else if(UserPermission::is_access_country()) {
							$block_name = self::BLOCK_COUNTRY;
						}
						else if(UserPermission::is_access_area()) {
							$block_name = self::BLOCK_AREA;
						}
						else if(UserPermission::is_access_product()) {
							$block_name = self::BLOCK_PRODUCT;
						}
						else if(UserPermission::is_access_landing()) {
							$block_name = self::BLOCK_LANDING;
						}
						else if(UserPermission::is_access_auto_ban()) {
							$block_name = self::BLOCK_AUTO_BAN;
						}
						break;

					case self::GROUP_USER:
						if(UserPermission::is_access_user_list()) {
							$block_name = self::BLOCK_USER_LIST;
						}
						else if(UserPermission::is_access_user_ban_list()) {
							$block_name = self::BLOCK_USER_BAN_LIST;
						}
						break;

					case self::GROUP_TEAM:
						if(UserPermission::is_access_team_list()) {
							$block_name = self::BLOCK_TEAM_LIST;
						}
						else if(UserPermission::is_access_team_ban_list()) {
							$block_name = self::BLOCK_TEAM_BAN_LIST;
						}
						break;
				}
				$re_check_permission = false;
				break;
		}

		if($block_name == null) {
			return ServerErrorHandler::error_404();
		}

		if($group_name == self::GROUP_SYSTEM) {

			switch($block_name) {
				case self::BLOCK_CONFIGURATION:
					if($re_check_permission && !UserPermission::is_access_configuration()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_configuration($action);
					break;
	
				case self::BLOCK_MAILER:
					if($re_check_permission && !UserPermission::is_access_mailer_setting()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_mailer($action);
					break;
	
				case self::BLOCK_ROLE:
					if($re_check_permission && !UserPermission::is_access_role()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_role($action);
					break;

				case self::BLOCK_COUNTRY:
					if($re_check_permission && !UserPermission::is_access_country()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_country($action);
					break;

				case self::BLOCK_AREA:
					if($re_check_permission && !UserPermission::is_access_area()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_area($action);
					break;

				case self::BLOCK_CURRENCY:
					if($re_check_permission && !UserPermission::is_access_currency()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_currency($action);
					break;

				case self::BLOCK_PROVINCE:
					if($re_check_permission && !UserPermission::is_access_country()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_province($action);
					break;

				case self::BLOCK_DISTRICT:
					if($re_check_permission && !UserPermission::is_access_country()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_district($action);
					break;

				case self::BLOCK_WARD:
					if($re_check_permission && !UserPermission::is_access_country()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_ward($action);
					break;

				case self::BLOCK_PRODUCT:
					if($re_check_permission && !UserPermission::is_access_product()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_product($action);
					break;

				case self::BLOCK_LANDING:
					if($re_check_permission && !UserPermission::is_access_landing()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_landing($action);
					break;
				case self::BLOCK_AUTO_BAN:
					if($re_check_permission && !UserPermission::is_access_auto_ban()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_auto_ban($action);
					break;
			}

		} else if($group_name == self::GROUP_USER) {
			
			switch($block_name) {
				case self::BLOCK_USER_LIST:
					if($re_check_permission && !UserPermission::is_access_user_list()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_user_list($action);
					break;
	
				case self::BLOCK_USER_BAN_LIST:
					if($re_check_permission && !UserPermission::is_access_user_ban_list()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_user_ban_list($action);
					break;
			}

		} else if($group_name == self::GROUP_TEAM) {


			switch($block_name) {
				case self::BLOCK_TEAM_LIST:
					if($re_check_permission && !UserPermission::is_access_team_list()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_team_list($action);
					break;
	

				case self::BLOCK_TEAM_BAN_LIST:
					if($re_check_permission && !UserPermission::is_access_team_ban_list()) {
						return ServerErrorHandler::error_404();
					}

					$block_view = self::block_team_ban_list($action);
					break;
			}

		}


		if(!$block_view) {
			return ServerErrorHandler::error_404();
		}

		$title = $block_view['title'].' - '.lang('system', 'admin_panel');


		return View::render('admin_panel.index', compact(
			'title',
			'group_name',
			'block_name',
			'block_view',
			'notification_approval_team'
		));
	}
}





?>