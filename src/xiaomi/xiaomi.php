<?php
namespace pushbox\xiaomi;

/**
 * Created by PhpStorm.
 * User: abner
 * Date: 17/4/5
 * Time: 下午4:40
 */
class xiaomi
{

    //行为
    protected static $_behavior = null;

    //动作
    protected static $_action = null;

    //参数集合
    protected static $_paramList = [];

    //message 对象
    protected static $_messageObj = null;

    //返回值
    protected static $_response = null;

    public function __construct()
    {
    }

    /**
     * 小米推送行为
     *
     * 行为:      1.透传   0.通知栏  默认: 0
     *
     * @param $behavior
     * @return integer
     */
    public function _behavior($behavior = false)
    {
        self::$_behavior = intval($behavior);
    }

    /**
     * 小米监测动作
     *
     * 动作: 1.one  2.multi   3.all  默认:one
     *
     * @param $action
     * @return integer
     */
    public function _action($action = "one")
    {
        return self::$_action = $action;
    }

    /**
     * 小米
     *
     * 接受参数
     *
     * @param $paramList
     */
    public function _init($paramList = [])
    {
        if (empty($paramList)) {
            throw new \Exception("参数未知");
        }
        self::$_paramList = $paramList;

        $systemMessage = null;
        //获取设备类型
        if ($paramList['system'] == "android") {
            Constants::setPackage(config('site.XM_ANDROID_SECRET'));
            Constants::setSecret(config('site.XM_ANDROID_PACKAGE'));
            $systemMessage = $this->android();
        }
        if ($paramList['system'] == "iphone") {
            Constants::setBundleId(config('site.XM_IPHONE_BUNDLEID'));
            Constants::setSecret(config('site.XM_IPHONE_SECRET'));
            $systemMessage = $this->iphone();
        }
        if ($systemMessage == null) {
            throw new \Exception("未知的系统设备");
        }
        //组装message
        $message = $systemMessage;
        //动作
        $targetMessage = new TargetedMessage();
        switch (self::$_action) {
            case "one":
                $targetMessage->setTarget($paramList['target'], TargetedMessage::TARGET_TYPE_REGID); // 设置发送目标。可通过regID,alias和topic三种方式发送
                break;
            case "multi":
                $targetMessage->setTarget($paramList['target'], TargetedMessage::TARGET_TYPE_REGID); // 设置发送目标。可通过regID,alias和topic三种方式发送
                break;
            case "all":
                $targetMessage->setTarget($paramList['target'], TargetedMessage::TARGET_TYPE_REGID); // 设置发送目标。可通过regID,alias和topic三种方式发送
                break;
            default;
        }
        $targetMessage->setMessage($message);
        self::$_messageObj = $message;
    }

    public function _push()
    {
        $sender = new Sender();
        if (self::$_action == "one") {
            self::$_response = $sender->send(self::$_messageObj, self::$_paramList['target'])->getRaw();
        }
        if (self::$_action == "multi") {
            self::$_response = $sender->sendToIds(self::$_messageObj, self::$_paramList['target'])->getRaw();
        }
        if (self::$_action == "all") {
            self::$_response = $sender->broadcastAll(self::$_messageObj)->getRaw();
        }
    }

    public function _response()
    {
        return self::$_response;
    }


    public function android()
    {
        $message = new Builder();
        $message->passThrough(self::$_behavior);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
        if (isset(self::$_paramList['title'])) {
            $message->title(self::$_paramList['title']);//通知栏 标题
        }
        if (isset(self::$_paramList['contents'])) {
            $message->description(self::$_paramList['contents']);//通知栏 内容
        }
        if (isset(self::$_paramList['data'])) {
            $message->payload(self::$_paramList['data']);//数据包
        }
        if (isset(self::$_paramList['data'])) {
            $message->extra('data',json_encode(self::$_paramList['data']));//数据包
        }
        $message->extra(Builder::notifyForeground, 1); // 应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0
        $message->notifyId(4); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message->build();
        return $message;
    }

    public function iphone()
    {
        $message = new IOSBuilder();
        if (isset(self::$_paramList['title'])) {
            $message->title(self::$_paramList['title']);//通知栏 内容
        }
        if (isset(self::$_paramList['contents'])) {
            $message->description(self::$_paramList['contents']);//通知栏 内容
        }
        $message->soundUrl('default');// 消息铃声
        $message->badge('4');// 数字角标
        if (isset(self::$_paramList['data'])) {
            $message->extra('data',json_encode(self::$_paramList['data']));//数据包
        }
        $message->build();
        return $message;
    }

}