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
        $userName = trim($_POST['userName']);
        $password = trim($_POST['userName']);
        if (empty($userName) || empty($password)) {
            throw new Exception('用户名或密码不能空!');
        }

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
        try {
            $userName = trim($_POST['userName']);
            $password = trim($_POST['password']);
            if (empty($userName) || empty($password)) {
                throw new Exception();
            }
            //防止xsl注入
            $passwordLength = (strlen($password) + mb_strlen($password)) / 2;
            if ($passwordLength < 6) {
                throw new Exception();
            }
            $userName = strip_tags($userName);

        } catch (Exception $e) {

        }
    }

    /**
     * 验证
     */
    public function verifyAction()
    {

    }
}