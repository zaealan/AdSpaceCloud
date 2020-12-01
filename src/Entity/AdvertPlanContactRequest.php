<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\AdvertisePlan;

/**
 * Description of AdvertPlanContactRequest
 * @ORM\Table(name="advert_plan_contact_request")
 * @ORM\Entity(repositoryClass="App\Entity\AdvertPlanContactRequestRepository")
 * @author aealan
 */
class AdvertPlanContactRequest {

    CONST NOTIFICATION_STATUS_NEW = 1;
    CONST NOTIFICATION_STATUS_PUBLISHER_NOTIFIED = 2;

    /**
     * @var integer
     * @ORM\Column(name="acr_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="contact_phone", type="string", length=255, nullable=true)
     */
    private $contactPhone;

    /**
     * @var string
     * @ORM\Column(name="contact_email", type="string", length=255, nullable=true)
     */
    private $contactEmail;

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
     * @ORM\Column(name="contact_notification_status", type="integer", nullable=true)
     */
    private $contactUsNotificationStatus;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContactPhone(): string {
        return $this->contactPhone;
    }

    /**
     * @return string
     */
    public function getContactEmail(): string {
        return $this->contactEmail;
    }

    /**
     * @return \App\Entity\AccountLicense
     */
    public function getLicense(): AccountLicense {
        return $this->license;
    }

    /**
     * @return AdvertisePlan
     */
    public function getAdvertPlan(): AdvertisePlan {
        return $this->advertPlan;
    }

    /**
     * @return bool
     */
    public function getContactUsNotificationStatus(): bool {
        return $this->contactUsNotificationStatus;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @param string $contactPhone
     * @return void
     */
    public function setContactPhone(string $contactPhone): void {
        $this->contactPhone = $contactPhone;
    }

    /**
     * @param string $contactEmail
     * @return void
     */
    public function setContactEmail(string $contactEmail): void {
        $this->contactEmail = $contactEmail;
    }

    /**
     * @param \App\Entity\AccountLicense $license
     * @return void
     */
    public function setLicense(AccountLicense $license): void {
        $this->license = $license;
    }

    /**
     * @param AdvertisePlan $advertPlan
     * @return void
     */
    public function setAdvertPlan(AdvertisePlan $advertPlan): void {
        $this->advertPlan = $advertPlan;
    }

    /**
     * @param bool $contactUsNotificationStatus
     * @return void
     */
    public function setContactUsNotificationStatus(bool $contactUsNotificationStatus): void {
        $this->contactUsNotificationStatus = $contactUsNotificationStatus;
    }

}
