<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of PushSent
 * @ORM\Table(name="push_sent")
 * @ORM\Entity(repositoryClass="App\Entity\PushSentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PushSent {

    const STATUS_PUSH_PENDING = 1;
    const STATUS_PUSH_READED = 2;
    const STATUS_CURL_PENDING = 1;
    const STATUS_CURL_RESPONDED = 2;
    const STATUS_CURL_FINISHED = 3;
    const PUSH_TYPE_SYNC_DOWNUP = 1;
    const PUSH_TYPE_SYNC_UPDOWN = 2;
    const PUSH_TYPE_APK_UPDATE = 3;
    const PUSH_TYPE_LICENSE_DISABLED = 4;
    const PUSH_TYPE_LICENSE_ENABLED = 5;
    const PUSH_TYPE_LICENSE_RESET_UID = 6;
    const PUSH_TYPE_VALIDATE_ACCESSCODE = 7;
    const PUSH_TYPE_UPLOAD_S3_BACKUP = 8;
    const PUSH_TYPE_ASK_SYNC_DOWNUP = 9;
    const PUSH_TYPE_PING_TO_ANDROID = 10;
    const PUSH_TYPE_CURL_TO_OMT = 11;
    const PUSH_TYPE_UPLOAD_S3_LOGS = 12;
    const PUSH_TYPE_UPLOAD_S3_ERROR_LOGS = 13;
    const PUSH_TYPE_CUSTOM_PUSH_MESSAGE = 14;
    
    const SENDED_FIRST_BY_PARSE = 1;
    const SENDED_FIRST_BY_PUSHY = 2;
    const SENDED_FIRST_BY_GCM = 3;
    const SENDED_FIRST_BY_PUBNUB = 4;
    const SENDED_FIRST_BY_ONESIGNAL = 5;
    const SENDED_FIRST_BY_LICENSOR = 6;
    
    const SENDED_STAGE_PUSHY = 1;
    const SENDED_STAGE_PUBNUB = 2;

    /**
     * @var integer
     * @ORM\Column(name="ps_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(name="ps_sent_date", type="datetime", nullable=true)
     */
    private $sentDate;

    /**
     * @var string
     * @ORM\Column(name="ps_verification_code", type="string", length=16, nullable=true, unique=true)
     */
    private $verificationCode;

    /**
     * @var \DateTime
     * @ORM\Column(name="ps_respond_date", type="datetime", nullable=true)
     */
    private $respondDate;

    /**
     * @var integer
     * @ORM\Column(name="ps_status", type="integer", length=1, nullable=true, options={"default":"1"})
     */
    private $pushStatus;

    /**
     * @var integer
     * @ORM\Column(name="ps_type", type="integer", length=1, nullable=true)
     */
    private $pushType;

    /**
     * @var \App\Entity\AccountLicense
     * @ORM\ManyToOne(targetEntity="App\Entity\AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ps_license", referencedColumnName="al_id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $psLicense;

    /**
     * @var string
     * @ORM\Column(name="ps_application_mode", type="boolean", nullable=true, options={"default":"1"})
     */
    private $applicationMode;

    /**
     * @var string
     * información extra que llega sobre el servicio
     * @ORM\Column(name="ps_data", type="text", nullable=true)
     */
    private $dataInPush;

    /**
     * @var string
     * @ORM\Column(name="ps_is_resend", type="integer", nullable=true, options={"default":"0"})
     */
    private $pushToResend = 0;

    /**
     * @var \DateTime
     * @ORM\Column(name="ps_resend_date", type="datetime", nullable=true)
     */
    private $resendDate;

    /**
     * @var integer
     * @ORM\Column(name="ps_first_by", type="integer", length=1, nullable=true)
     */
    private $firstBy;

    /**
     * @var boolean
     * @ORM\Column(name="ps_asked_response", type="boolean", nullable=true)
     */
    private $askedResponse;

    /**
     * @var boolean
     * @ORM\Column(name="ps_error_email", type="boolean", nullable=true)
     */
    private $errorNotifyEmail;

    /**
     * @var boolean
     * @ORM\Column(name="ps_reset_completed", type="boolean", nullable=true)
     */
    private $wasResetCompleted;

    /**
     * @var \DateTime
     * @ORM\Column(name="ps_curl_send_date", type="datetime", nullable=true)
     */
    private $omtCurlSendDate;
    
    /**
     * @var string
     * json del curl enviado a OMT
     * @ORM\Column(name="ps_data_for_curl", type="text", nullable=true)
     */
    private $dataInCurl;

    /**
     * @var string
     * json del curl enviado a OMT
     * @ORM\Column(name="ps_data_for_curl_response", type="text", nullable=true)
     */
    private $dataInResponseCurl;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="ps_curl_updated_date", type="datetime", nullable=true)
     */
    private $omtCurlUpdatedDate;

    /**
     * @var integer
     * @ORM\Column(name="ps_curl_status", type="integer", length=1, nullable=true, options={"default":"1"})
     */
    private $omtCurlStatus;

    /**
     * @var integer
     * @ORM\Column(name="ps_curl_type", type="integer", length=1, nullable=true)
     */
    private $omtCurlType;
    
    /**
     * @var int
     * @ORM\Column(name="ps_curl_omt_resend_counter", type="integer", nullable=true, options={"default":"0"})
     */
    private $omtCurlResendCounter = 0;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="ps_curl_resend_date", type="datetime", nullable=true)
     */
    private $omtCurlResendDate;
    
    /**
     * @var boolean
     * @ORM\Column(name="ps_is_testing_push", type="boolean", nullable=true, options={"default":"0"})
     */
    private $isTestingPush = false;
    
    /**
     * @var int
     * @ORM\Column(name="ps_new_sended_stage", type="integer", nullable=true, options={"default":"1"})
     */
    private $newPushSendedStage = 1;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getNewPushSendedStage() {
        return $this->newPushSendedStage;
    }

    /**
     * @param type $newPushSendedStage
     */
    public function setNewPushSendedStage($newPushSendedStage) {
        $this->newPushSendedStage = $newPushSendedStage;
    }

    /**
     * @return type
     */
    public function getIsTestingPush() {
        return $this->isTestingPush;
    }
    
    /**
     * @param type $isTestingPush
     */
    public function setIsTestingPush($isTestingPush) {
        $this->isTestingPush = $isTestingPush;
    }
 
    /**
     * @return type
     */
    public function getDataInResponseCurl() {
        return $this->dataInResponseCurl;
    }

    /**
     * @return \DateTime
     */
    public function getOmtCurlUpdatedDate() {
        return $this->omtCurlUpdatedDate;
    }

    /**
     * @param \DateTime $omtCurlUpdatedDate
     */
    public function setOmtCurlUpdatedDate(\DateTime $omtCurlUpdatedDate = null) {
        $this->omtCurlUpdatedDate = $omtCurlUpdatedDate;
    }

    /**
     * @return \DateTime
     */
    public function getOmtCurlSendDate() {
        return $this->omtCurlSendDate;
    }

    /**
     * @param \DateTime $omtCurlSendDate
     */
    public function setOmtCurlSendDate(\DateTime $omtCurlSendDate = null) {
        $this->omtCurlSendDate = $omtCurlSendDate;
    }

    /**
     * @param type $dataInResponseCurl
     */
    public function setDataInResponseCurl($dataInResponseCurl) {
        $this->dataInResponseCurl = $dataInResponseCurl;
    }

    /**
     * @return \DateTime
     */
    public function getOmtCurlResendDate() {
        return $this->omtCurlResendDate;
    }

    /**
     * @param \DateTime $omtCurlResendDate
     */
    public function setOmtCurlResendDate(\DateTime $omtCurlResendDate = null) {
        $this->omtCurlResendDate = $omtCurlResendDate;
    }

    /**
     * @return type
     */
    public function getOmtCurlResendCounter() {
        return $this->omtCurlResendCounter;
    }

    /**
     * @param type $omtCurlResendCounter
     */
    public function setOmtCurlResendCounter($omtCurlResendCounter) {
        $this->omtCurlResendCounter = $omtCurlResendCounter;
    }

    /**
     * @return type
     */
    public function getDataInCurl() {
        return $this->dataInCurl;
    }

    /**
     * @return type
     */
    public function getOmtCurlStatus() {
        return $this->omtCurlStatus;
    }

    /**
     * @return type
     */
    public function getOmtCurlType() {
        return $this->omtCurlType;
    }

    /**
     * @param type $dataInCurl
     */
    public function setDataInCurl($dataInCurl) {
        $this->dataInCurl = $dataInCurl;
    }

    /**
     * @param type $omtCurlStatus
     */
    public function setOmtCurlStatus($omtCurlStatus) {
        $this->omtCurlStatus = $omtCurlStatus;
    }

    /**
     * @param type $omtCurlType
     */
    public function setOmtCurlType($omtCurlType) {
        $this->omtCurlType = $omtCurlType;
    }

    /**
     * @return type
     */
    public function getSentDate() {
        return $this->sentDate;
    }

    /**
     * @return type
     */
    public function getVerificationCode() {
        return $this->verificationCode;
    }

    /**
     * @return type
     */
    public function getRespondDate() {
        return $this->respondDate;
    }

    /**
     * @return type
     */
    public function getPushStatus() {
        return $this->pushStatus;
    }

    /**
     * @return type
     */
    public function getPushType() {
        return $this->pushType;
    }

    /**
     * @return type
     */
    public function getPsLicense() {
        return $this->psLicense;
    }

    /**
     * @param \DateTime $sentDate
     */
    public function setSentDate(\DateTime $sentDate = null) {
        $this->sentDate = $sentDate;
    }

    /**
     * @param type $verificationCode
     */
    public function setVerificationCode($verificationCode) {
        $this->verificationCode = $verificationCode;
    }

    /**
     * @param \DateTime $respondDate
     */
    public function setRespondDate(\DateTime $respondDate = null) {
        $this->respondDate = $respondDate;
    }

    /**
     * @param type $pushStatus
     */
    public function setPushStatus($pushStatus) {
        $this->pushStatus = $pushStatus;
    }

    /**
     * @param type $pushType
     */
    public function setPushType($pushType) {
        $this->pushType = $pushType;
    }

    /**
     * @param \App\Entity\AccountLicense $psLicense
     */
    public function setPsLicense(\App\Entity\AccountLicense $psLicense = null) {
        $this->psLicense = $psLicense;
    }

    /**
     * @return type
     */
    public function getApplicationMode() {
        return $this->applicationMode;
    }

    /**
     * @param type $applicationMode
     */
    public function setApplicationMode($applicationMode) {
        $this->applicationMode = $applicationMode;
    }

    /**
     * @return type
     */
    public function getDataInPush() {
        return $this->dataInPush;
    }

    /**
     * @param type $dataInPush
     */
    public function setDataInPush($dataInPush) {
        $this->dataInPush = $dataInPush;
    }

    /**
     * @return type
     */
    public function getPushToResend() {
        return $this->pushToResend;
    }

    /**
     * @param type $pushToResend
     */
    public function setPushToResend($pushToResend) {
        $this->pushToResend = $pushToResend;
    }

    /**
     * @return type
     */
    public function getResendDate() {
        return $this->resendDate;
    }

    /**
     * @param \DateTime $resendDate
     */
    public function setResendDate(\DateTime $resendDate = null) {
        $this->resendDate = $resendDate;
    }

    /**
     * @return type
     */
    public function getFirstBy() {
        return $this->firstBy;
    }

    /**
     * @param type $firstBy
     */
    public function setFirstBy($firstBy) {
        $this->firstBy = $firstBy;
    }

    /**
     * @return type
     */
    public function getAskedResponse() {
        return $this->askedResponse;
    }

    /**
     * @param type $askedResponse
     */
    public function setAskedResponse($askedResponse) {
        $this->askedResponse = $askedResponse;
    }

    /**
     * @return type
     */
    public function getErrorNotifyEmail() {
        return $this->errorNotifyEmail;
    }

    /**
     * @param type $errorNotifyEmail
     */
    public function setErrorNotifyEmail($errorNotifyEmail) {
        $this->errorNotifyEmail = $errorNotifyEmail;
    }

    /**
     * @return type
     */
    public function getWasResetCompleted() {
        return $this->wasResetCompleted;
    }

    /**
     * @param type $wasResetCompleted
     */
    public function setWasResetCompleted($wasResetCompleted) {
        $this->wasResetCompleted = $wasResetCompleted;
    }

}
