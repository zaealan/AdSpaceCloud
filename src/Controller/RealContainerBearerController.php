<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

/**
 * Description of RealContainerBearerController
 * @author aealan
 */
class RealContainerBearerController extends AbstractController {
    
    public static function getSubscribedServices() {
        
        return [
            // Default dummy container services
            'router' => '?'.RouterInterface::class,
            'request_stack' => '?'.RequestStack::class,
            'http_kernel' => '?'.HttpKernelInterface::class,
            'serializer' => '?'.SerializerInterface::class,
            'session' => '?'.SessionInterface::class,
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'templating' => '?'.EngineInterface::class,
            'twig' => '?'.Environment::class,
            'doctrine' => '?'.ManagerRegistry::class,
            'form.factory' => '?'.FormFactoryInterface::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
            'message_bus' => '?'.MessageBusInterface::class,
            'messenger.default_bus' => '?'.MessageBusInterface::class,
            
            // Other spicy licensor services
//            'licensor_s3_storage' => '?'. \App\ServicesClasses\AmazonS3Service::class,
            'access_control' => '?'. \App\Util\AccessControl::class,
//            'jwt_authentication_encoder' => '?'. \Lexik\Bundle\JWTAuthenticationBundle\Encoder::class,
//            'rabbitmq_logger' => '?'. \Symfony\Bridge\Monolog\Logger::class,
            'symfony_mailer' => '?'. \Swift_Mailer::class,
//            'simple_paginator' => '?'. \Frcho\SimplePaginatorBundle\Paginator\Paginator::class,
//            'symfony_validator' => '?'. \Symfony\Component\Validator\Validator\TraceableValidator::class,
            'security_password_encoder' => '?'. \Symfony\Component\Security\Core\Encoder\UserPasswordEncoder::class,
//            'symfony_translator' => '?'. \Symfony\Component\Translation\DataCollectorTranslator::class,
//            'database_create' => '?'. \App\Service\ServiceLevel::class,
//            'new_entity_manager_connection' => '?'. \App\Service\ServiceLevel::class,
            'symfony_security_authentication_utils' => '?'. \Symfony\Component\Security\Http\Authentication\AuthenticationUtils::class,
            
            // Producers services
//            'mailer_test_producer' => '?'. \App\Producer\MailerTestProducer::class,
//            'license_database_creation_producer' => '?'. \App\Producer\LicenseDatabaseCreationProducer::class,
//            'omt_restaurant_sync_producer' => '?'. \App\Producer\OmtRestaurantSyncProducer::class,
//            'incoming_android_sync_producer' => '?'. \App\Producer\DownUpIncomingAndroidSyncProducer::class,
//            'incoming_web_android_sync_response_producer' => '?'. \App\Producer\UpDownAndroidResponseSyncProducer::class,
//            'cleanse_for_fatty_android_database_producer' => '?'. \App\Producer\CleanseForFattyAndroidDatabaseProducer::class,
            
            // Behold the real kernel as a service
            'real_kernel' => '?'. \App\Kernel::class
        ];
    }

}
