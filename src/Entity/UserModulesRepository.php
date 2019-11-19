<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Repositorio el cual contiene todas lasconsultas relacionadas a los modulos
 * de usuarios en la aplicacion de licensor
 * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
 */
class UserModulesRepository extends EntityRepository {

    /**
     * Esta consulta permite obtener los modulos de un usuario de una compañia
     * para su posterior edicion, solo lista los modulos los cuales esten
     * activos para la compañia del usuario
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param integer $userId llave primaria del usuario registrado en la
     * base dedatos de licensor para el que se le consultaran los modulos
     * habilitados en el sistema
     * @return array[ModuleUserCompanyAuction] arreglo que contiene todos los
     * modulos habilitados para el usuario especificado en la aplicacion
     */
    public function findUserModulesToEdition($userId, $search = '') {
        $textParameters = '';
        $parameters = [];

        // seccion en donde se filtran los parametros de busqueda para los modulos
        if ($search != '') {
            if (isset($search['only_active']) && $search['only_active'] != '') {
                $textParameters .= " AND modu.umAccess = TRUE";
            }
            if (isset($search['slug']) && $search['slug'] != '') {
                $textParameters .= " AND mod.moSlug = :slug ";
                $parameters['slug'] = $search['slug'];
            }
        }
        $em = $this->getEntityManager();
        $parameters['userId'] = $userId;
        if ($userId) {
            $userCompany = $em->getRepository('App:User')->find($userId);
            $parameters['idCompany'] = $userCompany->getUsCompany()->getId();
        }

        // seccion en donde se arma el DQL de la consulta
        $dql = 'SELECT modu
            FROM App:UserModules modu
            JOIN App:CompanyModule com WITH (com.cmAccess = TRUE AND modu.umCompanyModu = com.cmId)
            JOIN App:Module mod WITH (com.cmModule = mod.id)
            JOIN App:Module modTest WITH (mod.id = modu.umModule)
            WHERE modu.umUser = :userId ' . $textParameters . '
            AND com.cmCompany = :idCompany
            ORDER BY mod.moOrder ASC';
        $consult = $em->createQuery($dql);

        $consult->setParameters($parameters);
        return $consult->getResult();
    }

}
