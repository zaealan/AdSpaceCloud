<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of ManuallySendPushAPK
 * @ORM\Table(name="manually_send_push_apk")
 * @ORM\Entity(repositoryClass="App\Entity\ManuallySendPushAPKRepository")
 */
class ManuallySendPushAPK {

    /**
     * @var integer
     * @ORM\Column(name="mspa_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="mspa_apk_name", type="string", length=64, nullable=false, unique=true)
     * @Assert\NotBlank(message="Please upload an .apk file!")
     * @Assert\File(mimeTypes={"application/apk", "application/zip"})
     */
    private $apkName;

    /**
     * @var string
     * @ORM\Column(name="mspa_install_after_download", type="boolean", nullable=true, options={"default":"1"})
     */
    private $installAfterDownload;

    /**
     * @var string
     * @ORM\Column(name="mspa_install_code", type="string", length=64, nullable=false)
     */
    private $installCode;

    /**
     * @var string
     * @ORM\Column(name="mspa_version_name", type="string", length=64, nullable=false)
     */
    private $versionName;

    /**
     * @var array
     * @ORM\Column(name="mspa_send_to", type="json_array", nullable=true)
     */
    private $sendTo;

    /**
     * @var \DateTime
     * @ORM\Column(name="mspa_send_datetime", type="datetime", nullable=true)
     */
    private $sendDateTime;

    /**
     * @var string
     * @ORM\Column(name="mspa_isreaded_server", type="boolean", nullable=true, options={"default":"0"})
     */
    private $isReadedByServer;

    /**
     * @var string
     * @ORM\Column(name="mspa_application_mode", type="boolean", nullable=true, options={"default":"1"})
     */
    private $applicationMode;

    /**
     * @var string
     * @ORM\Column(name="mspa_has_error", type="boolean", nullable=true, options={"default":"0"})
     */
    private $hasPersistentError;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getApkName() {
        return $this->apkName;
    }

    /**
     * @return type
     */
    public function getInstallAfterDownload() {
        return $this->installAfterDownload;
    }

    /**
     * @return type
     */
    public function getInstallCode() {
        return $this->installCode;
    }

    /**
     * @return type
     */
    public function getVersionName() {
        return $this->versionName;
    }

    /**
     * @return type
     */
    public function getSendTo() {
        return $this->sendTo;
    }

    /**
     * @return type
     */
    public function getSendDateTime() {
        return $this->sendDateTime;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $apkName
     */
    public function setApkName($apkName) {
        $this->apkName = $apkName;
    }

    /**
     * @param type $installAfterDownload
     */
    public function setInstallAfterDownload($installAfterDownload) {
        $this->installAfterDownload = $installAfterDownload;
    }

    /**
     * @param type $installCode
     */
    public function setInstallCode($installCode) {
        $this->installCode = $installCode;
    }

    /**
     * @param type $versionName
     */
    public function setVersionName($versionName) {
        $this->versionName = $versionName;
    }

    /**
     * @param type $sendTo
     */
    public function setSendTo($sendTo) {
        $this->sendTo = $sendTo;
    }

    /**
     * @param \DateTime $sendDateTime
     */
    public function setSendDateTime(\DateTime $sendDateTime = null) {
        $this->sendDateTime = $sendDateTime;
    }

    /**
     * @return type
     */
    public function getIsReadedByServer() {
        return $this->isReadedByServer;
    }

    /**
     * @return type
     */
    public function getApplicationMode() {
        return $this->applicationMode;
    }

    /**
     * @return type
     */
    public function getHasPersistentError() {
        return $this->hasPersistentError;
    }

    /**
     * @param type $isReadedByServer
     */
    public function setIsReadedByServer($isReadedByServer) {
        $this->isReadedByServer = $isReadedByServer;
    }

    /**
     * @param type $applicationMode
     */
    public function setApplicationMode($applicationMode) {
        $this->applicationMode = $applicationMode;
    }

    /**
     * @param type $hasPersistentError
     */
    public function setHasPersistentError($hasPersistentError) {
        $this->hasPersistentError = $hasPersistentError;
    }

}
