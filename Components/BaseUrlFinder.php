<?php

namespace NetzhirschRedirect\Components;

use NetzhirschRedirect\Models\LocationByIP\LocationByIP;
use NetzhirschRedirect\Models\Shop\Shop;

class BaseUrlFinder
{

    public function findUrl($em){
        $shop = Shopware()->Shop();
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['NetzhirschRedirect'];
        $configReader = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
        $config = $configReader->getByPluginName($plugin->getName(),$shop);

        if (empty($config['active']) || !isset($config['redirectRule'])) {
            return null;
        }

        $redirectRule = $config['redirectRule'];
        $countryCode = [];
        if (strpos($redirectRule, 'ip') !== false) {
            $countryCodeTmp = $this->getCountryCodeByIP($em);
            if (empty($countryCodeTmp))
                return null;
            $countryCode['byIp'] = $countryCodeTmp;
        }

        if (strpos($redirectRule, 'browser') !== false) {
            $repoShop = $em->getRepository(Shop::class);
            /** @var Shop[] $subShops */
            $subShops = $repoShop->getActiveShops();
            $possibleLanguages = [];

            foreach ($subShops as $shop) {
                $local = $shop->getLocale();
                $local = $local->getLocale();
                $local = explode('_', $local);
                $possibleLanguages[] = $local[0];
            }

            $countryCodeTmp = $this->getCountryCodeByBrowser($possibleLanguages);
            if (empty($countryCodeTmp))
                return null;

            $countryCode['byBrowser'] = $countryCodeTmp;
        }

        $repoShop = $em->getRepository(Shop::class);

        if (!empty($config['locales'])) {
            $locales = $config['locales'];
            $subShop = $repoShop->findOneOrNullByLocalIds($locales);
        } else {
            switch($redirectRule) {
                case 'ip':
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byIp']);
                    break;
                case 'browser':
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byBrowser']);
                    break;
                case 'ip/browser':
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byIp']);
                    if (empty($subShop))
                        $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byBrowser']);
                    break;
                case 'browser/ip':
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byBrowser']);
                    if (!empty($subShop))
                        $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byIp']);
                    break;
            }
        }

        if (empty($subShop))
            return null;

        return $subShop->getBaseUrl();
    }

    private function getCountryCodeByIP($em){

        if (!isset($_SERVER["REMOTE_ADDR"]))
            return null;

        $ipClient = $_SERVER["REMOTE_ADDR"];

        $repoLocationByIP = $em->getRepository(LocationByIP::class);
        $locationByIP = $repoLocationByIP->findByIPRange($ipClient);
        if (empty($locationByIP))
            return null;

        return $locationByIP->getCountryCode();
    }

    /**
     * @param array $possibleLanguages
     * @return null
     */
    private function getCountryCodeByBrowser($possibleLanguages)
    {
        if (!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) || empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
            return null;

        $languages = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

        $languages = preg_split('/,\s*/', $languages);
        foreach ($languages as $language) {

            $res = preg_match (
                '/^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i',
                $language,
                $matches
            );

            if (!$res) {
                continue;
            }

            $langCode = explode ('-', $matches[1]);
            foreach ($possibleLanguages as $possibleLanguage) {
                if (in_array ($possibleLanguage, $langCode)) {
                    return $langCode[0];
                }

            }
        }

        return null;
    }
}
