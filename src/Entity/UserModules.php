<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserModules
 * @ORM\Table(name="user_modules", indexes={@ORM\Index(name="um_user_id", columns={"um_user_id"}), @ORM\Index(name="um_module_id", columns={"um_module_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\UserModulesRepository")
 */
class UserModules {

    /**
     * @var integer
     * @ORM\Column(name="um_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $umId;

    /**
     * @var integer
     * @ORM\Column(name="um_access", type="boolean", nullable=true)
     */
    private $umAccess;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="um_user_id", referencedColumnName="us_id")
     * })
     */
    private $umUser;

    /**
     * @var \App\Entity\Module
     * @ORM\ManyToOne(targetEntity="App\Entity\Module")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="um_module_id", referencedColumnName="mo_id")
     * })
     */
    private $umModule;

    /**
     * @var \App\Entity\Company
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyModule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="um_company_module_id", referencedColumnName="cm_id")
     * })
     */
    protected $umCompanyModu;

    /**
     * @return type
     */
    public function getUmId() {
        return $this->umId;
    }

    /**
     * @return type
     */
    public function getUmAccess() {
        return $this->umAccess;
    }

    /**
     * @return type
     */
    public function getUmUser() {
        return $this->umUser;
    }

    /**
     * @return type
     */
    public function getUmModule() {
        return $this->umModule;
    }

    /**
     * @return type
     */
    public function getUmCompanyModu() {
        return $this->umCompanyModu;
    }

    /**
     * @param \App\Entity\CompanyModule $umCompanyModu
     */
    public function setUmCompanyModu(\App\Entity\CompanyModule $umCompanyModu) {
        $this->umCompanyModu = $umCompanyModu;
    }

    /**
     * @param type $umId
     */
    public function setUmId($umId) {
        $this->umId = $umId;
    }

    /**
     * @param type $umAccess
     */
    public function setUmAccess($umAccess) {
        $this->umAccess = $umAccess;
    }

    /**
     * @param \App\Entity\User $umUser
     */
    public function setUmUser(\App\Entity\User $umUser) {
        $this->umUser = $umUser;
    }

    /**
     * @param \App\Entity\Module $umModule
     */
    public function setUmModule(\App\Entity\Module $umModule) {
        $this->umModule = $umModule;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->umModule->getMoModuleName();
    }

}
