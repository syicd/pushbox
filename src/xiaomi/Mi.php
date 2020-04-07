<?php
namespace pushbox\xiaomi;

use Illuminate\Support\Facades\App;

/**
 * Created by PhpStorm.
 * User: abner
 * Date: 17/4/5
 * Time: 下午4:40
 */
class Mi
{

    private static $_instance = null;

    //系统
    private $_system = null;

    //推送类型 默认 消息栏   1:透传
    private $_through = 0;

    /**
     * 参数初始化
     */
    public function __construct($system,$through = 0)
    {
        $this->_system = $system;
        $this->_through = $through;
    }

    /**
     * 获取视实例 -
     *
     * @param $system
     * @param $through
     * @return null|Mi
     */
    public static function getInstance($system,$through = 0)
    {
        return new self($system,$through);
    }


    /**
     * 执行推送
     *
     * @param $paramList
     * @return bool
     * @throws \Exception
     */
    public function push($paramList)
    {
        try {
            if ($this->_system == "iphone") {
                return $this->iphonePush($paramList);
            } else {
                return $this->androidPush($paramList);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 参数说明:
     *          1.title - 标题
     *          2.desc - 通知栏的descption 描述
     *          3.through - 0:通知栏  1:透传  | 默认 : 通知栏 0
     *          4.data - 数据包
     *
     * 1.如果alias是数组为批量推送
     * 2.
     *
     * @param $paramList
     * @return string
     * @throws \Exception
     */
    public function iphonePush($paramList)
    {
        try {

            if(App::environment('production')){
                Constants::setPackage(config('site.XM_IPHONE_BUNDLEID'));
                Constants::setSecret(config('site.XM_IPHONE_SECRET'));
                Constants::useOfficial();
            }else{
                Constants::setPackage(config('site.XM_IPHONE_DEV_BUNDLEID'));
                Constants::setSecret(config('site.XM_IPHONE_DEV_SECRET'));
                Constants::useSandbox();
            }

            $message = new IOSBuilder();
            $message->title($paramList['title']);
            $message->description($paramList['desc']);
            $message->soundUrl('default');// 消息铃声
            $message->badge('1');// 数字角标
            if (isset($paramList['data'])) {
                $payload = $paramList['data'];
                $payload['title'] = $paramList['title'];
                $message->extra('data', json_encode($payload));
            }
            $message->build();
            $sender = new Sender();
            if (isset($paramList['alias']) && is_array($paramList['alias'])) {
                return $sender->sendToIds($message, $paramList['alias'])->getRaw();
            } else {
                if (isset($paramList['all'])) {
                    return $sender->broadcastAll($message);
                } else {
                    return $sender->send($message, $paramList['alias'])->getRaw();
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *  参数说明:
     *          1.title - 标题
     *          2.desc - 通知栏的descption 描述
     *          3.through - 0:通知栏  1:透传  | 默认 : 通知栏 0
     *          4.data - 数据包
     *
     * 1.如果alias是数组为批量推送
     * 2.
     *
     * @param $paramList
     * @return string
     * @throws \Exception
     */
    public function androidPush($paramList)
    {
        try {
            if(App::environment('production')){
                Constants::setPackage(config('site.XM_ANDROID_PACKAGE'));
                Constants::setSecret(config('site.XM_ANDROID_SECRET'));
            }else{
                Constants::setPackage(config('site.XM_ANDROID_DEV_PACKAGE'));
                Constants::setSecret(config('site.XM_ANDROID_DEV_SECRET'));
            }
            Constants::useOfficial();

            $sender = new Sender();
            $message = new Builder();
            $message->title($paramList['title']);  // 通知栏的title
            $message->description($paramList['desc']); // 通知栏的descption
            $message->passThrough($this->_through);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
            $message->extra(Builder::notifyForeground, 1); // 应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0
            if(isset($paramList['data'])){
                $payload = json_encode($paramList['data']);
                $message->extra('data', $payload);//数据包
                $message->payload($payload); // 携带的数据，点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
            }
            $message->notifyId(4); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
            $message->notifyType(1);
            $message->build();
            $targetMessage = new TargetedMessage();
            if(isset($paramList['alias'])){
                $targetMessage->setTarget($paramList['alias'], TargetedMessage::TARGET_TYPE_REGID); // 设置发送目标。可通过regID,alias和topic三种方式发送
            }
            $targetMessage->setMessage($message);
            if (isset($paramList['alias']) && is_array($paramList['alias'])) {
                return $sender->sendToIds($message, $paramList['alias'])->getRaw();
            } else {
                if (isset($paramList['all'])) {
                    return $sender->broadcastAll($message);
                } else {
                    return $sender->send($message, $paramList['alias'])->getRaw();
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}