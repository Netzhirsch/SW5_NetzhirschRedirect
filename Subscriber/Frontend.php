<?php

namespace NetzhirschRedirect\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Components\Theme\LessDefinition;

class Frontend implements SubscriberInterface
{

	/**
	 * @var string $pluginDirectory
	 */
	private $pluginDirectory;

    public function __construct(
		$pluginDirectory
	)
	{
		$this->pluginDirectory = $pluginDirectory;
    }

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'Enlight_Controller_Action_PostDispatch_Frontend_Index' => 'onFrontendPostDispatch',
			'Enlight_Controller_Dispatcher_ControllerPath_Widgets_Redirect' => 'getWidgetsRedirectController',
            'Theme_Compiler_Collect_Plugin_Javascript' => 'addJsFiles',
            'Theme_Compiler_Collect_Plugin_Less' => 'addLessFile'
		];
	}

	public function onFrontendPostDispatch(Enlight_Event_EventArgs $args) {
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['NetzhirschRedirect'];
        $path   = $plugin->getPath();
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view       = $controller->View();
        $view->addTemplateDir($path . '/Resources/views');
        $configReader = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
        $config = $configReader->getByPluginName($plugin->getName());
		
        $withoutConfirmation = ($config['withoutConfirmation']) ? 'on' : 'off';
        $view->assign('withoutConfirmation',$withoutConfirmation);
        
		$active = ($config['active']) ? 'on' : 'off';
        $view->assign('active',$active);
	}

	public function getWidgetsRedirectController() {
	    return $this->pluginDirectory . '/Controllers/Widgets/Redirect.php';
	}

    public function addJsFiles()
    {
        $jsFiles = array($this->pluginDirectory . '/Resources/views/frontend/_public/src/js/netzhirsch-pop-up-before-redirect.js');
        return new ArrayCollection($jsFiles);
    }
    public function addLessFile()
    {
        $less = new LessDefinition(array(),
            array(
                $this->pluginDirectory .
                '/Resources/views/frontend/_public/src/less/netzhirschRedirect.less'
            ), __DIR__);

        return new ArrayCollection(array(
            $less
        ));
    }
}
