<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ErrorAndroidEmailSent
 * @ORM\Table(name="error_android_email")
 * @ORM\Entity(repositoryClass="App\Entity\ErrorAndroidEmailSentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ErrorAndroidEmailSent {

    /**
     * esta listo para ser cargado en el sistema
     */
    const STATUS_SERVER_NOTIFIED = 1;

    /**
     * la respuesta esta lista para ser entregada
     */
    const STATUS_EMAIL_SENT = 2;

    /**
     * @var integer
     * @ORM\Column(name="eae_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="eae_error_msg", type="text")
     */
    private $errorMsg;

    /**
     * @var string
     * @ORM\Column(name="eae_device_uid", type="string", length=255)
     */
    private $uidErrorDevice;

    /**
     * @var \App\Entity\AccountLicense
     * @ORM\ManyToOne(targetEntity="App\Entity\AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eae_license", referencedColumnName="al_id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $liceseError;

    /**
     * @var \DateTime
     * @ORM\Column(name="eae_date_reported", type="datetime")
     */
    private $dateErrorReported;

    /**
     * @var \DateTime
     * @ORM\Column(name="eae_date_email_sent", type="datetime", nullable=true)
     */
    private $dateErrorEmailSent;

    /**
     * @var integer
     * @ORM\Column(name="eae_email_record_status", type="integer", nullable=false, options={"default":"1"})
     */
    private $emailRecordStatus;

    /**
     * @var string
     * @ORM\Column(name="eae_mailed_to", type="string", length=255, nullable=false)
     */
    private $mailedTo;

    /**
     * @var integer
     * @ORM\Column(name="eae_error_code", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $errorCode;

    /**
     * @var \DateTime
     * @ORM\Column(name="eae_date_last_report", type="datetime", nullable=true)
     */
    private $dateErrorLastReport;

    /**
     * @var integer
     * @ORM\Column(name="eae_error_counter", type="integer", nullable=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $errorCounter;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getErrorCode() {
        return $this->errorCode;
    }

    /**
     * @param type $errorCode
     */
    public function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
    }

    /**
     * @return type
     */
    public function getErrorMsg() {
        return $this->errorMsg;
    }

    /**
     * @return type
     */
    public function getUidErrorDevice() {
        return $this->uidErrorDevice;
    }

    /**
     * @return type
     */
    public function getLiceseError() {
        return $this->liceseError;
    }

    /**
     * @return type
     */
    public function getDateErrorReported() {
        return $this->dateErrorReported;
    }

    /**
     * @return type
     */
    public function getDateErrorEmailSent() {
        return $this->dateErrorEmailSent;
    }

    /**
     * @return type
     */
    public function getEmailRecordStatus() {
        return $this->emailRecordStatus;
    }

    /**
     * @return type
     */
    public function getMailedTo() {
        return $this->mailedTo;
    }

    /**
     * @param type $errorMsg
     */
    public function setErrorMsg($errorMsg) {
        $this->errorMsg = $errorMsg;
    }

    /**
     * @param type $uidErrorDevice
     */
    public function setUidErrorDevice($uidErrorDevice) {
        $this->uidErrorDevice = $uidErrorDevice;
    }

    /**
     * @param \App\Entity\AccountLicense $liceseError
     */
    public function setLiceseError(\App\Entity\AccountLicense $liceseError = null) {
        $this->liceseError = $liceseError;
    }

    /**
     * @param \DateTime $dateErrorReported
     */
    public function setDateErrorReported(\DateTime $dateErrorReported) {
        $this->dateErrorReported = $dateErrorReported;
    }

    /**
     * @param \DateTime $dateErrorEmailSent
     */
    public function setDateErrorEmailSent(\DateTime $dateErrorEmailSent) {
        $this->dateErrorEmailSent = $dateErrorEmailSent;
    }

    /**
     * @param type $emailRecordStatus
     */
    public function setEmailRecordStatus($emailRecordStatus) {
        $this->emailRecordStatus = $emailRecordStatus;
    }

    /**
     * @param type $mailedTo
     */
    public function setMailedTo($mailedTo) {
        $this->mailedTo = $mailedTo;
    }

    /**
     * @return type
     */
    public function getDateErrorLastReport() {
        return $this->dateErrorLastReport;
    }

    /**
     * @param \DateTime $dateErrorLastReport
     */
    public function setDateErrorLastReport(\DateTime $dateErrorLastReport = null) {
        $this->dateErrorLastReport = $dateErrorLastReport;
    }

    /**
     * @return type
     */
    public function getErrorCounter() {
        return $this->errorCounter;
    }

    /**
     * @param type $errorCounter
     */
    public function setErrorCounter($errorCounter) {
        $this->errorCounter = $errorCounter;
    }

}
