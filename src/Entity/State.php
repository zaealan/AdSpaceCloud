<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * State
 * @ORM\Table(name="state", indexes={@ORM\Index(name="st_country_id", columns={"st_country_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\StateRepository")
 */
class State {

    /**
     * @var integer
     * @ORM\Column(name="st_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $stId;

    /**
     * @var string
     * @ORM\Column(name="st_name", type="string", length=50, nullable=false)
     */
    private $stName;

    /**
     * @var \App\Entity\Country
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="st_country_id", referencedColumnName="co_id")
     * })
     */
    private $stCountry;

    /**
     * @return type
     */
    public function getStId() {
        return $this->stId;
    }

    /**
     * @return type
     */
    public function getStName() {
        return $this->stName;
    }

    /**
     * @return type
     */
    public function getStCountry() {
        return $this->stCountry;
    }

    /**
     * @param type $stId
     */
    public function setStId($stId) {
        $this->stId = $stId;
    }

    /**
     * @param type $stName
     */
    public function setStName($stName) {
        $this->stName = $stName;
    }

    /**
     * @param \App\Entity\Country $stCountry
     */
    public function setStCountry(\App\Entity\Country $stCountry) {
        $this->stCountry = $stCountry;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->stName;
    }

}
