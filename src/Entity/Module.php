<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Module
 * @ORM\Table(name="module")
 * @ORM\Entity
 */
class Module {

    const MODULE_LICENSOR_USERS = 'module_licensor_users';
    const MODULE_LICENSOR_USERS_CREATE = 'module_licensor_create_user_data';
    const MODULE_LICENSOR_USERS_EDIT = 'module_licensor_edit_user_data';
    const MODULE_LICENSOR_USERS_DELETE = 'module_licensor_delete_user_data';
    const MODULE_LICENSOR_LICENSE = 'module_licensor_license';
    const MODULE_LICENSOR_LICENSE_CREATE = 'module_licensor_create_license_data';
    const MODULE_LICENSOR_LICENSE_EDIT = 'module_licensor_edit_license_data';
    const MODULE_LICENSOR_LICENSE_DELETE = 'module_licensor_delete_license_data';
    const MODULE_LICENSOR_ACCOUNT = 'module_licensor_account';
    const MODULE_LICENSOR_ACCOUNT_CREATE = 'module_licensor_create_account_data';
    const MODULE_LICENSOR_ACCOUNT_EDIT = 'module_licensor_edit_account_data';
    const MODULE_LICENSOR_ACCOUNT_DELETE = 'module_licensor_delete_account_data';
    const MODULE_LICENSOR_LICENSE_DEVICE = 'module_licensor_license_device_data';
    const MODULE_LICENSOR_LICENSE_DEVICE_NEW = 'module_licensor_new_license_device_data';
    const MODULE_LICENSOR_LICENSE_DEVICE_EDIT = 'module_licensor_edit_license_device_data';
    const MODULE_LICENSOR_COMPANY = 'module_licensor_company';
    const MODULE_LICENSOR_COMPANY_CREATE = 'module_licensor_create_company_data';
    const MODULE_LICENSOR_COMPANY_EDIT = 'module_licensor_edit_company_data';
    const MODULE_LICENSOR_COMPANY_DELETE = 'module_licensor_delete_company_data';
    const MODULE_LICENSOR_DATA_BASES_MANAGEMENT = 'module_licensor_data_bases_management';
    const MODULE_LICENSOR_SHOW_DATA_BASES_MANAGEMENT = 'module_licensor_show_data_bases_management';
    const MODULE_LICENSOR_EDIT_DATA_BASES_MANAGEMENT = 'module_licensor_edit_data_bases_management';
    const MODULE_LICENSOR_REPORTS = 'module_licensor_reports';
    const MODULE_LICENSOR_REPORTS_LICENSES_SOLD = 'module_licensor_reports_licenses_sold';

    /**
     * @var integer
     * @ORM\Column(name="mo_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="mo_module_name", type="text", nullable=false)
     */
    private $moModuleName;

    /**
     * @var integer
     * @ORM\Column(name="mo_status", type="smallint", nullable=true)
     */
    private $moStatus;

    /**
     *  @var float
     *  @ORM\Column(name="mo_order", type="float", nullable=true) 
     */
    protected $moOrder;

    /**
     * @var string
     * @ORM\Column(name="mo_slug", type="string", length=100) 
     */
    protected $moSlug;

    /**
     * Un modulo puede tener otro modulo padre, lo que lo hace un submodulo
     * @ORM\ManyToOne(targetEntity="App\Entity\Module")
     * @ORM\JoinColumn(name="mo_parent", referencedColumnName="mo_id", nullable=true)
     */
    protected $parent;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getMoModuleName() {
        return $this->moModuleName;
    }

    /**
     * @return type
     */
    public function getMoStatus() {
        return $this->moStatus;
    }

    /**
     * @return type
     */
    public function getMoOrder() {
        return $this->moOrder;
    }

    /**
     * @return type
     */
    public function getMoSlug() {
        return $this->moSlug;
    }

    /**
     * @return type
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @param type $moModuleName
     */
    public function setMoModuleName($moModuleName) {
        $this->moModuleName = $moModuleName;
    }

    /**
     * @param type $moStatus
     */
    public function setMoStatus($moStatus) {
        $this->moStatus = $moStatus;
    }

    /**
     * @param type $moOrder
     */
    public function setMoOrder($moOrder) {
        $this->moOrder = $moOrder;
    }

    /**
     * @param type $moSlug
     */
    public function setMoSlug($moSlug) {
        $this->moSlug = $moSlug;
    }

    /**
     * @param \App\Entity\Module $parent
     */
    public function setParent(\App\Entity\Module $parent = null) {
        $this->parent = $parent;
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->moModuleName;
    }

}
