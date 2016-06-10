<?php
/**
 * Created by PhpStorm.
 * User: Wxiyou
 * Date: 2016/5/21
 * Time: 11:24
 */
namespace Logic;

use Model\Good;
use Model\Order;
use Model\Payment\Log;
use Model\Product;
use Model\User;
use Yaf\Exception;

class OrderLogic
{
    //是否需要发票
    protected static $is_need_invoice = array(
        0 => '不需要',
        1 => '需要'
    );

    //发票类型
    protected static $invoice_type = array(
        1 => '个人', //默认
        2 => '单位'
    );

    //订单状态
    const ORDER_STATUS_WAIT_PAY = 0; // 待付款。
    const ORDER_STATUS_PAY_OK = 1; // 已付款。
    const ORDER_STATUS_DELIVER = 2; // 已发货。
    const ORDER_STATUS_SUCCESS = 3; // 交易成功。
    const ORDER_STATUS_CLOSED = 4; // 交易关闭。
    const ORDER_STATUS_CANCELED = 5; // 已取消。

    //商户后台操作
    public static $operate = array(
        0 => array(5),
        1 => array(2),
        2 => array(3,4), //可写脚本
    );

    protected $orderModel = '';
    protected function __construct()
    {
        $this->orderModel = new Order();
    }
    /**
     * 用户下单。
     * -- Example start --
     * $data = [
     *      'user_id'          => '用户ID',
     *      'goods_list'       => '商品列表',
     *      'address_id'       => '收货地址ID。如果是临时购买。则此值填写-1',
     *      'need_invoice'     => '是否需要发票：0不需要、1需要',
     *      'invoice_type'     => '发票类型：1个人、2单位',
     *      'invoice_name'     => '发票抬头',
     *      'buyer_message'    => '买家留言。100这个字符。',
     *      'new_address_info' => '新的收货地址。如果address_id不等于-1,则此值有没有设置都无效。',
     * ];
     *
     * $new_address_info = [
     *      'realname'         => '收货人真实姓名',
     *      'district_code'    => '区县code或街道code',
     *      'zipcode'          => '邮政编码',
     *      'mobilephone'      => '手机号码',
     *      'receiver_address' => '收货详细地址。除省市区街道外的部分地址信息。',
     * ];
     *
     * $goods_list = [
     *      [
     *          'goods_id'   => '商品ID',
     *          'product_id' => '货品ID',
     *          'quantity'   => '购买数量',
     *      ],
     *      [
     *          'goods_id'   => '商品ID',
     *          'product_id' => '货品ID',
     *          'quantity'   => '购买数量',
     *      ],
     *      ......
     * ];
     * -- Example end --
     * @param array $data 订单信息。
     * @return boolean
     */

