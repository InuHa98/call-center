<?php

####### index #######
Router::all(['/', RouteMap::ROUTES['dashboard']], function() {
	return Controller::load('dashboardController@index');
}, 'dashboard');

####### Ajax #######
Router::all(RouteMap::ROUTES['ajax'], function($name, $action) {
	return Controller::load('ajaxController', compact('name', 'action'));
});

####### auth ########
Router::match(['GET', 'POST'], RouteMap::ROUTES['login'], function(){
	return Controller::load('authController@login');
}, 'is_auth_route');

Router::get(RouteMap::ROUTES['logout'], function(){
	return Controller::load('authController@logout');
}, 'is_auth_route');

Router::match(['GET', 'POST'], RouteMap::ROUTES['change_password'], function(){
	return Controller::load('authController@change_password');
});

Router::group(RouteMap::ROUTES['forgot_password'], function(){

	Router::match(['GET', 'POST'], '/', function(){
		return Controller::load('authController@forgot_password_request');
	});

	Router::match(['GET', 'POST'], '/{key}', function($key) {
		return Controller::load('authController@forgot_password_change', $key);
	});
}, 'is_auth_route');

####### profile #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['profile'], function($id, $block, $action){
	$id = strtolower($id);

	if($id == 'me')
	{
		return Controller::load('profileController@me', compact('block', 'action'));
	}

	return Controller::load('profileController@user', compact('id', 'block'));

});

####### Notification #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['notification'], function($id) {
	return Controller::load('notificationController@index', ['id' => $id]);
});

####### Admin-Panel #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['admin_panel'], function($group, $block = null, $action = null) {
	return Controller::load('adminPanelController@index', compact('group', 'block', 'action'));
}, 'admin_panel');


####### Team #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['team'], function($id, $block, $action){
	return Controller::load('teamController@index', compact('id', 'block', 'action'));
}, 'team');


####### Advertiser #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['advertiser'], function($action){
	return Controller::load('advertiserController@index', compact('action'));
}, 'advertiser');

####### caller #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['caller'], function($block, $action){
	return Controller::load('callerController@index', compact('block', 'action'));
}, 'caller');

####### shipper #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['shipper'], function($block, $action){
	return Controller::load('shipperController@index', compact('block', 'action'));
}, 'shipper');

####### Order #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['order'], function($block, $action){
	return Controller::load('orderController@index', compact('block', 'action'));
}, 'order');

####### Messenger #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['messenger'], function($block, $id) {
	return Controller::load('messengerController@index', ['block' => $block, 'id' => $id]);
});

####### Statistic #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['statistics'], function($block) {
	return Controller::load('statisticsController@index', ['block' => $block]);
});

####### Payment #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['payment'], function($block, $action) {
	return Controller::load('paymentController@index', ['block' => $block, 'action' => $action]);
}, 'payment');

####### Blacklist #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['blacklist'], function($action) {
	return Controller::load('blacklistController@index', ['action' => $action]);
}, 'blacklist');

####### Order Management #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['order_management'], function($block, $action){
	return Controller::load('orderManagementController@index', compact('block', 'action'));
}, 'order_management');

?>