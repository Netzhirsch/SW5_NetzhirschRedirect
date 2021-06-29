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
                ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
            return null;
        }
    }

    /**
     * @param $locale
     * @return Shop|null
     */
    public function findOneOrNullByLocal(
        $locale
    )
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        try {
            return $builder->select('shop')
                ->from(Shop::class, 'shop')
                ->leftJoin('shop.locale', 'locale')
                ->where('locale.id LIKE :locale')
                ->setParameter('locale', '%'.$locale)
                ->getQuery()
                ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
            return null;
        }
    }
}
