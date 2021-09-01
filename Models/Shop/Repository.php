<?php

namespace NetzhirschRedirect\Models\Shop;

use Doctrine\ORM\NonUniqueResultException;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;

/**
 * Class Repository
 */
class Repository extends ShopRepository
{

    /**
     * @param $locale
     * @return array|mixed
     */
    public function findByLocalesInShop($locale)
    {

        $db = Shopware()->Db();

        $shopId = $db->fetchRow(
            '
				SELECT cv.shop_id FROM `s_core_config_values` AS cv
                LEFT JOIN s_core_config_elements ce ON cv.element_id = ce.id
                LEFT JOIN s_core_config_forms cf ON ce.form_id = cf.id
                LEFT JOIN s_core_shops cs ON cs.id = cv.shop_id
                LEFT JOIN s_core_locales cl ON cl.id = cs.locale_id
                WHERE ce.name = ? AND cl.locale = ?
				',
            [
                'locales',
                $locale,
            ]
        ) ?: [];

        if (!empty($shopId))
            return $shopId;

        if (strpos($locale, '_')) {
            $parameters = explode('_', $locale);
            $shopId = $db->fetchRow(
                '
				SELECT cv.shop_id FROM `s_core_config_values` AS cv
                LEFT JOIN s_core_config_elements ce ON cv.element_id = ce.id
                LEFT JOIN s_core_config_forms cf ON ce.form_id = cf.id
                LEFT JOIN s_core_shops cs ON cs.id = cv.shop_id
                LEFT JOIN s_core_locales cl ON cl.id = cs.locale_id
                WHERE ce.name = ? AND cl.locale LIKE ? OR cl.locale LIKE ?
				',
                [
                    'locales',
                    '%'.$parameters[0].'%',
                    '%'.$parameters[1].'%'
                ]
            ) ?: [];
        } else {
            $shopId = $db->fetchRow(
                '
                    SELECT cv.shop_id FROM `s_core_config_values` AS cv
                    LEFT JOIN s_core_config_elements ce ON cv.element_id = ce.id
                    LEFT JOIN s_core_config_forms cf ON ce.form_id = cf.id
                    LEFT JOIN s_core_shops cs ON cs.id = cv.shop_id
                    LEFT JOIN s_core_locales cl ON cl.id = cs.locale_id
                    WHERE ce.name = ? AND cl.locale LIKE ?
                    ',
                [
                    'locales',
                    '%'.$locale.'%',
                ]
            ) ?: [];
        }
        return $shopId;

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
            return $builder->select('shop')
                ->from(Shop::class, 'shop')
                ->leftJoin('shop.locale', 'locale')
                ->where('locale.locale LIKE :countryCode')
                ->setParameter('countryCode', '%'.$countryCode)
                ->getQuery()
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
}
