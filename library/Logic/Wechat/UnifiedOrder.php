<?php
/**
 * 微信统一下单支付入口
 */
namespace Logic\Wechat;

class UnifiedOrder extends Base
{
    public $url = '';
    public $time_out = '';
    public function __construct()
    {
        $this->url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $this->time_out = 30;
    }
    /*
     * $params array requestParams
     * $params requestParams['appid']   //公众号id
     * $params requestParams['mch_id']  //商户ID
     * $params requestParams['nonce_str']  //随机字符串
     * $params requestParams['sign']  //签名
     * $params requestParams['body']  //商品描述
     * $params requestParams['out_trade_no']  //商户订单号
     * $params requestParams['total_fee']   //总金额
     * $params requestParams['spbill_create_ip']  //终端ip
     * $params requestParams['notify_url']  //回调地址
     * $params requestParams['trade_type']  //交易类型
     */
    public function payEntrance($requestParams)
    {
        if (empty($requestParams['appid'])) {
            throw new \Exception('公众号ID 不能为空!');
        } elseif (!filter_var($requestParams['mch_id'], FILTER_VALIDATE_INT)) {
            throw new \Exception('商户ID不能为空!');
        } elseif (!filter_var($requestParams['out_trade_no'], FILTER_VALIDATE_INT)) {
            throw new \Exception('商户订单号不能为空!');
        } elseif (empty($requestParams['total_fee'])) {
            throw new \Exception('交易金额不能为空!');
        } elseif (empty($requestParams['notify_url'])) {
            throw new \Exception('通知地址不能为空!');
        } elseif (empty($requestParams['trade_type'])) {
            throw new \Exception('交易类型不能为空!');
        } elseif (empty($requestParams['body'])) {
            throw new \Exception('商品描述信息不能为空!');
        }

        if (!is_numeric($requestParams['total_fee'])) {
            throw new \Exception('交易金额必须为数字!');
        }

        $requestParams['nonce_str'] = $this->createRandomStr();
        $requestParams['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];
        $requestParams['sign'] = $this->createSign($requestParams);
        $xml = $this->array2xml($requestParams);
        $this->postXmlCurl($xml, $this->url, $this->time_out);
    }
}