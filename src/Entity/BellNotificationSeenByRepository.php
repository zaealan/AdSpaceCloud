<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

use App\Entity\BellNotificationSeenBy;

/**
 * Description of BellNotificationSeenByRepository
 * @author aealan
 */
class BellNotificationSeenByRepository extends EntityRepository {

    /**
     * @param type $notificationToBell
     * @return type
     */
    public function searchBellNotiesToRequeueInLicensorBell($notificationToBell) {
        $textParameters = 'bns.id <> 0 ';
        $parameters = [];
        
        if ($notificationToBell) {
            $parameters['notifiedStatus'] = BellNotificationSeenBy::STATUS_NOTIFIED;
            $parameters['seedToCheck'] = $notificationToBell;
            $textParameters = "bns.notificationStatus = :notifiedStatus AND bns.disabledByNonApplication IS NULL AND nlb.id = :seedToCheck ";
        }

        $em = $this->getEntityManager();
        $dql = "SELECT bns "
                . "FROM App:BellNotificationSeenBy bns "
                . "JOIN App:NotificationInLicensorBell nlb WITH nlb.id = bns.seenNotification "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getResult();
    }
    
    /**
     * @param type $notificationToBell
     * @return type
     */
    public function searchBellNotiesToCloseInLicensorBell($notificationToBell) {
        $textParameters = 'bns.id = 0 ';
        $parameters = [];
        if ($notificationToBell) {
            $parameters['seedToCheck'] = $notificationToBell->getId();
            $parameters['disabledNoti'] = true;
            $textParameters = "bns.notificationStatus = :notifiedStatus AND bns.disabledByNonApplication <> :disabledNoti AND nlb.id = :seedToCheck ";
        }

        $em = $this->getEntityManager();
        $dql = "SELECT bns "
                . "FROM App:BellNotificationSeenBy bns "
                . "JOIN App:NotificationInLicensorBell nlb WITH nlb.id = bns.seenNotification "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getResult();
    }

    /**
     * @param type $userRole
     * @return type
     */
    public function serachUnotifiedNewUserByRole($userRole, $notyId) {
        $parameters = [];
        
        $subSelect = "SELECT IDENTITY(bns.userWhoSaw) "
                . "FROM App:BellNotificationSeenBy bns "
                . "JOIN App:NotificationInLicensorBell nlb WITH (bns.seenNotification = nlb.id AND nlb.typeRoleToNotify = :roleTypeToNoty AND nlb.id = :notyId)";
        
        $parameters['notyId'] = $notyId;
        $parameters['notifyRole'] = $userRole;
        $parameters['roleTypeToNoty'] = $userRole;
        
        $textParameters = "(usi.usType <= :notifyRole AND usi.id NOT IN ($subSelect))";
        
        $em = $this->getEntityManager();
        $dql = "SELECT usi "
                . "FROM App:User usi "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getResult();
    }

    /**
     * @param type $user
     * @param type $actualYesterday
     * @return type
     */
    public function searchAndGetAllNotificationsToBell($user, $actualYesterday) {
        $parameters = [];
        
        $dql = "SELECT bns "
                . "FROM App:BellNotificationSeenBy bns "
                . "WHERE bns.userWhoSaw = :loguedUser AND bns.notificationStatus <= :notifiedStatus AND bns.dateCreated >= :actualYesterday "
                . "ORDER BY bns.dateCreated DESC";
        
        $parameters['notifiedStatus'] = BellNotificationSeenBy::STATUS_NOTIFIED;
        $parameters['actualYesterday'] = $actualYesterday->format('Y-m-d H:i:s');
        $parameters['loguedUser'] = $user->getId();
        
        $em = $this->getEntityManager();
       
        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getResult();
    }
    
    public function searchAndCountAllNewNotificationsToBell($user, $actualYesterday) {
        $parameters = [];
        
        $dql = "SELECT COUNT(bns.id) "
                . "FROM App:BellNotificationSeenBy bns "
                . "WHERE bns.userWhoSaw = :loguedUser AND bns.notificationStatus = :notifiedStatus AND bns.dateCreated >= :actualYesterday "
                . "ORDER BY bns.dateCreated DESC";
        
        $parameters['notifiedStatus'] = BellNotificationSeenBy::STATUS_UNNOTIFIED;
        $parameters['actualYesterday'] = $actualYesterday->format('Y-m-d H:i:s');
        $parameters['loguedUser'] = $user->getId();
        
        $em = $this->getEntityManager();
       
        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getSingleScalarResult();
    }
}
