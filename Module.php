<?php
/**
 * Datatables ZF2 Module
 *
 * @link http://github.com/aiolos/datatables for the repository
 */

namespace Datatables;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'Datatable' => 'Datatables\View\Helper\Datatable',
            )
        );
    }
}
