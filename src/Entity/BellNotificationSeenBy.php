<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of BellNotificationSeenBy
 * @author aealan
 * @ORM\Table(name="notification_seen_by")
 * @ORM\Entity(repositoryClass="App\Entity\BellNotificationSeenByRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BellNotificationSeenBy {

    /**
     * esta listo para ser cargado en el sistema
     */
    const STATUS_UNNOTIFIED = 1;

    /**
     * la respuesta esta lista para ser entregada
     */
    const STATUS_NOTIFIED = 2;

    /**
     * la respuesta esta lista para ser entregada
     */
    const STATUS_CLOSED = 3;

    /**
     * @var integer
     * @ORM\Column(name="nsb_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nsb_saw_by_user", referencedColumnName="us_id", nullable=false)
     * })
     */
    private $userWhoSaw;

    /**
     * @var \App\Entity\NotificationInLicensorBell
     * @ORM\ManyToOne(targetEntity="App\Entity\NotificationInLicensorBell")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nsb_seen_notification", referencedColumnName="nil_id", nullable=false)
     * })
     */
    private $seenNotification;

    /**
     * @var \DateTime
     * @ORM\Column(name="nsb_date_seen", type="datetime", nullable=true)
     */
    private $dateSeen;

    /**
     * @var boolean
     * @ORM\Column(name="nsb_disabled_by_recurrency", type="boolean", nullable=true)
     */
    private $disabledByRecurrency;

    /**
     * @var boolean
     * @ORM\Column(name="nsb_disabled_by_nonaplication", type="boolean", nullable=true)
     */
    private $disabledByNonApplication;

    /**
     * @var integer
     * @ORM\Column(name="nsb_status", type="integer", nullable=true)
     */
    private $notificationStatus;

    /**
     * @var \DateTime
     * @ORM\Column(name="nsb_date_created", type="datetime")
     */
    private $dateCreated;
    
    /**
     * @var string
     * @ORM\Column(name="nsb_notification_url", type="string")
     */
    private $urlForNotification;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getUrlForNotification() {
        return $this->urlForNotification;
    }

    /**
     * @param type $urlForNotification
     */
    public function setUrlForNotification($urlForNotification) {
        $this->urlForNotification = str_replace('/localhost', '/level.localhost', $urlForNotification);
    }
        
    /**
     * @return \DateTime
     */
    public function getDateCreated(): \DateTime {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated(\DateTime $dateCreated = null) {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return type
     */
    public function getDisabledByNonApplication() {
        return $this->disabledByNonApplication;
    }

    /**
     * @param type $disabledByNonApplication
     */
    public function setDisabledByNonApplication($disabledByNonApplication) {
        $this->disabledByNonApplication = $disabledByNonApplication;
    }

    /**
     * @return type
     */
    public function getNotificationStatus() {
        return $this->notificationStatus;
    }

    /**
     * @param type $notificationStatus
     */
    public function setNotificationStatus($notificationStatus) {
        $this->notificationStatus = $notificationStatus;
    }

    /**
     * @return type
     */
    public function getDisabledByRecurrency() {
        return $this->disabledByRecurrency;
    }

    /**
     * @param type $disabledByRecurrency
     */
    public function setDisabledByRecurrency($disabledByRecurrency) {
        $this->disabledByRecurrency = $disabledByRecurrency;
    }

    /**
     * @return \App\Entity\User
     */
    public function getUserWhoSaw(): \App\Entity\User {
        return $this->userWhoSaw;
    }

    /**
     * @return \App\Entity\NotificationInLicensorBell
     */
    public function getSeenNotification(): \App\Entity\NotificationInLicensorBell {
        return $this->seenNotification;
    }

    /**
     * @return \DateTime
     */
    public function getDateSeen(): \DateTime {
        return $this->dateSeen;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param \App\Entity\User $userWhoSaw
     */
    public function setUserWhoSaw(\App\Entity\User $userWhoSaw) {
        $this->userWhoSaw = $userWhoSaw;
    }

    /**
     * @param \App\Entity\NotificationInLicensorBell $seenNotification
     */
    public function setSeenNotification(\App\Entity\NotificationInLicensorBell $seenNotification) {
        $this->seenNotification = $seenNotification;
    }

    /**
     * @param \DateTime $dateSeen
     */
    public function setDateSeen(\DateTime $dateSeen = null) {
        $this->dateSeen = $dateSeen;
    }

    /**
     * @return type
     */
    public function showEverything() {
        return get_object_vars($this);
    }

}
