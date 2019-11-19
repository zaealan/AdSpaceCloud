<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Company;

/**
 * User
 * @ORM\Table(name="user", indexes={@ORM\Index(name="user_company_fk_1", columns={"us_company_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface, \Serializable {

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /* full actuacion */
    const USER_SUPER_ADMIN = 1;
    /* full actuacion */
    const USER_ADMINISTRATOR = 2;
    /* puede crear usuarios */
    const USER_LICENSE_MANAGER = 3;
    /* ver reportes y listas */
    const USER_REPORT_VIEWER = 4;
    /* usuario gestor de datos de BDs */
    const USER_ADMIN_DATABASES = 5;

    /**
     * @var integer
     * @ORM\Column(name="us_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="us_type", type="integer")
     */
    protected $usType;

    /**
     * @var string
     * @ORM\Column(name="us_name", type="string", length=50, nullable=true)
     */
    private $usName;

    /**
     * @var string
     * @ORM\Column(name="us_last_name", type="string", length=50, nullable=true)
     */
    private $usLastName;

    /**
     * @var string
     * @ORM\Column(name="us_email", type="string", length=50, nullable=true)
     */
    private $usEmail;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="us_phone_number", type="string", length=50, nullable=true)
     */
    private $usPhoneNumber;

    /**
     * @var \DateTime
     * @ORM\Column(name="us_date_created", type="date", nullable=true)
     */
    private $usDateCreated;

    /**
     * @var integer
     * @ORM\Column(name="us_status", type="smallint", nullable=true)
     */
    private $usStatus;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="us_parent_id", referencedColumnName="us_id")
     * })
     */
    private $usUserParent;

    /**
     * @var \App\Entity\Company
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="us_company_id", referencedColumnName="co_id")
     * })
     */
    private $usCompany;

    /**
     * @var string
     * @ORM\Column(name="us_token", type="string", length=10, unique=true)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="us_password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(name="us_salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @var string
     * @ORM\Column(name="us_deleted", type="boolean", nullable=false)
     */
    private $deleted;

    /**
     * @var string
     * @ORM\Column(name="us_is_omt", type="boolean", nullable=false, options={"default":"0"})
     */
    private $isOMTUser;

    /**
     * @var string
     * @ORM\Column(name="us_second_pass", type="text", nullable=true)
     */
    private $secondPass;

    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getSecondPass() {
        return $this->secondPass;
    }

    /**
     * @param type $secondPass
     */
    public function setSecondPass($secondPass) {
        $this->secondPass = $secondPass;
    }

    /**
     * @return type
     */
    public function getUsType() {
        return $this->usType;
    }

    /**
     * @param type $usType
     */
    public function setUsType($usType) {
        $this->usType = $usType;
    }

    /**
     * @return type
     */
    public function getUsName() {
        return $this->usName;
    }

    /**
     * @return type
     */
    public function getUsLastName() {
        return $this->usLastName;
    }

    /**
     * @return type
     */
    public function getUsEmail() {
        return $this->usEmail;
    }

    /**
     * @return type
     */
    public function getUsPhoneNumber() {
        return $this->usPhoneNumber;
    }

    /**
     * @return type
     */
    public function getUsDateCreated() {
        return $this->usDateCreated;
    }

    /**
     * @return type
     */
    public function getUsStatus() {
        return $this->usStatus;
    }

    /**
     * @return type
     */
    public function getUsUserParent() {
        return $this->usUserParent;
    }

    /**
     * @return type
     */
    public function getUsCompany() {
        return $this->usCompany;
    }

    /**
     * @param type $usName
     */
    public function setUsName($usName) {
        $this->usName = $usName;
    }

    /**
     * @param type $usLastName
     */
    public function setUsLastName($usLastName) {
        $this->usLastName = $usLastName;
    }

    /**
     * @param type $usEmail
     */
    public function setUsEmail($usEmail) {
        $this->usEmail = $usEmail;
    }

    /**
     * @param type $usPhoneNumber
     */
    public function setUsPhoneNumber($usPhoneNumber) {
        $this->usPhoneNumber = $usPhoneNumber;
    }

    /**
     * @param \DateTime $usDateCreated
     */
    public function setUsDateCreated(\DateTime $usDateCreated = null) {
        $this->usDateCreated = $usDateCreated;
    }

    /**
     * @param type $usStatus
     */
    public function setUsStatus($usStatus) {
        $this->usStatus = $usStatus;
    }

    /**
     * @param type $usUserParent
     */
    public function setUsUserParent($usUserParent) {
        $this->usUserParent = $usUserParent;
    }

    /**
     * @param Company $usCompany
     */
    public function setUsCompany(\App\Entity\Company $usCompany = null) {
        $this->usCompany = $usCompany;
    }

    /**
     * @return type
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param type $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return type
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * @param type $deleted
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
    }

    /**
     * @ORM\PrePersist
     */
    public function defaultDeleted() {
        $this->deleted = false;
    }

    /**
     * @return type
     */
    public function getIsOMTUser() {
        return $this->isOMTUser;
    }

    /**
     * @param type $isOMTUser
     */
    public function setIsOMTUser($isOMTUser) {
        $this->isOMTUser = $isOMTUser;
    }

    /**
     * Get Roles
     * @return Array
     */
    public function getRoles() {
        $companyStatus = Company::STATUS_INACTIVE;
        $isSuperAdmin = static::USER_SUPER_ADMIN;

        if ($this->getUsCompany()->getCoStatus() == $companyStatus && $isSuperAdmin != $this->usType) {
            return ['ROLE_INACTIVE'];
        }

        $return = ['ROLE_INACTIVE'];

        if ($this->getDeleted() == false) {
            if ($this->usStatus == static::STATUS_ACTIVE) {
                if ($this->usType == static::USER_ADMINISTRATOR) {
                    $return = ['ROLE_ADMINISTRATOR'];
                } elseif ($this->usType == static::USER_SUPER_ADMIN) {
                    $return = ['ROLE_SUPER_ADMIN'];
                } elseif ($this->usType == static::USER_LICENSE_MANAGER) {
                    $return = ['ROLE_LICENSE_MANAGER'];
                } elseif ($this->usType == static::USER_REPORT_VIEWER) {
                    $return = ['ROLE_REPORT_VIEWER'];
                } elseif ($this->usType == static::USER_ADMIN_DATABASES) {
                    $return = ['ROLE_ADMIN_DATABASES'];
                }
            } elseif ($this->usStatus == static::STATUS_INACTIVE) {
                $return = ['ROLE_INACTIVE'];
            }
        }

        return $return;
    }

    /**
     * get text status Active Inactive
     * @return string
     */
    public function getTextStatus() {
        $text = '';
        switch ($this->usStatus) {
            case static::STATUS_ACTIVE: $text = 'Active';
                break;
            case static::STATUS_INACTIVE: $text = 'Inactive';
                break;
            default:
                $text = 'Inactive';
        }
        return $text;
    }

    /**
     * Get text type super_admin, administrator, license_manager,
     * report_viewer, data_bases_administrator
     * @return string
     */
    public function getTextType() {
        $text = '';
        switch ($this->usType) {
            case static::USER_SUPER_ADMIN: $text = 'Super Admin';
                break;
            case static::USER_ADMINISTRATOR: $text = 'Administrator';
                break;
            case static::USER_LICENSE_MANAGER: $text = 'License Manager';
                break;
            case static::USER_REPORT_VIEWER: $text = 'Report Viewer';
                break;
            case static::USER_ADMIN_DATABASES: $text = 'Data Bases Administrator';
                break;
            default:
                $text = 'Report Viewer';
        }
        return $text;
    }

    /**
     * @return type
     */
    public function serialize() {
        return serialize([
            $this->id,
            $this->usType,
            $this->usName,
            $this->usLastName,
            $this->usEmail,
            $this->usPhoneNumber,
            $this->usStatus,
            $this->usDateCreated,
            $this->usCompany,
            $this->username,
            $this->password,
            $this->salt
        ]);
    }

    /**
     * @param type $serialized
     */
    public function unserialize($serialized) {
        list(
                $this->id,
                $this->usType,
                $this->usName,
                $this->usLastName,
                $this->usEmail,
                $this->usPhoneNumber,
                $this->usStatus,
                $this->usDateCreated,
                $this->usCompany,
                $this->username,
                $this->password,
                $this->salt
                ) = unserialize($serialized);
    }

    /**
     * @param type $usPassword
     */
    public function setPassword($usPassword) {
        $this->password = $usPassword;
    }

    /**
     * @return type
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param type $salt
     */
    public function setSalt($salt) {
        $this->salt = $salt;
    }

    /**
     * @return type
     */
    public function getSalt() {
        return $this->salt;
    }

    public function eraseCredentials() {
        
    }

    /**
     * @return type
     */
    public function __toString() {
        return $this->getUsName();
    }

    /**
     * Filter Searach Parameters organiza el WHERE
     * @param String $alias
     * @param Array $search
     * @return Array
     */
    public static function filterSearchParameters($alias, $search) {

        $textParameters = '';
        $parameters = [];

        if (isset($search['usName']) && $search['usName'] != '') {
            $textParameters .= " AND " . $alias . ".usName LIKE :usName";
            $parameters['usName'] = "%" . $search['usName'] . "%";
        }
        if (isset($search['usEmail']) && $search['usEmail'] != '') {
            $textParameters .= " AND " . $alias . ".usEmail LIKE :usEmail";
            $parameters['usEmail'] = "%" . $search['usEmail'] . "%";
        }
        if (isset($search['usStatus']) && $search['usStatus'] != '') {
            $textParameters .= " AND " . $alias . ".usStatus = :usStatus";
            $parameters['usStatus'] = $search['usStatus'];
        }
        if (isset($search['usType']) && $search['usType'] != '') {
            $textParameters .= " AND " . $alias . ".usType = :usType";
            $parameters['usType'] = $search['usType'];
        }
        if (isset($search['deleted'])) {
            $textParameters .= " AND " . $alias . ".deleted = :deleted";
            $parameters['deleted'] = $search['deleted'];
        }

        return ['text' => $textParameters, 'parameters' => $parameters];
    }

    /**
     * filter order parameters para consulta DQL segun el order solicitado
     * @param String $alias
     * @param Array $order
     * @return string el ORDER BY adecuado segun corresponda
     */
    public static function filterOrderParameters($alias, $order) {

        $orderBy = ' ORDER BY ' . $alias . '.usName ASC';

        if (isset($order ['order_by_user_name']) && $order ['order_by_user_name'] != '') {
            if ($order ['order_by_user_name'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".usName DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".usName ASC";
            }
        } elseif (isset($order ['order_by_user_email']) && $order ['order_by_user_email'] != '') {
            if ($order ['order_by_user_email'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".usEmail DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".usEmail ASC";
            }
        } elseif (isset($order ['order_by_user_status']) && $order ['order_by_user_status'] != '') {
            if ($order ['order_by_user_status'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".usStatus DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".usStatus ASC";
            }
        } elseif (isset($order ['order_by_user_type']) && $order ['order_by_user_type'] != '') {
            if ($order ['order_by_user_type'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".usType DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".usType ASC";
            }
        }

        return $orderBy;
    }

}
