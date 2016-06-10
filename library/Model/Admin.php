<?php
namespace Model;

use Common\Db\DbBase;

/**
 * Created by PhpStorm.
 * User: Wxiyou
 * Date: 2016/5/19
 * Time: 20:34
 */
class Admin extends DbBase
{
    protected $table_name = 'ms_admin';

    /*
     * string $type == 1 编辑
     * array $params = ['admin_id', 'realname', 'username'];
     *
     * string $type == 2  激活/删除
     * array $params = ['admin_id', 'status']
     *
     * string $type == 3 修改密码
     * array $params = ['admin_id', 'salt', 'password']
     */
    public function editInfo($params, $type)
    {
        if ($type == 1) {
            $data = ['realname' => $params['realname'], 'username' => $params['username']];
            $where = ['admin_id' => $params['admin_id'], 'status' => 1];
        } elseif ($type == 2) {
            $data = ['status' => $params['status']];
            $where = ['admin_id' => $params['admin_id']];
        } elseif ($type == 3) {
            $data = ['password' => $params['password'], 'salt' => $params['salt']];
            $where = ['admin_id' => $params['admin_id'], 'status' => 1];
        } else {
            return false;
        }
        return $this->update($data, $where);
    }
}