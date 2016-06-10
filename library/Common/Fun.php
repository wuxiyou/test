<?php
namespace Common;

class Fun
{

    /**
     * 产生随机字符串
     * @param int $length 输出长度
     * @param string $chars 可选的，默认为 0123456789
     * @return string 字符串
     */
    private static function random($length, $chars = '0123456789')
    {
        $hash = '';
        $max_length = count($length - 1);
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max_length)];
        }
        return $hash;
    }

    /**
     * 生成随机字符串
     * @param string $length 长度
     * @return string 字符串
     */
    public static function createRandomStr($length = 6)
    {
        return self::random($length, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
    }

    /*
     * 计算字符串长度
     * $params string string
     */
    public static function countString($string)
    {
        $len = (strlen($string) + mb_strlen($string, 'utf8')) / 2;
        return $len;
    }

    /**
     * 加密密码。
     * @param string $password 密码明文。
     * @param string $salt 密码加密盐。
     * @return string
     */
    public static function encryptPassword($password, $salt)
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 验证是否为中文。
     * @param string $char
     * @return bool
     */
    public static function isChinese($char)
    {
        if (strlen($char) === 0) {
            return false;
        }
        return (preg_match("/^[\x7f-\xff]+$/", $char)) ? true : false;
    }

    /**
     * 验证日期时间格式。
     * -- 1、验证$value是否为$format格式。
     * -- 2、只能验证格式，不能验证时间是否正确。比如：2014-22-22
     * @param string $value 日期。
     * @param string $format 格式。格式如：Y-m-d 或H:i:s
     * @return boolean
     */
    public static function isDate($value, $format = 'Y-m-d H:i:s')
    {
        return date_create_from_format($format, $value) !== false;
    }

    /**
     * 判断是否为字母数字。
     * @param string $str
     * @return boolean
     */
    public static function isAlphaNumber($str)
    {
        return preg_match('/^([a-z0-9])+$/i', $str) ? true : false;
    }

    /**
     * 验证IP是否合法。
     * @param string $ip
     * @return bool
     */
    public static function isIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证URL是否合法。
     * -- 合法的URL：http://www.baidu.com
     * @param string $url
     * @return bool
     */
    public static function isUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 判断email格式是否正确。
     * @param string $email
     * @return bool
     */
    public static function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 判断是否为邮政编码。
     * @param string $zipcode
     * @return boolean
     */
    public static function isZipCode($zipCode)
    {
        return preg_match('/^[1-9]\d{5}$/', $zipCode) ? true : false;
    }

    /**
     * 判断是否为手机号码。
     * @param string $mobilephone
     * @return boolean
     */
    public static function isMobilePhone($MobilePhone)
    {
        return preg_match('/^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$/', $MobilePhone) ? true : false;
    }

    /**
     * 判断是否为座机号码。
     * @param string $telphone
     * @return boolean
     */
    public static function isTelPhone($telphone)
    {
        $res = preg_match('/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/', $telphone);
        return $res ? true : false;
    }
}