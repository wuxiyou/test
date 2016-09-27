<?php
/**
 * Created by PhpStorm.
 * User: kl-pc-07
 * Date: 2016/9/27
 * Time: 10:17
 */
class Auth extends Yaf_Controller_Abstract
{
    private $session = array();
    public function init()
    {
        $this->session = Yaf_Session::getInstance();
    }

    /**
     * 登录验证
     */
    public function loginAction()
    {

    }

    /**
     * 退出操作
     */
    public function logoutAction()
    {

    }

    /**
     * 注册操作
     */
    public function registerAction()
    {

    }

    /**
     * 验证
     */
    public function verifyAction()
    {

    }
}