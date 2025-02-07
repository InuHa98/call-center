<?php

trait Block_auto_ban {

	private static function block_auto_ban($action = null) {
		$title = lang('admin_panel', 'system_auto_ban');

		$success = null;
		$error = null;

		$status = env(DotEnv::AUTO_BAN, 0);
		$order = abs(intval(Request::post(DotEnv::AUTO_BAN_ORDER_CHECK, env(DotEnv::AUTO_BAN_ORDER_CHECK, DotEnv::AUTO_BAN_MIN_ORDER))));
		$rpo = abs(floatval(Request::post(DotEnv::AUTO_BAN_RPO, env(DotEnv::AUTO_BAN_RPO, 100))));

		if($order < DotEnv::AUTO_BAN_MIN_ORDER) {
			$order = DotEnv::AUTO_BAN_MIN_ORDER;
		}

		if($rpo > 100) {
			$rpo = 100;
		}

		if($rpo < 0) {
			$rpo = 0;
		}

		if(Security::validate() == true)
        {
			$status = intval(Request::post(DotEnv::AUTO_BAN, 0));
			if(App::update_config([
				DotEnv::AUTO_BAN => $status,
				DotEnv::AUTO_BAN_ORDER_CHECK => $order,
				DotEnv::AUTO_BAN_RPO => $rpo
			])) {
				$success = lang('system', 'success_save');
			} else {
				$error = lang('system', 'default_error');
			}	
		}

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.system',
			'view_block' => 'admin_panel.block.system.auto_ban',
			'data' => compact(
				'success',
				'error',
				'status',
				'order',
				'rpo'
			)
		];
	}
}

?>