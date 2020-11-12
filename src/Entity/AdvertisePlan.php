<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\AccountLicense;

/**
 * Description of AdvertisePlan
 * @ORM\Table(name="place_advert_plan")
 * @ORM\Entity(repositoryClass="App\Entity\AdvertisePlanRepository")
 * @author aealan
 */
class AdvertisePlan {

    CONST ADVERT_PLAN_STATUS_SCHEDULED = 1;
    CONST ADVERT_PLAN_STATUS_RUNNING = 2;
    CONST ADVERT_PLAN_STATUS_FINISHED = 3;

    /**
     * @var integer
     * @ORM\Column(name="pap_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="description", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(name="has_video", type="boolean", nullable=true)
     */
    private $hasVideo;

    /**
     * @var string
     * @ORM\Column(name="has_images", type="boolean", nullable=true)
     */
    private $hasImages;

    /**
     * @var string
     * @ORM\Column(name="in_queue", type="boolean", nullable=true)
     */
    private $isInQueue;

    /**
     * @var integer
     * @ORM\Column(name="has_error", type="boolean", nullable=true)
     */
    private $hasError;

    /**
     * @var AccountLicense
     * @ORM\ManyToOne(targetEntity="AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="place_advert_id", referencedColumnName="al_id")
     * })
     */
    private $advertPlace;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $createdDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="starting_date", type="datetime", nullable=false)
     */
    private $startingDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="queued_date", type="datetime", nullable=true)
     */
    private $inQueuedDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="ending_date", type="datetime", nullable=false)
     */
    private $endingDate;

    /**
     * @var integer
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(name="rerun_times", type="integer", length=3, nullable=false)
     */
    private $rerunTimes;

    /**
     * @var string
     * @ORM\Column(name="rerun_duration_in_seconds", type="integer", length=3, nullable=false)
     */
    private $timeDurationInSeconds;

    /**
     * @var string
     * @ORM\Column(name="clients_number", type="integer", length=2, nullable=false)
     */
    private $clientsNumber;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getClientsNumber() {
        return $this->clientsNumber;
    }

    /**
     * @param type $clientsNumber
     */
    public function setClientsNumber($clientsNumber) {
        $this->clientsNumber = $clientsNumber;
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
    public function getName() {
        return $this->name;
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
    public function getHasVideo() {
        return $this->hasVideo;
    }

    /**
     * @return type
     */
    public function getHasImages() {
        return $this->hasImages;
    }

    /**
     * @return type
     */
    public function getTimeDurationInSeconds() {
        return $this->timeDurationInSeconds;
    }

    /**
     * @return type
     */
    public function getNumberOfClients() {
        return $this->numberOfClients;
    }

    /**
     * @return type
     */
    public function getIsInQueue() {
        return $this->isInQueue;
    }

    /**
     * @return type
     */
    public function getHasError() {
        return $this->hasError;
    }

    /**
     * @return \App\Entity\AccountLicense
     */
    public function getAdvertPlace(): AccountLicense {
        return $this->advertPlace;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate() {
        return $this->createdDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartingDate() {
        return $this->startingDate;
    }

    /**
     * @return \DateTime
     */
    public function getInQueuedDate() {
        return $this->inQueuedDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndingDate() {
        return $this->endingDate;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @param type $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @param type $hasVideo
     */
    public function setHasVideo($hasVideo) {
        $this->hasVideo = $hasVideo;
    }

    /**
     * @param type $hasImages
     */
    public function setHasImages($hasImages) {
        $this->hasImages = $hasImages;
    }

    /**
     * @param type $numberOfClients
     */
    public function setNumberOfClients($numberOfClients) {
        $this->numberOfClients = $numberOfClients;
    }

    /**
     * @param type $isInQueue
     */
    public function setIsInQueue($isInQueue) {
        $this->isInQueue = $isInQueue;
    }

    /**
     * @param type $hasError
     */
    public function setHasError($hasError) {
        $this->hasError = $hasError;
    }

    /**
     * @param \App\Entity\AccountLicense $advertPlace
     */
    public function setAdvertPlace(AccountLicense $advertPlace) {
        $this->advertPlace = $advertPlace;
    }

    /**
     * @param \DateTime $createdDate
     */
    public function setCreatedDate(\DateTime $createdDate) {
        $this->createdDate = $createdDate;
    }

    /**
     * @param \DateTime $startingDate
     */
    public function setStartingDate(\DateTime $startingDate) {
        $this->startingDate = $startingDate;
    }

    /**
     * @param \DateTime $inQueuedDate
     */
    public function setInQueuedDate(\DateTime $inQueuedDate = null) {
        $this->inQueuedDate = $inQueuedDate;
    }

    /**
     * @param \DateTime $endingDate
     */
    public function setEndingDate(\DateTime $endingDate) {
        $this->endingDate = $endingDate;
    }

    /**
     * @return type
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param type $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return type
     */
    public function getRerunTimes() {
        return $this->rerunTimes;
    }

    /**
     * @param type $rerunTimes
     */
    public function setRerunTimes($rerunTimes) {
        $this->rerunTimes = $rerunTimes;
    }

    /**
     * Get text type super_admin, administrator, license_manager,
     * report_viewer, data_bases_administrator
     * @return string
     */
    public function getTextStatus() {
        $text = '';
        switch ($this->status) {
            case static::ADVERT_PLAN_STATUS_SCHEDULED: $text = 'Programada';
                break;
            case static::ADVERT_PLAN_STATUS_RUNNING: $text = 'Corriendo';
                break;
            case static::ADVERT_PLAN_STATUS_FINISHED: $text = 'Terminado';
                break;
            default:
                $text = 'Estado No Determinado';
        }
        return $text;
    }

}
