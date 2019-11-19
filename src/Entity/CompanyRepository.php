<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of CompanyRepository
 * @author jocampo
 */
class CompanyRepository extends EntityRepository {

    /**
     * Metodo que se encarga de encontrar todas la compañias revendedoras de
     * registradas en la aplicacion para el rol del administrador
     * ya que este tiene acceso a todos los registros sin importar quien lo
     * hubiese creado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search array con los parametros de busqueda para las
     * empresas revendedoras registradas
     * @param type $order string con el parametro de ordenamiento para las
     * compañias revendedoras a buscar registradas
     * @return type array con las compañias resultantes de la busqueda de
     * compañias registradas
     */
    public function findResellersForAdminUser($search = '', $order = '') {

        $textParameters = "";
        $parameters = [];
        $orderBy = "";

        $textSelect = "SELECT co ";
        $withLicDataBase = " ";

        if ($search != '') {
            $dataParametersBasicData = Company::filterSearchParameters('co', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['userCreators']) && $search ['userCreators'] != '') {
                if (isset($search['myCompany']) && $search['myCompany'] != '') {
                    $textParameters .= " AND (co.id = :myCompany OR co.coUserCreator IN " . $search ['userCreators'] . ")";
                    $parameters['myCompany'] = $search['myCompany'];
                } else {
                    $textParameters .= " AND co.coUserCreator IN " . $search ['userCreators'];
                }
            }
        }

        if ($order != '') {

            $basicOrder = Company::filterOrderParameters('co', $order);
            $orderBy = $basicOrder;
        } else {
            $orderBy = " ORDER BY co.coCompanyName ASC";
        }

        $em = $this->getEntityManager();
        $sql = "$textSelect FROM App:Company co "
                . "$withLicDataBase "
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($sql);
        $query->setParameters($parameters);
        
        $resellers = $query->getResult();

        return $resellers;
    }

    /**
     * Metodo que se encarga de encontrar todas la compañias revendedoras de
     * registradas en la aplicacion para el rol del superadministrador
     * ya que este tiene acceso a todos los registros sin importar quien lo
     * hubiese creado
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $search array con los parametros de busqueda para las
     * empresas revendedoras registradas
     * @param type $order string con el parametro de ordenamiento para las
     * compañias revendedoras a buscar registradas
     * @return type arreglo con las compañias resultantes de la busqueda de
     * compañias registradas
     */
    public function findResellersForSuperadminUser($search = '', $order = '') {

        $textParameters = "";
        $parameters = [];
        $orderBy = "";

        $textSelect = "SELECT co ";
        $withLicDataBase = " ";

        if ($search != '') {
            $dataParametersBasicData = Company::filterSearchParameters('co', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search ['userCreators']) && $search ['userCreators'] != '') {
                $textParameters .= " AND co.coUserCreator IN :userCreators ";
                $parameters ['userCreators'] = $search ['userCreators'];
            }
        }

        if ($order != '') {
            $basicOrder = Company::filterOrderParameters('co', $order);
            $orderBy = $basicOrder;
        } else {
            $orderBy = " ORDER BY co.coCompanyName ASC";
        }

        $em = $this->getEntityManager();
        $sql = "$textSelect FROM App:Company co "
                . "$withLicDataBase "
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($sql);
        $query->setParameters($parameters);
        $resellers = $query->getResult();

        return $resellers;
    }

}
