<?php


class Shopware_Controllers_Widgets_Redirect extends Enlight_Controller_Action {

    const SESSION_KEY = 'firstRun';
    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [ 'index' ];
    }

    /**
     * Pre dispatch method
     */
	public function indexAction() {


        $session = Shopware()->Session();
        $firstRun = $session->get(self::SESSION_KEY);

        if ($firstRun)
            return null;

        $shop = $this->getRedirectShop();
        if (empty($shop))
            return;

        $configReader = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['NetzhirschRedirect'];
        $config = $configReader->getByPluginName($plugin->getName(),Shopware()->Shop());

        $currentUrl = $_SERVER['REDIRECT_URL'];

        if ($config['withoutConfirmation'] && $currentUrl != $shop->getBaseUrl()) {
            $session->offsetSet(self::SESSION_KEY, true);
            header('Location: '.$shop->getBaseUrl());
            exit();
        }
	}

    /**
     * @throws Exception
     */
    private function getRedirectShop(){

        try {
            $redirectUrlService = $this->container->get('netzhirsch_redirect.components.base_url_finder');
        } catch (Exception $e) {
            return null;
        }

        return $redirectUrlService->findUrl($this->getModelManager());
    }



}
