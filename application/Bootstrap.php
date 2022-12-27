<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{   
    protected function _initBar()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('Api_');
        $loader->registerNamespace(array('Api_'));
        $router = $this->setRouter();

        // Создание объекта front контроллера
        $front = Zend_Controller_Front::getInstance();
        // Настройка front контроллера, указание базового URL, правил маршрутизации
        $front->setRouter($router);
        $registry = Zend_Registry::getInstance();
        $registry->constants = new Zend_Config( $this->getApplication()->getOption('constants'));
    }

    public function setRouter()
    {
        // Подключение файла правил маршрутизации
        $router = new Zend_Controller_Router_Rewrite();
        // Если переменная router не является объектом Zend_Controller_Router_Abstract, выбрасываем исключение
        if (!($router instanceof Zend_Controller_Router_Abstract)) {
            throw new Exception('Incorrect config file: routes');
        }
        return $router;
    }
    
}

