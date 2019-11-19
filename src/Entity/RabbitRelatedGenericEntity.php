<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * @author aealan
 * 
 * Description of RabbitRelatedGenericEntity
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class RabbitRelatedGenericEntity {

    const STATUS_INITIATED = 0;
    const STATUS_STANDBY = 1;
    const STATUS_PROCCESSED = 2;
    const STATUS_DELIVERED = 3;

    /**
     * @var integer
     * @ORM\Column(name="rrg_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="rrg_status", type="integer", length=11, nullable=false, options={"default":"0"})
     */
    private $status;

    /**
     * @var \App\Entity\AccountLicense
     * @ORM\ManyToOne(targetEntity="App\Entity\AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rrg_license", referencedColumnName="al_id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $license;

    /**
     * @var \DateTime
     * @ORM\Column(name="rrg_date_asked", type="datetime")
     */
    private $dateAsked;

    /**
     * @var \DateTime
     * @ORM\Column(name="rrg_date_proccessed", type="datetime", nullable=true)
     */
    private $dateProccessed;

    /**
     * @var \DateTime
     * @ORM\Column(name="rrg_date_delivered", type="datetime", nullable=true)
     */
    private $dateDelivered;

    /**
     * @var boolean
     * @ORM\Column(name="rrg_rabbit_readed", type="boolean", nullable=true, options={"default":0})
     */
    private $isReadedByRabbit = false;

    /**
     * @var boolean
     * @ORM\Column(name="rrg_application_mode", type="boolean", nullable=true, options={"default":0})
     */
    private $applicationMode = false;

    /**
     * @var boolean
     * @ORM\Column(name="rrg_has_error", type="boolean", nullable=true, options={"default":0})
     */
    private $hasPersistentError = false;

    /**
     * @var array
     * @ORM\Column(name="rrg_obtained_error", type="json_array", nullable=true)
     */
    private $obtainedError;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getStatus() {
        return $this->status;
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
     * @return \DateTime
     */
    public function getDateProccessed(): \DateTime {
        return $this->dateProccessed;
    }

    /**
     * @return \DateTime
     */
    public function getDateDelivered(): \DateTime {
        return $this->dateDelivered;
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
     * @return type
     */
    public function getObtainedError() {
        return $this->obtainedError;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $status
     */
    public function setStatus($status) {
        $this->status = $status;
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
     * @param \DateTime $dateProccessed
     */
    public function setDateProccessed(\DateTime $dateProccessed = null) {
        $this->dateProccessed = $dateProccessed;
    }

    /**
     * @param \DateTime $dateDelivered
     */
    public function setDateDelivered(\DateTime $dateDelivered = null) {
        $this->dateDelivered = $dateDelivered;
    }

    /**
     * @param type $isReadedByRabbit
     */
    public function setIsReadedByRabbit($isReadedByRabbit) {
        $this->isReadedByRabbit = $isReadedByRabbit;
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

    /**
     * @param type $obtainedError
     */
    public function setObtainedError($obtainedError) {
        $this->obtainedError = $obtainedError;
    }

    /**
     * @ORM\PrePersist 
     */
    public function syncPrePersist(LifecycleEventArgs $args) {
        
    }

    /**
     * @ORM\PreUpdate
     */
    public function syncPreUpdate() {
        
    }

}
