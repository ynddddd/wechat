<?php



namespace ynddddd\Wechat;


abstract class WechatBase
{
    /**
     * @return WechatHttpClient
     */
    public function getClient()
    {
        return new WechatHttpClient();
    }

    /**
     * @param array $result
     * @return array
     */
    abstract protected function getClientResult($result);
}
