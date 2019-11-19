<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

/**
 * Description 
 * @author frcho
 */
class CustomLog {

    /**
     * @var ContainerInterface
     */
    private $container;
    private $logger;

    /**
     * Constructor de la clase
     * @author LUIS FERNANDO GRANADOS
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->setContainer($container);
    }

    /**
     * Getter of container
     * @return type
     */
    private function getContainer() {
        return $this->container;
    }

    /**
     * Setter of container
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private function setContainer(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Getter of container
     * @return type
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * Getter of container
     * @return type
     */
    public function setLogger(Logger $logger) {
        return $this->logger = $logger;
    }

    public function createChannel($name) {
        $log = new Logger($name);
        $pathLog = $this->getContainer()->get('kernel')->getLogDir() . '/' . $name . '.log';
        $log->pushHandler(new RotatingFileHandler($pathLog, 10, Logger::INFO));
        $this->logger = $log;
        return $log;
    }

    public function addNotice($msg, array $context = array()) {
        // add records to the log
//        $this->getLogger()->addInfo($msg, $context);
        $this->logger->addInfo($msg, $context);
    }

    public function addInfo($logger, $msg, array $context = array()) {
        // add records to the log
        $logger->addInfo($msg, $context);
    }

}
