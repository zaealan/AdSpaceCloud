<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use App\Entity\Account;

/**
 * Repositorio en el que se contienen todos los metodos con las consultas a
 * que la aplicacion realiza sobre la entidad cuentas (Account)
 * @author jocampo
 */
class AccountRepository extends EntityRepository {

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce
     * en el momento de cargar por primera vez el módulo accounts, es decir
     * , el index de usuarios.
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search array con los parametros de busqueda para las
     * cuentas de los clientes registradas
     * @param type $order string con el parametro de ordenamiento para las
     * cuentas de los clientes a buscar registradas
     * @return type array con las cuentas resultantes de la busqueda de
     * compañias registradas
     */
    public function accountForNormalUsers($search = '', $order = '') {

        $textParameters = "";
        $parameters = [];
        $orderBy = "";

        $textSelect = "SELECT ac ";
        $withLicDataBase = "LEFT JOIN App:AccountLicense al WITH al.alAccountLicense = ac.id ";

        if ($search != '') {
            //validamos los parametros de busqueda sobre la entidad Account
            $dataParametersBasicData = Account::filterSearchParameters('ac', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['alLicenseUsername']) && $search ['alLicenseUsername'] != '') {
                $textParameters .= " AND al.alLicenseUsername LIKE :alLicenseUsername";
                $parameters ['alLicenseUsername'] = $search ['alLicenseUsername'];
            }
            if (isset($search ['deviceUid']) && $search ['deviceUid'] != '') {
                $textParameters .= " AND al.deviceUid LIKE :deviceUid";
                $parameters ['deviceUid'] = "%" . $search ['deviceUid'] . "%";
            }
        }

        if ($order != '') {
            //validamos los parametros de ordenamiento sobre la entidad Account
            if ($basicOrder = Account::filterOrderParameters('ac', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = " ORDER BY al.alLicenseUsername ASC";
                } else {
                    $orderBy = " ORDER BY al.alLicenseUsername DESC";
                }
            }
        } else {
            $orderBy = " ORDER BY ac.acName ASC";
        }

