<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use App\Entity\AccountLicense;

use App\Util\Util;

/**
 * Description of LicenseFiltersRepository
 * @author hHernandez
 */
class AccountLicenseRepository extends EntityRepository {

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce en el momento
     * de cargar por primera vez el módulo accounts, es decir, el index de
     * usuarios.
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search
     * @param type $order
     * @param type $count
     * @return array[AccountsActivas]
     */
    public function accountLicensesForNormalUsers($search = '', $order = '', $count = false) {

        $textSelect = '';
        $withLicDataBase = '';
        $textParameters = '';
        $parameters = [];
        $orderBy = " ";

        if ($count) {
            $textSelect = 'SELECT COUNT(liceac) ';
            $withLicDataBase = 'JOIN App:LicenseDataBase ldb WITH ldb.license = liceac.id';
        } else {
            $textSelect = 'SELECT liceac, ldb.isDatabaseCreated, ldb.isSchemaCreated ';
            $withLicDataBase = 'JOIN App:LicenseDataBase ldb WITH ldb.license = liceac.id ';
        }

        if ($search != '') {
            //validamos los parametros de busqueda sobre la entidad AccountLicense
            $dataParametersBasicData = AccountLicense::filterSearchParameters('liceac', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['alLicenseStatus']) && $search ['alLicenseStatus'] != '') {
                if ($search ['alLicenseStatus'] == AccountLicense::LICENSE_STATUS_ACTIVE || $search ['alLicenseStatus'] == AccountLicense::LICENSE_STATUS_INACTIVE) {
                    $textParameters .= " AND liceac.alLicenseStatus = :alLicenseStatus";
                    $parameters ['alLicenseStatus'] = $search ['alLicenseStatus'];
                } else {
                    $textParameters .= " AND liceac.hasAndroid = :hasAndroid";
                    if ($search ['alLicenseStatus'] == 3) {
                        $parameters ['hasAndroid'] = true;
                    } else {
                        $parameters ['hasAndroid'] = false;
                    }
                }
            }
            if (isset($search['usCompany']) && $search['usCompany'] != '') {
                $parameters['usCompany'] = $search['usCompany'];
                $textParameters .= ' AND us.usCompany = :usCompany';
            }
        }

        if ($order != '') {
            //validamos los parametros de ordenamiento sobre la entidad AccountLicense
            if ($basicOrder = AccountLicense::filterOrderParameters('liceac', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = ' ORDER BY liceac.alContacName ASC';
                } else {
                    $orderBy = ' ORDER BY liceac.alContacName DESC';
                }
            }
        } else {
            $orderBy = " ORDER BY liceac.alContacName ASC";
        }

