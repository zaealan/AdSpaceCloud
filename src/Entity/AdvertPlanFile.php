<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\AccountLicense;

/**
 * Description of LicenseAdvertImage
 * @ORM\Table(name="place_advert_file")
 * @ORM\Entity(repositoryClass="App\Entity\AdvertPlanFileRepository")
 * @author aealan
 */
class AdvertPlanFile {

    CONST ADVERT_MAIN_IMAGE = 'MAINIMAGE';
    CONST ADVERT_MAIN_IMAGE_FILTER = 'mainimage';
    CONST ADVERT_ICON_IMAGE = 'ICONIMAGE';
    CONST ADVERT_ICON_IMAGE_FILTER = 'iconimage';
    CONST ADVERT_BACKGROUND_IMAGE = 'BACKGROUNDIMAGE';
    CONST ADVERT_BACKGROUND_IMAGE_FILTER = 'backgroundimage';

    /**
     * @var integer
     * @ORM\Column(name="pai_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="original_name", type="string", length=255, nullable=false)
     */
    private $originalName;

    /**
     * @var string
     *
     * @ORM\Column(name="mimetype", type="string", length=255, nullable=false)
     */
    private $mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=255, nullable=false)
     */
    private $extension;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=false)
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=255, nullable=false)
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $isUploadedInAws = false;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="sorting", type="integer", nullable=true)
     */
    private $sorting;

    /**
     * @var AccountLicense
     *
     * @ORM\ManyToOne(targetEntity="AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="license_station_id", referencedColumnName="al_id")
     * })
     */
    private $license;

    /**
     * @var AdvertisePlan
     * @ORM\ManyToOne(targetEntity="AdvertisePlan")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plan_advert_id", referencedColumnName="pap_id")
     * })
     */
    private $advertPlan;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive;
    
    /**
     * @var string
     * @ORM\Column(name="duration_in_seconds", type="integer", length=8, nullable=false)
     */
    private $timeDurationInSeconds;

    /**
     * @return type
     */
    public function getTimeDurationInSeconds() {
        return $this->timeDurationInSeconds;
    }

    /**
     * @param type $timeDurationInSeconds
     */
    public function setTimeDurationInSeconds($timeDurationInSeconds) {
        $this->timeDurationInSeconds = $timeDurationInSeconds;
    }

    /**
     * @return type
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * @param type $isActive
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getOriginalName() {
        return $this->originalName;
    }

    /**
     * @return type
     */
    public function getMimetype() {
        return $this->mimetype;
    }

    /**
     * @return type
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * @return type
     */
    public function getFileName() {
        return $this->fileName;
    }

    /**
     * @return type
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @return type
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return type
     */
    public function getIsUploadedInAws() {
        return $this->isUploadedInAws;
    }

    /**
     * @return type
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return type
     */
    public function getSorting() {
        return $this->sorting;
    }

    /**
     * @return AccountLicense
     */
    public function getLicense(): AccountLicense {
        return $this->license;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $originalName
     */
    public function setOriginalName($originalName) {
        $this->originalName = $originalName;
    }

    /**
     * @param type $mimetype
     */
    public function setMimetype($mimetype) {
        $this->mimetype = $mimetype;
    }

    /**
     * @param type $extension
     */
    public function setExtension($extension) {
        $this->extension = $extension;
    }

    /**
     * @param type $fileName
     */
    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    /**
     * @param type $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * @param type $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @param type $isUploadedInAws
     */
    public function setIsUploadedInAws($isUploadedInAws) {
        $this->isUploadedInAws = $isUploadedInAws;
    }

    /**
     * @param type $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @param type $sorting
     */
    public function setSorting($sorting) {
        $this->sorting = $sorting;
    }

    /**
     * @param AccountLicense $license
     */
    public function setLicense(AccountLicense $license) {
        $this->license = $license;
    }

    /**
     * @return \App\Entity\AdvertisePlan
     */
    public function getAdvertPlan(): AdvertisePlan {
        return $this->advertPlan;
    }

    /**
     * @param \App\Entity\AdvertisePlan $advertPlan
     */
    public function setAdvertPlan(AdvertisePlan $advertPlan) {
        $this->advertPlan = $advertPlan;
    }

}
