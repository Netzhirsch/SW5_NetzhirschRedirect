<?php

namespace NetzhirschRedirect\Models\Shop;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Shopware\Components\Logger;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;

/**
 * Class Repository
 */
class Repository extends ShopRepository
{

    /**
     * @param $locale
     * @param Logger $logger
     * @return array|mixed
     */
    public function findByLocalesInShop($locale,Logger $logger)
    {

        $configValues = $this->getConfigValuesByConfigLocales($logger);

        if (!empty($shopId = $this->getShopIdByConfig($configValues, $logger, $locale)))
            return $shopId;

        return null;

    }

    /**
     * @param $countryCode
     * @return Shop|null
     */
    public function findOneOrNullByCountryCode(
        $countryCode
    )
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        try {
            $qb =  $builder->select('shop')
                ->from(Shop::class, 'shop')
                ->leftJoin('shop.locale', 'locale')
            ;
            if (strstr($countryCode,'_')) {
                $qb
                    ->where('locale.locale = :countryCode')
                    ->setParameter('countryCode', $countryCode)
                ;
            } else {
                $qb
                    ->where('locale.locale LIKE :countryCode')
                    ->setParameter('countryCode', '%'.$countryCode)
                ;
            }

            return $qb ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param $locales
     * @return Shop|null
     */
    public function findOneOrNullByLocalIds(
        $locales
    )
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        try {
            return $builder->select('shop')
                ->from(Shop::class, 'shop')
                ->leftJoin('shop.locale', 'locale')
                ->where('locale.id IN (:locales)')
                ->setParameter('locales', $locales)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param $logger
     * @return array|null
     */
    private function getConfigValuesByConfigLocales($logger) {
        $db = Shopware()->Db();
        try {
            $values = $db->fetchAll(
                '
                    SELECT cv.value,cv.shop_id FROM `s_core_config_values` AS cv
                    LEFT JOIN s_core_config_elements ce ON cv.element_id = ce.id
                    LEFT JOIN s_core_config_forms cf ON ce.form_id = cf.id
                    LEFT JOIN s_core_shops cs ON cs.id = cv.shop_id
                    LEFT JOIN s_core_config_values cl ON cl.id = cs.locale_id
                    WHERE ce.name = ? AND cv.element_id = ce.id
                    ',
                [
                    'locales'
                ]
            ) ?: [];
        } catch (Exception $exception) {
            $logger->error($exception->getMessage());
            return null;
        }

        $shopConfigLocales = [];
        foreach ($values as $value) {
            $shopConfigLocales[] =
                [
                    'local' => @unserialize($value['value']),
                    'shopId' => $value['shop_id']
                ]
            ;
        }
        return $shopConfigLocales;
    }

    private function getShopIdByConfig($shopConfigLocales,$logger,$locale) {
        if (empty($shopConfigLocales))
            return null;

        foreach ($shopConfigLocales as $shopConfigLocale) {
            $tmpLocales = $shopConfigLocale['local'];
            $locals = [];
            if (is_array($tmpLocales)) {
                foreach ($tmpLocales as $tmpLocale) {
                    try {
                        $local = Shopware()->Models()->find(Locale::class, $tmpLocale);
                    } catch (Exception $e) {
                        $logger->error($e->getMessage());
                        return null;
                    }
                    $local = $local->getLocale();
                    if ($locale == $local)
                        return $shopConfigLocale['shopId'];
                    $locals[] = $local;

                }
            } else {
                $logger->error('Languages need to be config from select.');
                return null;
            }
            foreach ($locals as $local) {
                if (strstr($local,$locale))
                    return $shopConfigLocale['shopId'];
            }
        }
        return null;
    }
}
