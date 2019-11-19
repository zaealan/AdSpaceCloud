<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LicenseDevice
 * @ORM\Table(name="license_rate")
 * @ORM\Entity(repositoryClass="App\Entity\LicenseRateRepository")
 */
class LicenseRate {

    const LICENSE_TYPE_SERVER = 1;
    const LICENSE_TYPE_CLIENT = 2;

    /**
     * @var integer
     * @ORM\Column(name="lr_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Entity\Account
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lr_company_id", referencedColumnName="co_id", nullable=true)
     * })
     */
    private $lrCompanyId;

    /**
     * @var integer
     * @ORM\Column(name="lr_device_type", type="integer", nullable=true, options={"default":"1"})
     */
    private $lrDevicesType = 1;

    /**
     * @var string
     * @ORM\Column(name="lr_price", type="decimal", precision=10, scale=2, nullable=true, options={"default":"0.00"})
     */
    private $lrPrice = 0;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getLrCompanyId() {
        return $this->lrCompanyId;
    }

    /**
     * @return type
     */
    public function getLrDevicesType() {
        return $this->lrDevicesType;
    }

    /**
     * @return type
     */
    public function getLrPrice() {
        return $this->lrPrice;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param \App\Entity\Company $lrCompanyId
     */
    public function setLrCompanyId(\App\Entity\Company $lrCompanyId) {
        $this->lrCompanyId = $lrCompanyId;
    }

    /**
     * @param type $lrDevicesType
     */
    public function setLrDevicesType($lrDevicesType) {
        $this->lrDevicesType = $lrDevicesType;
    }

    /**
     * @param type $lrPrice
     */
    public function setLrPrice($lrPrice) {
        $this->lrPrice = $lrPrice;
    }

}
