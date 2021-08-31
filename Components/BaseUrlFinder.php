<?php

namespace NetzhirschRedirect\Components;

use NetzhirschRedirect\Models\LocationByIP\LocationByIP;
use NetzhirschRedirect\Models\Shop\Repository;
use NetzhirschRedirect\Models\Shop\Shop;
use Shopware\Models\Shop\DetachedShop;

class BaseUrlFinder
{

    /**
     * @param $em
     * @return null|Shop|DetachedShop
     */
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

        /** @var Repository $repoShop */
        $repoShop = $em->getRepository(Shop::class);

        if (strpos($redirectRule, 'browser') !== false) {
            $countryCodeByBrowser = $this->getLocalByBrowser();
            $shopId = $repoShop->findByLocalesInShop($countryCodeByBrowser);
            $shop = $repoShop->getById($shopId['shop_id']);
            if (!empty($shop))
                return $shop;

            /** @var Shop[] $subShops */
            $subShops = $repoShop->getActiveShops();
            $possibleLanguages = [];

            foreach ($subShops as $shop) {
                $local = $shop->getLocale();
                $local = $local->getLocale();
                $local = explode('_', $local);
                $possibleLanguages[] = $local[0];
            }

            $countryCodeTmp = $this->getLocalByBrowser($possibleLanguages);
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

        return $subShop;
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
     * @return null
     */
    private function getLocalByBrowser()
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

            return str_replace('-', '_', $language);
        }

        return null;
    }
}
