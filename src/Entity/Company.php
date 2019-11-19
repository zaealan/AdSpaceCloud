<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Company
 * @ORM\Table(name="company", indexes={@ORM\Index(name="company_city_fk_1", columns={"co_city_id"}), @ORM\Index(name="company_user_fk_1", columns={"co_user_creator"})})
 * @ORM\Entity(repositoryClass="App\Entity\CompanyRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Company {

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * @var integer
     * @ORM\Column(name="co_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="co_company_name", type="string", length=100, nullable=false)
     */
    private $coCompanyName;

    /**
     * @var string
     * @ORM\Column(name="co_address", type="string", length=100, nullable=false)
     */
    private $coAddress;

    /**
     * @var string
     * @ORM\Column(name="co_company_identification", type="string", length=100, nullable=true)
     */
    private $coCompanyIdentification;

    /**
     * @var \DateTime
     * @ORM\Column(name="co_date_created", type="datetime", nullable=false)
     */
    private $coDateCreated;

    /**
     * @var integer
     * @ORM\Column(name="co_status", type="smallint", nullable=true)
     */
    private $coStatus;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="co_user_creator", referencedColumnName="us_id")
     * })
     */
    private $coUserCreator;

    /**
     * @var \App\Entity\City
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="co_city_id", referencedColumnName="ci_id")
     * })
     */
    private $city;

    /**
     * @var \App\Entity\Zipcode
     * @ORM\ManyToOne(targetEntity="App\Entity\Zipcode")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="al_zip_code_id", referencedColumnName="zc_id", nullable=true)
     * })
     */
    private $zipcode;

    /**
     * @var integer
     * @ORM\Column(name="co_is_kijho", type="smallint", nullable=true, options={"default":"0"})
     */
    private $coIsKijho = 0;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getCoAddress() {
        return $this->coAddress;
    }

    /**
     * @param type $coAddress
     */
    public function setCoAddress($coAddress) {
        $this->coAddress = $coAddress;
    }

    /**
     * @return type
     */
    public function getCoCompanyName() {
        return $this->coCompanyName;
    }

    /**
     * @return type
     */
    public function getCoCompanyIdentification() {
        return $this->coCompanyIdentification;
    }

    /**
     * @return type
     */
    public function getCoDateCreated() {
        return $this->coDateCreated;
    }

    /**
     * @return type
     */
    public function getCoUserCreator() {
        return $this->coUserCreator;
    }

    /**
     * @return type
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @return type
     */
    public function getCoStatus() {
        return $this->coStatus;
    }

    /**
     * @return type
     */
    public function getZipcode() {
        return $this->zipcode;
    }

    /**
     * @param type $coCompanyName
     */
    public function setCoCompanyName($coCompanyName) {
        $this->coCompanyName = $coCompanyName;
    }

    /**
     * @param type $coCompanyIdentification
     */
    public function setCoCompanyIdentification($coCompanyIdentification) {
        $this->coCompanyIdentification = $coCompanyIdentification;
    }

    /**
     * @param \DateTime $coDateCreated
     */
    public function setCoDateCreated(\DateTime $coDateCreated) {
        $this->coDateCreated = $coDateCreated;
    }

    /**
     * @param \App\Entity\User $coUserCreator
     */
    public function setCoUserCreator(\App\Entity\User $coUserCreator) {
        $this->coUserCreator = $coUserCreator;
    }

    /**
     * @param \App\Entity\City $coCity
     */
    public function setCity(\App\Entity\City $coCity) {
        $this->city = $coCity;
    }

    /**
     * @param type $coStatus
     */
    public function setCoStatus($coStatus = 1) {
        $this->coStatus = $coStatus;
    }

    /**
     * @param \App\Entity\Zipcode $alZipCode
     */
    public function setZipcode(\App\Entity\Zipcode $alZipCode = null) {
        $this->zipcode = $alZipCode;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->getCoCompanyName() . "";
    }

    /**
     * @return string
     */
    public function getTextStatus() {
        $text = '';
        switch ($this->coStatus) {
            case static::STATUS_ACTIVE: $text = 'Active';
                break;
            case static::STATUS_INACTIVE: $text = 'Inactive';
                break;
            default:
                $text = 'Inactive';
        }
        return $text;
    }

    /**
     * @return type
     */
    public function getCoIsKijho() {
        return $this->coIsKijho;
    }

    /**
     * @param type $coIsKijho
     */
    public function setCoIsKijho($coIsKijho) {
        $this->coIsKijho = $coIsKijho;
    }

    /**
     * Fucion generico para realizar busquedas de esta entidad
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $alias
     * @param type $search
     * @return type
     */
    public static function filterSearchParameters($alias, $search) {

        $textParameters = '';
        $parameters = [];

        if (isset($search ['coCompanyName']) && $search ['coCompanyName'] != '') {
            $textParameters .= " AND " . $alias . ".coCompanyName LIKE :coCompanyName";
            $parameters ['coCompanyName'] = "%" . $search ['coCompanyName'] . "%";
        }
        if (isset($search ['coCompanyIdentification']) && $search ['coCompanyIdentification'] != '') {
            $textParameters .= " AND " . $alias . ".coCompanyIdentification LIKE :coCompanyIdentification";
            $parameters ['coCompanyIdentification'] = "%" . $search ['coCompanyIdentification'] . "%";
        }
        if (isset($search ['coStatus']) && $search ['coStatus'] != '') {
            $textParameters .= " AND " . $alias . ".coStatus = :coStatus";
            $parameters ['coStatus'] = $search ['coStatus'];
        }

        return ['text' => $textParameters, 'parameters' => $parameters];
    }

    /**
     * FilterOrderParameters retorna el adecaudo orderBy segun los parametros
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param string $alias
     * @param Array $order
     * @return string
     */
    public static function filterOrderParameters($alias, $order) {

        $orderBy = ' ORDER BY ' . $alias . '.coCompanyName ASC';

        if (isset($order ['order_by_company_name']) && $order ['order_by_company_name'] != '') {
            if ($order ['order_by_company_name'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".coCompanyName DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".coCompanyName ASC";
            }
        } elseif (isset($order ['order_by_company_identification']) && $order ['order_by_company_identification'] != '') {
            if ($order ['order_by_company_identification'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".coCompanyIdentification DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".coCompanyIdentification ASC";
            }
        } elseif (isset($order ['order_by_company_status']) && $order ['order_by_company_status'] != '') {
            if ($order ['order_by_company_status'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".coStatus DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".coStatus ASC";
            }
        }

        return $orderBy;
    }

}
