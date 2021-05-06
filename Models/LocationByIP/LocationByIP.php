<?php


namespace NetzhirschRedirect\Models\LocationByIP;


use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_netzhirsch_location_by_ip")
 * @ORM\HasLifecycleCallbacks
 */
class LocationByIP extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @ORM\Column(name="ip_from", type="string", nullable=true)
     *
     */
    private $ipFrom;

    /**
     * @ORM\Column(name="ip_to", type="string", nullable=true)
     */
    private $ipTo;

    /**
     * @ORM\Column(name="country_code", type="string", length=2, nullable=true)
     */
    private $countryCode;

    /**
     * @ORM\Column(name="country_name", type="string", length=64, nullable=true)
     */
    private $countryName;

    /**
     * @return mixed
     */
    public function getIpFrom()
    {
        return $this->ipFrom;
    }

    /**
     * @param mixed $ipFrom
     */
    public function setIpFrom($ipFrom)
    {
        $this->ipFrom = $ipFrom;
    }

    /**
     * @return mixed
     */
    public function getIpTo()
    {
        return $this->ipTo;
    }

    /**
     * @param mixed $ipTo
     */
    public function setIpTo($ipTo)
    {
        $this->ipTo = $ipTo;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return mixed
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * @param mixed $countryName
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;
    }
}
