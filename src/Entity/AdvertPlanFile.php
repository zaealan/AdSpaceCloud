<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
     * @var string
     *
     * @ORM\Column(name="file_type", type="string", length=255, nullable=false)
     */
    private $fileType;
    
    /**
     * @var integer
     * @ORM\Column(name="number_of_watches", type="integer", length=8, nullable=true)
     */
    private $numberOfWatches;
    
    /**
     * @var integer
     * @ORM\Column(name="number_of_interaction", type="integer", length=8, nullable=true)
     */
    private $numberOfInteractions;
    
    /**
     * @var integer
     * @ORM\Column(name="number_of_requested_info", type="integer", length=8, nullable=true)
     */
    private $numberOfRequestedInfo;
    
    /**
     * @var string
     * @ORM\Column(name="advetr_client_email", type="string", length=100, nullable=false)
     * @Assert\Email(message = "The email '{{ value }}' is not a valid")
     */
    private $clientEmail;
    
    //////////////////// BackGroundImage
    
    /**
     * @var string
     *
     * @ORM\Column(name="bg_original_name", type="string", length=255, nullable=false)
     */
    private $originalBackGroundName;

    /**
     * @var string
     *
     * @ORM\Column(name="bg_mimetype", type="string", length=255, nullable=false)
     */
    private $backGroundMimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="bg_extension", type="string", length=255, nullable=false)
     */
    private $backGroundExtension;

    /**
     * @var string
     *
     * @ORM\Column(name="bg_file_name", type="string", length=255, nullable=false)
     */
    private $backGroundFileName;

    /**
     * @var string
     *
     * @ORM\Column(name="bg_size", type="string", length=255, nullable=false)
     */
    private $backGroundSize;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_bg_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $backGroundIsUploadedInAws = false;
    
    ///////////////// Logo Image
    
    /**
     * @var string
     *
     * @ORM\Column(name="lg_original_name", type="string", length=255, nullable=false)
     */
    private $originalLogoName;

    /**
     * @var string
     *
     * @ORM\Column(name="lg_mimetype", type="string", length=255, nullable=false)
     */
    private $logoMimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="lg_extension", type="string", length=255, nullable=false)
     */
    private $logoExtension;

    /**
     * @var string
     *
     * @ORM\Column(name="lg_file_name", type="string", length=255, nullable=false)
     */
    private $logoFileName;

    /**
     * @var string
     *
     * @ORM\Column(name="lg_size", type="string", length=255, nullable=false)
     */
    private $logoSize;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_lg_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $logoIsUploadedInAws = false;
    
    ///////////////// Descriptive Image 1
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv1_original_name", type="string", length=255, nullable=false)
     */
    private $originalDev1Name;

    /**
     * @var string
     *
     * @ORM\Column(name="dv1_mimetype", type="string", length=255, nullable=false)
     */
    private $dev1Mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="dv1_extension", type="string", length=255, nullable=false)
     */
    private $dev1Extension;

    /**
     * @var string
     *
     * @ORM\Column(name="dv1_file_name", type="string", length=255, nullable=false)
     */
    private $dev1FileName;

    /**
     * @var string
     *
     * @ORM\Column(name="dv1_size", type="string", length=255, nullable=false)
     */
    private $dev1Size;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_dv1_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $dev1IsUploadedInAws = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv1_description", type="text", nullable=true)
     */
    private $dev1Description;
    
    ///////////////// Descriptive Image 2
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv2_original_name", type="string", length=255, nullable=false)
     */
    private $originalDev2Name;

    /**
     * @var string
     *
     * @ORM\Column(name="dv2_mimetype", type="string", length=255, nullable=false)
     */
    private $dev2Mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="dv2_extension", type="string", length=255, nullable=false)
     */
    private $dev2Extension;

    /**
     * @var string
     *
     * @ORM\Column(name="dv2_file_name", type="string", length=255, nullable=false)
     */
    private $dev2FileName;

    /**
     * @var string
     *
     * @ORM\Column(name="dv2_size", type="string", length=255, nullable=false)
     */
    private $dev2Size;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_dv2_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $dev2IsUploadedInAws = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv2_description", type="text", nullable=true)
     */
    private $dev2Description;
    
    ///////////////// Descriptive Image 3
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv3_original_name", type="string", length=255, nullable=false)
     */
    private $originalDev3Name;

    /**
     * @var string
     *
     * @ORM\Column(name="dv3_mimetype", type="string", length=255, nullable=false)
     */
    private $dev3Mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="dv3_extension", type="string", length=255, nullable=false)
     */
    private $dev3Extension;

    /**
     * @var string
     *
     * @ORM\Column(name="dv3_file_name", type="string", length=255, nullable=false)
     */
    private $dev3FileName;

    /**
     * @var string
     *
     * @ORM\Column(name="dv3_size", type="string", length=255, nullable=false)
     */
    private $dev3Size;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_dv3_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $dev3IsUploadedInAws = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv3_description", type="text", nullable=true)
     */
    private $dev3Description;
    
    ///////////////// Descriptive Image 4
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv4_original_name", type="string", length=255, nullable=false)
     */
    private $originalDev4Name;

    /**
     * @var string
     *
     * @ORM\Column(name="dv4_mimetype", type="string", length=255, nullable=false)
     */
    private $dev4Mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="dv4_extension", type="string", length=255, nullable=false)
     */
    private $dev4Extension;

    /**
     * @var string
     *
     * @ORM\Column(name="dv4_file_name", type="string", length=255, nullable=false)
     */
    private $dev4FileName;

    /**
     * @var string
     *
     * @ORM\Column(name="dv4_size", type="string", length=255, nullable=false)
     */
    private $dev4Size;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_dv4_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $dev4IsUploadedInAws = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv4_description", type="text", nullable=true)
     */
    private $dev4Description;
    
    ///////////////// Descriptive Image 5
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv5_original_name", type="string", length=255, nullable=false)
     */
    private $originalDev5Name;

    /**
     * @var string
     *
     * @ORM\Column(name="dv5_mimetype", type="string", length=255, nullable=false)
     */
    private $dev5Mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="dv5_extension", type="string", length=255, nullable=false)
     */
    private $dev5Extension;

    /**
     * @var string
     *
     * @ORM\Column(name="dv5_file_name", type="string", length=255, nullable=false)
     */
    private $dev5FileName;

    /**
     * @var string
     *
     * @ORM\Column(name="dv5_size", type="string", length=255, nullable=false)
     */
    private $dev5Size;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_dv5_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $dev5IsUploadedInAws = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv5_description", type="text", nullable=true)
     */
    private $dev5Description;
    
    ///////////////// Descriptive Image 6
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv6_original_name", type="string", length=255, nullable=false)
     */
    private $originalDev6Name;

    /**
     * @var string
     *
     * @ORM\Column(name="dv6_mimetype", type="string", length=255, nullable=false)
     */
    private $dev6Mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="dv6_extension", type="string", length=255, nullable=false)
     */
    private $dev6Extension;

    /**
     * @var string
     *
     * @ORM\Column(name="dv6_file_name", type="string", length=255, nullable=false)
     */
    private $dev6FileName;

    /**
     * @var string
     *
     * @ORM\Column(name="dv6_size", type="string", length=255, nullable=false)
     */
    private $dev6Size;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_dv6_uploaded_in_aws", type="boolean", nullable=true)
     */
    private $dev6IsUploadedInAws = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dv6_description", type="text", nullable=true)
     */
    private $dev6Description;
    
    /**
     * @return type
     */
    public function getNumberOfWatches() {
        return $this->numberOfWatches;
    }

    /**
     * @return type
     */
    public function getNumberOfInteractions() {
        return $this->numberOfInteractions;
    }

    /**
     * @return type
     */
    public function getNumberOfRequestedInfo() {
        return $this->numberOfRequestedInfo;
    }

    /**
     * @return type
     */
    public function getClientEmail() {
        return $this->clientEmail;
    }

    /**
     * @param type $numberOfWatches
     */
    public function setNumberOfWatches($numberOfWatches) {
        $this->numberOfWatches = $numberOfWatches;
    }

    /**
     * @param type $numberOfInteractions
     */
    public function setNumberOfInteractions($numberOfInteractions) {
        $this->numberOfInteractions = $numberOfInteractions;
    }

    /**
     * @param type $numberOfRequestedInfo
     */
    public function setNumberOfRequestedInfo($numberOfRequestedInfo) {
        $this->numberOfRequestedInfo = $numberOfRequestedInfo;
    }

    /**
     * @param type $clientEmail
     */
    public function setClientEmail($clientEmail) {
        $this->clientEmail = $clientEmail;
    }

    /**
     * @return type
     */
    public function getFileType() {
        return $this->fileType;
    }

    /**
     * @param type $fileType
     */
    public function setFileType($fileType) {
        $this->fileType = $fileType;
    }

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

    public function getOriginalBackGroundName() {
        return $this->originalBackGroundName;
    }

    public function getBackGroundMimetype() {
        return $this->backGroundMimetype;
    }

    public function getBackGroundExtension() {
        return $this->backGroundExtension;
    }

    public function getBackGroundFileName() {
        return $this->backGroundFileName;
    }

    public function getBackGroundSize() {
        return $this->backGroundSize;
    }

    public function getBackGroundIsUploadedInAws() {
        return $this->backGroundIsUploadedInAws;
    }

    public function getOriginalLogoName() {
        return $this->originalLogoName;
    }

    public function getLogoMimetype() {
        return $this->logoMimetype;
    }

    public function getLogoExtension() {
        return $this->logoExtension;
    }

    public function getLogoFileName() {
        return $this->logoFileName;
    }

    public function getLogoSize() {
        return $this->logoSize;
    }

    public function getLogoIsUploadedInAws() {
        return $this->logoIsUploadedInAws;
    }

    public function getOriginalDev1Name() {
        return $this->originalDev1Name;
    }

    public function getDev1Mimetype() {
        return $this->dev1Mimetype;
    }

    public function getDev1Extension() {
        return $this->dev1Extension;
    }

    public function getDev1FileName() {
        return $this->dev1FileName;
    }

    public function getDev1Size() {
        return $this->dev1Size;
    }

    public function getDev1IsUploadedInAws() {
        return $this->dev1IsUploadedInAws;
    }

    public function getDev1Description() {
        return $this->dev1Description;
    }

    public function getOriginalDev2Name() {
        return $this->originalDev2Name;
    }

    public function getDev2Mimetype() {
        return $this->dev2Mimetype;
    }

    public function getDev2Extension() {
        return $this->dev2Extension;
    }

    public function getDev2FileName() {
        return $this->dev2FileName;
    }

    public function getDev2Size() {
        return $this->dev2Size;
    }

    public function getDev2IsUploadedInAws() {
        return $this->dev2IsUploadedInAws;
    }

    public function getDev2Description() {
        return $this->dev2Description;
    }

    public function getOriginalDev3Name() {
        return $this->originalDev3Name;
    }

    public function getDev3Mimetype() {
        return $this->dev3Mimetype;
    }

    public function getDev3Extension() {
        return $this->dev3Extension;
    }

    public function getDev3FileName() {
        return $this->dev3FileName;
    }

    public function getDev3Size() {
        return $this->dev3Size;
    }

    public function getDev3IsUploadedInAws() {
        return $this->dev3IsUploadedInAws;
    }

    public function getDev3Description() {
        return $this->dev3Description;
    }

    public function getOriginalDev4Name() {
        return $this->originalDev4Name;
    }

    public function getDev4Mimetype() {
        return $this->dev4Mimetype;
    }

    public function getDev4Extension() {
        return $this->dev4Extension;
    }

    public function getDev4FileName() {
        return $this->dev4FileName;
    }

    public function getDev4Size() {
        return $this->dev4Size;
    }

    public function getDev4IsUploadedInAws() {
        return $this->dev4IsUploadedInAws;
    }

    public function getDev4Description() {
        return $this->dev4Description;
    }

    public function getOriginalDev5Name() {
        return $this->originalDev5Name;
    }

    public function getDev5Mimetype() {
        return $this->dev5Mimetype;
    }

    public function getDev5Extension() {
        return $this->dev5Extension;
    }

    public function getDev5FileName() {
        return $this->dev5FileName;
    }

    public function getDev5Size() {
        return $this->dev5Size;
    }

    public function getDev5IsUploadedInAws() {
        return $this->dev5IsUploadedInAws;
    }

    public function getDev5Description() {
        return $this->dev5Description;
    }

    public function getOriginalDev6Name() {
        return $this->originalDev6Name;
    }

    public function getDev6Mimetype() {
        return $this->dev6Mimetype;
    }

    public function getDev6Extension() {
        return $this->dev6Extension;
    }

    public function getDev6FileName() {
        return $this->dev6FileName;
    }

    public function getDev6Size() {
        return $this->dev6Size;
    }

    public function getDev6IsUploadedInAws() {
        return $this->dev6IsUploadedInAws;
    }

    public function getDev6Description() {
        return $this->dev6Description;
    }

    public function setOriginalBackGroundName($originalBackGroundName) {
        $this->originalBackGroundName = $originalBackGroundName;
    }

    public function setBackGroundMimetype($backGroundMimetype) {
        $this->backGroundMimetype = $backGroundMimetype;
    }

    public function setBackGroundExtension($backGroundExtension) {
        $this->backGroundExtension = $backGroundExtension;
    }

    public function setBackGroundFileName($backGroundFileName) {
        $this->backGroundFileName = $backGroundFileName;
    }

    public function setBackGroundSize($backGroundSize) {
        $this->backGroundSize = $backGroundSize;
    }

    public function setBackGroundIsUploadedInAws($backGroundIsUploadedInAws) {
        $this->backGroundIsUploadedInAws = $backGroundIsUploadedInAws;
    }

    public function setOriginalLogoName($originalLogoName) {
        $this->originalLogoName = $originalLogoName;
    }

    public function setLogoMimetype($logoMimetype) {
        $this->logoMimetype = $logoMimetype;
    }

    public function setLogoExtension($logoExtension) {
        $this->logoExtension = $logoExtension;
    }

    public function setLogoFileName($logoFileName) {
        $this->logoFileName = $logoFileName;
    }

    public function setLogoSize($logoSize) {
        $this->logoSize = $logoSize;
    }

    public function setLogoIsUploadedInAws($logoIsUploadedInAws) {
        $this->logoIsUploadedInAws = $logoIsUploadedInAws;
    }

    public function setOriginalDev1Name($originalDev1Name) {
        $this->originalDev1Name = $originalDev1Name;
    }

    public function setDev1Mimetype($dev1Mimetype) {
        $this->dev1Mimetype = $dev1Mimetype;
    }

    public function setDev1Extension($dev1Extension) {
        $this->dev1Extension = $dev1Extension;
    }

    public function setDev1FileName($dev1FileName) {
        $this->dev1FileName = $dev1FileName;
    }

    public function setDev1Size($dev1Size) {
        $this->dev1Size = $dev1Size;
    }

    public function setDev1IsUploadedInAws($dev1IsUploadedInAws) {
        $this->dev1IsUploadedInAws = $dev1IsUploadedInAws;
    }

    public function setDev1Description($dev1Description) {
        $this->dev1Description = $dev1Description;
    }

    public function setOriginalDev2Name($originalDev2Name) {
        $this->originalDev2Name = $originalDev2Name;
    }

    public function setDev2Mimetype($dev2Mimetype) {
        $this->dev2Mimetype = $dev2Mimetype;
    }

    public function setDev2Extension($dev2Extension) {
        $this->dev2Extension = $dev2Extension;
    }

    public function setDev2FileName($dev2FileName) {
        $this->dev2FileName = $dev2FileName;
    }

    public function setDev2Size($dev2Size) {
        $this->dev2Size = $dev2Size;
    }

    public function setDev2IsUploadedInAws($dev2IsUploadedInAws) {
        $this->dev2IsUploadedInAws = $dev2IsUploadedInAws;
    }

    public function setDev2Description($dev2Description) {
        $this->dev2Description = $dev2Description;
    }

    public function setOriginalDev3Name($originalDev3Name) {
        $this->originalDev3Name = $originalDev3Name;
    }

    public function setDev3Mimetype($dev3Mimetype) {
        $this->dev3Mimetype = $dev3Mimetype;
    }

    public function setDev3Extension($dev3Extension) {
        $this->dev3Extension = $dev3Extension;
    }

    public function setDev3FileName($dev3FileName) {
        $this->dev3FileName = $dev3FileName;
    }

    public function setDev3Size($dev3Size) {
        $this->dev3Size = $dev3Size;
    }

    public function setDev3IsUploadedInAws($dev3IsUploadedInAws) {
        $this->dev3IsUploadedInAws = $dev3IsUploadedInAws;
    }

    public function setDev3Description($dev3Description) {
        $this->dev3Description = $dev3Description;
    }

    public function setOriginalDev4Name($originalDev4Name) {
        $this->originalDev4Name = $originalDev4Name;
    }

    public function setDev4Mimetype($dev4Mimetype) {
        $this->dev4Mimetype = $dev4Mimetype;
    }

    public function setDev4Extension($dev4Extension) {
        $this->dev4Extension = $dev4Extension;
    }

    public function setDev4FileName($dev4FileName) {
        $this->dev4FileName = $dev4FileName;
    }

    public function setDev4Size($dev4Size) {
        $this->dev4Size = $dev4Size;
    }

    public function setDev4IsUploadedInAws($dev4IsUploadedInAws) {
        $this->dev4IsUploadedInAws = $dev4IsUploadedInAws;
    }

    public function setDev4Description($dev4Description) {
        $this->dev4Description = $dev4Description;
    }

    public function setOriginalDev5Name($originalDev5Name) {
        $this->originalDev5Name = $originalDev5Name;
    }

    public function setDev5Mimetype($dev5Mimetype) {
        $this->dev5Mimetype = $dev5Mimetype;
    }

    public function setDev5Extension($dev5Extension) {
        $this->dev5Extension = $dev5Extension;
    }

    public function setDev5FileName($dev5FileName) {
        $this->dev5FileName = $dev5FileName;
    }

    public function setDev5Size($dev5Size) {
        $this->dev5Size = $dev5Size;
    }

    public function setDev5IsUploadedInAws($dev5IsUploadedInAws) {
        $this->dev5IsUploadedInAws = $dev5IsUploadedInAws;
    }

    public function setDev5Description($dev5Description) {
        $this->dev5Description = $dev5Description;
    }

    public function setOriginalDev6Name($originalDev6Name) {
        $this->originalDev6Name = $originalDev6Name;
    }

    public function setDev6Mimetype($dev6Mimetype) {
        $this->dev6Mimetype = $dev6Mimetype;
    }

    public function setDev6Extension($dev6Extension) {
        $this->dev6Extension = $dev6Extension;
    }

    public function setDev6FileName($dev6FileName) {
        $this->dev6FileName = $dev6FileName;
    }

    public function setDev6Size($dev6Size) {
        $this->dev6Size = $dev6Size;
    }

    public function setDev6IsUploadedInAws($dev6IsUploadedInAws) {
        $this->dev6IsUploadedInAws = $dev6IsUploadedInAws;
    }

    public function setDev6Description($dev6Description) {
        $this->dev6Description = $dev6Description;
    }

}
