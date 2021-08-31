<?php


class Shopware_Controllers_Frontend_Redirect extends Enlight_Controller_Action {

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [ 'ajaxRedirect' ];
    }

    /**
     * @throws Exception
     */
    public function ajaxRedirectAction()
    {
        Shopware()->Container()->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $redirectUrl = $this->container->get('netzhirsch_redirect.components.base_url_finder');
        $shop = $redirectUrl->findUrl($this->getModelManager());
        if (empty($shop)) {
            echo '';
            return;
        }
        $url = substr($shop->getBaseUrl(), 1);

        echo $url;
    }
}
