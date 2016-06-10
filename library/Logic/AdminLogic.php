<?php
namespace Logic;

use Common\Fun;
use Model\Admin;
use Yaf\Exception;

class AdminLogic
{
    //TODO  以下未根据权限进行操作
    /*
     * 后台添加管理员
     * $paarams = array(
     *      'realname' => '',
     *      'username' => '',
     *      'password' => '',
     *      'mobilephone' => '',
     * );
     */
    public function addAdmin($params)
    {
        if (!is_array($params)) {
            throw new \Exception('数据格式不正确');
        }
        if (!isset($params['realname']) ||
            !isset($params['username']) ||
            !isset($params['password']) ||
            !isset($params['mobilephone'])
        ) {
            throw new \Exception('管理员信息不能为空');
        }

        //过滤html代码
        $params['realname'] = strip_tags($params['realname']);
        $params['username'] = strip_tags($params['username']);

        $realNameLength = Fun::countString($params['realname']);
        $userNameLength = Fun::countString($params['username']);
        $passwordLength = Fun::countString($params['password']);

        if ($realNameLength > 10 || $userNameLength > 10) {
            throw new \Exception('用户名不能大于10个字符');
        }

        //验证手机号  todo  缺少正则验证
        if (!is_numeric($params['mobilephone'])) {
            throw new \Exception('手机号必须为数字');
        }

        if ($passwordLength < 6) {
            throw new \Exception('用户密码不能少于6位!');
        }

        //查询用户是否已添加过
        $adminModel = new Admin();
        $res = $adminModel->fetchOne([], array('realname' => $params['realname']));
        if (isset($res) && $res['status'] == 0) {
            throw new \Exception('用户已存在,请联系管理员进行激活!');
        } elseif (isset($res) && $res['status'] == -2) {
            throw new \Exception('用户已存在!');
        }
        $params['salt'] = Fun::createRandomStr(6);
        $params['password'] = Fun::encryptPassword($params['password'], $params['salt']);
        $result = $adminModel->insert($params);
        return ($result) ? true : false;
    }

    //编辑
    public function editAdminInfo($params)
    {
        if (!is_array($params)) {
            throw new \Exception('编辑数据格式不正确!');
        }
        if (!empty($params['admin_id'])) {
            throw new \Exception('无效请求!');
        }
        if (!isset($params['realname']) || !isset($params['username'])) {
            throw new \Exception('修改信息不能为空');
        }
        //过滤html代码
        $params['realname'] = strip_tags($params['realname']);
        $params['username'] = strip_tags($params['username']);

        //验证用户名长度
        $realNameLength = Fun::countString($params['realname']);
        $userNameLength = Fun::countString($params['username']);
        if ($realNameLength > 10 || $userNameLength > 10) {
            throw new \Exception('用户名不能大于10个字符');
        }

        $adminModel = new Admin();
        $result = $adminModel->editInfo($params, $type = 1);
        return $result;
    }

    //激活 $type == 1/删除 $type == -2
    public function updateStatus($admin_id, $type)
    {
        if (empty($admin_id)) {
            throw new \Exception('无效请求!');
        }
        $adminModel = new Admin();
        $info = $adminModel->fetchOne([], array('admin_id' => $admin_id));
        if (empty($info) || $info['status'] == -2) {
            throw new \Exception('该用户已被删除!');
        }
        if ($type == 1) {
            if ($info['status'] == 1) {
                throw new \Exception('该用户已是可用状态!');
            }
            $params['status'] = 1;
            $params['admin_id'] = $admin_id;
        } elseif ($type == -2) {
            $params['status'] = -2;
            $params['admin_id'] = $admin_id;
        } else {
            return false;
        }

        $result = $adminModel->editInfo($params, $type = 2);
        return $result;
    }

    /*
     * 修改用户密码
     * $params = array(
            'admin_id' => ,
            'username' => ,
            'realname' => ,
            'password' => ,
        );
     */
    public function updatePassword($params)
    {
        if (!is_array($params) ||
            !isset($params['username']) ||
            !isset($params['password']) ||
            empty($params['admin_id'])
        ) {
            throw new \Exception('无效请求!');
        }

        $passwordLength = Fun::countString($params['password']);
        if ($passwordLength < 6) {
            throw new \Exception('密码长度不能少于6位');
        }

        $adminModel = new Admin();
        $info = $adminModel->fetchOne([], ['admin_id' => $params['admin_id'], 'username' => $params['username']]);
        if (empty($info) || $info['status'] == -2) {
            throw new \Exception('该用户不存在!');
        }
        $params['salt'] = Fun::createRandomStr(6);
        $params['password'] = Fun::encryptPassword($params['password'], $params['salt']);
        $result = $adminModel->editInfo($params, 3);
        return ($result) ? true : false;
    }
}
