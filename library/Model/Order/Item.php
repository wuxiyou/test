<?php
/**
 * Created by PhpStorm.
 * User: Wxiyou
 * Date: 2016/5/22
 * Time: 10:42
 */

namespace Model\Order;

use Common\Db\DbBase;

class Item extends DbBase
{
    protected $table_name = 'ms_order_item';
}