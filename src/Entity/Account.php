<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Account
 * @ORM\Table(name="account", indexes={@ORM\Index(name="ac_user", columns={"ac_user"})})
 * @ORM\Entity(repositoryClass="App\Entity\AccountRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Account {

    const ACCOUNT_STATUS_ACTIVE = 0;
    const ACCOUNT_STATUS_INACTIVE = 1;

    /**
     * @var integer
     * @ORM\Column(name="ac_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="ac_name", type="string", length=50, nullable=false)
     */
    private $acName;

    /**
     * @var string
     * @ORM\Column(name="ac_nick_name", type="string", length=255, nullable=true, unique=true)
     */
    private $acNickName;

    /**
     * @var string
     * @ORM\Column(name="ac_phone_number", type="string", length=50, nullable=false)
     */
    private $acPhoneNumber;

    /**
     * @var string
     * @ORM\Column(name="ac_email", type="string", length=50, nullable=true)
     */
    private $acEmail;

    /**
     * @var string
     * @ORM\Column(name="ac_contact_name", type="string", length=50, nullable=false)
     */
    private $acContactName;

    /**
     * @var \DateTime
     * @ORM\Column(name="ac_date_created", type="datetime", nullable=true)
     */
    private $acDateCreated;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ac_user", referencedColumnName="us_id", nullable=true)
     * })
     */
    private $acUser;

    /**
     * @var string
     * @ORM\Column(name="ac_suit_po_box", type="string", nullable=true)
     */
    private $acSuitPoBox;

    /**
     * @var string
     * @ORM\Column(name="ac_address", type="string", nullable=true)
     */
    private $acAddress;

    /**
     * @var \App\Entity\City
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ac_city_id", referencedColumnName="ci_id", nullable=true)
     * })
     */
    private $city;

    /**
     * @var \App\Entity\Zipcode
     * @ORM\ManyToOne(targetEntity="App\Entity\Zipcode")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ac_zip_code_id", referencedColumnName="zc_id", nullable=true)
     * })
     */
    private $zipcode;

    /**
     * @var string
     * @ORM\Column(name="ac_deleted", type="boolean", nullable=false)
     */
    private $deleted;

    /**
     * @var string
     * @ORM\Column(name="omt_sync", type="guid", nullable=true)
     */
    private $omtSync;
    
    /**
     * @return type
     */
    public function getOmtSync() {
        return $this->omtSync;
    }

    /**
     * @param type $omtSync
     */
    public function setOmtSync($omtSync) {
        $this->omtSync = $omtSync;
    }
    
    /**
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return type
     */
    public function getAcName() {
        return $this->acName;
    }

    /**
     * @return type
     */
    public function getAcPhoneNumber() {
        return $this->acPhoneNumber;
    }

    /**
     * @return type
     */
    public function getAcEmail() {
        return $this->acEmail;
    }

    /**
     * @return type
     */
    public function getAcContactName() {
        return $this->acContactName;
    }

    /**
     * @return type
     */
    public function getAcDateCreated() {
        return $this->acDateCreated;
    }

    /**
     * @return type
     */
    public function getAcUser() {
        return $this->acUser;
    }

    /**
     * @param type $acName
     */
    public function setAcName($acName) {
        $this->acName = $acName;
    }

    /**
     * @param type $acPhoneNumber
     */
    public function setAcPhoneNumber($acPhoneNumber) {
        $this->acPhoneNumber = $acPhoneNumber;
    }

    /**
     * @param type $acEmail
     */
    public function setAcEmail($acEmail) {
        $this->acEmail = $acEmail;
    }

    /**
     * @param type $acContactName
     */
    public function setAcContactName($acContactName) {
        $this->acContactName = $acContactName;
    }

    /**
     * @param \DateTime $acDateCreated
     */
    public function setAcDateCreated(\DateTime $acDateCreated = NULL) {
        $this->acDateCreated = $acDateCreated;
    }

    /**
     * @param \App\Entity\User $acUser
     */
    public function setAcUser(\App\Entity\User $acUser = null) {
        $this->acUser = $acUser;
    }

    /**
     * @return type
     */
    public function __toString() {
        return "" . $this->acName;
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
     * @param type $acNickName
     */
    public function setAcNickName($acNickName) {
        $this->acNickName = $acNickName;
    }

    /**
     * @return type
     */
    public function getAcNickName() {
        return $this->acNickName;
    }

    /**
     * @ORM\PrePersist
     */
    public function defaultDeleted() {
        $this->deleted = false;
    }

    /**
     * @return string
     */
    public function accountStatus() {
        if (!$this->getDeleted()) {
            return 'Active';
        } else {
            return 'Inactive';
        }
    }

    /**
     * @return type
     */
    public function getAcSuitPoBox() {
        return $this->acSuitPoBox;
    }

    /**
     * @return type
     */
    public function getAcAddress() {
        return $this->acAddress;
    }

    /**
     * @return type
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @return type
     */
    public function getZipcode() {
        return $this->zipcode;
    }

    /**
     * @param type $acSuiPoBox
     */
    public function setAcSuitPoBox($acSuiPoBox) {
        $this->acSuitPoBox = $acSuiPoBox;
    }

    /**
     * @param type $acAddress
     */
    public function setAcAddress($acAddress) {
        $this->acAddress = $acAddress;
    }

    /**
     * @param \App\Entity\City $acCitiId
     */
    public function setCity(\App\Entity\City $acCitiId) {
        $this->city = $acCitiId;
    }

    /**
     * @param \App\Entity\Zipcode $zipcode
     */
    public function setZipcode(\App\Entity\Zipcode $zipcode) {
        $this->zipcode = $zipcode;
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $alias slachichas y mas
     * @param type $search
     * @return type
     */
    public static function filterSearchParameters($alias, $search) {

        $textParameters = '';
        $parameters = [];

        if (isset($search ['acName']) && $search ['acName'] != '') {
            $textParameters .= " AND " . $alias . ".acName LIKE :acName";
            $parameters ['acName'] = "%" . $search ['acName'] . "%";
        }
        if (isset($search ['acContactName']) && $search ['acContactName'] != '') {
            $textParameters .= " AND " . $alias . ".acContactName LIKE :acContactName";
            $parameters ['acContactName'] = "%" . $search ['acContactName'] . "%";
        }
        if (isset($search ['acEmail']) && $search ['acEmail'] != '') {
            $textParameters .= " AND " . $alias . ".acEmail LIKE :acEmail";
            $parameters ['acEmail'] = "%" . $search ['acEmail'] . "%";
        }
        if (isset($search ['deleted']) && $search ['deleted'] != '') {
            $textParameters .= " AND " . $alias . ".deleted = :deleted";
            $parameters ['deleted'] = $search ['deleted'];
        }
        if (isset($search ['acUser']) && $search ['acUser'] != '') {
            $textParameters .= " AND " . $alias . ".acUser = :acUser";
            $parameters ['acUser'] = $search ['acUser'];
        }

        return ['text' => $textParameters, 'parameters' => $parameters];
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $alias slachichas y mas
     * @param type $order
     * @return string
     */
    public static function filterOrderParameters($alias, $order) {

        $orderBy = ' ORDER BY ' . $alias . '.acName ASC';

        if (isset($order ['order_by_contac_name']) && $order ['order_by_contac_name'] != '') {
            if ($order ['order_by_contac_name'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".acContactName DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".acContactName ASC";
            }
        } elseif (isset($order ['order_by_account_email']) && $order ['order_by_account_email'] != '') {
            if ($order ['order_by_account_email'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".acEmail DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".acEmail ASC";
            }
        } elseif (isset($order ['order_by_account_name']) && $order ['order_by_account_name'] != '') {
            if ($order ['order_by_account_name'] % 2) {
                $orderBy = " ORDER BY " . $alias . ".acName DESC";
            } else {
                $orderBy = " ORDER BY " . $alias . ".acName ASC";
            }
        }

        return $orderBy;
    }

}
