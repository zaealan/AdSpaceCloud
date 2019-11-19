<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\RabbitRelatedGenericEntity;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Description of CustomPushMessage
 * @ORM\Table(name="custom_push_message")
 * @ORM\Entity(repositoryClass="App\Entity\CustomPushMessageRepository")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="id",
 *          column=@ORM\Column(
 *              name     = "cpm_id",
 *              type     = "integer"
 *          )
 *      )
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class CustomPushMessage extends RabbitRelatedGenericEntity {

    private $id;
    
    /**
     * @var string
     * @ORM\Column(name="cpm_message_title", type="string", length=64, nullable=false)
     */
    private $messageTitle;
    
    /**
     * @var array
     * @ORM\Column(name="cpm_message_body", type="text", nullable=true)
     */
    private $messageBody;

    /**
     * @var array
     * @ORM\Column(name="mspa_send_to", type="json_array", nullable=true)
     */
    private $sendTo;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="cpm_send_datetime", type="datetime", nullable=true)
     */
    private $sendDateTime;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getMessageTitle() {
        return $this->messageTitle;
    }

    /**
     * @return type
     */
    public function getMessageBody() {
        return $this->messageBody;
    }

    /**
     * @return type
     */
    public function getSendTo() {
        return $this->sendTo;
    }

    /**
     * @return \DateTime
     */
    public function getSendDateTime(): \DateTime {
        return $this->sendDateTime;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $messageTitle
     */
    public function setMessageTitle($messageTitle) {
        $this->messageTitle = $messageTitle;
    }

    /**
     * @param type $messageBody
     */
    public function setMessageBody($messageBody) {
        $this->messageBody = $messageBody;
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
