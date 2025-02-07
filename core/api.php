<?php

####### Postback #######
Router::all(RouteMap::ROUTES['postback'], function($name, $action) {
	return Controller::load('postbackController', compact('name', 'action'));
});

?>