<?php
/**
 * Created by PhpStorm.
 * User: Wxiyou
 * Date: 2016/5/22
 * Time: 10:39
 */
namespace Model;

use Common\Db\DbBase;

class Product extends DbBase
{
    protected $table_name = 'ms_product';

    /*
     * @var 设置货品库存
     * @array $params = ['product_id', 'stock'];
     * return bool
     */
    public static function setStock($params)
    {
        if (!isset($params['product_id']) || $params['product_id'] == 0) {
            throw new \Exception('货品ID不能为空!');
        }
        $db = new DbBase();
        $productInfo = $db->fetchOne(['id', 'stock'], ['product_id' => $params['product_id'], 'status' => 1]);
        if (empty($productInfo)) {
            throw new \Exception('货品已经下架!');
        }
        if (!is_numeric($params['stock'])) {
            throw new \Exception('库存数量必须为数字!');
        }
        $setStock = bcadd($productInfo, $params['stock']);
        if ($setStock < 0) {
            throw new \Exception('货品库存不足!');
        }
        $res = $db->update(['stock' => $setStock], ['product_id' => $params['product_id']]);

        return $res;
    }
}