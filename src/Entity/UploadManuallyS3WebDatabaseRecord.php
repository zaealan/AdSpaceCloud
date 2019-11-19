<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of UploadManuallyS3WebDatabaseRecord
 * @author aealan
 * @ORM\Table(name="upload_manually_s3_web_database_record")
 * @ORM\Entity(repositoryClass="App\Entity\UploadManuallyS3WebDatabaseRecordRepository")
 */
class UploadManuallyS3WebDatabaseRecord {

    /**
     * @var integer
     * @ORM\Column(name="s3w_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Entity\AccountLicense
     * @ORM\ManyToOne(targetEntity="App\Entity\AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="s3_license", referencedColumnName="al_id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $license;

    /**
     * @var \DateTime
     * @ORM\Column(name="s3w_date_asked", type="datetime")
     */
    private $dateAsked;

    /**
     * @var string
     * @ORM\Column(name="s3w_uploaded_url", type="text", nullable=true, length=1000)
     */
    private $urlInS3;

    /**
     * @var \DateTime
     * @ORM\Column(name="s3w_date_done", type="datetime", nullable=true)
     */
    private $dateDone;

    /**
     * @var boolean
     * @ORM\Column(name="rrg_rabbit_readed", type="boolean", nullable=true, options={"default":0})
     */
    private $isReadedByRabbit = false;

    /**
     * @var boolean
     * @ORM\Column(name="rrg_has_error", type="boolean", nullable=true, options={"default":0})
     */
    private $hasPersistentError = false;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return \App\Entity\AccountLicense
     */
    public function getLicense(): \App\Entity\AccountLicense {
        return $this->license;
    }

    /**
     * @return \DateTime
     */
    public function getDateAsked(): \DateTime {
        return $this->dateAsked;
    }

    /**
     * @return type
     */
    public function getUrlInS3() {
        return $this->urlInS3;
    }

    /**
     * @return \DateTime
     */
    public function getDateDone(): \DateTime {
        return $this->dateDone;
    }

    /**
     * @return type
     */
    public function getIsReadedByRabbit() {
        return $this->isReadedByRabbit;
    }

    /**
     * @return type
     */
    public function getHasPersistentError() {
        return $this->hasPersistentError;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param \App\Entity\AccountLicense $license
     */
    public function setLicense(\App\Entity\AccountLicense $license = null) {
        $this->license = $license;
    }

    /**
     * @param \DateTime $dateAsked
     */
    public function setDateAsked(\DateTime $dateAsked) {
        $this->dateAsked = $dateAsked;
    }

    /**
     * @param type $urlInS3
     */
    public function setUrlInS3($urlInS3) {
        $this->urlInS3 = $urlInS3;
    }

    /**
     * @param \DateTime $dateDone
     */
    public function setDateDone(\DateTime $dateDone = null) {
        $this->dateDone = $dateDone;
    }

    /**
     * @param type $isReadedByRabbit
     */
    public function setIsReadedByRabbit($isReadedByRabbit) {
        $this->isReadedByRabbit = $isReadedByRabbit;
    }

    /**
     * @param type $hasPersistentError
     */
    public function setHasPersistentError($hasPersistentError) {
        $this->hasPersistentError = $hasPersistentError;
    }

}
