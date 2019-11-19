<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 * @ORM\Table(name="country")
 * @ORM\Entity
 */
class Country {

    /**
     * @var integer
     * @ORM\Column(name="co_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $coId;

    /**
     * @var string
     * @ORM\Column(name="co_name", type="string", length=50, nullable=false)
     */
    private $coName;

    /**
     * @var string
     * @ORM\Column(name="co_value", type="string", length=2, nullable=true, unique=true)
     */
    private $coVal;

    /**
     * @var string
     * @ORM\Column(name="co_three_value", type="string", length=3, nullable=true, unique=true)
     */
    private $coThreeVal;

    /**
     * @return type
     */
    public function getCoId() {
        return $this->coId;
    }

    /**
     * @return type
     */
    public function getCoName() {
        return $this->coName;
    }

    /**
     * @param type $coId
     */
    public function setCoId($coId) {
        $this->coId = $coId;
    }

    /**
     * @param type $coName
     */
    public function setCoName($coName) {
        $this->coName = $coName;
    }

    /**
     * @return type
     */
    public function getCoVal() {
        return $this->coVal;
    }

    /**
     * @param type $coVal
     */
    public function setCoVal($coVal) {
        $this->coVal = $coVal;
    }

    /**
     * @return type
     */
    public function getCoThreeVal() {
        return $this->coThreeVal;
    }

    /**
     * @param type $coThreeVal
     */
    public function setCoThreeVal($coThreeVal) {
        $this->coThreeVal = $coThreeVal;
    }

}
