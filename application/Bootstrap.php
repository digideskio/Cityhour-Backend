<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initAutoload()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Plugins_');
    }

    protected function _initPlugins()
    {
        // Access plugin
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Plugins_SelectLayout());
    }

    protected function _initDefaultRoutes()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->getRouter()->addDefaultRoutes();
    }

    protected function _initRestRoute() {
        $this->bootstrap('frontController');
        $frontController = Zend_Controller_Front::getInstance();
        $restRoute = new Zend_Rest_Route($frontController, array(), array(
            'v1',
        ));
        $frontController->getRouter()->addRoute('rest', $restRoute);
    }

    public function _initCache()
    {
        $frontendOpts = array(
            'caching' => true,
            'lifetime' => 3600,
            'automatic_serialization' => true
        );

        $backendOpts = array(
            'servers' =>array(
                array(
                    'host'   => 'localhost',
                    'port'   => 11211,
                    'weight' => 1
                )
            ),
            'compression' => false
        );

        $cache = Zend_Cache::factory('Core', 'Memcached', $frontendOpts, $backendOpts);

        // Next, set the cache to be used with all table objects
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }
}


class Plugins_SelectLayout extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $config     = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')->getOptions();
        $moduleName = $request->getModuleName();

        if (isset($config[$moduleName]['resources']['layout']['layout'])) {
            $layoutScript = $config[$moduleName]['resources']['layout']['layout'];
            Zend_Layout::getMvcInstance()->setLayout($layoutScript);
        }

        if (isset($config[$moduleName]['resources']['layout']['layoutPath'])) {
            $layoutPath = $config[$moduleName]['resources']['layout']['layoutPath'];
            $moduleDir = Zend_Controller_Front::getInstance()->getModuleDirectory();
            Zend_Layout::getMvcInstance()->setLayoutPath(
                $moduleDir. DIRECTORY_SEPARATOR .$layoutPath
            );
        }
    }
}