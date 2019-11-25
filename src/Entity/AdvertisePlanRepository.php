<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use App\Entity\AdvertisePlan;

/**
 * Description of AdvertisePlanRepository
 *
 * @author aealan
 */
class AdvertisePlanRepository extends EntityRepository {

    /**
     * Consulta para conocer la cantidad de pagos con tarjeta en estado
     * -PRE- segun la fecha seleccionada
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param DateTime $dateCheck fecha para consultar los pagos no asentados
     * @param type $dayTime
     * @return type
     */
    public function getActualActiveAdvertPlanForDevice($actualDate) {
        $em = $this->getEntityManager();

        $dql = "SELECT advertPl.id, advertPl.name, advertPl.description, (advertPl.startingDate) AS startingDate, (advertPl.endingDate) AS endingDate "
                . "FROM App:AdvertisePlan advertPl "
                . "JOIN App:AdvertPlanFile advertFl WITH advertFl.advertPlan = advertPl.id "
                . "WHERE advertPl.status = :active AND '$actualDate' > advertPl.startingDate AND '$actualDate' < advertPl.endingDate "
                . "ORDER BY advertPl.startingDate ASC ";
        $consult = $em->createQuery($dql);
        $consult->setMaxResults(1);
        
        $consult->setParameters(['active' => AdvertisePlan::ADVERT_PLAN_STATUS_RUNNING]);

        return $consult->getResult();
    }
    
    /**
     * Consulta para conocer la cantidad de pagos con tarjeta en estado
     * -PRE- segun la fecha seleccionada
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param DateTime $dateCheck fecha para consultar los pagos no asentados
     * @param type $dayTime
     * @return type
     */
    public function getAdvertPlanFilesForDevice($advertPlanId) {
        $em = $this->getEntityManager();

        $dql = "SELECT advertFl "
                . "FROM App:AdvertPlanFile advertFl "
                . "WHERE advertFl.advertPlan = :activePlanId AND advertFl.fileName IS NOT NULL "
                . "ORDER BY advertFl.sorting ASC ";
        $consult = $em->createQuery($dql);
        
        $consult->setParameters(['activePlanId' => $advertPlanId]);

        return $consult->getArrayResult();
    }
    
}