    public function createOrder($data)
    {
        if (empty($data['user_id'])) {
            throw new \Exception('用户ID不能为空!');
        }
        if (empty($data) || !is_array($data)) {
            throw new \Exception('购买信息有误!');
        }
        if (!isset($data['good_list']) || !is_array($data['good_list'])) {
            throw new \Exception('没有购买任何商品!');
        }

        if (!empty($data['buyer_message']) && mb_strlen($data['buyer_message'], 'utf8') > 50) {
            throw new \Exception('买家留言不能大于50个字符!');
        }

        if (!isset($data['need_invoice']) || !in_array($data['need_invoice'], self::$is_need_invoice)) {
            throw new \Exception('是否需要发票选择有误');
        }

        if ($data['need_invoice'] == 1) {
            if (!isset($invoice_name) || mb_strlen($data['invoice_name'], 'utf8') > 30) {
                throw new \Exception('发票抬头信息不宜超过30个字');
            }
            if (!in_array($data['invoice_type'], self::$invoice_type)) {
                throw new \Exception('发票类型选择有误!');
            }
            $data['invoice_type'] = isset($data['invoice_type']) ? $data['invoice_tpe'] : self::$invoice_type[1];
        } else {
            $data['invoice_type'] = 1;
            $data['invoice_name'] = '';
        }
        $userModel = new User();
        $userInfo = $userModel->fetchOne(['id,name,status'], ['user_id' => $data['user_id']]);
        if (empty($userInfo) || $userInfo['status'] != 1) {
            throw new \Exception('用户信息不存在!');
        }
        //收货地址  一个用户存在多个收货地址
        $addressLogic = new AddressLogic();
        $address_info = array('user_id' => $data['user_id'], 'address_id' => $data['address_id']);
        $address_info = $addressLogic->address($address_info);
        $insert_data = array(
            'order_sn' => self::getOrderSn($data['user_id'], 'sn'),
            'user_id' => $data['user_id'],
            'total_price' => 0,
            'payment_price' => 0,
            'pay_status' => 0,  //待支付
            'order_status' => 0, //未付款
            'need_invoice' => $data['need_invoice'],
            'invoice_type' => $data['invoice_type'],
            'invoice_name' => $data['invoice_name'],
            'receiver_name' => $address_info['receiver_name'],
            'receiver_province' => $address_info['province_name'],
            'receiver_city' => $address_info['city_name'],
            'receiver_district' => $address_info['receiver_district'],
            'receiver_street' =>  $address_info['receiver_street'],
            'receiver_address' => $address_info['receiver_address'],
            'receiver_zip' => $address_info['receiver_zip'],
            'receiver_mobile' => $address_info['receiver_mobile'],
        );
        $orderModel = new Order();
        $orderModel->beginTransaction();
        $orderId = $orderModel->insert($insert_data);
        try {
            if ($orderId) {
                $price_info = self::addOrderItem($data['user_id'], $orderId, $data['goods_list']);
                $res_status = $orderModel->update($price_info, ['order_id' => $orderId]);
                if (!$res_status) {
                    throw new \Exception('订单异常!');
                }
            } else {
                throw new \Exception('订单添加异常!');
            }
        } catch (\Exception $e) {
            $orderModel->rollBack();
            throw new \Exception($e->getMessage());
        }
        $orderModel->commit();
        $res = $this->pay($orderId, $data['user_id']);
        if (!$res) {
            throw new \Exception('支付异常');
        }
        return array('success' => true, 'message' => '下单成功', 'data' => array('order_id' => $orderId));
    }

