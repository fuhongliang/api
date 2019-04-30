<?php
require_once base_path('lib/umeng/notification/IOSNotification.php');

class IOSBroadcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}