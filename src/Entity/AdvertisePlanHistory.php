<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\AdvertisePlan;

/**
 * Description of AdvertisePlanHistory
 * @ORM\Table(name="place_advert_plan_history")
 * @ORM\Entity(repositoryClass="App\Entity\LicenseAdvertImageRepository")
 * @author aealan
 */
class AdvertisePlanHistory {

    /**
     * @var integer
     * @ORM\Column(name="paph_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
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
     * @ORM\Column(name="ending_date", type="datetime", nullable=false)
     */
    private $endingDate;
    
    /**
     * @var AccountLicense
     * @ORM\ManyToOne(targetEntity="AdvertisePlan")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plan_advert_id", referencedColumnName="pap_id")
     * })
     */
    private $advertPlan;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate(): \DateTime {
        return $this->createdDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartingDate(): \DateTime {
        return $this->startingDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndingDate(): \DateTime {
        return $this->endingDate;
    }

    /**
     * @return \App\Entity\AccountLicense
     */
    public function getAdvertPlan(): AdvertisePlan {
        return $this->advertPlan;
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
     * @param \DateTime $endingDate
     */
    public function setEndingDate(\DateTime $endingDate) {
        $this->endingDate = $endingDate;
    }

    /**
     * @param \App\Entity\AccountLicense $advertPlan
     */
    public function setAdvertPlan(AdvertisePlan $advertPlan) {
        $this->advertPlan = $advertPlan;
    }

}