    //购物明细表
    private static function addOrderItem($userId, $orderId, $good_list)
    {
        $total_price = 0;
        $payment_price  = 0;
        $freight_price = 0;
        $orderItemModel = new Order\Item();
        $goodModel = new Good();
        $productModel = new Product();
        foreach ($good_list as $key => $val) {
            if (!isset($val['good_id']) || !is_numeric($val['good_id'])) {
                throw new \Exception('商品ID 无效!');
            }

            if (!isset($val['product_id']) || !is_numeric($val['product_id'])) {
                throw new \Exception('货品ID 无效!');
            }

            if (!isset($val['quantity']) || !is_numeric($val['quantity']) || $val['quantity'] <= 0) {
                throw new \Exception('购买数量不正确!');
            }

            $goodInfo = $goodModel->fetchOne([], ['goods_id' => $val['good_id']]);
            if (empty($goodInfo)) {
                throw new \Exception('商品信息已发生改变!');
            }
            if ($goodInfo['status'] != 1) {
                throw new \Exception('商品已发生变动,请刷新页面!');
            }

            if ($goodInfo['marketable'] != 1) {
                throw new \Exception($goodInfo['goods_name'].'已经下架!');
            }
            $productWhere = array('product_id' => $val['product_id'], 'goods_id' => $goodInfo['good_id']);
            $productInfo = $productModel->fetchOne([], $productWhere);
            if (empty($productInfo)) {
                throw new \Exception('货品信息不存在!');
            }

            if ($productInfo['status'] != 1) {
                throw new \Exception('货品信息不存在!');
            }

            if ($productInfo['stock'] <= 0) {
                throw new \Exception($goodInfo['goods_name'].'库存不足!');
            }

            if ($val['quantity'] > $productInfo['stock']) {
                throw new \Exception('购买'.$goodInfo['goods_name'].'大于现有库存');
            }

            $total_price = bcadd($total_price, bcmul($productInfo['market_price'], $val['quantity'], 2), 2);
            $payment_price = bcadd($payment_price, bcmul($productInfo['sales_price'], $val['quantity'], 2), 2);
            $data = [
                'order_id'      => $orderId,
                'goods_id'      => $goodInfo['goods_id'],
                'goods_name'    => $goodInfo['goods_name'],
                'product_id'    => $productInfo['product_id'],
                'spec_val'      => $productInfo['spec_val'],
                'market_price'  => $productInfo['market_price'],
                'sales_price'   => $productInfo['sales_price'],
                'quantity'      => $val['quantity'],
                'created_time'  => $_SERVER['REQUEST_TIME'],
                'created_by'    => $userId,
                'payment_price' => $total_price,
                'total_price'   => $payment_price,
                'good_status'   => 1,
            ];
            $ok = $orderItemModel->insert($data);
            if (!$ok) {
                throw new \Exception('操作失败!');
            }
            //更新商品库存
            $stock_data = array('product_id' => $productInfo['product_id'], 'stock' => '-'.$val['quantity']);
            $res = $productModel::setStock($stock_data);
            if (!$res) {
                throw new \Exception('库存修改失败!');
            }
        }
        $return_data = array(
            'total_price' => $total_price,
            'payment_price' => $payment_price,
            'freight_price' => $freight_price,
        );
        return $return_data;
    }

    /*
     * @var 订单支付操作
     * @var string orderId
     * @return bool
     */
    public function pay($orderId, $user_id)
    {
        if (empty($orderId)) {
            throw new \Exception('订单ID不能为空!');
        }
        if (!is_numeric($orderId)) {
            throw new \Exception('订单参数错误!');
        }
        if (!isset($user_id) || !is_numeric($user_id)) {
            throw new \Exception('用户参数错误!');
        }
        $orderModel = new Order();
        $userModel = new User();
        $userInfo = $userModel->fetchOne(['id'], array('user_id' => $user_id));
        if (!$userInfo) {
            throw new \Exception('用户信息异常!');
        }
        $order_where = array('order_id' => $orderId, 'status' => 1);
        $orderInfo = $orderModel->fetchOne(['id','pay_status','payment_price','order_status'], $order_where);
        if (empty($orderInfo)) {
            throw new \Exception('不存在该订单!');
        }
        if ($orderInfo['pay_status'] == self::ORDER_STATUS_PAY_OK) {
            throw new \Exception('订单已经支付过!');
        }
        if ($orderInfo['order_status'] !== self::ORDER_STATUS_WAIT_PAY) {
            throw new \Exception('订单已'.$orderInfo['order_status']);
        }
        //生成支付日志
        $payModel = new Log();
        $pay_data = [
            'user_id' => $user_id,
            'payment_code' => 2,  //默认微信支付
            'order_id' => $orderId,
            'serial_number' => 0,  //支付流水号，支付成功时更新
            'amount' => $orderInfo['payment_price']
        ];
        $ok = $payModel->insert($pay_data);
        if (!$ok) {
            throw new \Exception('支付异常');
        }
        // todo 调起支付接口
        $payParams = array(
            'payment_id' => $ok[0],
            'order_id' => $orderId,
            'mch_id' => 1001, //商户编码
            'pay_amount' => $orderInfo['payment_price'],
            'sign' => '' //签名验证
        );
        //todo  支付返回结果  返回格式待定  array | json [payment_id,order_id, serial_number]
        $payOk = 0;
        if (!is_array($payOk)) {
            throw new \Exception('支付失败');
        }
        $payInfo = $payModel->fetchOne(['serial_number'], ['payment_id' => $payOk['payment_id']]);
        if (!$payInfo) {
            throw new \Exception('支付异常');
        }
        if (isset($payInfo['serial_number'])) {
            throw new \Exception('该订单已支付!');
        }
        $payModel->update(['serial_number' => $payOk['serial_number']], ['payment_id' => $payOk['payment_id']]);
        $orderModel->update(['pay_status' => 1, 'order_status' => 1], ['order_id' => $payOk['order_id']]);
        return false;
    }

