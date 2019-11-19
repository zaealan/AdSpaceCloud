<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use App\Entity\User;

/**
 * Description of UserRepository
 * @author jocampo
 */
class UserRepository extends EntityRepository {

    /**
     * Esta consulta permite obtener los usuarios en estado activo, los cuales
     * son los únicos que deben visualizarce en el momento de cargar por primera
     * vez, el módulo de usuarios, es decir, el index de usuarios.
     * @author jocampo <jocampo@kijho.com> 29/07/2016
     * @param type $search
     * @param type $order
     * @return array[UsuariosActivos]
     */
    public function usersForSuperadminUsers($search = '', $order = '') {

        $textParameters = "";
        $parameters = [];
        $orderBy = "";

        $textSelect = "SELECT us ";
        $withLicDataBase = "JOIN App:User us WITH co.id = us.usCompany ";

        $search['deleted'] = 0;

        if ($search != '') {
            $dataParametersBasicData = User::filterSearchParameters('us', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search['notMe']) && $search['notMe'] != '') {
                $parameters['notMe'] = $search['notMe'];
                $textParameters .= ' AND us.id <> :notMe';
            }
            if (isset($search['notSuper']) && $search['notSuper'] != '') {
                $parameters['notSuper'] = $search['notSuper'];
                $textParameters .= ' AND us.usType <> :notSuper';
            }
            if (isset($search['usCompany']) && $search['usCompany'] != '') {
                if (isset($search['me']) && $search['me'] != '') {
                    $parameters['me'] = $search['me'];
                    $parameters['usCompany'] = $search['usCompany'];
                    $textParameters .= ' AND (co.coUserCreator = :me OR us.usCompany = :usCompany)';
                } else {
                    $parameters['usCompany'] = $search['usCompany'];
                    $textParameters .= ' AND us.usCompany = :usCompany';
                }
            }
        }

        if ($order != '') {
            if ($basicOrder = User::filterOrderParameters('us', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = " ORDER BY co.coCompanyIdentification DESC";
                } else {
                    $orderBy = " ORDER BY co.coCompanyIdentification ASC";
                }
            }
        } else {
            $orderBy = " ORDER BY us.usName ASC";
        }

        $em = $this->getEntityManager();
        $dql = "$textSelect "
                . "FROM App:Company co "
                . $withLicDataBase
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        $userByReseller = $query->getResult();

        return $userByReseller;
    }

    /**
     * Esta consulta permite obtener las cuentas en estado activo, es decir,
     * deleted=0, los cuales son las únicas cuentas que deben visualizarce en el momento
     * de cargar por primera vez el módulo accounts, es decir, el index de
     * usuarios.
     * @author jocampo <jocampo@kijho.com> 29/07/2016
     * @param type $search
     * @param type $order
     * @return array[AccountsActivas]
     */
    public function usersForAdminUsers($search = '', $order = '') {

        $textParameters = "";
        $parameters = [];
        $orderBy = "";

        $textSelect = "SELECT us ";
        $withLicDataBase = "JOIN App:User us WITH co.id = us.usCompany ";

        $search['deleted'] = 0;

        if ($search != '') {
            $dataParametersBasicData = User::filterSearchParameters('us', $search);
            $textParameters .= $dataParametersBasicData['text'];
            $parameters = array_merge($dataParametersBasicData['parameters'], $parameters);

            if (isset($search['notMe']) && $search['notMe'] != '') {
                $parameters['notMe'] = $search['notMe'];
                $textParameters .= ' AND us.id <> :notMe';
            }
            if (isset($search['notSuper']) && $search['notSuper'] != '') {
                $parameters['notSuper'] = $search['notSuper'];
                $textParameters .= ' AND us.usType <> :notSuper';
            }
            if (isset($search['usCompany']) && $search['usCompany'] != '') {
                if (isset($search['me']) && $search['me'] != '') {
                    $parameters['me'] = $search['me'];
                    $parameters['usCompany'] = $search['usCompany'];
                    $textParameters .= ' AND co.coUserCreator = :me AND us.usCompany = :usCompany AND us.usUserParent = :me';
                } else {
                    $parameters['usCompany'] = $search['usCompany'];
                    $textParameters .= ' AND us.usCompany = :usCompany';
                }
            }
        }

        if ($order != '') {
            if ($basicOrder = User::filterOrderParameters('us', $order)) {
                $orderBy = $basicOrder;
            } elseif (isset($order ['pepito']) && $order ['pepito'] != '') {
                if ($order ['pepito'] % 2) {
                    $orderBy = " ORDER BY co.coCompanyIdentification DESC";
                } else {
                    $orderBy = " ORDER BY co.coCompanyIdentification ASC";
                }
            }
        } else {
            $orderBy = " ORDER BY us.usName ASC";
        }

        $em = $this->getEntityManager();
        $dql = "$textSelect "
                . "FROM App:Company co "
                . $withLicDataBase
                . "WHERE 1=1 $textParameters $orderBy ";
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        $userByReseller = $query->getResult();

        return $userByReseller;
    }

}
