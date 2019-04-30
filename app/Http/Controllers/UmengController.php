<?php

namespace App\Http\Controllers;

require_once base_path('lib/umeng/notification/android/AndroidBroadcast.php');
require_once base_path('lib/umeng/notification/android/AndroidFilecast.php');
require_once base_path('lib/umeng/notification/android/AndroidGroupcast.php');
require_once base_path('lib/umeng/notification/android/AndroidUnicast.php');
require_once base_path('lib/umeng/notification/android/AndroidCustomizedcast.php');
require_once base_path('lib/umeng/notification/android/AndroidBroadcast.php');
require_once base_path('lib/umeng/notification/android/AndroidBroadcast.php');
require_once base_path('lib/umeng/notification/android/AndroidBroadcast.php');
require_once base_path('lib/umeng/notification/ios/IOSBroadcast.php');
require_once base_path('lib/umeng/notification/ios/IOSFilecast.php');
require_once base_path('lib/umeng/notification/ios/IOSGroupcast.php');
require_once base_path('lib/umeng/notification/ios/IOSUnicast.php');
require_once base_path('lib/umeng/notification/ios/IOSCustomizedcast.php');

class UmengController
{
    protected static $a = NULL;
    protected static $appMasterSecret = NULL;
    protected static $timestamp = NULL;
    protected static $validation_token = NULL;

    function __construct()
    {
        self::$a          = getenv('UMENG_APPKEY');
        self::$appMasterSecret = getenv('UMENG_APP_MASTER_SECRET');
        self::$timestamp       = strval(time());
    }

    static function sendAndroidUnicast($device_tokens) {
        try {
            $unicast = new \AndroidUnicast();
            $unicast->setAppMasterSecret(self::$appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           self::$a);
            $unicast->setPredefinedKeyValue("timestamp",        self::$timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $device_tokens);
            $unicast->setPredefinedKeyValue("ticker",           "Android unicast ticker");
            $unicast->setPredefinedKeyValue("title",            "Android unicast title");
            $unicast->setPredefinedKeyValue("text",             "Android unicast text");
            $unicast->setPredefinedKeyValue("after_open",       "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $unicast->setPredefinedKeyValue("production_mode", "false");
            // Set extra fields
            $unicast->setExtraField("test", "helloworld");
            print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

}