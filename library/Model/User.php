<?php
namespace Model;

use Common\Db\DbBase;

class User extends DbBase
{
    /**
     * 表名。
     * @var string
     */
    protected $table_name = 'ms_users';

    /*
     * params = array(
     *      'username' => '',
     *      'password' => '',
     *      'ask' => '',
     *      'answer' => '',
     *      'accept'=> '',
     * )
     */
    public function addUser($params)
    {
        if (empty($params)) {
            throw new \Exception('数据不能为空!');
        }
        if (!is_array($params)) {
            throw new \Exception('数据格式不对!');
        }
        if (empty($params['username'])) {
            throw new \Exception('用户名不能为空!');
        }

        if (empty($params['password'])) {
            throw new \Exception('密码不能为空!');
        }
        return $this->insert($params);
    }
}