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
        $this->loadView('\blog_index');
        return false;
    }


}