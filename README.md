# PHP Wechat SDK

微信公众号 / 小程序 PHP SDK，提供接口调用、支付与订阅消息封装。

## 安装

```bash
composer require ynddddd/wechat
```

## 要求

- doctrine/cache `~1.3,<1.7`
- guzzlehttp/guzzle `~6.0`

## 快速开始

```php
require __DIR__ . '/vendor/autoload.php';

use ynddddd\Wechat\Wechat;

$wechat = new Wechat([
    'appId' => 'your-app-id',
    'appSecret' => 'your-app-secret',
]);

$accessToken = $wechat->getAccessToken();
```

`getAccessToken(true)` 可强制刷新，不走缓存。

## 缓存配置

access token 默认使用文件缓存，也可切换到 Redis、Memcached 或 APCu。

```php
use ynddddd\Wechat\Wechat;

$wechat = new Wechat([
    'appId' => 'your-app-id',
    'appSecret' => 'your-app-secret',
    'cache' => [
        'target' => Wechat::CACHE_TARGET_REDIS,
        'host' => '127.0.0.1',
        'port' => 6379,
        // 'password' => '',
    ],
]);
```

可用的 `target`：

| 常量 | 值 | 说明 |
| --- | --- | --- |
| `Wechat::CACHE_TARGET_FILE` | `file` | 文件缓存（默认），可用 `dir` 指定目录 |
| `Wechat::CACHE_TARGET_REDIS` | `redis` | 需要 `redis` 扩展 |
| `Wechat::CACHE_TARGET_MEMCACHED` | `memcached` | 需要 `memcached` 扩展 |
| `Wechat::CACHE_TARGET_APCU` | `apcu` | 需要 `apcu` 扩展 |

## 接口调用

`WechatApi` 用于调用任意微信接口，自动附加 access token 并统一处理错误码。

```php
use ynddddd\Wechat\WechatApi;

$api = new WechatApi($wechat);

$result = $api->apiGet('https://api.weixin.qq.com/cgi-bin/user/get');
$result = $api->apiPost('https://api.weixin.qq.com/cgi-bin/menu/create', $data);
```

## 小程序登录

用 `wx.login` 返回的 `code` 换取 session 信息（含 `openid`、`session_key`）：

```php
$session = $wechat->jsCodeToSession($code);
$openid = $session['openid'];
```

解密 `wx.getUserInfo` 返回的加密数据：

```php
$userInfo = $wechat->decryptData($encryptedData, $iv, $code);
```

## 微信支付

```php
use ynddddd\Wechat\WechatPay;

$pay = new WechatPay([
    'appId' => 'your-app-id',
    'mchId' => 'your-mch-id',
    'key' => 'your-api-key',
    // 退款、企业付款等需要证书
    'certPemFile' => '/path/to/apiclient_cert.pem',
    'keyPemFile' => '/path/to/apiclient_key.pem',
]);

$result = $pay->unifiedOrder([
    'body' => '商品描述',
    'out_trade_no' => 'ORDER20260827001',
    'total_fee' => 1,
    'trade_type' => 'JSAPI',
    'openid' => 'user-openid',
    'notify_url' => 'https://example.com/notify',
]);
```

支持的方法：

| 方法 | 说明 |
| --- | --- |
| `unifiedOrder` | 统一下单 |
| `orderQuery` | 查询订单 |
| `closeOrder` | 关闭订单 |
| `refund` | 申请退款（需证书） |
| `refundQuery` | 查询退款 |
| `transfers` | 企业付款（需证书） |
| `getTransferInfo` | 查询企业付款 |
| `payBank` | 企业付款到银行卡（需证书） |
| `queryBank` | 查询企业付款到银行卡 |

回调验签：

```php
$pay->validateSignByXmlResult($xml);      // 处理微信回调 XML
$pay->validateSignByArrayResult($array);
```

## 订阅消息

```php
use ynddddd\Wechat\WechatSubscribe;

$subscribe = new WechatSubscribe($wechat);

// 添加模板
$tid = 434;                    // 模板标题 id，可通过接口或小程序后台获取
$kidList = [6, 5, 9, 1];       // 模板关键词列表，可用 getPubTemplateKeyWordsById 获取
$sceneDesc = '下单成功通知';     // 服务场景描述，非必填
$result = $subscribe->addTemplate($tid, $kidList, $sceneDesc);

// 发送订阅消息
$subscribe->send([
    'touser' => 'user-openid',
    'template_id' => 'your-template-id',
    'page' => 'pages/index/index',
    'data' => [
        'thing1' => ['value' => '订单已发货'],
    ],
]);
```

其他方法：

| 方法 | 说明 |
| --- | --- |
| `getCategory` | 获取小程序类目 |
| `getPubTemplateTitleList` | 获取类目下的公共模板标题 |
| `getPubTemplateKeyWordsById` | 获取模板标题下的关键词 |
| `getTemplateList` | 获取已添加的模板列表 |
| `deleteTemplate` | 删除模板 |

## 异常处理

所有接口错误都会抛出 `ynddddd\Wechat\WechatException`。

```php
use ynddddd\Wechat\WechatException;

try {
    $accessToken = $wechat->getAccessToken();
} catch (WechatException $e) {
    echo $e->getMessage();
}
```

## 许可

[Apache-2.0](LICENSE)