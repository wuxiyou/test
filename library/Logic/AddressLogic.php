<?php
/**
 * Created by PhpStorm.
 * User: Wxiyou
 * Date: 2016/5/21
 * Time: 18:43
 */
namespace Logic;

use Common\Fun;
use Model\Address;
use Model\District;

class AddressLogic
{
    /*
    * 查询用户的收货地址
    * $params = array (
    *      'user_id' => ''
    *      'address_id' => ''
    *      'addressInfo' => array(
    *          'real_name'         => '收货人真实姓名',
    *          'district_code'    => '区县code或街道code',
    *          'zipCode'          => '邮政编码',
    *          'mobile_phone'      => '手机号码',
    *          'receiver_address' => '收货详细地址。除省市区街道外的部分地址信息。',
    *      )
    * );
    */
    public function address($params)
    {
        /*
         * [1] 第一次设置收货地址 设置为默认收货地址
         * [2] 已经有多个收货地址
         */
        $addressModel = new Address();
        $districtModel = new District();
        if (!isset($params) || !is_array($params)) {
            throw new \Exception('收货地址参数不正确!');
        }

        if (!isset($params['user_id'])) {
            throw new \Exception('用户ID不能为空!');
        }

        if (isset($params['address_id'])) {
            $files = array('realname,zipcode,mobilephone,district_code,region_type,address');
            $where = array('user_id' => $params['user_id'], 'address_id' => $params['address_id'], 'status' => 1);
            $address_info = $addressModel->fetchOne($files, $where);
            if (empty($address_info)) {
                throw new \Exception('收货地址已变更!');
            }

            if ($address_info['region_type'] == 4) {
                $where_district = array('street_code' => $address_info['district_code']);
            } else {
                $where_district = array('district_code' => $address_info['district_code']);
            }
            $districtInfo = $districtModel->fetchOne([], $where_district);
            if (empty($districtInfo)) {
                throw new \Exception('收货地址不正确，请更改或新增!');
            }
            $address_info['receiver_province'] = $districtInfo['province_name'];
            $address_info['receiver_city'] = $districtInfo['city_name'];
            $address_info['receiver_district'] = $districtInfo['district_name'];
            $address_info['receiver_street'] = $districtInfo['street_name'];
            $address_info['receiver_name'] = $address_info['realname'];
            $address_info['receiver_mobile'] = $address_info['mobilephone'];
            $address_info['receiver_zip'] = $address_info['zipcode'];
            $address_info['receiver_address'] = $address_info['address'];
        } else {
            if (empty($params['addressInfo']) || count($params['info']) !== 5) {
                throw new \Exception('收货地址不完善!');
            }
            $real_name = Fun::countString($params['addressInfo']['real_name']);
            if (empty($params['addressInfo']['real_name']) || $real_name > 10) {
                throw new \Exception('收货人姓名不合法!');
            }
            if (empty($params['addressInfo']['zipCode'])) {
                throw new \Exception('收货编码不能为空!');
            }

            if (!Fun::isZipCode($params['addressInfo']['zipCode'])) {
                throw new \Exception('请选择正确的收货编码!');
            }
            if (empty($params['addressInfo']['district_code'])) {
                throw new \Exception('');
            }
            if (empty($params['addressInfo']['mobile_phone']) || !is_numeric($params['addressInfo']['mobile_phone'])) {
                throw new \Exception('手机号码不能为空,且须为数字!');
            }
            if (!Fun::isMobilePhone($params['addressInfo']['mobile_phone'])) {
                throw new \Exception('手机号码不正确!');
            }
            if (empty($params['addressInfo']['receiver_address'])) {
                throw new \Exception('收货详细地址不能为空!');
            }
            $where  = ['street_code' => $params['addressInfo']['district_code'], 'status' => 1];
            $fields = array('district_id,district_code,region_type,province_name,city_name,district_name,street_name');
            $districtInfo = $districtModel->fetchOne($fields, $where);
            if (empty($districtInfo)) {
                unset($where['street_code']);
                $where['district_code'] = $params['addressInfo']['district_code'];
                $districtInfo = $districtModel->fetchOne($fields, $where);
            }
            //判断用户的收货地址个数
            $user_where = [
                'user_id' => $params['user_id'],
                'status'  => 1
            ];
            $count = $addressModel->count($user_where);
            if ($count <= 5) {
                $insert_data = [
                    'user_id'       => $params['user_id'],
                    'realname'      => $params['addressInfo']['real_name'],
                    'zipcode'       => $params['addressInfo']['zipCode'],
                    'mobilephone'   => $params['addressInfo']['mobile_phone'],
                    'district_code' => $params['addressInfo']['district_code'],
                    'region_type'   => $districtInfo['region_type'],
                    'address'       => $params['addressInfo']['receiver_address'],
                    'status'        => 1,
                    'created_time'  => $_SERVER['REQUEST_TIME']
                ];
                $ok = $addressModel->insert($insert_data);
                if (!$ok) {
                    throw new \Exception('收货地址新增失败!');
                }
            } else {
                throw new \Exception('收货地址最多只能添加五个!');
            }

            $address_info = [
                'receiver_name'     => $params['addressInfo']['real_name'],
                'receiver_province' => $districtInfo['province_name'],
                'receiver_city'     => $districtInfo['city_name'],
                'receiver_district' => $districtInfo['district_name'],
                'receiver_street'   => $districtInfo['street_name'],
                'receiver_address'  => $params['addressInfo']['receiver_address'],
                'receiver_zip'      => $params['addressInfo']['zipCode'],
                'receiver_mobile'   => $params['addressInfo']['mobile_phone'],
            ];
        }

        return $address_info;
    }
}