        $em = $this->getEntityManager();
        $dql = "$textSelect FROM App:Company co "
                . "JOIN App:User us WITH co.id = us.usCompany "
                . "JOIN App:Account ac WITH us.id = ac.acUser "
                . "JOIN App:AccountLicense liceac WITH ac.id = liceac.alAccountLicense "
                . "$withLicDataBase "
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        if ($count) {
            return $query->getSingleScalarResult();
        } else {
            return $query->getResult();
        }
    }

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce en el momento
     * de cargar por primera vez el módulo accounts, es decir, el index de
     * usuarios.
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search
     * @param type $order
     * @param type $count
     * @return array[AccountsActivas]
     */
    public function accountLicensesForAdminUsers($search = '', $order = '', $count = false) {

        $textSelect = '';
        $withLicDataBase = '';
        $textParameters = '';
        $parameters = [];
        $orderBy = " ";

        if ($count) {
            $textSelect = 'SELECT COUNT(liceac) ';
            $withLicDataBase = 'JOIN App:LicenseDataBase ldb WITH ldb.license = liceac.id';
        } else {
            $textSelect = 'SELECT liceac, ldb.isDatabaseCreated, ldb.isSchemaCreated ';
            $withLicDataBase = 'JOIN App:LicenseDataBase ldb WITH ldb.license = liceac.id ';
        }

        if ($search != '') {
            //validamos los parametros de busqueda sobre la entidad AccountLicense
            $dataParametersBasicData = AccountLicense::filterSearchParameters('liceac', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['alLicenseStatus']) && $search ['alLicenseStatus'] != '') {
                if ($search ['alLicenseStatus'] == AccountLicense::LICENSE_STATUS_ACTIVE || $search ['alLicenseStatus'] == AccountLicense::LICENSE_STATUS_INACTIVE) {
                    $textParameters .= " AND liceac.alLicenseStatus = :alLicenseStatus";
                    $parameters ['alLicenseStatus'] = $search ['alLicenseStatus'];
                } else {
                    $textParameters .= " AND liceac.hasAndroid = :hasAndroid";
                    if ($search ['alLicenseStatus'] == 3) {
                        $parameters ['hasAndroid'] = true;
                    } else {
                        $parameters ['hasAndroid'] = false;
                    }
                }
            }
            if (isset($search['usCompany']) && $search['usCompany'] != '') {
                $parameters['usCompany'] = $search['usCompany'];
                $textParameters .= ' AND us.usCompany = :usCompany';
            }
        }

        if ($order != '') {
            //validamos los parametros de ordenamiento sobre la entidad AccountLicense
            if ($basicOrder = AccountLicense::filterOrderParameters('liceac', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = ' ORDER BY liceac.alContacName ASC';
                } else {
                    $orderBy = ' ORDER BY liceac.alContacName DESC';
                }
            }
        } else {
            $orderBy = " ORDER BY liceac.alContacName ASC";
        }

        $em = $this->getEntityManager();
        $dql = "$textSelect FROM App:Company co "
                . "JOIN App:User us WITH co.id = us.usCompany "
                . "JOIN App:Account ac WITH us.id = ac.acUser "
                . "JOIN App:AccountLicense liceac WITH ac.id = liceac.alAccountLicense "
                . "$withLicDataBase "
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        if ($count) {
            return $query->getSingleScalarResult();
        } else {
            return $query->getResult();
        }
    }

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce en el momento
     * de cargar por primera vez el módulo accounts, es decir, el index de
     * usuarios.
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search
     * @param type $order
     * @param type $count
     * @return array[AccountsActivas]
     */
    public function accountLicensesForSuperAdminUsers($search = '', $order = '', $count = false) {

        $textSelect = '';
        $withLicDataBase = '';
        $textParameters = '';
        $parameters = [];
        $orderBy = " ";

        if ($count) {
            $textSelect = 'SELECT COUNT(liceac) ';
            $withLicDataBase = ' ';
        } else {
            $textSelect = "SELECT liceac, '' AS auxSelectField ";
            $withLicDataBase = ' ';
        }

        if ($search != '') {
            //validamos los parametros de busqueda sobre la entidad AccountLicense
            $dataParametersBasicData = AccountLicense::filterSearchParameters('liceac', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['alLicenseStatus']) && $search ['alLicenseStatus'] != '') {
                if ($search ['alLicenseStatus'] == AccountLicense::LICENSE_STATUS_ACTIVE || $search ['alLicenseStatus'] == AccountLicense::LICENSE_STATUS_INACTIVE) {
                    $textParameters .= " AND liceac.alLicenseStatus = :alLicenseStatus";
                    $parameters ['alLicenseStatus'] = $search ['alLicenseStatus'];
                } else {
                    $textParameters .= " AND liceac.hasAndroid = :hasAndroid";
                    if ($search ['alLicenseStatus'] == 3) {
                        $parameters ['hasAndroid'] = true;
                    } else {
                        $parameters ['hasAndroid'] = false;
                    }
                }
            }
        }

        if ($order != '') {
            //validamos los parametros de ordenamiento sobre la entidad AccountLicense
            if ($basicOrder = AccountLicense::filterOrderParameters('liceac', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = ' ORDER BY liceac.alContacName ASC';
                } else {
                    $orderBy = ' ORDER BY liceac.alContacName DESC';
                }
            }
        } else {
            $orderBy = " ORDER BY liceac.alContacName ASC";
        }

        $em = $this->getEntityManager();
        $dql = "$textSelect FROM App:Account ac "
                . "JOIN App:AccountLicense liceac WITH ac.id = liceac.alAccountLicense "
                . "$withLicDataBase "
                . "WHERE 1=1 $textParameters $orderBy";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        if ($count) {
            return $query->getSingleScalarResult();
        } else {
            return $query->getResult();
        }
    }

    /**
     * Funcion encargada de obtener las licencias segun un parametro
     * de busqueda de tipo estado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $statusFilter
     * @return type
     */
    public function findLisenceByStatus($statusFilter = null) {
        $textParameters = '1 ';
        $parameters = array();
        if ($statusFilter != null) {
            $textParameters = " license.alLicenseStatus = :status ";
            $parameters['status'] = $statusFilter;
        }

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getResult();
    }

    /**
     * Funcion encargada de obtener las licencias segun un parametro
     * de busqueda de tipo estado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $statusFilter
     * @return type
     */
    public function findLisencesForLoginTest($statusFilter = null, $numTestinlicenses = 5) {
        $textParameters = '1';
        $parameters = [];
        if ($statusFilter != null) {
            $textParameters = " license.isTesting = :testing";
            $parameters['testing'] = $statusFilter;
        }

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults($numTestinlicenses);
        return $consult->getResult();
    }

    /**
     * Funcion encargada de obtener las licencias segun un parametro
     * de busqueda de tipo estado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @return type
     */
    public function findLisencesForRealLoginTest($numTestinlicenses = 5) {

        $textParameters = " license.typeTestLicense > 0 AND license.isTesting IS NOT NULL ";

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);

        $consult->setMaxResults($numTestinlicenses);
        return $consult->getResult();
    }

    /**
     * Funcion encargada de obtener las licencias segun un parametro
     * de busqueda de tipo estado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $licenseNickname
     * @return type
     */
    public function findLisenceForOMTLogin($licenseNickname = null) {
        $textParameters = 'license.hasLogedOMT = :isLoged AND license.omtSync IS NOT NULL ';
        $parameters['isLoged'] = true;
        if ($licenseNickname != null) {
            $textParameters = " license.alLicenseUsername = :nickname ";
            $parameters['nickname'] = $licenseNickname;
        }

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getArrayResult();
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search
     * @return type
     */
    public function getActiveLicensesToValidateVersionForUpdate() {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['hasLoginAndroid'] = true;

        $em = $this->getEntityManager();
        $dql = "SELECT license.id, license.alLicenseUsername, license.androidVersionName "
                . "FROM App:AccountLicense license "
                . "WHERE license.alLicenseStatus = :status AND license.hasAndroid = :hasLoginAndroid ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults(50);
        return $consult->getResult();
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @return type
     */
    public function getAllLicensesWithPushyOrUID() {

        $em = $this->getEntityManager();
        $dql = "SELECT li.alLicenseUsername, li.deviceUid, li.pushyKey "
                . "FROM App:AccountLicense li "
                . "WHERE (li.deviceUid IS NOT NULL OR li.pushyKey IS NOT NULL) ORDER BY li.id DESC";

        $consult = $em->createQuery($dql);
        return $consult->getArrayResult();
    }

    public function findActiveLicensesAutocomp($acTerm) {
        $em = $this->getEntityManager();

        $query = "SELECT li.id, li.alLicenseUsername AS value, CONCAT(CONCAT(CONCAT(li.alRestaurantName,' ('),li.alLicenseUsername),')') AS label
            FROM App:AccountLicense li
            JOIN App:Account ac WITH li.alAccountLicense = ac.id
            WHERE li.alLicenseStatus = :licactive AND ac.deleted = :accactive AND li.hasAndroid = :lichasandroid
            AND CONCAT(CONCAT(CONCAT(li.alRestaurantName,' ('),li.alLicenseUsername),')') LIKE :term ";

        $dealers = $em->createQuery($query);
        $dealers->setParameter('term', $acTerm);
        $dealers->setParameter('licactive', AccountLicense::LICENSE_STATUS_ACTIVE);
        $dealers->setParameter('accactive', Account::ACCOUNT_STATUS_ACTIVE);
        $dealers->setParameter('lichasandroid', true);
        $dealers->setMaxResults(25);
        $result = $dealers->getArrayResult();

        return $result;
    }

    /**
     * @param type $data
     * @return type
     */
    public function setDataNewRecordOMT($data) {
        $em = $this->getEntityManager();

        if (isset($data->nickname)) {
            $restaurant = $em->getRepository('App:AccountLicense')->findBy(['alLicenseUsername' => $data->nickname]);
            if (isset($restaurant[0])) {
                $entity = $restaurant[0];
            } else {
                $entity = new AccountLicense();
            }
        } else {
            $entity = new AccountLicense();
        }

        $entity->setAlDateCreated(new \DateTime('now'));
        $entity->setAlLicenseStatus(1);
        $responseToOmtArray = $this->setDataUpdateOMT($entity, $data);
        return $responseToOmtArray;
    }

    /**
     * @param type $entity
     * @param type $data
     * @return type
     */
    public function setDataUpdateOMT($entity, $data) {

        $em = $this->getEntityManager();

        if (isset($data->rest_level_db_nickname) && $data->rest_level_db_nickname != '') {
            $entity->setAlLicenseUsername($data->rest_level_db_nickname);
        } if (isset($data->rest_name) && $data->rest_name != '') {
            $entity->setAlRestaurantName($data->rest_name);
        } if (isset($data->rest_contact_person) && $data->rest_contact_person != '') {
            $entity->setAlContacName($data->rest_contact_person);
        } if (isset($data->resem_email) && $data->resem_email != '') {
            $entity->setAlLicenseEmail($data->resem_email);
        } if (isset($data->resph_number) && $data->resph_number != '') {
            $entity->setAlPhoneNumber($data->resph_number);
        } if (isset($data->restad_street) && $data->restad_street != '') {
            $entity->setAlAddres($data->restad_street);
        } if (isset($data->restad_suit_number) && $data->restad_suit_number != '') {
            $entity->setAlSuitPoBox($data->restad_suit_number);
        } if (isset($data->id) && $data->id != '') {
            $entity->setOmtSync($data->id);
        }

        $em->persist($entity);

        $resulQuery = $this->flushObjects();
        if (isset($resulQuery['result']) && $resulQuery['result'] == "__OK__") {
            $syncRespArray[$entity->getId()] = $entity->getOmtSync();
        } else {
            if (isset($resulQuery['msn'])) {
                $syncRespArray = $resulQuery['msn'];
            } else {
                $syncRespArray = 'Unknown error!';
            }
        }

        return $syncRespArray;
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @return string
     */
    public function flushObjects() {
        $em = $this->getEntityManager();
        $resulQuery['entity'] = $this->getClassName();

        try {
            $em->flush();
            $resulQuery['msn'] = "query satisfactory";
            $resulQuery['result'] = "__OK__";
        } catch (\Exception $ex) {
            $resulQuery['msn'] = "query error " . $ex->getMessage();
            $resulQuery['result'] = "__KO__";
        }
        return $resulQuery;
    }

    /**
     * Entrega los datos de la categoria
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $parameters
     * @param type $useEqual
     * @param type $initialInsert
     * @param type $container
     * @return string
     */
    public function getDataDownloadOMT($parameters = NULL, $useEqual = "<>", $initialInsert = false, $container = null) {
        $where = "1 = 1 ";
        if ($parameters !== NULL) {
            $where .= "AND li.id $useEqual :prdtllaCodigo ";
        }

        $em = $this->getEntityManager();
        $dql = "SELECT li.alLicenseUsername AS rest_level_db_nickname, "
                . "li.alRestaurantName AS rest_name, "
                . "li.alContacName AS rest_contact_person, "
                . "li.alLicenseEmail AS resem_email, "
                . "li.alPhoneNumber AS resph_number, "
                . "(li.alDateCreated) AS rest_inauguration_date, "
                . "li.alAddres AS restad_street, "
                . "li.alSuitPoBox AS restad_suit_number, "
                . "li.deviceUid AS rest_leveldevice_uid, "
                . "li.pushyKey AS rest_pushy_key, "
                . "z.zcName AS restad_zip_code, "
                . "li.alLongitude AS restad_longitude, "
                . "li.alLatitude AS restad_latitude, "
                . "c.ciName AS restad_city_name, "
                . "s.stName AS restad_state_name, "
                . "co.coName AS restad_country_name, "
                . "'Is from LEVEL' AS rest_description, "
                . "li.id AS id, "
                . "li.id AS rest_id, "
                . "li.id AS restad_id, "
                . "li.id AS resem_id, "
                . "li.id AS resph_id, "
                . "li.id AS restme_id, "
                . "1 AS rest_is_from_level, "
                . "IDENTITY(li.alAccountLicense) AS account, "
                . "li.id AS omt, "
                . "li.id AS level "
                . "FROM App:AccountLicense li "
                . "JOIN App:Zipcode z WITH li.zipcode = z.zcId "
                . "JOIN App:City c WITH li.city = c.id "
                . "JOIN App:State s WITH c.ciState = s.stId  "
                . "JOIN App:Country co WITH s.stCountry = co.coId "
                . "WHERE $where ";

        $query = $em->createQuery($dql);
        if ($parameters !== NULL) {
            if ($parameters instanceof AccountLicense) {
                $query->setParameter("prdtllaCodigo", $parameters->getId());
            } else {
                $query->setParameter("prdtllaCodigo", $parameters);
            }
        }

        $theArrayResult = $query->getResult();

        if (strstr($theArrayResult[0]['restad_street'], ',', true)){
            $theArrayResult[0]['restad_street'] = strstr($theArrayResult[0]['restad_street'], ',', true);
        }
        $theArrayResult[0]['rest_name'] = Util::replaceCharactersEspecials($theArrayResult[0]['rest_name'], false);
        $theArrayResult[0]['restad_city_name'] = Util::replaceCharactersEspecials($theArrayResult[0]['restad_city_name']);
        $theArrayResult[0]['restad_state_name'] = Util::replaceCharactersEspecials($theArrayResult[0]['restad_state_name']);
        $theArrayResult[0]['restad_country_name'] = Util::replaceCharactersEspecials($theArrayResult[0]['restad_country_name']);

        return $theArrayResult;
    }

    /**
     * Funcion encargada de obtener las licencias segun un parametro
     * de busqueda de tipo estado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $licensesInIds
     * @return type
     */
    public function findLicensesForMasterUpdateSender($licensesInIds) {
        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE license.id IN $licensesInIds ";

        $consult = $em->createQuery($dql);
        return $consult->getResult();
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $lastDay
     * @return type
     */
    public function getActiveRawLicensesToMakeSomeGeneralTask() {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['hasLoginAndroid'] = true;

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE license.alLicenseStatus = :status AND license.hasAndroid = :hasLoginAndroid";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults(10);
        return $consult->getResult();
    }
    
    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $lastDay
     * @return type
     */
    public function getUncheckedScheduleMenuLicenses() {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        
        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE license.alLicenseStatus = :status AND license.scheduleMenuChecked IS NULL";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults(10);
        return $consult->getResult();
    }
    
    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $lastDay
     * @return type
     */
    public function getActiveLicensesToMakeS3AutomaticRequest($lastDay) {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['hasLoginAndroid'] = true;

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE license.alLicenseStatus = :status AND license.hasAndroid = :hasLoginAndroid AND (license.lastDateS3DBUpload < '" . $lastDay->format('Y-m-d H:i:s') . "' OR license.lastDateS3DBUpload IS NULL)";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults(10);
        return $consult->getResult();
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $lastDay
     * @return type
     */
    public function getActiveLicensesToMakeDataBaseAndroidCleanseRequest($lastDay, $databaseMaxSize, $maxResultForQueue) {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['hasLoginAndroid'] = true;
        $parameters['maxDatabaseSize'] = $databaseMaxSize;

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE license.alLicenseStatus = :status "
                . "AND license.hasAndroid = :hasLoginAndroid "
                . "AND (license.lastConsecutiveAndroidDryingDB < '" . $lastDay->format('Y-m-d H:i:s') . "' OR license.lastConsecutiveAndroidDryingDB IS NULL)"
                . "AND license.androidDatabaseSize > :maxDatabaseSize "
                . "ORDER BY license.androidDatabaseSize DESC, license.lastConsecutiveAndroidDryingDB ASC "
        ;

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults($maxResultForQueue);
        return $consult->getResult();
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $lastDay
     * @return type
     */
    public function getActiveLicensesToMakeWebDataBaseBackUpRequest($lastDay, $maxResultForQueue) {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['hasLoginAndroid'] = true;

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE license.alLicenseStatus = :status "
                . "AND license.hasAndroid = :hasLoginAndroid "
                . "AND (license.lastDateS3WebDBUpload < '" . $lastDay->format('Y-m-d H:i:s') . "' OR license.lastDateS3WebDBUpload IS NULL)"
                . "ORDER BY license.lastDateS3WebDBUpload ASC ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults($maxResultForQueue);
        return $consult->getResult();
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $lastDay
     * @return type
     */
    public function getDistinctVersionsByLicense() {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['hasLoginAndroid'] = true;

        $em = $this->getEntityManager();
        $dql = "SELECT DISTINCT(license.androidVersionName) AS version "
                . "FROM App:AccountLicense license "
                . "WHERE license.alLicenseStatus = :status AND license.hasAndroid = :hasLoginAndroid AND license.androidVersionName IS NOT NULL";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);

        return $consult->getResult();
    }

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce en el momento
     * de cargar por primera vez el módulo accounts, es decir, el index de
     * usuarios.
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search
     * @param type $order
     * @param type $count
     * @return array[AccountsActivas]
     */
    public function searchLicenseInformationList($search = '', $order = '', $count = false,$dataSize) {

        $textSelect = '';
        $withLicDataBase = '';
        $textParameters = '';
        $parameters = [];
        $orderBy = " ";
        $em = $this->getEntityManager();

        if ($count) {
            $textSelect = 'SELECT COUNT(liceac) ';
        } else {
            $textSelect = 'SELECT liceac ';
        }

        if ($search != '') {
            //validamos los parametros de busqueda sobre la entidad AccountLicense
            $dataParametersBasicData = AccountLicense::filterSearchParameters('liceac', $search, $dataSize);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            $parameters ['hasAndroid'] = true;
        }

        if ($order != '') {
            //validamos los parametros de ordenamiento sobre la entidad AccountLicense
            if ($basicOrder = AccountLicense::filterOrderParameters('liceac', $order)) {
                $orderBy = $basicOrder;
            }
        } else {
            $orderBy = " ORDER BY liceac.id ASC";
        }

        $dql = "$textSelect FROM "
                . "App:AccountLicense liceac "
                . "$withLicDataBase "
                . "WHERE liceac.hasAndroid = :hasAndroid $textParameters $orderBy";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        if ($count) {
            return $query->getSingleScalarResult();
        } else {
            return $query->getResult();
        }
    }

    /**
     * @return type
     */
    public function getNonTestingLicenseToCountBilledInvoices() {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['isTesting'] = true;

        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . "FROM App:AccountLicense license "
                . "WHERE license.isTesting = :isTesting AND license.alLicenseStatus = :status ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);

        return $consult->getResult();
    }

    /**
     * @return type
     */
    public function getNonTestingLicenseToCronTypeServiceCommand() {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;

        $em = $this->getEntityManager();
        $dql = "SELECT lc "
                . "FROM App:AccountLicense lc "
                . "WHERE lc.alLicenseStatus = :status ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);

        return $consult->getResult();
    }

    /**
     * @return type
     */
    public function getTheLastesVersionRegisteredInDataBaseByWorkingAPKs() {

        $parameters = [];
        $parameters['status'] = AccountLicense::LICENSE_STATUS_ACTIVE;
        $parameters['hasAndroid'] = true;

        $em = $this->getEntityManager();
        $dql = "SELECT license.androidVersionName "
                . "FROM App:AccountLicense license "
                . "WHERE license.hasAndroid = :hasAndroid AND license.alLicenseStatus = :status "
                . "ORDER BY license.androidVersionName DESC ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults(1);

        return $consult->getSingleResult();
    }
    
    /**
     * Funcion encargada de obtener las licencias segun un parametro
     * de busqueda de tipo estado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $licensesInIds
     * @return type
     */
    public function findByNameAndStateName($cityName, $stateName) {

        $em = $this->getEntityManager();
        $dql = "SELECT ct "
                . "FROM App:City ct "
                . "JOIN App:State st WITH ct.ciState = st.stId "
                . "WHERE ct.ciName LIKE :cityName AND st.stName LIKE :stateName ";

        $consult = $em->createQuery($dql);
        $consult->setParameter('cityName', $cityName);
        $consult->setParameter('stateName', $stateName);
        $consult->setMaxResults(1);
        
        return $consult->getResult();
    }
    
    /**
     * @param type $definitionInterval
     * @return type
     */
    public function getLicensesFromArrayInterval($definitionInterval) {
        $em = $this->getEntityManager();
        $dql = "SELECT license "
                . " FROM App:AccountLicense license "
                . " WHERE license.id > " . (current($definitionInterval)-1) . " AND license.id < " . (next($definitionInterval)+1)
                . " ORDER BY license.id ASC ";

        $consult = $em->createQuery($dql);

        return $consult->getResult();
    }

    public function searchRestaurant($search = null){

        $em = $this->getEntityManager();
        $where =" ";
        if(!isset($search['page'])){
            $search['page'] = 1;
        }
        if(!isset($search['limit'])){
            $search['limit'] = 10;
        }

        if(isset($search['nickname'])){
            $where .= " AND license.alLicenseUsername = '". $search['nickname'] . "'";
        }else if(isset($search['accountNickname'])){
            $where .= " AND license.alLicenseUsername = '". $search['accountNickname'] . "' ";
        }

        $dql = "SELECT license  FROM App:AccountLicense license 
                JOIN App:RestaurantLoyaltyConfiguration rlc WITH rlc.license = license.id
                WHERE rlc.license = license.id AND (rlc.useLocalPoints = 1 OR rlc.useGlobalPoints = 1) ". $where ;

        $consult = $em->createQuery($dql)
                ->setFirstResult($search['limit'] * ($search['page'] - 1)) // Offset
                ->setMaxResults($search['limit']); // Limit;
        $result =$consult->getResult();

        return $result;

    }
    
    /**
     * Esta funcion permite consultar las licencias para la respectiva limpieza de licensor
     * 
     */
    public function searchForLicensesByCleaningDate($search = null, $maxLicense ){
        
        $em = $this->getEntityManager();
        
        $dql = "SELECT license  
                FROM App:AccountLicense license
                ORDER BY license.lastCleaningOfLicensorDatabase ASC  ";
            
        $consult = $em->createQuery($dql);
        $consult->setMaxResults($maxLicense);

        return $consult->getResult();
    }

}
