<?php

namespace NetzhirschRedirect\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use NetzhirschRedirect\Components\BaseUrlFinder;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Theme\LessDefinition;
use function Composer\Autoload\includeFile;

class Frontend implements SubscriberInterface
{

	/**
	 * @var string $pluginDirectory
	 */
	private $pluginDirectory;
    private $baseUrlFinder;
    private ModelManager $modelManager;

    public function __construct(
		$pluginDirectory,
        BaseUrlFinder $baseUrlFinder,
        ModelManager $modelManager
	)
	{
		$this->pluginDirectory = $pluginDirectory;
        $this->baseUrlFinder = $baseUrlFinder;
        $this->modelManager = $modelManager;
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
        $withoutConfirmation = '';
        $language = '';
        $local = '';
        $active = '';

        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view       = $controller->View();

	    if (!$this->isSearchBot()) {
            $plugin = Shopware()->Container()->get('kernel')->getPlugins()['NetzhirschRedirect'];
            $path   = $plugin->getPath();
            $view->addTemplateDir($path . '/Resources/views');
            $configReader = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
            $actualShop = Shopware()->Shop();
            $config = $configReader->getByPluginName($plugin->getName(),$actualShop);

            $withoutConfirmation = ($config['withoutConfirmation']) ? 'on' : 'off';

            $active = ($config['active']) ? 'on' : 'off';
            $redirectShop = $this->baseUrlFinder->findUrl($this->modelManager);
            $local = $redirectShop->getLocale();
            $language = $local->getLanguage();
            $local = $local->getTerritory();
            if (empty($redirectShop) || $actualShop->getId() == $redirectShop->getId())
                $active = 'off';
        }

        $view->assign('withoutConfirmation',$withoutConfirmation);
        $view->assign('active',$active);
        $view->assign('language',$language);
        $view->assign('local',$local);
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

    /**
     * @return bool
     * https://developers.google.com/search/docs/advanced/crawling/verifying-googlebot?visit_id=637660131470366219-258089992&rd=1
     */
    private function isSearchBot()
    {
        $isSearchBotByAgent = (
            isset($_SERVER['HTTP_USER_AGENT'])
            && preg_match('/bot|crawl|google|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
        );

        $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

        $isSearchBotByIp = (
            isset($hostname)
            && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $hostname)
        );

        $reversIp = gethostbyname($hostname);

        $isBotDns = $_SERVER['REMOTE_ADDR'] == $reversIp;

        if($isSearchBotByIp && $isBotDns)
            return true;

        if ($isSearchBotByAgent || $isSearchBotByIp)
            return true;

        return false;
    }
}
