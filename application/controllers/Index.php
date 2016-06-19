<?php
use Logic\AdminLogic;
use Model\User;

class IndexController extends BaseController
{

    public function init()
    {
        session_start();
        $_SESSION['username'] = "admin";
    }

    public function indexAction()
    {
        $this->_view->word = "hello world";
        //or
        //$this->getView()->word = "hello world";
    }

    public function getTestAction()
    {
        $params = array();
        $test = new AdminLogic();
        $a = $test->addAdmin($params);
        echo $a;
        return false;
    }

    public function userAction()
    {
        $data = array(
            'username' => 'test',
            'password' => '123456',
        );
        $user = new User();
        $result = $user->addUser($data);
        print_r($result);
        return false;
    }

    public function selectAction()
    {
        $user = new User();
        $res = $user->fetchAll([]);
        print_r($res);
        return false;
    }

    public function selectSortAction(&$array)
    {
        //$temp = 0;
        $count = count($array);
        for ($i = 0; $i < $count - 1; $i++) {
            $min_val = $array[$i];
            $min_index = $i;
            for ($j = $i + 1; $j < $count; $j++) {
                if ($min_val > $array[$j]) {
                    $min_val = $array[$j];
                    $min_index = $j;
                }
            }
            $temp = $array[$i];
            $array[$i] = $array[$min_index];
            $array[$min_index] = $temp;
        }
        return $array;
    }

    public function select1Action()
    {
        $array = array(array(1, 4, 3), 15, 20, -1, 26, -50, 100, array(-100, -20));
        $test = $this->selectSortAction($array);
        echo "<pre>";
        print_r($test);
        return false;
    }

    public function excelAction()
    {
        $excel = new PHPExcel();
        print_r($excel);
        return false;
    }

    public function arrayAction()
    {
        $arr = array(
            array('id' => 1, 'num' => 2),
            array('id' => 2, 'num' => 3),
            array('id' => 3, 'num' => 4)
        );
        $new = array(
            array('id' => 1, 'price' => 0.8),
            array('id' => 2, 'price' => 0.9),
            array('id' => 3, 'price' => 1)
        );

        $newArr = array();
        /*foreach ($arr as $key => $val) {
            if (!isset($newArr[$val['id']])){
                $newArr[$val['id']]['amount'] = 0;
            }
            $newArr[$val['id']]['amount'] = bcmul($val['num'],$new[$key]['price'],2);
        }*/

        /*foreach ($arr as $key => $val) {
            $newArr[$val['id']] = $val;
            foreach ($new as $k => $v) {
                $newArr[$v['id']]['price'] = $v['price'];
            }
        }*/

        foreach (array_merge($arr, $new) as $key => $val) {
            foreach ($val as $k => $v) {
                $newArr[$val['id']][$k] = $v;
            }
        }
        $test = array('total' => 0);
        foreach ($newArr as $vv) {
            $newArr[$vv['id']]['amount'] = bcmul($vv['num'], $vv['price'], 2);
            $test['total'] += bcmul($vv['num'], $vv['price'], 2);
        }
        echo "<pre>";
        print_r(array_values($newArr));
        print_r($test);
        return false;
    }

    public function test7Action()
    {
        /*$a = bcmul(19.9997, 1, 2);
        echo $a;exit;*/
        $param = array(
            '0' => array('id' => 1, 'title' => 'test'),
            '1' => array('id' => 2, 'title' => 'test1')
        );
        $param1 = array(
            'good' => array(
                array('id' => 1, 'num' => 4),
                array('id' => 2, 'num' => 5),
            ),
        );
        foreach ($param1 as $k => $v) {
            $param1 = $v;  //todo  三维数组改为二维数组
            $newArray = array_merge($param, $param1);
        }
        $good_list = [];  //得到三维数组
        foreach ($newArray as $key => $val) {
            //$good_list[$val['id']] = '';
            foreach ($val as $k => $v) {
                $good_list[$val['id']][$k] = $v;
            }
        }
        echo "<pre>";
        print_r($newArray);
        return false;
    }

    public function sumAction()
    {
        $arr = array(
            array('id' => 1, 'condition' => 50),
            array('id' => 2, 'condition' => 100),
            array('id' => 3, 'condition' => 150)
        );
        $length = count($arr);
        for ($i = 0; $i < $length - 1; $i++) {

        }
    }

    public function excel1Action()
    {
        $test = new PHPExcel();
        print_r($test);
        return false;
    }

    public function micAction()
    {
        $microtime = microtime();
        list($usec, $sec) = explode(' ', $microtime);
        print_r($microtime);
        return false;
    }

    public function arrAction()
    {
        $array = array(1);
        if (empty($array)) {
            echo 24;
        } else {
            echo 5235;
        }exit;
        for ($i = 1; $i < 21; $i++) {
            for ($j = 1; $j < 16; $j++) {
                printf("%d%'.02d\n", $i, $j);
            }
            echo '<br>';
        }
        return false;
    }

    public function formatData()
    {

    }
}