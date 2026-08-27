<?php

namespace ynddddd\Wechat;


use Throwable;

class WechatException extends \Exception
{
    protected $raw;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $raw = null)
    {
        $this->raw = $raw;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取原始信息
     * @return mixed
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
