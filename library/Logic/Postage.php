<?php
namespace Logic;

class Postage
{
    /*
     * $params = array(
     *       'good_list' => array(
                array('num'=>,'postage_id'=>,'weight'=>,'volume'=>),
                array('num'=>,'postage_id'=>,'weight'=>,'volume'=>),
                array('num'=>,'postage_id'=>,'weight'=>,'volume'=>),
            ),
        'area_id' => '区域id'
     * );
     * 商品中有可能是计件，计重，按区域计算
     */
    public function startPostage($params)
    {
        if (empty($params['good_list']) || count($params['good_list'] == 0) || !is_array($params['good_list'])) {
            throw new \Exception('参数错误!');
        }
        //按规则分组
        $rule_good = array();
        foreach ($params['good_list'] as $key => $val) {
            $rule_good[$val['postage_id']][] = $val;
        }

        //读取所有设置的规则
        $rule = array();
        foreach ($rule as $k => $v) {
            $v['area_id'] = explode(',', $v['sps_area_id']);  //拆分省区域
            $rule[$v['id']]['type'] = $v['type'];
            $rule[$v['id']]['list'][] = $v;
        }

        //计算各规则的费用
        foreach ($rule_good as $kk => $vv) {

        }
    }
}