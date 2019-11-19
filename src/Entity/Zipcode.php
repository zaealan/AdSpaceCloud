<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zipcode
 * @ORM\Table(name="zipcode", indexes={@ORM\Index(name="zipcode_city_fk_1", columns={"zc_city_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\ZipcodeRepository")
 */
class Zipcode {

    const STATUS_GMG_UNCHECKED = 0;
    const STATUS_GMG_CHECKED_MODIFIED = 1;
    const STATUS_GMG_CHECKED_UNMODIFIED = 2;
    const STATUS_GMG_UNCONSISTENT_CHECKED = 3;
    const STATUS_GMG_UNCONSISTENT_UNCHECKED = 4;
    const STATUS_GMG_UNCONSISTENT_CHECKED_NULL = 5;
    const STATUS_GMG_CONSISTENT_ADDED_BY_COMMAND = 6;

    /**
     * @var integer
     * @ORM\Column(name="zc_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $zcId;

    /**
     * @var string
     * @ORM\Column(name="zc_name", type="string", length=50, nullable=false)
     */
    private $zcName;

    /**
     * @var string
     * @ORM\Column(name="zc_latitude", type="decimal", precision=10, scale=8, nullable=false)
     */
    private $zcLatitude;

    /**
     * @var string
     * @ORM\Column(name="zc_longitude", type="decimal", precision=10, scale=8, nullable=false)
     */
    private $zcLongitude;

    /**
     * @var string
     * @ORM\Column(name="zc_city_id", type="string", length=50, nullable=false)
     */
    private $zcCity;

    /**
     * @var string
     * @ORM\Column(name="zc_state", type="string", length=40, nullable=false)
     */
    private $state;

    /**
     * @var boolean
     * @ORM\Column(name="zc_user_created", type="boolean", nullable=true)
     */
    private $zcUserCreated;

    /**
     * @var boolean
     * @ORM\Column(name="zc_gmg_checked", type="integer", nullable=true, options={"default":"0"})
     */
    private $gmgChecked;

    /**
     * @var boolean
     * @ORM\Column(name="zc_omt_created", type="boolean", nullable=true, options={"default":"0"})
     */
    private $omtCreated;

    /**
     * @return type
     */
    public function getZcId() {
        return $this->zcId;
    }

    /**
     * @return type
     */
    public function getZcName() {
        return $this->zcName;
    }

    /**
     * @return type
     */
    public function getZcLatitude() {
        return $this->zcLatitude;
    }

    /**
     * @return type
     */
    public function getZcLongitude() {
        return $this->zcLongitude;
    }

    /**
     * @return type
     */
    public function getZcCity() {
        return $this->zcCity;
    }

    /**
     * @param type $zcId
     */
    public function setZcId($zcId) {
        $this->zcId = $zcId;
    }

    /**
     * @param type $zcName
     */
    public function setZcName($zcName) {
        $this->zcName = $zcName;
    }

    /**
     * @param type $zcLatitude
     */
    public function setZcLatitude($zcLatitude) {
        $this->zcLatitude = $zcLatitude;
    }

    /**
     * @param type $zcLongitude
     */
    public function setZcLongitude($zcLongitude) {
        $this->zcLongitude = $zcLongitude;
    }

    /**
     * @param type $zcCity
     */
    public function setZcCity($zcCity) {
        $this->zcCity = $zcCity;
    }

    /**
     * @return type
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @param type $state
     */
    public function setState($state) {
        $this->state = $state;
    }

    /**
     * @return type
     */
    public function getZcUserCreated() {
        return $this->zcUserCreated;
    }

    /**
     * @param type $zcUserCreated
     */
    public function setZcUserCreated($zcUserCreated) {
        $this->zcUserCreated = $zcUserCreated;
    }

    /**
     * @return type
     */
    public function getGmgChecked() {
        return $this->gmgChecked;
    }

    /**
     * @param type $gmgChecked
     */
    public function setGmgChecked($gmgChecked) {
        $this->gmgChecked = $gmgChecked;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->zcName;
    }

    function getOmtCreated() {
        return $this->omtCreated;
    }

    function setOmtCreated($omtCreated) {
        $this->omtCreated = $omtCreated;
    }

}
