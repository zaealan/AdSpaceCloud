<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyModule
 * @ORM\Table(name="company_module", indexes={@ORM\Index(name="cm_company_id", columns={"cm_company_id"}), @ORM\Index(name="cm_module_id", columns={"cm_module_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\CompanyModuleRepository")
 */
class CompanyModule {

    /**
     * @var integer
     * @ORM\Column(name="cm_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cmId;

    /**
     * @var integer
     * @ORM\Column(name="cm_access", type="smallint", nullable=true)
     */
    private $cmAccess;

    /**
     * @var \App\Entity\Company
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cm_company_id", referencedColumnName="co_id")
     * })
     */
    private $cmCompany;

    /**
     * @var \App\Entity\Module
     * @ORM\ManyToOne(targetEntity="App\Entity\Module")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cm_module_id", referencedColumnName="mo_id")
     * })
     */
    private $cmModule;

    /**
     * @return type
     */
    public function getCmId() {
        return $this->cmId;
    }

    /**
     * @return type
     */
    public function getCmAccess() {
        return $this->cmAccess;
    }

    /**
     * @return type
     */
    public function getCmCompany() {
        return $this->cmCompany;
    }

    /**
     * @return type
     */
    public function getCmModule() {
        return $this->cmModule;
    }

    /**
     * @param type $cmId
     */
    public function setCmId($cmId) {
        $this->cmId = $cmId;
    }

    /**
     * @param type $cmAccess
     */
    public function setCmAccess($cmAccess) {
        $this->cmAccess = $cmAccess;
    }

    /**
     * @param \App\Entity\Company $cmCompany
     */
    public function setCmCompany(\App\Entity\Company $cmCompany) {
        $this->cmCompany = $cmCompany;
    }

    /**
     * @param \App\Entity\Module $cmModule
     */
    public function setCmModule(\App\Entity\Module $cmModule) {
        $this->cmModule = $cmModule;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->cmModule->getMoModuleName();
    }

}
