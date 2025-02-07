<?php 

class postbackController {
    const BLOCK_LANDING = 'Landing';
    const BLOCK_PURCHASE = 'Purchase';

    const ACTION_VIEW = 'View';
    const ACTION_CONVERSION = 'Conversion';

    const PARAM_ID = 'id';
    const PARAM_KEY = 'key';
    const PARAM_PRODUCT = 'product';
    const PARAM_LANDING = 'landing';
    const PARAM_ADVERTISER = 'advertiser';
    const PARAM_LAST_NAME = 'last_name';
    const PARAM_FIRST_NAME = 'first_name';
    const PARAM_PHONE = 'number_phone';
    const PARAM_ADDRESS = 'address';

    const POSTBACK_PRODUCT = '{{product_id}}';
    const POSTBACK_LANDING = '{{landing_id}}';
    const POSTBACK_ADVERTISER = '{{advertiser_id}}';

    public function __construct($name, $action = null)
	{
		$method = str_replace('-', '_', strtolower($name));
		if(!method_exists($this, $method))
		{
			return self::result(403, lang('api', 'method_not_found'));
		}
		$this->$method($action);
	}

	private static function result($code, $message, $data = null)
	{
		exit(json_encode([
			'code' => $code,
			'message' => $message,
			'data' => $data
		], JSON_PRETTY_PRINT));
	}

	private static function check_method_accept($accept_method)
	{
		if(!is_array($accept_method))
		{
			$accept_method = [strtoupper($accept_method)];
		}
		else
		{
			$accept_method = array_map(function($value) {
				return strtoupper($value);
			}, $accept_method);
		}
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		if(!in_array($method, $accept_method)) {
            exit(self::result(403, lang('api', 'access_is_denied')));
        }
        return true;
	}

    public static function build_postback($landing) {
        $url = $landing['domain'].'/'.ltrim($landing['postback'], '/');
        $url = str_replace(self::POSTBACK_LANDING, $landing['id'], $url);
        $url = str_replace(self::POSTBACK_PRODUCT, $landing['product_id'], $url);
        $url = str_replace(self::POSTBACK_ADVERTISER, Auth::$data['id'], $url);
        return $url;
    }

    private static function landing($action = null)
	{
        self::check_method_accept('get');

        $id = intval(Request::get(self::PARAM_ID, null));
        $key = trim(Request::get(self::PARAM_KEY, null));
        
        $landing = LandingPage::get([
            'id' => $id,
            'key' => $key
        ]);

        if($landing) {
            switch($action) {
                case self::ACTION_VIEW:
                    if(LandingStatistics::add_view($landing['id'])) {
                        exit(self::result(200, lang('api', 'success')));
                    }
                    break;
    
                /*
                case self::ACTION_CONVERSION:
                    if(LandingStatistics::add_conversion($landing['id'])) {
                        exit(self::result(200, lang('api', 'success')));
                    }
                    break;
                */
            }

        }
        exit(self::result(409, lang('api', 'failed')));
	}

    private static function purchase($key = null) {
        $product_id = intval(Request::post(self::PARAM_PRODUCT, null));
        $advertiser_id = intval(Request::post(self::PARAM_ADVERTISER, null));
        $landing_id = intval(Request::post(self::PARAM_LANDING, null));
        $first_name = trim(Request::post(self::PARAM_FIRST_NAME, null));
        $last_name = trim(Request::post(self::PARAM_LAST_NAME, null));
        $number_phone = Request::post(self::PARAM_PHONE, []);
        $address = trim(Request::post(self::PARAM_ADDRESS, null));

        if(!is_array($number_phone)) {
            $number_phone = [$number_phone];
        }

        $landing = LandingPage::get([
            'id' => $landing_id,
            'key' => $key
        ]);

        if($landing) {

            $advertiser = User::get([
                'id' => $advertiser_id
            ]);
    
            if($advertiser && UserPermission::is_advertisers($advertiser)) {
                
    
                $product = Product::get(['id' => $product_id]);
                if(!$product) {
                    exit(self::result(404, lang('errors', 'product_not_found')));
                }
    
                $country = Country::get(['id' => $product['country_id']]);
    
                if(!$country) {
                    exit(self::result(404, lang('errors', 'country_not_found')));
                }
    
                $number_phone = filter_phone($number_phone, $country);
    
                $data = [
                    'status' => Order::STATUS_PENDING_CONFIRM,
                    'landing_id' => $landing['id'],
                    'country_id' => $country['id'],
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'product_image' => $product['image'],
                    'product_price' => $product['price'],
                    'currency_id' => $country['currency_id'],
                    'currency_exchange_rate' => $country['exchange_rate'],
                    'currency' => $country['currency'],
                    'ads_cost' => $product['ads_cost'],
                    'delivery_cost' => $product['delivery_cost'],
                    'import_cost' => $product['import_cost'],
                    'order_first_name' => $first_name,
                    'order_last_name' => $last_name,
                    'order_phone' => $number_phone,
                    'order_address' => $address
                ];
                $team = Team::get(['id' => $advertiser['team_id']]);
                if($team) {
                    $data = array_merge($data, [
                        'ads_team_id' => $team['id'],
                        'ads_user_id' => $advertiser['id'],
                        'profit_leader_ads' => $team['profit_ads'],
                        'deduct_leader_ads' => $team['deduct_ads'],
                        'profit_member_ads' => $advertiser['profit_ads'],
                        'deduct_member_ads' => $advertiser['deduct_ads']
                    ]);               
                }
    
                if(Order::create($data)) {
                    exit(self::result(200, lang('api', 'success')));
                }
            }
        }



        exit(self::result(409, lang('api', 'failed')));
    }
}
	
	
		

?>