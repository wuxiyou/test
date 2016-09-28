<?php
	class Bootstrap extends Yaf_Bootstrap_Abstract{
        /**
         * 注册配置到全局环境。
         * -- 1、率先执行，以便后续的程序都能读取到配置文件。
         */
        public function _initConfig() {
            $config = \Yaf_Application::app()->getConfig();
            \Yaf_Registry::set("config", $config);
            date_default_timezone_set($config->get('timezone'));
        }

        public function _initDefaultName(Yaf_Dispatcher $dispatcher){
            $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction('index');
        }
	}