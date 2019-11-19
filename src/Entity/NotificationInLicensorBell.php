<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author aealan
 * Description of NotificationInLicensorBell
 * @ORM\Table(name="notification_licensor_bell")
 * @ORM\Entity(repositoryClass="App\Entity\NotificationInLicensorBellRepository")
 * @ORM\HasLifecycleCallbacks
 */
class NotificationInLicensorBell {

    const NOTIFICATION_TYPE_SUCCESS = 'msgNotification';
    const NOTIFICATION_TYPE_WARNING = 'msgWarning';
    const NOTIFICATION_TYPE_ALERT = 'msgError';

    /* full actuation */
    const ROLE_SUPER_ADMIN = 1;
    /* full actuation */
    const ROLE_ADMINISTRATOR = 2;
    /* puede crear usuarios */
    const ROLE_LICENSE_MANAGER = 3;
    /* ver reportes y listas */
    const ROLE_REPORT_VIEWER = 4;
    /* usuario gestor de datos de BDs */
    const ROLE_ADMIN_DATABASES = 5;
    const CONTEXT_NOTIFICATION_ANDROID_LOGIN = 1;
    const CONTEXT_NOTIFICATION_ANDROID_SYNC = 2;
    const CONTEXT_NOTIFICATION_ANDROID_UPDATE = 3;
    const CONTEXT_NOTIFICATION_ANDROID_CLEANSE = 4;
    const CONTEXT_NOTIFICATION_LEVELLITE_LOGIN = 20;
    const CONTEXT_NOTIFICATION_OMT_EXPORT = 40;
    const CONTEXT_NOTIFICATION_LEVELOMT_SYNC = 41;
    const CONTEXT_NOTIFICATION_OMTLEVEL_SYNC = 42;

    /**
     * @var integer
     * @ORM\Column(name="nil_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="nil_notification_label", type="string", nullable=true)
     */
    private $notificationLabel;
    
    /**
     * @var string
     * @ORM\Column(name="nil_notification_title", type="string", nullable=true)
     */
    private $notificationTitle;

    /**
     * @var string
     * @ORM\Column(name="nil_notification_msg", type="text", nullable=true)
     */
    private $notificationMsg;

    /**
     * @var \DateTime
     * @ORM\Column(name="nil_date_created", type="datetime")
     */
    private $dateCreated;

    /**
     * @var integer
     * @ORM\Column(name="nil_iterative_notification", type="boolean", nullable=true, options={"default":"0"})
     */
    private $iterativeNotification = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="nil_requeue_date", type="datetime", nullable=true)
     */
    private $dateIterativeRequeue;

    /**
     * @var \App\Entity\AccountLicense
     * @ORM\ManyToOne(targetEntity="App\Entity\AccountLicense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nil_license", referencedColumnName="al_id", nullable=true)
     * })
     */
    private $licese;

    /**
     * @var integer
     * @ORM\Column(name="nil_type_role_notify", type="integer", nullable=true)
     */
    private $typeRoleToNotify;

    /**
     * @var integer
     * @ORM\Column(name="nil_context", type="integer")
     */
    private $context;

    /**
     * @var \DateTime
     * @ORM\Column(name="nil_picked_cron_date", type="datetime", nullable=true)
     */
    private $pickedtByValidatingCronDate;
    
    /**
     * @var string
     * @ORM\Column(name="nil_notification_alt", type="string", nullable=true)
     */
    private $notificationAlt;
    
    /**
     * @var string
     * @ORM\Column(name="nil_notification_html_title", type="string", nullable=true)
     */
    private $notificationHTMLTitle;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getNotificationAlt() {
        return $this->notificationAlt;
    }

    /**
     * @return type
     */
    public function getNotificationHTMLTitle() {
        return $this->notificationHTMLTitle;
    }

    /**
     * @param type $notificationAlt
     */
    public function setNotificationAlt($notificationAlt) {
        $this->notificationAlt = $notificationAlt;
    }

    /**
     * @param type $notificationHTMLTitle
     */
    public function setNotificationHTMLTitle($notificationHTMLTitle) {
        $this->notificationHTMLTitle = $notificationHTMLTitle;
    }

    /**
     * @return type
     */
    public function getNotificationTitle() {
        return $this->notificationTitle;
    }

    /**
     * @param type $notificationTitle
     */
    public function setNotificationTitle($notificationTitle) {
        $this->notificationTitle = $notificationTitle;
    }

    /**
     * @return \DateTime
     */
    public function getPickedtByValidatingCronDate(): \DateTime {
        return $this->pickedtByValidatingCronDate;
    }

    /**
     * @param \DateTime $pickedtByValidatingCronDate
     */
    public function setPickedtByValidatingCronDate(\DateTime $pickedtByValidatingCronDate = null) {
        $this->pickedtByValidatingCronDate = $pickedtByValidatingCronDate;
    }

    /**
     * @return type
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * @param type $context
     */
    public function setContext($context) {
        $this->context = $context;
    }

    /**
     * @return type
     */
    public function getTypeRoleToNotify() {
        return $this->typeRoleToNotify;
    }

    /**
     * @param type $typeRoleToNotify
     */
    public function setTypeRoleToNotify($typeRoleToNotify) {
        $this->typeRoleToNotify = $typeRoleToNotify;
    }

    /**
     * @return \App\Entity\AccountLicense
     */
    public function getLicese(): \App\Entity\AccountLicense {
        return $this->licese;
    }

    /**
     * @param \App\Entity\AccountLicense $licese
     */
    public function setLicese(\App\Entity\AccountLicense $licese = null) {
        $this->licese = $licese;
    }

    /**
     * @return type
     */
    public function getNotificationLabel() {
        return $this->notificationLabel;
    }

    /**
     * @return type
     */
    public function getNotificationMsg() {
        return $this->notificationMsg;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated(): \DateTime {
        return $this->dateCreated;
    }

    /**
     * @return type
     */
    public function getIterativeNotification() {
        return $this->iterativeNotification;
    }

    /**
     * @return \DateTime
     */
    public function getDateIterativeRequeue(): \DateTime {
        return $this->dateIterativeRequeue;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $notificationLabel
     */
    public function setNotificationLabel($notificationLabel) {
        $this->notificationLabel = $notificationLabel;
    }

    /**
     * @param type $notificationMsg
     */
    public function setNotificationMsg($notificationMsg) {
        $this->notificationMsg = $notificationMsg;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated(\DateTime $dateCreated) {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @param type $iterativeNotification
     */
    public function setIterativeNotification($iterativeNotification) {
        $this->iterativeNotification = $iterativeNotification;
    }

    /**
     * @param \DateTime $dateIterativeRequeue
     */
    public function setDateIterativeRequeue(\DateTime $dateIterativeRequeue = null) {
        $this->dateIterativeRequeue = $dateIterativeRequeue;
    }

}
