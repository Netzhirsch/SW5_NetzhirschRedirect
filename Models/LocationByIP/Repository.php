<?php

namespace NetzhirschRedirect\Models\LocationByIP;

use Doctrine\ORM\NonUniqueResultException;
use Shopware\Components\Model\ModelRepository;

/**
 * Class Repository
 */
class Repository extends ModelRepository
{

    /**
     * @param $ipClient
     * @return LocationByIP|null
     */
    public function findByIPRange(
        $ipClient
    )
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        try {
            return $builder->select('locationByIP')
                ->from(LocationByIP::class, 'locationByIP')
                ->where('locationByIP.ipFrom <= :ipClient')
                ->andWhere('locationByIP.ipTo >= :ipClient')
                ->setParameter('ipClient', $ipClient)
                ->getQuery()
                ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
            return null;
        }
    }
}
