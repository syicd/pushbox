## PUSHBOX
 - 集成第三方消息推送；
 - 完善中......
 
 
### Installation

```shell
composer require "syicd/pushbox:dev-master" -vvv
```

### Usage

配置（以 laravel 为例）
```php
    修改 env
        XM_SECRET=
        XM_PACKAGE=
```

基本使用:

```php
<?php
        //组织参数
        $param = [
            'title' => '这是消息推送的title',
            'contents' => '消息内容',
            'data' => "{'data':'这是json数据包'}",
            'target' => '目标regid',
        ];
        //实例化对象
        $xm = new xiaomi();
        $xm->_behavior();//行为:      1.透传   0.通知栏  默认: 0
        $xm->_action();//动作:        1.one  2.multi   3.all  默认:one
        $xm->_init($param);//初始化对象 并接受参数
        $xm->_push();//执行推送
        $xm->_response();//接受返回信息  json
```
