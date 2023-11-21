<?php
/**
 *  @package   WT AmoCRM Radical Form
 *  @copyright Copyright Sergey Tolkachyov
 *  @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Wt_seo_meta_templates_joomshopping\Extension\Wt_seo_meta_templates_joomshopping;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $subject = $container->get(DispatcherInterface::class);
                $config  = (array) PluginHelper::getPlugin('system', 'wt_seo_meta_templates_joomshopping');
                $plugin = new Wt_seo_meta_templates_joomshopping($subject, $config);
                $plugin->setApplication(\Joomla\CMS\Factory::getApplication());
                return $plugin;
            }
        );
    }
};