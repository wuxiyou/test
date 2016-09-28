<?php
/**
 *  控制器基类
 */

class BaseController extends Yaf_Controller_Abstract
{
    public function loadView($viewFile)
    {
        $viewExt = Yaf_Application::app()->getConfig()->application->view->ext;
        $viewPath = $this->getViewpath();
        $path_file = $viewPath . $viewFile . '.' . $viewExt;
        if (!file_exists($path_file)) {
            throw new Exception('视图文件不存在!');
        }

        $this->getView()->assign("view_file", $path_file);
        $this->getView()->display(APPLICATION_PATH . '/WEB-INF/views/template.' . $viewExt);

        return false;
    }
}