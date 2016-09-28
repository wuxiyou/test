<?php

/**
 * Blog 模块
 * Class IndexController
 */
class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->_view->word = "hello world";
        echo 456;exit;
        $this->loadView('index.phtml');
        return false;
    }


}