        //creacion del query con los parametros correspondientes
        $em = $this->getEntityManager();
        $dql = "$textSelect FROM App:Account ac "
                . "$withLicDataBase "
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        return $query->getResult();
    }

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce
     * en el momento de cargar por primera vez el módulo accounts, es decir,
     * el index de usuarios.
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search array con los parametros de busqueda para las
     * cuentas de los clientes registradas
     * @param type $order string con el parametro de ordenamiento para las
     * cuentas de los clientes a buscar registradas
     * @return type array con las cuentas resultantes de la busqueda de
     * compañias registradas
     */
    public function accountForAdminUsers($search = '', $order = '') {

        $textParameters = "";
        $parameters = [];
        $orderBy = "";

        $textSelect = "SELECT ac ";
        $withLicDataBase = "LEFT JOIN App:AccountLicense al WITH al.alAccountLicense = ac.id ";

        if ($search != '') {
            //validamos los parametros de busqueda sobre la entidad Account
            $dataParametersBasicData = Account::filterSearchParameters('ac', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['userId']) && $search ['userId'] != '') {
                $userId = $search ['userId'];
            }
            if (isset($search ['alLicenseUsername']) && $search ['alLicenseUsername'] != '') {
                $textParameters .= " AND al.alLicenseUsername LIKE :alLicenseUsername";
                $parameters ['alLicenseUsername'] = $search ['alLicenseUsername'];
            }
            if (isset($search ['deviceUid']) && $search ['deviceUid'] != '') {
                $textParameters .= " AND al.deviceUid LIKE :deviceUid";
                $parameters ['deviceUid'] = "%" . $search ['deviceUid'] . "%";
            }
        }

        if ($order != '') {
            //validamos los parametros de ordenamiento sobre la entidad Account
            if ($basicOrder = Account::filterOrderParameters('ac', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = " ORDER BY al.alLicenseUsername ASC";
                } else {
                    $orderBy = " ORDER BY al.alLicenseUsername DESC";
                }
            }
        } else {
            $orderBy = " ORDER BY ac.acName ASC";
        }

        //creacion del query con los parametros correspondientes
        $em = $this->getEntityManager();
        $dql = "$textSelect FROM App:Account ac "
                . "$withLicDataBase "
                . "WHERE ac.acUser IN ("
                . " SELECT u.id FROM App:User u "
                . "     WHERE u.usCompany = ("
                . "         SELECT IDENTITY(user.usCompany) FROM App:User user "
                . "         WHERE user.id = $userId )"
                . ") $textParameters $orderBy";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        return $query->getResult();
    }

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce
     * en el momento de cargar por primera vez el módulo accounts, es decir,
     * el index de usuarios.
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search array con los parametros de busqueda para las
     * cuentas de los clientes registradas
     * @param type $order string con el parametro de ordenamiento para las
     * cuentas de los clientes a buscar registradas
     * @return type array con las cuentas resultantes de la busqueda de
     * compañias registradas
     */
    public function accountForSuperAdminUsers($search = '', $order = '') {

        $textParameters = "";
        $parameters = [];
        $orderBy = "";

        $textSelect = "SELECT ac ";
        $withLicDataBase = "LEFT JOIN App:AccountLicense al WITH al.alAccountLicense = ac.id ";

        if ($search != '') {
            //validamos los parametros de busqueda sobre la entidad Account
            $dataParametersBasicData = Account::filterSearchParameters('ac', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['alLicenseUsername']) && $search ['alLicenseUsername'] != '') {
                $textParameters .= " AND al.alLicenseUsername LIKE :alLicenseUsername";
                $parameters ['alLicenseUsername'] = "%" . $search ['alLicenseUsername'] . "%";
            }
            if (isset($search ['deviceUid']) && $search ['deviceUid'] != '') {
                $textParameters .= " AND al.deviceUid LIKE :deviceUid";
                $parameters ['deviceUid'] = "%" . $search ['deviceUid'] . "%";
            }
        }

        if ($order != '') {
            //validamos los parametros de ordenamiento sobre la entidad Account
            if ($basicOrder = Account::filterOrderParameters('ac', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = " ORDER BY al.alLicenseUsername ASC";
                } else {
                    $orderBy = " ORDER BY al.alLicenseUsername DESC";
                }
            }
        } else {
            $orderBy = " ORDER BY ac.acName ASC";
        }

        //creacion del query con los parametros correspondientes
        $em = $this->getEntityManager();
        $dql = "$textSelect FROM App:Account ac "
                . "$withLicDataBase "
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        return $query->getResult();
    }

    /**
     * @param type $data
     * @param type $log
     * @param type $account
     * @param type $em
     * @return type
     */
    public function setDataNewRecordOMT($data) {
        $em = $this->getEntityManager();

        if (isset($data->nickname)) {
            $account = $em->getRepository('App:Account')->findBy(['acNickName' => $data->ac_nick_name]);
            if (isset($account[0])) {
                $entity = $account[0];
            } else {
                $entity = new Account();
            }
        } else {
            $entity = new Account();
        }

        $responseToLevelArray = $this->setDataUpdateOMT($entity, $data);
        return $responseToLevelArray;
    }

    /**
     * @param type $entity
     * @param type $data
     * @return type
     */
    public function setDataUpdateOMT($entity, $data) {

        $em = $this->getEntityManager();

        if (isset($data->ac_name) && $data->ac_name != '') {
            $entity->setAcName($data->ac_name);
        }
        if (isset($data->ac_nick_name) && $data->ac_nick_name != '') {
            $entity->setAcNickName($data->ac_nick_name);
        }
        if (isset($data->ac_phone_number) && $data->ac_phone_number != '') {
            $entity->setAcPhoneNumber($data->ac_phone_number);
        }
        if (isset($data->ac_email) && $data->ac_email != '') {
            $entity->setAcEmail($data->ac_email);
        }
        if (isset($data->ac_contact_name) && $data->ac_contact_name != '') {
            $entity->setAcContactName($data->ac_contact_name);
        }
        if (isset($data->ac_suit_po_box) && $data->ac_suit_po_box != '') {
            $entity->setAcSuitPoBox($data->ac_suit_po_box);
        }
        
        if (isset($data->address_street) && $data->address_street != '') {
            $entity->setAcAddress($data->address_street);
        }
        if (isset($data->address_zip_code) && $data->address_zip_code != '') {
            $country = $em->getRepository('App:Zipcode')->findBy(['zcName' => $data->address_zip_code]);
            if (isset($country[0])) {
                $entity->setZipcode($country[0]);
            }
        }
        if (isset($data->address_country_name) && $data->address_country_name != '') {
            $country = $em->getRepository('App:Country')->findBy(['coName' => $data->address_country_name]);
            if (isset($country[0])) {
                if (isset($data->address_state_name) && $data->address_state_name != '') {
                    $state = $em->getRepository('App:State')->findBy(['stName' => $data->address_state_name, 'stCountry' => $country[0]->getStId()]);
                    if (isset($state[0])) {
                        if (isset($data->address_city_name) && $data->address_city_name != '') {
                            $city = $em->getRepository('App:City')->findBy(['ciName' => $data->address_city_name, 'ciState' => $state[0]->getId()]);
                            if (isset($city[0])) {
                                $entity->setCity($city[0]);
                            }
                        }
                    }
                }
            }
        }
        if (isset($data->address_latitude) && $data->address_latitude != '') {
            $entity->setAcSuitPoBox($data->address_latitude);
        }
        if (isset($data->address_longitude) && $data->address_longitude != '') {
            $entity->setAcSuitPoBox($data->address_longitude);
        }
        if (isset($data->id) && $data->id != '') {
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
            $where .= "AND ac.id $useEqual :prdtllaCodigo ";
        }

        $em = $this->getEntityManager();
        $dql = "SELECT "
                . "ac.id, "
                . "ac.acName AS ac_name, "
                . "ac.acNickName AS ac_nick_name, "
                . "ac.acPhoneNumber AS ac_phone_number, "
                . "ac.acEmail AS ac_email, "
                . "ac.acContactName AS ac_contact_name, "
                . "ac.acSuitPoBox AS ac_suit_po_box, "
                . "ac.acAddress AS address_street, "
                . "ci.ciName AS address_city_name, "
                . "st.stName AS address_state_name, "
                . "co.coName AS address_country_name, "
                . "zi.zcName AS address_zip_code, "
                . "zi.zcLatitude AS address_latitude, "
                . "zi.zcLongitude AS address_longitude, "
                . "ac.omtSync AS omt, "
                . "ac.id AS account_id, "
                . "ac.id AS level "
                . "FROM App:Account ac "
                . "JOIN App:City ci WITH ac.city = ci.id "
                . "JOIN App:Zipcode zi WITH ac.zipcode = zi.zcId "
                . "JOIN App:State st WITH ci.ciState = st.stId "
                . "JOIN App:Country co WITH st.stCountry = co.coId "
                . "WHERE $where ";

        $query = $em->createQuery($dql);
        if ($parameters !== NULL) {
            if ($parameters instanceof AccountLicense) {
                $query->setParameter("prdtllaCodigo", $parameters->getAlAccountLicense()->getId());
            } else {
                $query->setParameter("prdtllaCodigo", $parameters);
            }
        }

        $theArrayResult = $query->getResult();

        return $theArrayResult;
    }

}
