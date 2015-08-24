<?php
namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    /**
     * 后台主菜单
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav nav-pills nav-stacked nav-bracket');
        $menu->setChildrenAttribute('id', 'leftmenu');

        $menu->addChild('Dashboard', array('route' => 'admin_index'));
        $menu->addChild('user', array('route' => 'admin_user', 'label' => '授权用户'));
        $menu->addChild('appLog', array('route' => 'admin_app_log', 'label' => '转发朋友日志'));
        $menu->addChild('timelineLog', array('route' => 'admin_timeline_log', 'label' => '转发朋友圈日志'));

        /*
        $creation = $menu->addChild('creation', array('route' => 'admin_creation', 'label' => '作品信息'));
        $creation->setAttribute('class', 'nav-parent');
        $creation->setChildrenAttribute('class', 'children');
        $creation->addChild('creation', array('route' => 'admin_creation',  'routeParameters' => array('fixed'=>'n'),  'label' => '用户作品'));
        $creation->addChild('fixedCreation', array('route' => 'admin_creation', 'routeParameters' => array('fixed'=>'y'), 'label' => '首页作品'));
        */
        return $menu;
    }
}