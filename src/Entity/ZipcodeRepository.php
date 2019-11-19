<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use App\Entity\Zipcode;

/**
 * Description of ZipcodeRepository
 * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
 */
class ZipcodeRepository extends EntityRepository {

    /**
     * Funcion que se encarga de obtener los zipcodes extras de un pais para
     * una licencia creada en licensor, estos zipcodes quedaran almacenados
     * en la base de datos de la licencia y seran posteriormente pasados al
     * servidor android en el login inicial
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param array $country llave primara del pais al cual se le buscaran los
     * zipcodes para la licencia
     */
    public function getExtraZipcodesByContry($country) {

        $em = $this->getEntityManager();

        $dql = "SELECT z "
                . "FROM App:Zipcode z "
                . "JOIN App:City c WITH z.zcCity = c.ciName "
                . "JOIN App:State s WITH c.ciState = s.stId "
                . "JOIN App:Country co WITH s.stCountry = co.coId "
                . "WHERE co.coVal = :country ";

        $query = $em->createQuery($dql);
        if ($country) {
            $query->setParameter("country", $country);
        }

        $theArrayResult = $query->getArrayResult();

        return $theArrayResult;
    }

    /**
     * Funcion que se encarga de obtener los zipcodes extras de un pais para
     * una licencia creada en licensor, estos zipcodes quedaran almacenados
     * en la base de datos de la licencia y seran posteriormente pasados al
     * servidor android en el login inicial
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param array $country llave primara del pais al cual se le buscaran los
     * zipcodes para la licencia
     */
    public function getZipcodesBy100KmsInnerCirclet($country, $maxLat, $minLat, $maxLong, $minLong, $excludeZips = '') {

        $em = $this->getEntityManager();

        $parameters = [];
        $parameters['country'] = $country;
        $parameters['maxLat'] = $maxLat;
        $parameters['minLat'] = $minLat;
        $parameters['maxLong'] = $maxLong;
        $parameters['minLong'] = $minLong;

        $extraSQL = "";
        if ($excludeZips != '') {
            $extraSQL = " AND z.zcName NOT IN $excludeZips";
        }
        
        $comparativeMinLat = '>';
        $comparativeMaxLat = '<';

        $comparativeMinLong = '>';
        $comparativeMaxLong = '<';

        $dql = "SELECT z "
                . "FROM App:Zipcode z "
                . "JOIN App:City c WITH z.zcCity = c.ciName "
                . "JOIN App:State s WITH c.ciState = s.stId "
                . "JOIN App:Country co WITH s.stCountry = co.coId "
                . "WHERE co.coVal = :country AND z.zcLatitude $comparativeMaxLat :maxLat AND z.zcLatitude $comparativeMinLat :minLat "
                . " AND z.zcLongitude $comparativeMaxLong :maxLong AND z.zcLongitude $comparativeMinLong :minLong $extraSQL GROUP BY z.zcId";

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        $theArrayResult = $query->getArrayResult();

        return $theArrayResult;
    }

    /**
     * Funcion encargada de buscar los zipcodes que requieren ser validados por
     * la API de GMG (Geolocalizador de Google) con el fin de tener veracidad
     * en los datos de todos los zipcodes
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @return type
     */
    public function getZipcodesForGMGCheck() {

        $em = $this->getEntityManager();

        $params = ['byUser' => false, 'notChecked' => Zipcode::STATUS_GMG_UNCHECKED];

        $dql = "SELECT z "
                . "FROM App:Zipcode z "
                . "WHERE (z.zcUserCreated = :byUser OR z.zcUserCreated IS NULL) AND z.gmgChecked = :notChecked "
                . "ORDER BY z.zcId ASC ";

        $query = $em->createQuery($dql);
        $query->setParameters($params);
        $query->setMaxResults(50);

        $theArrayResult = $query->getResult();

        return $theArrayResult;
    }

    /**
     * Funcion encargada de obtener un paquet de 50 zipcodes validados por la
     * API de Google para comprarlos con los zipcodes de las licencias con el
     * fin de validar tambien estos ultimos
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param array $zipcodeGroup grupo de zipcodes que seran validados segun
     * los zipcodes ya validados en licensor
     * @return type
     */
    public function getZipcodesForLevelGMGCheck($zipcodeGroup) {
        $em = $this->getEntityManager();

        $params = ['byUser' => false, 'checked1' => Zipcode::STATUS_GMG_CHECKED_MODIFIED, 'checked2' => Zipcode::STATUS_GMG_CHECKED_UNMODIFIED, 'checked3' => Zipcode::STATUS_GMG_CONSISTENT_ADDED_BY_COMMAND];

        $dql = "SELECT z "
                . "FROM App:Zipcode z "
                . "WHERE (z.zcUserCreated = :byUser OR z.zcUserCreated IS NULL) AND (z.gmgChecked = :checked1 OR z.gmgChecked = :checked2 OR z.gmgChecked = :checked3) AND z.zcName IN " . $zipcodeGroup
                . "GROUP BY z.zcName ORDER BY z.zcId ASC ";

        $query = $em->createQuery($dql);
        $query->setParameters($params);
        $query->setMaxResults(50);

        $theArrayResult = $query->getResult();

        return $theArrayResult;
    }

    /**
     * Funcion que se encarga de validar los zipcodes repetidos dentro de la
     * base de datos de licensor, todos los zipcodes repetidos son validaos
     * con la API de Google para posteriormente dejar solo el correcto
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @return type
     */
    public function getZipcodesCheckedAndDuplicated() {
        $em = $this->getEntityManager();

        $params = ['byUser' => false, 'checked' => Zipcode::STATUS_GMG_UNCHECKED, 'duplicatedNumber' => 2];

        $dql = "SELECT z.zcName AS Zipcode, COUNT(z.zcId) AS IsTheSame "
                . "FROM App:Zipcode z "
                . "WHERE (z.zcUserCreated = :byUser OR z.zcUserCreated IS NULL) AND z.gmgChecked <> :checked "
                . "GROUP BY z.zcName HAVING IsTheSame >= :duplicatedNumber ";

        $query = $em->createQuery($dql);
        $query->setParameters($params);
        $query->setMaxResults(500);

        $theArrayResult = $query->getResult();

        return $theArrayResult;
    }

}
