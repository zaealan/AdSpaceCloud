<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AccountLicense
 * @ORM\Table(name="place_license", indexes={@ORM\Index(name="al_user_creator_id", columns={"al_user_creator_id"}), @ORM\Index(name="al_account_license_id", columns={"al_account_license_id"}), @ORM\Index(name="al_zip_code_id", columns={"al_zip_code_id"}), @ORM\Index(name="al_city_id", columns={"al_city_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\AccountLicenseRepository")
 */
class AccountLicense {

    const LICENSE_STATUS_PENDING = 0;
    const LICENSE_STATUS_ACTIVE = 1;
    const LICENSE_STATUS_INACTIVE = 2;
    const DEVICE_ANDROID_TYPE = 0;
    const DEVICE_LITE_TYPE = 1;
    const DEVICE_HYBRID_TYPE = 2;

    /**
     * @var integer
     * @ORM\Column(name="al_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="al_restaurant_name", type="string", length=100, nullable=false)
     */
    private $alRestaurantName;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_date_created", type="datetime", nullable=false)
     */
    private $alDateCreated;

    /**
     * @var integer
     * @ORM\Column(name="al_license_status", type="integer", nullable=false)
     */
    private $alLicenseStatus;

    /**
     * @var string
     * @ORM\Column(name="al_license_username", type="string", length=50, nullable=true)
     */
    private $alLicenseUsername;

    /**
     * @var string
     * @ORM\Column(name="al_contac_name", type="string", length=50, nullable=false)
     */
    private $alContacName;

    /**
     * @var string
     * @ORM\Column(name="al_license_email", type="string", length=100, nullable=false)
     * @Assert\Email(message = "The email '{{ value }}' is not a valid")
     */
    private $alLicenseEmail;

    /**
     * @var string
     * @ORM\Column(name="al_addres", type="string", length=100, nullable=false)
     */
    private $alAddres;

    /**
     * @var string
     * @ORM\Column(name="al_suit_po_box", type="string", length=50, nullable=true)
     */
    private $alSuitPoBox;

    /**
     * @var string
     * @ORM\Column(name="al_longitude", type="decimal", precision=16, scale=10, nullable=true)
     */
    private $alLongitude;

    /**
     * @var string
     * @ORM\Column(name="al_latitude", type="decimal", precision=16, scale=10, nullable=true)
     */
    private $alLatitude;

    /**
     * @var string
     * @ORM\Column(name="al_phone_number", type="string", length=50, nullable=false)
     */
    private $alPhoneNumber;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="al_user_creator_id", referencedColumnName="us_id", nullable=true)
     * })
     */
    private $alUserCreator;

    /**
     * @var \App\Entity\Account
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="al_account_license_id", referencedColumnName="ac_id")
     * })
     */
    private $alAccountLicense;

    /**
     * @var \App\Entity\Zipcode
     * @ORM\ManyToOne(targetEntity="App\Entity\Zipcode")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="al_zip_code_id", referencedColumnName="zc_id", nullable=true)
     * })
     */
    private $zipcode;

    /**
     * @var \App\Entity\City
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="al_city_id", referencedColumnName="ci_id", nullable=true)
     * })
     */
    private $city;

    /**
     * @var string
     * @ORM\Column(name="al_license_key", type="string", length=255, nullable=true, unique=true)
     */
    private $alLicenseKey;

    /**
     * @var string
     * @ORM\Column(name="al_has_android", type="boolean", nullable=true)
     */
    private $hasAndroid;

    /**
     * @var string
     * @ORM\Column(name="al_android_vname", type="string", length=8, nullable=true)
     */
    private $androidVersionName;

    /**
     * @var string
     * @ORM\Column(name="al_device_uid", type="string", length=60, nullable=true, unique=true)
     */
    private $deviceUid;

    /**
     * @var boolean
     * @ORM\Column(name="al_is_callcenter", type="boolean", nullable=true)
     */
    private $isCallCenter;

    /**
     * @var string
     * @ORM\Column(name="al_pushy_key", type="string", length=48, nullable=true)
     */
    private $pushyKey;

    /**
     * @var string
     * @ORM\Column(name="al_gcm_key", type="string", length=48, nullable=true)
     */
    private $gcmKey;

    /**
     * @var boolean
     * @ORM\Column(name="al_is_testing", type="boolean", nullable=true)
     */
    private $isTesting;

    /**
     * @var string
     * @ORM\Column(name="al_android_ip", type="string", length=16, nullable=true)
     */
    private $androidIP;

    /**
     * @var integer
     * @ORM\Column(name="al_times_checked_ip", type="integer", length=4, nullable=true, options={"default":"0"})
     */
    private $timesCheckedIP;

    /**
     * @var string
     * @ORM\Column(name="al_apk_install_code", type="string", length=64, nullable=true, options={"default":"Standard"})
     */
    private $apkInstallCode;

    /**
     * @var string
     * @ORM\Column(name="al_has_loged_omt", type="boolean", nullable=true)
     */
    private $hasLogedOMT;

    /**
     * @var integer
     * @ORM\Column(name="al_type_test_license", type="integer", length=1, nullable=true)
     */
    private $typeTestLicense;

    /**
     * @var string
     * @ORM\Column(name="auth_seed", type="string", length=64, nullable=true)
     */
    private $authSeed;

    /**
     * @var integer
     * @ORM\Column(name="is_plus_license", type="boolean", nullable=true, options={"default":"1"})
     */
    private $isPlusLicense = true;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_consecutive_android_drying", type="datetime", nullable=true)
     */
    private $lastConsecutiveAndroidDryingDB;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_s3_dbupload", type="datetime", nullable=true)
     */
    private $lastDateS3DBUpload;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_auto_cleanse_date", type="datetime", nullable=true)
     */
    private $lastDateAutoCleanseMade;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_duplicated_invoice_check", type="datetime", nullable=true)
     */
    private $lastDuplicatedInvoiceCheck;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_timeclock_out_check", type="datetime", nullable=true)
     */
    private $lastTimeClockOutCheck;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_device_expiration_check", type="datetime", nullable=true)
     */
    private $lastDeviceExpirationCheck;

    /**
     * @var boolean
     * @ORM\Column(name="al_zipcodes_are_needed", type="boolean", nullable=true, options={"default":"0"})
     */
    private $needZipcodes = false;

    /**
     * @var boolean
     * @ORM\Column(name="has_level_zero", type="boolean", nullable=true, options={"default":"0"})
     */
    private $hasLevelZero = false;

    /**
     * @var string
     * @ORM\Column(name="al_level_zero_percentage", type="decimal", precision=5, scale=3, nullable=true, options={"default":"0.000"})
     */
    private $levelZeroPercentage;

    /**
     * @var string
     * @ORM\Column(name="al_level_zero_gateway_percentage", type="decimal", precision=5, scale=3, nullable=true, options={"default":"0.000"})
     */
    private $levelZeroGatewayPercentage;

    /**
     * @var string
     * @ORM\Column(name="omt_sync", type="guid", nullable=true)
     */
    private $omtSync;

    /**
     * @var integer
     * @ORM\Column(name="al_android_database_size", type="integer", nullable=true)
     */
    private $androidDatabaseSize;

    /**
     * @var integer
     * @ORM\Column(name="al_is_level_light", type="boolean", nullable=true, options={"default":"0"})
     */
    private $isLevelLight = false;

    /**
     * @var array
     * @ORM\Column(name="al_levellight_user", type="json_array", nullable=true)
     */
    private $levelLightUser;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_s3_web_dbupload", type="datetime", nullable=true)
     */
    private $lastDateS3WebDBUpload;

    /**
     * @var array
     * @ORM\Column(name="al_last_logued_device_kind", type="integer", nullable=true, options={"default":"1"})
     */
    private $lastloguedDeviceKind = 1;

    /**
     * @var int
     * @ORM\Column(name="al_service_prices_already_run", type="datetime", nullable=true)
     */
    private $servicePricesAlreadyRun;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_nonsynced_brandservice", type="datetime", nullable=true)
     */
    private $nonSyncedBrandAndServiceCheck;

    /**
     * @var integer
     * @ORM\Column(name="al_lastCleanseLeftDays", type="integer", nullable=true)
     */
    private $androidLastCleanseLeftDays;

    /**
     * @var int
     * @ORM\Column(name="al_registaction_already_cleaned", type="datetime", nullable=true)
     */
    private $registActionGarbageAlreadyCleanedDT;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_updated_date", type="datetime", nullable=true)
     */
    private $versionUpdatedDate;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="al_consecutive_android_drying_second_stage", type="datetime", nullable=true)
     */
    private $lastConsecutiveAndroidDryingDBSecondStage;

    /**
     * @var integer
     * @ORM\Column(name="al_last_login_was_ok", type="boolean", nullable=true, options={"default":"1"})
     */
    private $lastLoginWasOk = true;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_login_date", type="datetime", nullable=true)
     */
    private $lastLoginDate;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_validated_login_date", type="datetime", nullable=true)
     */
    private $lastValidatedLoginDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="al_last_cleaning_of_licensor_database", type="datetime", nullable=true)
     */
    private $lastCleaningOfLicensorDatabase;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="al_schedule_menu_checked", type="datetime", nullable=true)
     */
    private $scheduleMenuChecked;

    /**
     * @return \DateTime
     */
    public function getScheduleMenuChecked(): \DateTime {
        return $this->scheduleMenuChecked;
    }

    /**
     * @param \DateTime $scheduleMenuChecked
     */
    public function setScheduleMenuChecked(\DateTime $scheduleMenuChecked = null) {
        $this->scheduleMenuChecked = $scheduleMenuChecked;
    }

    /**
     * @return \DateTime
     */
    public function getLastCleaningOfLicensorDatabase() {
        return $this->lastCleaningOfLicensorDatabase;
    }

    /**
     * @param \DateTime $lastCleaningOfLicensorDatabase
     */
    public function setLastCleaningOfLicensorDatabase(\DateTime $lastCleaningOfLicensorDatabase = null) {
        $this->lastCleaningOfLicensorDatabase = $lastCleaningOfLicensorDatabase;
    }
    
    /**
     * @return \DateTime
     */
    public function getLastLoginDate(): \DateTime {
        return $this->lastLoginDate;
    }

    /**
     * @return \DateTime
     */
    public function getLastValidatedLoginDate(): \DateTime {
        return $this->lastValidatedLoginDate;
    }

    /**
     * @param \DateTime $lastLoginDate
     */
    public function setLastLoginDate(\DateTime $lastLoginDate = null) {
        $this->lastLoginDate = $lastLoginDate;
    }

    /**
     * @param \DateTime $lastValidatedLoginDate
     */
    public function setLastValidatedLoginDate(\DateTime $lastValidatedLoginDate = null) {
        $this->lastValidatedLoginDate = $lastValidatedLoginDate;
    }

    /**
     * @return type
     */
    public function getLastLoginWasOk() {
        return $this->lastLoginWasOk;
    }

    /**
     * @param type $lastLoginWasOk
     */
    public function setLastLoginWasOk($lastLoginWasOk) {
        $this->lastLoginWasOk = $lastLoginWasOk;
    }
        
    /**
     * @return \DateTime
     */
    public function getLastConsecutiveAndroidDryingDBSecondStage() {
        return $this->lastConsecutiveAndroidDryingDBSecondStage;
    }

    /**
     * @param \DateTime $lastConsecutiveAndroidDryingDBSecondStage
     */
    public function setLastConsecutiveAndroidDryingDBSecondStage(\DateTime $lastConsecutiveAndroidDryingDBSecondStage = null) {
        $this->lastConsecutiveAndroidDryingDBSecondStage = $lastConsecutiveAndroidDryingDBSecondStage;
    }

    /**
     * @return \DateTime
     */
    public function getVersionUpdatedDate(): \DateTime {
        return $this->versionUpdatedDate;
    }

    /**
     * @param \DateTime $versionUpdatedDate
     */
    public function setVersionUpdatedDate(\DateTime $versionUpdatedDate = null) {
        $this->versionUpdatedDate = $versionUpdatedDate;
    }

    /**
     * @return type
     */
    public function getRegistActionGarbageAlreadyCleanedDT() {
        return $this->registActionGarbageAlreadyCleanedDT;
    }

    /**
     * @param type $registActionGarbageAlreadyCleanedDT
     */
    public function setRegistActionGarbageAlreadyCleanedDT(\DateTime $registActionGarbageAlreadyCleanedDT = null) {
        $this->registActionGarbageAlreadyCleanedDT = $registActionGarbageAlreadyCleanedDT;
    }

    /**
     * @return type
     */
    public function getAndroidLastCleanseLeftDays() {
        return $this->androidLastCleanseLeftDays;
    }

    /**
     * @param type $androidLastCleanseLeftDays
     */
    public function setAndroidLastCleanseLeftDays($androidLastCleanseLeftDays) {
        $this->androidLastCleanseLeftDays = $androidLastCleanseLeftDays;
    }

    /**
     * @return type
     */
    public function getNonSyncedBrandAndServiceCheck() {
        return $this->nonSyncedBrandAndServiceCheck;
    }

    /**
     * @param \DateTime $nonSyncedBrandAndServiceCheck
     */
    public function setNonSyncedBrandAndServiceCheck(\DateTime $nonSyncedBrandAndServiceCheck = null) {
        $this->nonSyncedBrandAndServiceCheck = $nonSyncedBrandAndServiceCheck;
    }

    /**
     * @return type
     */
    public function getServicePricesAlreadyRun() {
        return $this->servicePricesAlreadyRun;
    }

    /**
     * @param type $servicePricesAlreadyRun
     */
    public function setServicePricesAlreadyRun(\DateTime $servicePricesAlreadyRun = null) {
        $this->servicePricesAlreadyRun = $servicePricesAlreadyRun;
    }

    /**
     * @return type
     */
    public function getLastloguedDeviceKind() {
        return $this->lastloguedDeviceKind;
    }

    /**
     * @param type $lastloguedDeviceKind
     */
    public function setLastloguedDeviceKind($lastloguedDeviceKind) {
        $this->lastloguedDeviceKind = $lastloguedDeviceKind;
    }

    /**
     * @return type
     */
    public function getLastDateS3WebDBUpload() {
        return $this->lastDateS3WebDBUpload;
    }

    /**
     * @param \DateTime $lastDateS3WebDBUpload
     */
    public function setLastDateS3WebDBUpload(\DateTime $lastDateS3WebDBUpload = null) {
        $this->lastDateS3WebDBUpload = $lastDateS3WebDBUpload;
    }

    /**
     * @return type
     */
    public function getLevelLightUser() {
        return $this->levelLightUser;
    }

    /**
     * @param type $levelLightUser
     */
    public function setLevelLightUser($levelLightUser) {
        $this->levelLightUser = $levelLightUser;
    }

    /**
     * @return type
     */
    public function getAndroidDatabaseSize() {
        return $this->androidDatabaseSize;
    }

    /**
     * @return type
     */
    public function getIsLevelLight() {
        return $this->isLevelLight;
    }

    /**
     * @param type $isLevelLight
     */
    public function setIsLevelLight($isLevelLight) {
        $this->isLevelLight = $isLevelLight;
    }

    /**
     * @param type $androidDatabaseSize
     */
    public function setAndroidDatabaseSize($androidDatabaseSize) {
        $this->androidDatabaseSize = $androidDatabaseSize;
    }

    /**
     * @return type
     */
    public function getOmtSync() {
        return $this->omtSync;
    }

    /**
     * @param type $omtSync
     */
    public function setOmtSync($omtSync) {
        $this->omtSync = $omtSync;
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
    public function getLevelZeroGatewayPercentage() {
        return $this->levelZeroGatewayPercentage;
    }

    /**
     * @param type $levelZeroGatewayPercentage
     */
    public function setLevelZeroGatewayPercentage($levelZeroGatewayPercentage) {
        $this->levelZeroGatewayPercentage = $levelZeroGatewayPercentage;
    }

    /**
     * @return type
     */
    public function getLevelZeroPercentage() {
        return $this->levelZeroPercentage;
    }

    /**
     * @param type $levelZeroPercentage
     */
    public function setLevelZeroPercentage($levelZeroPercentage) {
        $this->levelZeroPercentage = $levelZeroPercentage;
    }

    /**
     * @return type
     */
    public function getHasLevelZero() {
        return $this->hasLevelZero;
    }

    /**
     * @param type $hasLevelZero
     */
    public function setHasLevelZero($hasLevelZero) {
        $this->hasLevelZero = $hasLevelZero;
    }

    /**
     * @return \DateTime
     */
    public function getNeedZipcodes() {
        return $this->needZipcodes;
    }

    /**
     * @param \DateTime $needZipcodes
     */
    public function setNeedZipcodes($needZipcodes = null) {
        $this->needZipcodes = $needZipcodes;
    }

    /**
     * @return type
     */
    public function getLastDeviceExpirationCheck() {
        return $this->lastDeviceExpirationCheck;
    }

    /**
     * @param \DateTime $lastDeviceExpirationCheck
     */
    public function setLastDeviceExpirationCheck(\DateTime $lastDeviceExpirationCheck = null) {
        $this->lastDeviceExpirationCheck = $lastDeviceExpirationCheck;
    }

    /**
     * @return type
     */
    public function getLastTimeClockOutCheck() {
        return $this->lastTimeClockOutCheck;
    }

    /**
     * @param \DateTime $lastTimeClockOutCheck
     */
    public function setLastTimeClockOutCheck(\DateTime $lastTimeClockOutCheck = null) {
        $this->lastTimeClockOutCheck = $lastTimeClockOutCheck;
    }

    /**
     * @return \DateTime
     */
    public function getLastDuplicatedInvoiceCheck() {
        return $this->lastDuplicatedInvoiceCheck;
    }

    /**
     * @param \DateTime $lastDuplicatedInvoiceCheck
     */
    function setLastDuplicatedInvoiceCheck(\DateTime $lastDuplicatedInvoiceCheck = null) {
        $this->lastDuplicatedInvoiceCheck = $lastDuplicatedInvoiceCheck;
    }

    /**
     * @return \DateTime
     */
    public function getLastDateS3DBUpload() {
        return $this->lastDateS3DBUpload;
    }

    /**
     * @return \DateTime
     */
    public function getLastDateAutoCleanseMade() {
        return $this->lastDateAutoCleanseMade;
    }

    /**
     * @param \DateTime $lastDateAutoCleanseMade
     */
    public function setLastDateAutoCleanseMade(\DateTime $lastDateAutoCleanseMade = null) {
        $this->lastDateAutoCleanseMade = $lastDateAutoCleanseMade;
    }

    /**
     * @param \DateTime $lastDateS3DBUpload
     */
    public function setLastDateS3DBUpload(\DateTime $lastDateS3DBUpload = null) {
        $this->lastDateS3DBUpload = $lastDateS3DBUpload;
    }

    /**
     * @return type
     */
    public function getAndroidVersionName() {
        return $this->androidVersionName;
    }

    /**
     * @param type $androidVersionName
     */
    public function setAndroidVersionName($androidVersionName) {
        $this->androidVersionName = $androidVersionName;
    }

    /**
     * @return type
     */
    public function getAlRestaurantName() {
        return $this->alRestaurantName;
    }

    /**
     * @return type
     */
    public function getAlDateCreated() {
        return $this->alDateCreated;
    }

    /**
     * @return type
     */
    public function getAlLicenseStatus() {
        return $this->alLicenseStatus;
    }

    /**
     * @return type
     */
    public function getAlLicenseUsername() {
        return $this->alLicenseUsername;
    }

    /**
     * @return type
     */
    public function getAlContacName() {
        return $this->alContacName;
    }

    /**
     * @return type
     */
    public function getAlLicenseEmail() {
        return $this->alLicenseEmail;
    }

    /**
     * @return type
     */
    public function getAlAddres() {
        return $this->alAddres;
    }

    /**
     * @return type
     */
    public function getAlSuitPoBox() {
        return $this->alSuitPoBox;
    }

    /**
     * @return type
     */
    public function getAlLongitude() {
        return $this->alLongitude;
    }

    /**
     * @return type
     */
    public function getAlLatitude() {
        return $this->alLatitude;
    }

    /**
     * @return type
     */
    public function getAlPhoneNumber() {
        return $this->alPhoneNumber;
    }

    /**
     * @return type
     */
    public function getAlUserCreator() {
        return $this->alUserCreator;
    }

    /**
     * @return type
     */
    public function getAlAccountLicense() {
        return $this->alAccountLicense;
    }

    /**
     * @return type
     */
    public function getZipcode() {
        return $this->zipcode;
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
    public function getAlLicenseKey() {
        return $this->alLicenseKey;
    }

    /**
     * @param type $id
     */
    public function setid($id) {
        $this->id = $id;
    }

    /**
     * @param type $alRestaurantName
     */
    public function setAlRestaurantName($alRestaurantName) {
        $this->alRestaurantName = $alRestaurantName;
    }

    /**
     * @param \DateTime $alDateCreated
     */
    public function setAlDateCreated(\DateTime $alDateCreated = null) {
        $this->alDateCreated = $alDateCreated;
    }

    /**
     * @param type $alLicenseStatus
     */
    public function setAlLicenseStatus($alLicenseStatus) {
        $this->alLicenseStatus = $alLicenseStatus;
    }

    /**
     * @param type $alLicenseUsername
     */
    public function setAlLicenseUsername($alLicenseUsername) {
        $this->alLicenseUsername = $alLicenseUsername;
    }

    /**
     * @param type $alContacName
     */
    public function setAlContacName($alContacName) {
        $this->alContacName = $alContacName;
    }

    /**
     * @param type $alLicenseEmail
     */
    public function setAlLicenseEmail($alLicenseEmail) {
        $this->alLicenseEmail = $alLicenseEmail;
    }

    /**
     * @param type $alAddres
     */
    public function setAlAddres($alAddres) {
        $this->alAddres = $alAddres;
    }

    /**
     * @param type $alSuitPoBox
     */
    public function setAlSuitPoBox($alSuitPoBox = null) {
        $this->alSuitPoBox = $alSuitPoBox;
    }

    /**
     * @param type $alLongitude
     */
    public function setAlLongitude($alLongitude = null) {
        $this->alLongitude = $alLongitude;
    }

    /**
     * @param type $alLatitude
     */
    public function setAlLatitude($alLatitude = null) {
        $this->alLatitude = $alLatitude;
    }

    /**
     * @param type $alPhoneNumber
     */
    public function setAlPhoneNumber($alPhoneNumber) {
        $this->alPhoneNumber = $alPhoneNumber;
    }

    /**
     * @param \App\Entity\User $alUserCreator
     */
    public function setAlUserCreator(\App\Entity\User $alUserCreator = null) {
        $this->alUserCreator = $alUserCreator;
    }

    /**
     * @param \App\Entity\Account $alAccountLicense
     */
    public function setAlAccountLicense(\App\Entity\Account $alAccountLicense = null) {
        $this->alAccountLicense = $alAccountLicense;
    }

    /**
     * @param \App\Entity\Zipcode $alZipCode
     */
    public function setZipcode(\App\Entity\Zipcode $alZipCode = null) {
        $this->zipcode = $alZipCode;
    }

    /**
     * @param \App\Entity\City $alCity
     */
    public function setCity(\App\Entity\City $alCity = null) {
        $this->city = $alCity;
    }

    /**
     * @param type $alLicenseKey
     */
    public function setAlLicenseKey($alLicenseKey) {
        $this->alLicenseKey = $alLicenseKey;
    }

    /**
     * @return type
     */
    public function getHasAndroid() {
        return $this->hasAndroid;
    }

    /**
     * @param type $hasAndroid
     */
    public function setHasAndroid($hasAndroid) {
        $this->hasAndroid = $hasAndroid;
    }

    /**
     * @return type
     */
    public function getDeviceUid() {
        return $this->deviceUid;
    }

    /**
     * @param type $deviceUid
     */
    public function setDeviceUid($deviceUid) {
        $this->deviceUid = $deviceUid;
    }

    /**
     * @return type
     */
    public function getIsCallCenter() {
        return $this->isCallCenter;
    }

    /**
     * @param type $isCallCenter
     */
    public function setIsCallCenter($isCallCenter) {
        $this->isCallCenter = $isCallCenter;
    }

    /**
     * @return type
     */
    public function getPushyKey() {
        return $this->pushyKey;
    }

    /**
     * @param type $pushyKey
     */
    public function setPushyKey($pushyKey) {
        $this->pushyKey = $pushyKey;
    }

    /**
     * @return type
     */
    public function getGcmKey() {
        return $this->gcmKey;
    }

    /**
     * @param type $gcmKey
     */
    public function setGcmKey($gcmKey) {
        $this->gcmKey = $gcmKey;
    }

    /**
     * @return type
     */
    public function getIsTesting() {
        return $this->isTesting;
    }

    /**
     * @param type $isTesting
     */
    public function setIsTesting($isTesting) {
        $this->isTesting = $isTesting;
    }

    /**
     * @return type
     */
    public function getTimesCheckedIP() {
        return $this->timesCheckedIP;
    }

    /**
     * @param type $timesCheckedIP
     */
    public function setTimesCheckedIP($timesCheckedIP) {
        $this->timesCheckedIP = $timesCheckedIP;
    }

    /**
     * @return type
     */
    public function getAndroidIP() {
        return $this->androidIP;
    }

    /**
     * @param type $androidIP
     */
    public function setAndroidIP($androidIP) {
        $this->androidIP = $androidIP;
    }

    /**
     * @return type
     */
    public function getApkInstallCode() {
        return $this->apkInstallCode;
    }

    /**
     * @param type $apkInstallCode
     */
    public function setApkInstallCode($apkInstallCode) {
        $this->apkInstallCode = $apkInstallCode;
    }

    /**
     * @return type
     */
    public function getHasLogedOMT() {
        return $this->hasLogedOMT;
    }

    /**
     * @param type $hasLogedOMT
     */
    public function setHasLogedOMT($hasLogedOMT) {
        $this->hasLogedOMT = $hasLogedOMT;
    }

    /**
     * @return type
     */
    public function getTypeTestLicense() {
        return $this->typeTestLicense;
    }

    /**
     * @param type $typeTestLicense
     */
    public function setTypeTestLicense($typeTestLicense) {
        $this->typeTestLicense = $typeTestLicense;
    }

    /**
     * @return type
     */
    public function getAuthSeed() {
        return $this->authSeed;
    }

    /**
     * @param type $authSeed
     */
    public function setAuthSeed($authSeed) {
        $this->authSeed = $authSeed;
    }

    /**
     * @return type
     */
    public function getIsPlusLicense() {
        return $this->isPlusLicense;
    }

    /**
     * @param type $isPlusLicense
     */
    public function setIsPlusLicense($isPlusLicense) {
        $this->isPlusLicense = $isPlusLicense;
    }

    /**
     * @return type
     */
    public function getLastConsecutiveAndroidDryingDB() {
        return $this->lastConsecutiveAndroidDryingDB;
    }

    /**
     * @param type $lastConsecutiveAndroidDryingDB
     */
    public function setLastConsecutiveAndroidDryingDB(\DateTime $lastConsecutiveAndroidDryingDB = null) {
        $this->lastConsecutiveAndroidDryingDB = $lastConsecutiveAndroidDryingDB;
    }

    /**
     * @return string
     */
    public function getTextLicenseStatus() {
        $text = '';
        switch ($this->alLicenseStatus) {
            case static::LICENSE_STATUS_PENDING: $text = 'Pendig';
                break;
            case static::LICENSE_STATUS_ACTIVE: $text = 'Active';
                break;
            case static::LICENSE_STATUS_INACTIVE: $text = 'Inactive';
                break;
            default:
                break;
        }
        return $text;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->alRestaurantName . ' (' . $this->alLicenseUsername . ')';
    }

    /**
     * Funcion para realizar el filtrado de parametros de busqueda
     * correspondientes a esta entidad
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $alias salchichas y mucho mas
     * @param type $search arreglo con los parametros de busqueda a filtrar
     * correspondientes a la entidad
     * @return type un arreglo con un arreglo que contiene los parametros de
     * busqueda filtrados para una consulta y un string que sera parte del DQL
     * que realizara la busqueda correspondiente de las entidades en la base
     * de datos
     */
    public static function filterSearchParameters($alias, $search, $dataSize = "") {
        $textParameters = '';
        $parameters = [];

        if (isset($search ['alContacName']) && $search ['alContacName'] != '') {
            $textParameters .= " AND " . $alias . ".alContacName LIKE :alContacName";
            $parameters ['alContacName'] = "%" . $search ['alContacName'] . "%";
        }
        if (isset($search ['alLicenseEmail']) && $search ['alLicenseEmail'] != '') {
            $textParameters .= " AND " . $alias . ".alLicenseEmail LIKE :alLicenseEmail";
            $parameters ['alLicenseEmail'] = "%" . $search ['alLicenseEmail'] . "%";
        }
        if (isset($search ['alRestaurantName']) && $search ['alRestaurantName'] != '') {
            $textParameters .= " AND " . $alias . ".alRestaurantName LIKE :alRestaurantName";
            $parameters ['alRestaurantName'] = "%" . $search ['alRestaurantName'] . "%";
        }
        if (isset($search ['alAccountLicense']) && $search ['alAccountLicense'] != '') {
            $textParameters .= " AND " . $alias . ".alAccountLicense = :alAccountLicense";
            $parameters ['alAccountLicense'] = $search ['alAccountLicense'];
        }
        if (isset($search ['alLicenseUsername']) && $search ['alLicenseUsername'] != '') {
            $textParameters .= " AND " . $alias . ".alLicenseUsername LIKE :alLicenseUsername";
            $parameters ['alLicenseUsername'] = "%" . $search ['alLicenseUsername'] . "%";
        }
        if (isset($search['alUserCreator']) && $search['alUserCreator'] != '') {
            $parameters['alUserCreator'] = $search['alUserCreator'];
            $textParameters .= " AND " . $alias . ".alUserCreator = :alUserCreator";
        }
        if (isset($search ['deviceUid']) && $search ['deviceUid'] != '') {
            $textParameters .= " AND " . $alias . ".deviceUid LIKE :deviceUid";
            $parameters ['deviceUid'] = "%" . $search ['deviceUid'] . "%";
        }
        if (isset($search ['apkVersion']) && $search ['apkVersion'] != '') {
            $textParameters .= " AND " . $alias . ".androidVersionName LIKE :apkVersion";
            $parameters ['apkVersion'] = "%" . $search ['apkVersion'] . "%";
        }
        if (isset($search['androidLastCleanseLeftDays']) && $search['androidLastCleanseLeftDays'] != '') {
            $dateNow = new \DateTime('now');
            if ($search['androidLastCleanseLeftDays'] == 1) {
                $textParameters .= " AND " . $alias . ".lastConsecutiveAndroidDryingDB <= :androidClean"
                        . " AND " . $alias . ".androidDatabaseSize > " . $dataSize;
                $parameters ['androidClean'] = $dateNow;
            } else {
                $textParameters .= " AND " . $alias . ".androidDatabaseSize <= " . $dataSize;
            }
        }

        return ['text' => $textParameters, 'parameters' => $parameters];
    }

    /**
     * Funcion que permite filtrar los parametros de ordenamiento para la
     * entidad y retornar un DQL para realizar la consulta pertienete en la
     * base de datos
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $alias salchichas y mucho mas
     * @param type $order array que contiene los parametros de ordenamiento
     * para la entidad 
     * @return string que contiene el DQL con los parametros de ordenamiento
     * filtrados para esta entidad
     */
    public static function filterOrderParameters($alias, $order) {
        $orderBy = ' ORDER BY ' . $alias . '.alContacName ASC';

        if (isset($order ['order_by_contac_name']) && $order ['order_by_contac_name'] != '') {
            if ($order ['order_by_contac_name'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".alContacName DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".alContacName ASC";
            }
        } elseif (isset($order ['order_by_license_email']) && $order ['order_by_license_email'] != '') {
            if ($order ['order_by_license_email'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".alLicenseEmail DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".alLicenseEmail ASC";
            }
        } elseif (isset($order ['order_by_restaurant_name']) && $order ['order_by_restaurant_name'] != '') {
            if ($order ['order_by_restaurant_name'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".alRestaurantName DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".alRestaurantName ASC";
            }
        } elseif (isset($order ['order_by_nickname']) && $order ['order_by_nickname'] != '') {
            if ($order ['order_by_nickname'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".alLicenseUsername DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".alLicenseUsername ASC";
            }
        }

        return $orderBy;
    }

}
