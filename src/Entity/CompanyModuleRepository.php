<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of CompanyModuleRepository
 * @author zaealan
 */
class CompanyModuleRepository extends EntityRepository {

    /**
     * Esta funcion permita listar si un modulo que se le va agregar a un usuario
     * esta activo para la compañia a la que pertenece el usuario
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $slug
     * @param type $companyId
     * @return type
     */
    public function findByModuleCompany($slug, $companyId) {

        $em = $this->getEntityManager();
        $dql = 'SELECT moduCompa '
                . 'FROM App:CompanyModule moduCompa '
                . 'JOIN moduCompa.cmModule modu '
                . 'WHERE modu.moSlug = :slug AND moduCompa.cmCompany = :companyId ORDER BY modu.moOrder ASC';
        $consult = $em->createQuery($dql);
        $consult->setParameter('slug', $slug);
        $consult->setParameter('companyId', $companyId);
        return $consult->getSingleResult();
    }

    /**
     * Esta funcion permita listar los modulos que se le va agregar a un usuario
     * esta activo para la compañia a la que pertenece el usuario
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $companyId
     * @return type
     */
    public function findModulesCompanyForEdit($companyId) {

        $em = $this->getEntityManager();
        $dql = 'SELECT moduCompa '
                . 'FROM App:CompanyModule moduCompa '
                . 'JOIN moduCompa.cmModule modu '
                . 'WHERE moduCompa.cmCompany = :companyId ORDER BY modu.moOrder ASC';
        $consult = $em->createQuery($dql);
        $consult->setParameter('companyId', $companyId);
        return $consult->getResult();
    }

}
