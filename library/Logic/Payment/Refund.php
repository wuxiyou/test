<?php
/**
 *  退款逻辑
 * @author wuxy
 */
namespace Logic\Payment;

use Model\Pay\Order;
use Model\Refund\Log;

class Refund extends Log
{
    const REFUND_STATUS_WAIT_REFUND = 0; //未退款
    const REFUND_STATUS_DO_REFUND = 1; //退款处理中
    const REFUND_STATUS_REFUND_SUCCESS = 2; //退款成功

    /*
     * @array $params = array()
     */
    public function doRefund($order_id, $user_id, $amount, $payment_id, $params = array())
    {
        if (empty($order_id) || !filter_var($order_id, FILTER_VALIDATE_INT)) {
            throw new \Exception('参数错误', 1001);
        }
        if (!isset($user_id) || !filter_var($user_id, FILTER_VALIDATE_INT)) {
            throw new \Exception('参数错误', 1002);
        }
        if (!isset($payment_id) || !filter_var($payment_id, FILTER_VALIDATE_INT)) {
            throw new \Exception('参数错误', 1003);
        }

        if (!empty($params) && is_array($params)) {
            if (!isset($params['mch_id']) || !filter_var($params['mch_id'], FILTER_VALIDATE_INT)) {
                throw new \Exception('参数错误', 1004);
            }
        }

        if (empty($amount) || !is_numeric($amount)) {
            throw new \Exception('退款金额格式不正确', 1005);
        }

        $orderModel = new Order();
        $orderInfo = $orderModel->fetchOne(
            ['id', 'pay_amount', 'status', 'is_lock'],
            ['order_id' => $order_id, 'payment_id' => $payment_id, 'mch_id' => $params['mch_id']]
        );

        if (empty($orderInfo)) {
            throw new \Exception('支付订单异常!');
        }

        if (!in_array($orderInfo['status'], array(2))) {
            throw new \Exception('订单支付状态不正确', -1001);
        }

        if ($orderInfo['pay_amount'] === 0) {
            throw new \Exception('订单没有可退金额');
        }

        if ($amount > $orderInfo['amount']) {
            throw new \Exception('退款金额大于订单可退金额');
        }

        if ($orderInfo['is_lock'] === 1) {
            throw new \Exception('订单正在处理中，请稍后再试');
        }
        //查询退款列表
        $refundList = $this->fetchAll(
            ['id', 'refund_amount', 'status'],
            ['order_id' => $order_id, 'mch_id' => $params['mch_id'], 'user_id' => $user_id]
        );

        $refundAmount = 0;
        if (!empty($refundList)) {
            foreach ($refundList as $key => $val) {
                if ($val['status'] === 0 || $val['status'] === 3) {
                    unset($refundList[$key]);
                }
                $refundAmount = bcadd($refundAmount, $val['refund_amount'], 2);
            }
        }

        $refundTotal = bcadd($amount, $refundAmount, 2);
        if ($refundTotal > $orderInfo['refund_amount']) {
            throw new \Exception('退款金额大于实际支付金额');
        }

        //扣除商户资金  商户可扣资金 >= 退款申请金额

        $refund_data = array(
            'mch_id' => $params['mch_id'],
            'user_id' => $user_id,
            'order_id' => $order_id,
            'refund_no' => '',
            'refund_amount' => $amount,
            'status' => self::REFUND_STATUS_WAIT_REFUND,
        );
        try {
            //锁住订单
            $this->beginTransaction();
            $is_lock = $orderModel->update(['is_lock' => 1], ['pay_id' => $orderInfo['id']]);
            if (!$is_lock) {
                throw new \Exception('订单处理失败');
            }
            $is_ok = $this->insert($refund_data);
            //todo  扣除商户资金
            if ($is_ok) {
                $this->update(['status' => self::REFUND_STATUS_DO_REFUND], ['refund_id' => $is_ok[0]]);
                //发起第三方退款  todo 退款成功  修改支付日志和订单金额
            }

        } catch (\Exception $e) {
            $this->rollBack();
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function finishRefund()
    {

    }
}