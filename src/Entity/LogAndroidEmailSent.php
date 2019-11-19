<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ErrorAndroidEmailSent
 * @ORM\Table(name="log_android_email")
 * @ORM\Entity(repositoryClass="App\Entity\LogAndroidEmailSentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class LogAndroidEmailSent {

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
     * @ORM\Column(name="lae_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="lae_log_msg", type="text")
     */
    private $logMsg;

    /**
     * @var string
     * @ORM\Column(name="lae_device_uid", type="string", length=255)
     */
    private $uidLogDevice;

    /**
     * @var \App\Entity\AccountLicense
     * @ORM\ManyToOne(targetEntity="App\Entity\AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lae_license", referencedColumnName="al_id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $liceseLog;

    /**
     * @var \DateTime
     * @ORM\Column(name="lae_date_asked", type="datetime")
     */
    private $dateLogAsked;

    /**
     * @var \DateTime
     * @ORM\Column(name="lae_date_email_sent", type="datetime", nullable=true)
     */
    private $dateLogEmailSent;

    /**
     * @var integer
     * @ORM\Column(name="lae_email_record_status", type="integer", nullable=false, options={"default":"1"})
     */
    private $emailRecordStatus;

    /**
     * @var string
     * @ORM\Column(name="lae_mailed_to", type="string", length=255, nullable=false)
     */
    private $mailedTo;

    /**
     * @var integer
     * @ORM\Column(name="lae_log_code", type="integer")
     */
    private $logCode;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getLogMsg() {
        return $this->logMsg;
    }

    /**
     * @return type
     */
    public function getUidLogDevice() {
        return $this->uidLogDevice;
    }

    /**
     * @return type
     */
    public function getLiceseLog() {
        return $this->liceseLog;
    }

    /**
     * @return type
     */
    public function getDateLogAsked() {
        return $this->dateLogAsked;
    }

    /**
     * @return type
     */
    public function getDateLogEmailSent() {
        return $this->dateLogEmailSent;
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
     * @return type
     */
    public function getLogCode() {
        return $this->logCode;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $logMsg
     */
    public function setLogMsg($logMsg) {
        $this->logMsg = $logMsg;
    }

    /**
     * @param type $uidLogDevice
     */
    public function setUidLogDevice($uidLogDevice) {
        $this->uidLogDevice = $uidLogDevice;
    }

    /**
     * @param \App\Entity\AccountLicense $liceseLog
     */
    public function setLiceseLog(\App\Entity\AccountLicense $liceseLog = null) {
        $this->liceseLog = $liceseLog;
    }

    /**
     * @param \DateTime $dateLogAsked
     */
    public function setDateLogAsked(\DateTime $dateLogAsked) {
        $this->dateLogAsked = $dateLogAsked;
    }

    /**
     * @param \DateTime $dateLogEmailSent
     */
    public function setDateLogEmailSent(\DateTime $dateLogEmailSent = null) {
        $this->dateLogEmailSent = $dateLogEmailSent;
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
     * @param type $logCode
     */
    public function setLogCode($logCode) {
        $this->logCode = $logCode;
    }

}
