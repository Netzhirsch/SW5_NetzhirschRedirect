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

        $newUrl = $this->getRedirectUrl();
        if (empty($newUrl))
            return;

	    header('Location: '.$newUrl);
        exit();
	}

    private function getRedirectUrl(){

        $session = Shopware()->Session();
        $firstRun = $session->get(self::SESSION_KEY);
        if ($firstRun)
            return null;

        $redirectUrl = $this->container->get('netzhirsch_redirect.components.base_url_finder');
        return $redirectUrl->findUrl($session, $this->getModelManager());
    }



}
