<?php


namespace NetzhirschRedirect\Models\Shop;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Models\Shop\Shop as ShopwareShop;

/**
 * @ORM\Table(name="s_core_shops")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Shop extends ShopwareShop
{

}
