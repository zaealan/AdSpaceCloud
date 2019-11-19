<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of LicenseRateRepository
 * @author hector
 */
class LicenseRateRepository extends EntityRepository {

    /**
     * Funcion encargada de retornar un array de los parametros que se solicitan
     * en la base de datos con la informacion de los precions de los servidores
     * y los clientes android segun lo especificado para ese reseller
     * registrado en la base de datos de liecinsor
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $arrFields array con los campos de la entidad LicenseRepository
     * que se desean hallar
     * @param type $arrFindBy array con los campos para hacer el findBy
     * @return type array con la infromacion de los precios tanto de los
     * clientes android como de los servidores los cuales van configurados por
     * reseller (compañia tercera que tambien comercializa con LEVEL)
     */
    public function getDataRateByCompany($arrFields = NULL, $arrFindBy = NULL) {
        $foundFields = "*";
        $findBy = "";
        $parameters = array();

        if ($arrFields !== NULL && count($arrFields) > 0) {
            $comma = "";
            $foundFields = "";
            foreach ($arrFields as $fieldVal) {
                $foundFields .= "$comma $fieldVal";
                $comma = ",";
            }
        }

        if ($arrFindBy !== NULL && count($arrFindBy) > 0) {
            $and = "and";
            foreach ($arrFindBy as $objArray) {
                $and = $objArray[3];
                $findBy .= "$and lr.$objArray[0] = :$objArray[1] ";
                $parameters[$objArray[1]] = $objArray[2];
            }
        }

        $dql = "SELECT $foundFields FROM App:LicenseRate lr "
                . "WHERE 1=1 $findBy";

        $em = $this->getEntityManager();
        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        return $query->getResult();
    }

}
