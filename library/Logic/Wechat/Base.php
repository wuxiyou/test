<?php

/**
 * 微信基类
 * options
 *
 * @return void
 *
 * @author  wxy
 */

namespace Logic\Wechat;

class Base
{
    public function __construct()
    {

    }

    //生成32位的随机字符串
    public function createRandomStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    /**
     * 生成sign
     * options
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
     * @return string
     *
     * @author wxy
     */
    public function createSign($requestParams)
    {
        $key = '';
        foreach ($requestParams as $k => $v) {
            $Params[$k] = $v;
        }
        //[1] 按字典排序
        ksort($Params);
        $http = $this->formatParams($Params);
        //[2]添加KEY
        $http = $http . '&key=' . $key;
        //[3]md5
        $string = md5($http);

        //[4]转换为大写
        return strtoupper($string);
    }

    private function formatParams($request, $urlEnCode = false)
    {
        $this->isOneArray($request);

        $http = '';
        ksort($request);  //按字典ASCII排序
        foreach ($request as $k => $v) {
            if ($urlEnCode) {
                $v = urlencode($v);
            }
            $http .= $k . '=' . $v . '&';
        }
        $result = '';
        if (strlen($http) > 0) {
            $result = substr($http, 0, strlen($http) - 1);
        }

        return $result;
    }


    /**
     *    作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30)
    {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        //返回结果
        if ($data) {
            //curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";

            //curl_close($ch);
            return false;
        }
    }

    /**
     *    作用：使用证书，以post方式提交xml到对应的接口url
     */
    public function postXmlSSLCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, wx_pay_config::SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, wx_pay_config::SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);

            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);

            return false;
        }
    }


    /*
     * 验证必须的参数  是否存在
     *
     * $data  数据来源
     * $filed  必要的参数
     */

    public function mustParams($data, $returnData = array())
    {
        $filed = array(
            'appid',
            'mch_id',
            'nonce_str',
            'sign',
            'body',
            'out_trade_no',
            'total_fee',
            'spbill_create_ip',
            'notify_url',
            'trade_type'
        );
        $this->isOneArray($data);
        foreach ($data as $key => $val) {
            if (!in_array($key, $filed)) {
                throw new \Exception('缺少参数!');
            }
            if (empty($val)) {
                throw new \Exception($key . '不能为空!');
            }
        }

        return $data;
    }

    /*
     * 验证数组是否为一维数组
     * $params array data
     *
     * @return bool
     */
    public function isOneArray($data)
    {
        if (!is_array($data)) {
            throw new \Exception('数据不是数组形式!');
        }
        foreach ($data as $val) {
            if (is_array($val)) {
                throw new \Exception('数组必须为一维数组!');
            }
        }

        return true;
    }

    public function xml2array($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $array_data;
    }

    public function array2xml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]</" . $key . ">";
            }
        }
        $xml .= '</xml>';

        return $xml;
    }
}