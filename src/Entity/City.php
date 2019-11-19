<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 * @ORM\Table(name="city", indexes={@ORM\Index(name="city_state_fk_1", columns={"ci_state_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\CityRepository")
 */
class City {

    /**
     * @var integer
     * @ORM\Column(name="ci_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="ci_name", type="string", length=50, nullable=false)
     */
    private $ciName;

    /**
     * @var \App\Entity\State
     * @ORM\ManyToOne(targetEntity="App\Entity\State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ci_state_id", referencedColumnName="st_id")
     * })
     */
    private $ciState;

    /**
     * @var boolean
     * @ORM\Column(name="ci_is_manually_added", type="boolean", nullable=true) 
     */
    protected $isManuallyAdded;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getCiName() {
        return $this->ciName;
    }

    /**
     * @return type
     */
    public function getCiState() {
        return $this->ciState;
    }

    /**
     * @param type $ciId
     */
    public function setCiId($ciId) {
        $this->ciId = $ciId;
    }

    /**
     * @param type $ciName
     */
    public function setCiName($ciName) {
        $this->ciName = $ciName;
    }

    /**
     * @param \App\Entity\State $ciState
     */
    public function setCiState(\App\Entity\State $ciState) {
        $this->ciState = $ciState;
    }

    /**
     * @return type
     */
    public function getIsManuallyAdded() {
        return $this->isManuallyAdded;
    }

    /**
     * @param type $isManuallyAdded
     */
    public function setIsManuallyAdded($isManuallyAdded) {
        $this->isManuallyAdded = $isManuallyAdded;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->ciName;
    }

}
