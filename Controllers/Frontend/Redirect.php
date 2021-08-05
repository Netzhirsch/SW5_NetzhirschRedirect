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
        $newUrl = $redirectUrl->findUrl($this->getModelManager());
        if (empty($newUrl)) {
            echo '';
            return;
        }
        $newUrl = substr($newUrl, 1);

        echo $newUrl;
    }
}
