<?php

class ErrorController extends Yaf_Controller_Abstract
{
    public function errorAction($exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();

        //如果是数据库异常，则直接输出执行异常
        if ($exception instanceof Yaf_Exception_LoadFailed_Module && $message = '程序访问异常') {
        } elseif ($exception instanceof Yaf_Exception_LoadFailed_Controller && $message = '不存在此页面') {
        } elseif ($exception instanceof Yaf_Exception_LoadFailed_Action && $message = '不存在此页面') {
        } elseif ($exception instanceof Yaf_Exception_LoadFailed_View && $message = '不存在此页面') {
        } elseif ($exception instanceof Yaf_Exception_LoadFailed && $message = '加载错误') {
        } elseif ($exception instanceof Yaf_Exception && $message = '其他错误') {
        }

        $json = array('message' => $message, 'code' => $code);
        //如果是异步请求则直接输出json数据，否则显示错误页面
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($json);
            die();
        }

        $this->getView()->assign("json", $json);
    }
}