<?php
require_once base_path('lib/umeng/notification/IOSNotification.php');


class IOSListcast extends IOSNotification {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "listcast";
		$this->data["device_tokens"] = NULL;
	}

}