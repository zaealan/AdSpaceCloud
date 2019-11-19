<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of StateRepository
 * @author zaealan
 */
class StateRepository extends EntityRepository {

    /**
     * Funcion para obtener el nombre de las ciudades segun el pais y el estado
     * , esta funcion es utilizada para el bloque generico de formulario de
     * pais, ciudad, estado, zipcode en licensor
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $idCo id del registro del pais a consultar
     * @param type $idCi id del registro de la ciudad a consultar
     * @return type array con los datos de la ciudad para ser listados por
     * el autocompletar utilizado en el bloque de direcciones
     */
    public function selectStateByCityCountry($idCo, $idCi) {
        $em = $this->getEntityManager();
        $query = "SELECT s.stId AS id, s.stName AS name "
            . "FROM App:City c "
            . "JOIN App:State s WITH c.ciState = s.stId "
            . "JOIN App:Country co WITH co.coId = s.stCountry "
            . "WHERE c.id = :idCi AND co.coId = :idCo";

        $cities = $em->createQuery($query);
        $cities->setParameter('idCi', $idCi);
        $cities->setParameter('idCo', $idCo);
        $cities->setMaxResults(5);
        return $cities->getArrayResult();
    }

    /**
     * Funcion para obtener los estados de segun un pais, esta funcion es
     * utilizada por el bloque generico que se utiliza en la aplicacion para
     * los formuralios de
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $country el pais al cual se le consultaran sus estados
     * @return type arreglo con los estados pertenecientes al pais que se
     * paso por parametro
     */
    public function getStatesByCountry($country) {
        $em = $this->getEntityManager();
        $query = "SELECT s
            FROM App:State s  
            JOIN App:Country co WITH co.coId = s.stCountry
            WHERE co.coVal = :country";

        $cities = $em->createQuery($query);
        $cities->setParameter('country', $country);

        return $cities->getArrayResult();
    }

}
