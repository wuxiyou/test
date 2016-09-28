<?php

/**
 * Blog 模块
 * Class IndexController
 */
class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->loadView('index.phtml');
        return false;
    }


}