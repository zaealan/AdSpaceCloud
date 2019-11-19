<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of CityRepository
 * @author zaealan
 */
class CityRepository extends EntityRepository {

    /**
     * Metodo para realizar la consulta del autocompletado de ciudades para
     * el bloque generico que contiene la ciudad, estado y zipcode
     * de la aplicacion
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $term string con el cual se realizara la consulta para el
     * autocompletar
     * @param type $idCountry id del pais al cual pertenece la ciudad
     * @return type array con las entidades (ciudad) que corresponden al
     * termino que conicide en la consulta
     */
    public function autocompleteCitiesByCountry($term, $idCountry) {
        $em = $this->getEntityManager();
        $query = "SELECT c.id as id, CONCAT(CONCAT(CONCAT(c.ciName,' ('),s.stName),')') AS value, CONCAT(CONCAT(CONCAT(c.ciName,' ('),s.stName),')') as label, s.stId AS stateId, s.stName AS stateName "
            . "FROM App:City c "
            . "JOIN App:State s WITH c.ciState = s.stId "
            . "JOIN App:Country co WITH co.coId = s.stCountry "
            . "WHERE c.ciName like :term AND co.coId = :id";

        $cities = $em->createQuery($query);
        $cities->setParameter('term', $term);
        $cities->setParameter('id', $idCountry);
        $cities->setMaxResults(50);
        return $cities->getArrayResult();
    }

}
