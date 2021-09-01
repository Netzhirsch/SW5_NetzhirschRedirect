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

        if (strpos($redirectRule, 'browser') !== false) {
            $countryCodeTmp = $this->getLocalByBrowser();
            if (empty($countryCodeTmp))
                return null;

            $countryCode['byBrowser'] = $countryCodeTmp;
        }


        /** @var Repository $repoShop */
        $repoShop = $em->getRepository(Shop::class);

        $subShop = $this->getSubShopByRedirectRule($redirectRule, $repoShop, $countryCode);

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

    /**
     * @param Repository $repoShop
     * @return Shop|DetachedShop
     */
    private function findShopByLocalInPluginConfig($repoShop,$countryCodeByBrowser)
    {
        $shopId = $repoShop->findByLocalesInShop($countryCodeByBrowser);
        if (empty($shopId))
            return null;
        return $repoShop->getById($shopId['shop_id']);
    }

    /**
     * @param $redirectRule
     * @param Repository $repoShop
     * @param array $countryCode
     * @return Shop|DetachedShop|\Shopware\Models\Shop\Shop|null
     */
    private function getSubShopByRedirectRule(
        $redirectRule,
        Repository $repoShop,
        array $countryCode
    )
    {
        switch ($redirectRule) {
            case 'ip':
                $subShop = $this->findShopByLocalInPluginConfig($repoShop, $countryCode['byIp']);
                if (empty($subShop)) {
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byIp']);
                }
                break;
            case 'browser':
                $subShop = $this->findShopByLocalInPluginConfig($repoShop, $countryCode['byBrowser']);
                if (empty($subShop)) {
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byBrowser']);
                }
                break;
            case 'ip/browser':
                $subShop = $this->findShopByLocalInPluginConfig($repoShop, $countryCode['byIp']);
                if (empty($subShop)) {
                    $subShop = $this->findShopByLocalInPluginConfig($repoShop, $countryCode['byBrowser']);
                }
                if (empty($subShop)) {
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byIp']);
                }
                if (empty($subShop)) {
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byBrowser']);
                }
                break;
            case 'browser/ip':
                $subShop = $this->findShopByLocalInPluginConfig($repoShop, $countryCode['byBrowser']);
                if (empty($subShop)) {
                    $subShop = $this->findShopByLocalInPluginConfig($repoShop, $countryCode['byIp']);
                }
                if (empty($subShop)) {
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byBrowser']);
                }
                if (empty($subShop)) {
                    $subShop = $repoShop->findOneOrNullByCountryCode($countryCode['byIp']);
                }
                break;
            default:
                $subShop = null;
                break;
        }

        return $subShop;
    }
}