    //取消整笔订单
    public function cancel($orderId, $orderStatus)
    {
        $flag = false;
        if (!filter_var($orderId, FILTER_VALIDATE_INT)) {
            throw new \Exception('订单参数错误!');
        }
        $orderModel = new Order();
        $orderInfo = $orderModel->
        fetchOne(
            ['id', 'pay_status', 'order_status'],
            ['order_id' => $orderId, 'status' => 1]
        );

        if (!$orderInfo) {
            throw new \Exception('订单已失效!');
        }
        if ($orderStatus == 0) {
            if ($orderInfo['pay_status'] != self::ORDER_STATUS_WAIT_PAY || $orderInfo['order_status'] != 0) {
                throw new \Exception('该订单已支付,不可取消!');
            }
            $flag = true;
        } elseif ($orderStatus == self::ORDER_STATUS_PAY_OK) {
            $res = $this->audit($orderId);
        } else {
            throw new \Exception('无效请求');
        }

        if ($flag === true) {
            $is_ok = $orderModel->update(
                ['order_status' => self::ORDER_STATUS_CANCELED, 'cancel_time' => time()],
                ['order_id' => $orderId]
            );
            if (!$is_ok) {
                throw new \Exception('订单取消异常!');
            }
        } else {
            return false;
        }

        return true;
    }

    //对已支付的订单进行审核 退款
    public function audit($orderID)
    {
        if (!filter_var($orderID, FILTER_VALIDATE_INT)) {
            throw new \Exception('无效请求', 1001);
        }
        $orderInfo = $this->orderModel->fetchOne(['id', 'order_status'], ['order_id' => $orderID, 'status' => 1]);
        if (!$orderInfo) {
            throw new \Exception('无效订单', 1002);
        }
        if ($orderInfo['order_status'] != self::ORDER_STATUS_PAY_OK) {
            throw new \Exception('订单未支付', 1003);
        }
        return true;
    }

    /*
     * 取消订单里的部分商品
     * @array params = [
     *      ['goods_id', 'product_id', 'cancelNum', 'order_id'],
     *      ['goods_id', 'product_id', 'cancelNum', 'order_id'],
     * ];
     * @return bool
     */
    public function cancelGoods()
    {

    }

