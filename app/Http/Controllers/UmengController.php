<?php

namespace App\Http\Controllers;

use App\BModel;

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
    protected $appkey = NULL;
    protected $appMasterSecret = NULL;
    protected $timestamp = NULL;
    protected $validation_token = NULL;

    function __construct()
    {
        $this->appkey          = getenv('UMENG_APPKEY');
        $this->appMasterSecret = getenv('UMENG_APP_MASTER_SECRET');
        $this->timestamp       = strval(time());
    }

    /**安卓特定用户单薄
     * @param $store_id
     * @param $msg_title
     * @param $msg_text
     * @throws \Exception
     */
    function sendAndroidUnicast($store_id, $msg_title, $msg_text)
    {
        $device_tokens = BModel::getTableValue('umeng', ['store_id' => $store_id], 'device_tokens');
        try {
            $unicast = new \AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey", $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens", $device_tokens);
            $unicast->setPredefinedKeyValue("ticker", "Android unicast ticker");
            $unicast->setPredefinedKeyValue("title", $msg_title);
            $unicast->setPredefinedKeyValue("text", $msg_text);
            $unicast->setPredefinedKeyValue("after_open", "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $unicast->setPredefinedKeyValue("production_mode", getenv('PRODUCTION_MODE'));
            // Set extra fields
            $unicast->setExtraField("test", "helloworld");
            $unicast->send();
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    /**所有用户广播
     * @param $msg_title
     * @param $msg_text
     * @throws \Exception
     */
    function sendAndroidBroadcast($msg_title,$msg_text) {
        try {
            $brocast = new \AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker",           "Android broadcast ticker");
            $brocast->setPredefinedKeyValue("title",            $msg_title);
            $brocast->setPredefinedKeyValue("text",             $msg_text);
            $brocast->setPredefinedKeyValue("after_open",       "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // [optional]Set extra fields
            $brocast->setExtraField("test", "helloworld");
            $brocast->send();
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }


}