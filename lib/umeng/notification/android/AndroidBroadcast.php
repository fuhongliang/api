<?php

require_once base_path('lib/umeng/notification/AndroidNotification.php');

class AndroidBroadcast extends AndroidNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}