    /*
     *  订单操作
     * 未支付的订单
     * 1,取消订单，
     * 已支付订单（处理中）
     * 1，已发货，2.可交易取消并退款
     * 已发货的订单
     * 1，交易成功 -> 达到一定条件可以交易关闭(自动)
     * 申请退款(已支付的订单)
     *
     * $params $operateType  操作类型
     * $params $orderId 订单id
     * $array $params  附加参数
     */
    public function operateOrder($operateType, $orderId, $params = array())
    {
        if (!filter_var($orderId, FILTER_VALIDATE_INT)) {
            throw new \Exception('订单参数错误!');
        }
        if (!filter_var($operateType, FILTER_VALIDATE_INT)) {
            throw new \Exception('操作类型有误');
        }

        if (!is_array($params) || empty($params['flag'])) {
            throw new \Exception('参数错误!');
        }
        if (!filter_var($params['flag'], FILTER_VALIDATE_INT)) {
            throw new \Exception('无效操作!', 1001);
        }
        $keys_exits = array_key_exists($operateType, self::$operate);
        if ($keys_exits) {
            throw new \Exception('操作类型不正确!');
        }

        if (!in_array($params['flag'], self::$operate[$operateType])) {
            throw new \Exception('无效操作', 1002);
        }
        $orderInfo = $this->orderModel->fetchOne(
            ['id', 'pay_status', 'order_status'],
            ['order_id' => $orderId, 'status' => 1]
        );
        if (!$orderInfo) {
            throw new \Exception('订单不存在');
        }
        if ($orderInfo['order_status'] == self::ORDER_STATUS_CANCELED) {
            throw new \Exception('订单已取消');
        }

        if ($orderInfo['order_status'] == self::ORDER_STATUS_CLOSED) {
            throw new \Exception('交易已关闭');
        }

        //未支付，取消订单
        if ($operateType == 1) {
            if ($orderInfo['order_status'] != self::ORDER_STATUS_WAIT_PAY) {
                throw new \Exception('订单符合取消条件!');
            }
            if ($params['flag'] == 5) {
                $is_ok = $this->cancel($orderId, $orderInfo['order_status']);
            } else {
                $is_ok = false;
            }

        } elseif ($operateType == 2) { //已支付
            if ($orderInfo['order_status'] != self::ORDER_STATUS_PAY_OK) {
                throw new \Exception('订单未支付');
            }
            if ($params['flag'] == 2) {  //已发货
                $is_ok = $this->orderModel->update(
                    ['order_status' => self::ORDER_STATUS_DELIVER, 'shop_time' => time()],
                    ['order_id' => $orderId]
                );
            } elseif ($params['flag'] == 4) { //取消交易申请->商家审核(不超过7天)->同意取消(买家回寄商品)->商家退款并取消交易

            } else {
                $is_ok = false;
            }

        } elseif ($operateType == 3) {  //已发货
            if ($orderInfo['pay_status'] != 1 || $orderInfo['order_status'] != 2) {
                throw new \Exception('订单未支付或订单未发货!');
            }
            if ($params['flag'] == 3) { //交易完成(超过7天)->交易关闭
                $is_ok = $this->orderModel->update(
                    ['order_status' => self::ORDER_STATUS_SUCCESS, 'done_time' => time()],
                    ['order_id' => $orderId]
                );
            } else {
                $is_ok = false;
            }

        } else {
            throw new \Exception('无效操作!');
        }
        if (!$is_ok) {
            return false;
        }
        return true;
    }

    /**
     * 获取订单号。
     * -- 1、同网段的服务器产生的订单号不会重复。如：192.168.1.1 ~ 192.168.255.255
     * -- 2、多网段的服务器可能会产生重复的订单号。如果并发量不大的情况下，可以勉强使用。如果并发量太大，不要使用。
     * -- 3、订单号组成：前缀 + 时间戳(10位) + 微秒(6位) + 服务器IP编号(6位) + 用户ID(10位) = 订单号。
     * @param number $user_id 用户ID。订单号组成部分。用户来避免订单号重复。也可以通过订单号反解得到时间与用户ID等信息。
     * @param string $prefix 订单号前缀。不允许超过5个字符。
     * @return string
     */
    public static function getOrderSn($user_id, $prefix = '')
    {
        if (strlen($prefix) > 5) {
            throw new \Exception('订单前缀不能超过5个字符');
        }
        // [1]
        $microtime = microtime();
        list($usec, $sec) = explode(' ', $microtime);
        $usec = intval($usec * 1000000);
        $usec = sprintf('%06d', $usec);   //todo  sprintf  把百分号替换成对应值
        // [2]
        $server_ip     = $_SERVER['SERVER_ADDR'];
        $server_ip_int = ip2long($server_ip);
        $server_number = $server_ip_int % 1000000;
        // [3]
        $user_id = sprintf('%010d', $user_id);
        $order_sn = "{$sec}{$server_number}{$user_id}{$usec}";
        return "{$prefix}{$order_sn}";
    }
}