<?php
/**
 * Created by PhpStorm.
 * User: Wxiyou
 * Date: 2016/6/9
 * Time: 10:46
 */
namespace Model\Pay;

use Common\Db\DbBase;

class Order extends DbBase
{
    protected $table_name = 'ms_pay_order';

    const PAY_STATUS_WAIT_PAY = 1; //支付中
    const PAY_STATUS_DO_PAY = 2; //支付成功

}