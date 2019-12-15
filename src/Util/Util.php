<?php

namespace App\Util;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use App\Entity\City;
use App\Entity\State;
use App\Entity\Country;
use App\Entity\Zipcode;
use App\Entity\PushSent;
use App\Entity\CardAsClient;
use App\Entity\ReadFilesData;
use App\Entity\AccountLicense;
use App\Entity\LicenseDataBase;
use App\Entity\ReadFilesDataWeb;
use App\Util\WS\Util as Utilx;
use App\Util\WS\ForcedAsynchronousCommandsUtil;
use App\ServicesClasses\CustomLog;
use App\Util\UtilGMG\Geocode;
use App\Structure\Categoria;
use App\Structure\Producto;
use App\Structure\CategoriaTalla;
use App\Structure\ProductoTalla;
use App\Structure\BrandMenu;
use App\Structure\CategoryBrandService;
use Datetime;

//use App\Structure\OrderServiceTypes;
//use App\Structure\BrandTypeMenu;

/**
 * Utiliy class
 */
class Util {

    /**
     * Funcion para crear y verificar permisos del directorio
     * de una licencia en la base de datos
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $container Contenedor de la aplicacion
     * @param type $accountLicenseData Datos de la coneccion con la base de datos lvl web
     * @param type $nickname Nickname de la licencia del usuario
     */
    public static function beautifyActiveAdvertFullArray($container, $licenseToSync, $theActivePlanArray, $activePlanFilesArray) {
        
        foreach($activePlanFilesArray as $key => $activePlanSingleFile) {
            $imagePathAux1 = $container->getParameter('base_adspacecloud_host') . '/uploads/advertPlans/' . $licenseToSync->getAlAccountLicense()->getAcName();
            $imagePathAux2 = str_replace(' ', '_', $imagePathAux1);
            
            $imagePath = $imagePathAux2 . '/' . $licenseToSync->getAlLicenseUsername();
            $imagePathFinal = str_replace(' ', '_', $imagePath) . '/' . $activePlanSingleFile['fileName'];
            
            $activePlanFilesArray[$key]['filePath'] = $imagePathFinal;
        }
        
        $theActivePlanArray['files'] = $activePlanFilesArray;
        
        return $theActivePlanArray;
    }
    
    /**
     * Funcion para crear y verificar permisos del directorio
     * de una licencia en la base de datos
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $container Contenedor de la aplicacion
     * @param type $accountLicenseData Datos de la coneccion con la base de datos lvl web
     * @param type $nickname Nickname de la licencia del usuario
     */
    public static function createLicenseDirectory($container, $accountLicenseData, $nickname) {
        
        $theAccountDirector = $container->getParameter('advert_plan_files_directory') . '/' . $accountLicenseData->getAlAccountLicense()->getAcName();
        $theAccountDirector = str_replace(' ', '_', $theAccountDirector);

        $respArray = static::validateDirWritableReadable($theAccountDirector);

        $theAccountLicenseDirector = $theAccountDirector . '/' . $nickname;
        $theAccountLicenseDirector = str_replace(' ', '_', $theAccountLicenseDirector);

        $respArray = static::validateDirWritableReadable($theAccountLicenseDirector);

        return $respArray;
    }

    /**
     * Metodo para validar si un directoryo existe y si tiene los permisos
     * correctos para ser utilizados por licensor
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param string $theAccountDirector ruta del directorio a validar
     * @return array arreglo con indices indicando si fue exitosa o no la
     * validacion del directorio
     */
    public static function validateDirWritableReadable($theAccountDirector) {
        $fs = new Filesystem();
        $respArray = [];

        $respArray['result'] = '__OK__';
        $respArray['msg'] = '';
        $respArray['directory'] = $theAccountDirector;

        try {
            if (!is_dir($theAccountDirector)) {
                $fs->mkdir($theAccountDirector, 0774);
            }

            if (!is_writable($theAccountDirector)) {
                $respArray['result'] = '__KO__';
                $respArray['msg'] = 'Not allowed to write in directory';
            }

            if (!is_readable($theAccountDirector)) {
                $respArray['result'] = '__KO__';
                $respArray['msg'] = 'Not allowed to read directory ';
            }
        } catch (IOExceptionInterface $e) {
            $respArray['result'] = '__KO__';
            $respArray['msg'] = 'Not allowed to create or modify directory ' . $e->getPath();
        }

        return $respArray;
    }
    
    /**
     * Funcion que permite crear usuario en motor de base de datos mysql con
     * permisos especificos el cual es utilizado para la base de datos de cada
     * licencia creada por licensor
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $databaseUserRootBd Usuario con permisos administrativos
     * bases de datos
     * @param type $passwordUserRootBd Clave usuario
     * @param type $dataOptions Array con los datos para crear un usuario
     * relacionado a una base de datos
     * @param type $privilegesUserMysql Array de permisos para el usuario
     * a crear Ej: formato array('select','insert','update','delete')
     */
    public static function privilegesUserMysql($databaseUserRootBd, $passwordUserRootBd, $dataOptions, $privilegesUserMysql = null, $path = '', $customLog = '') {

        $channel = $customLog->createChannel('ResponseFromOMTS0');
        $customLog->setLogger($channel);

        $database = $dataOptions['database'];
        if ($databaseUserRootBd == $dataOptions['user']) {
            $user = 'testing';
        } else {
            $user = $dataOptions['user'];
        }
        $password = $dataOptions['pass'];
        $host = $dataOptions['host'];

        if ($privilegesUserMysql == null) {
            $privilegesUserMysql = [
                'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'ALTER', 'INDEX'
                , 'CREATE TEMPORARY TABLES', 'SHOW VIEW', 'CREATE ROUTINE',
                'ALTER ROUTINE', 'EXECUTE', 'CREATE VIEW', 'EVENT', 'TRIGGER'
            ];
        }

        $privileges = implode(",", $privilegesUserMysql);
//        $query = "mysql -h" . $host . " -u" . $databaseUserRootBd . " -p" . $passwordUserRootBd . " -e \"GRANT " . $privileges . " ON " . $database . ".* TO " . $user . "@" . $host . " IDENTIFIED BY " . "'$password'" . "\";";
        $query = "mysql -h" . $host . " -u" . $databaseUserRootBd . " -p" . $passwordUserRootBd . " -e \"GRANT " . $privileges . " ON " . $database . ".* TO " . $user . "@'%' IDENTIFIED BY " . "'$password'" . "\";";
//        $query =  "mysql -u".$databaseUserRootBd." -p".$passwordUserRootBd." -e \"GRANT ".$privileges." ON ".$database.".* TO ".$user."@".$host." IDENTIFIED BY "."'$password'"."\";";


        if ($path != '') {
            static::createFileJson($path, 'Con un changuito -> ' . $query . "\r");
        }

        try {
            shell_exec($query);
            $msg = "Created user account by database that create " . $database;
            $customLog->addInfo(
                    'Util:privilegesUserMysql', [
                'msg' => $msg,
            ]);
        } catch (\Exception $ex) {
            static::createFileJson($path, 'Pero que chingaos -> ' . $ex->getMessage() . "\r");
            $msg = "Cannot create user account by database " . $database;
            $customLog->addInfo(
                    'Util:privilegesUserMysql', [
                'msg' => $msg,
            ]);
        }

        return $msg;
    }

    /**
     * Retorna la fecha/hora actual seteada con el time zone de America/Chicago
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @return \DateTime
     */
    public static function getCurrentDate() {
        $timezone = new \DateTimeZone('America/Chicago');
        $dateTime = new \DateTime('now');
        $dateTime->setTimezone($timezone);
        return $dateTime;
    }

    /**
     * Retorna la fecha/hora actual seteada con el time zone de America/Chicago
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @return \DateTime
     */
    public static function getCurrentFirstHourDate() {
        $timezone = new \DateTimeZone('America/Chicago');
        $dateTime = new \DateTime('05:00:00');
        $dateTime->setTimezone($timezone);
        return $dateTime;
    }

    /**
     * Retorna la fecha/hora actual seteada para la zona horaria GTM-1 con el
     * fin de determinar el tiempop de expiracion para un push enviado al parse
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @return \DateTime
     */
    public static function getCurrentForPushExpirationDate() {
        $timezone = new \DateTimeZone('Etc/GMT-1');
        $dateTime = new \DateTime('now');
        $dateTime->setTimezone($timezone);
        $dateTime->modify('-1 hour');
        $dateTime->modify('+20 minutes');
        return $dateTime;
    }

    /**
     * Metodo utilizado para obtener un ramdom password que por defecto es de
     * 16 caracteres, es un password alfanumerico comunmente utilizado para los
     * codigos de verificacion de los push
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $number
     * @return type
     */
    public static function randomPassword($number = 16) {
        $alphabet = "abcdefghijkmnpqrstuwxyzABCDEFGHIJKLMNPQRSTUWXYZ23456789";

        /**
         * remember to declare $pass as an array
         */
        $pass = [];
        /**
         * put the length -1 in cache
         */
        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < $number; $i++) {
            $n = mt_rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        /**
         * turn the array into a string
         */
        return implode($pass);
    }

    /**
     * Metodo encargado de setear y gualrdar en la base de datos de licensor
     * toda la infromacion relacionada al envio de un push
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $em EntityManager con los parametros de conexion de licensor
     * @param type $pushEntity PushSend entidad que contiene toda la infromacion
     * relacionada a un push enviado por licensor
     * @param type $pushType integer que representa el tipo de push enviado a
     * registrar por licensor
     * @param type $pushKey string con el codigo o password de verificacion
     * que utilizara el servidor android para confirmar le llegada del push
     * @param type $license AccountLicense entidad que contiene toda la
     * informacion de la licencia registrada en licensor
     * @param type $boolAppMode boolean que indica el modo en el que el push se
     * registrara (modo seguro o no)
     * @param type $haveSublicense SubLicense entidad que contiene toda la
     * informacion de una sublicencia, esto se da en el caso de que el push se
     * fuese a neviar directamente a una sublicencia
     * @return string que contiene el codigo de verificacion del push si este
     * se registro con exito o un estring vacio de lo contrario
     */
    public static function createRegistPushSend($em, $pushEntity, $pushType, $pushKey, $license, $boolAppMode = null, $haveSublicense = false) {

        $pushEntity->setPushType($pushType);
        $pushEntity->setPushStatus(PushSent::STATUS_PUSH_PENDING);
        $actualDate = static::getCurrentDate();
        $pushEntity->setSentDate($actualDate);
        $pushEntity->setVerificationCode($pushKey);

        if (!$haveSublicense) {
            $pushEntity->setPsLicense($license);
        } else {
            $pushEntity->setPsSubLicense($license);
        }

        if ($boolAppMode) {
            $pushEntity->setApplicationMode(true);
        } else {
            $pushEntity->setApplicationMode(false);
        }

        $em->persist($pushEntity);
        try {
            $em->flush();
            return $pushEntity->getVerificationCode();
        } catch (\Exception $ex) {
            return '';
        }
    }

    /**
     * Metodo para registrar la informacion que se envio en el push mandado por
     * licensor, esta informacion puede ir encripdata o no segun si licensor
     * estaba trabajando en modo seguro o no
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $em EntityManager con los parametros de conexion de licensor
     * @param type $pushData string con los datos enviados en el push de tipo
     * json, el contenido de este json puede estar encriptado o no
     * @param type $pushVerificationCode string con el codigo de verificacion
     * del push enviado, con este codigo es con el que el servidor android
     * confirma la llegada de un push a licensor
     * @param type $isResendBool boolean que indica si este es un push de que
     * se esta reenviando o no
     * @return boolean boleano que indica si la actualizacion del registro del
     * push se realizo con exito o no
     */
    public static function setDataInPushRegist($em, $pushData, $pushVerificationCode, $isResendBool = null) {

        $search = ['verificationCode' => $pushVerificationCode];
        $pushSentWithRecord = $em->getRepository('App:PushSent')->findBy($search);

        if (!empty($pushSentWithRecord)) {
            $pushSentWithRecord[0]->setDataInPush($pushData);

            if ($isResendBool) {
                $pushSentWithRecord[0]->setPushToResend(((int) $pushSentWithRecord[0]->getPushToResend()) + 1);
            }

            $em->persist($pushSentWithRecord[0]);

            try {
                $em->flush();
                return true;
            } catch (\Exception $ex) {
                return $ex->getMessage();
            }
        }

        return false;
    }

    /**
     * Metodo para filtrar un arreglo de string segun un string como criterio
     * pasado como parametro al metodo ademas del arreglo a filtrar, este
     * metodo retorna un arreglo de string los cuales cumplieron el criterio
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $arr array en el cual se buscara un string determinado
     * @param type $strValue string que sera buscado en el array dado por
     * parametro a esta funcion
     * @return type array con los strings filtrados del array dado
     * como parametro segun el criterio de busqueda
     */
    public static function searchStringInArray($arr, $strValue = '') {
        $newArr = [];
        $i = 0;
        foreach ($arr as $data) {
            if (strpos($data, $strValue) === 0) {
                $newArr[$i] = $data;
                ++$i;
            }
        }
        return $newArr;
    }

    /**
     * Metodo para filtrar un arreglo de string segun un string como criterio
     * pasado como parametro al metodo ademas del arreglo a filtrar, este
     * metodo retorna un arreglo de string los cuales cumplieron el criterio
     * teniendo como condicion que la posicion 0 no sea valida para el filtrado
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $arr array en el cual se buscara un string determinado
     * @param type $strValue string que sera buscado en el array dado por
     * parametro a esta funcion
     * @return type array con los strings filtrados del array dado
     * como parametro segun el criterio de busqueda
     */
    public static function searchStringInArrayNotZeroPos($arr, $strValue = '') {
        $newArr = [];
        $i = 0;
        foreach ($arr as $data) {
            if (strpos($data, $strValue)) {
                $newArr[$i] = $data;
                ++$i;
            }
        }
        return $newArr;
    }

    /**
     * Metodo que permite obtener el idioma de un pais segun su el codigo de
     * dos letras de este, el idioma es repesentado por un entero mayor o igual
     * a cero (0 => ingles, 1 => español) dentro de los idiomas soportados
     * por licensor
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $country string con el codigo de dos letras del pais al cual
     * se le buscara el integer del idioma en el sistema
     * @return type integer que representa el idioma soportado por licensor
     */
    public static function getLanguageForLicense($country) {
        $conuntryLanguageArray = ['US', 'CO'];

        return array_search($country, $conuntryLanguageArray);
    }

    /**
     * Metodo para obtener el nombre de un estado o departamento segun su
     * codigo de dos letras, por ahora contiene solo la correspondencia para
     * departamentos de colombia
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $shortState string con el codigo de dos letras de un estado
     * o departamento segun el pais en donde este operando level
     * @return string que contiene el nombre del estado o departamento que
     * corresponde al codigo de dos letras dado al metodo
     */
    public static function getStateNameForNewLicense($shortState) {
        $conuntryStateName = [];

        $conuntryStateName['AQ'] = 'Antioquia';
        $conuntryStateName['RS'] = 'Risaralda';
        $conuntryStateName['QU'] = 'Quindio';

        if (isset($conuntryStateName[$shortState])) {
            return $conuntryStateName[$shortState];
        } else {
            return null;
        }
    }

    /**
     * Metodo para obtener un EntityManager con los parametros de conexion de
     * un licencia registrada en la aplicacion
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $container contenedor de la aplicacion symfony con los
     * parametros del contexto de ejecucion de licensor
     * @param type $dataOptionsRoot array con los datos de conexion para
     * determinada base de datos para la que se requiere un EntityManager
     * @return type EntityManager con la configuracion de los parametros de
     * conexion de la base de datos de una licencia
     */
    public static function emCreateConfiguration($container, $dataOptionsRoot) {

        /**
         * reconfigurar el entity manager para que use otra connection y
         * apunte a otro directorio de entidades, no el default, en este caso
         * la estructura de la aplicacion level
         */
        $pathStructure = $container->getParameter('level_directory_structure_dir');
        $proxies = $container->getParameter('level_directory_proxies');

        $applicationMode = $container->getParameter('kernel.environment');

        if ($applicationMode == "dev") {
            $cache = new \Doctrine\Common\Cache\ArrayCache;
        } else {
            $cache = new \Doctrine\Common\Cache\ApcCache;
        }

        /**
         * Esta funcionalidad la sacamos de la
         * url = http://doctrine-orm.readthedocs.org/en/latest/reference/advanced-configuration.html#proxy-objects
         *
         * la cual tiene un problema a la hora de registrar el driver
         * que registra los metadatos, pues utiliza el metodo newDefaultAnnotationDriver
         * el cual no reconoce de manera adecuada las annotaciones
         *
         * Se encuentra como arreglarlo en stackoverflow
         * url = http://stackoverflow.com/questions/9755518/doctrine-2-no-metadata-classes-to-process-by-ormgenerate-repositories
         *
         * en el que la solucion que se aporta es reemplazar el metodo
         * "newDefaultAnnotationDriver" por una nueva instancia de lector de annotations
         *
         *
         *   $reader = new AnnotationReader();
         *   $driverImpl = new AnnotationDriver($reader, array(new mapped entities path));
         *
         * para crear una implementacion de un driver el cualL se le pasa a la
         * instancia de configurador al metodo setMetadataDriverImpl
         */
        $config = new Configuration;
        $config->setMetadataCacheImpl($cache);

        $reader = new AnnotationReader();
        $driverImpl = new AnnotationDriver($reader, [$pathStructure]);
        $config->setMetadataDriverImpl($driverImpl);

        $config->setQueryCacheImpl($cache);
        $config->setProxyDir($proxies);
        $config->setProxyNamespace('Proxies');

        if ($applicationMode == "dev") {
            $config->setAutoGenerateProxyClasses(true);
        } else {
            $config->setAutoGenerateProxyClasses(false);
        }

        $em = EntityManager::create($dataOptionsRoot, $config);

        return $em;
    }

    /**
     * Funcion que permite crear base de datos y un usuario relacionado con ella
     * con permisos especificos dados
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $conteiner contenedor con el cual se puede acceder al metodo
     * get para llamar al servicio
     * @param type $dataOptions arreglo con los datos de conexion
     * para el usuario y el nombre de la base de datos a crear
     * @return string
     */
    public static function createDatabaseAndAccess($conteiner, $dataOptions) {
        /**
         * Servicio que llama al comando para crear base de datos
         * el cual espera como parametro
         * El nombre de la base de datos a crear
         */
        $createDatabase = $conteiner->get('database_create');
        $createDatabase->setContainer($conteiner);
        $result = $createDatabase->createDatabase($dataOptions);

        return $result;
    }

    /**
     * Funcion para crear schema con fixtures para la base de datos enviada
     * en array de parameters
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $conteiner contenedor con el cual se puede acceder al metodo
     * get para llamar al servicio
     * @param type $dataOptionsRoot array con datos de conexion root y nombre
     * de base de datos a crear schema y fixtures
     * @return string
     */
    public static function createSchemaAndFixtures($conteiner, $dataOptionsRoot) {
        /**
         * Servicio que llama funcion para crear scheme de database segun
         * nombre de base de datos dado.
         */
        $logger = $conteiner->get('rabbitmq_logger');
        $logger->info('Ready to create fixtures!');

        $path = Utilx::getValidActiveLogByBaseName($conteiner, 'superXXXDatabaseCreationConsumer', $conteiner->getParameter('level_directory_data_android') . 'superXXXDatabaseCreationConsumer0.txt');
        static::createFileJson($path, 'DatabaseLicenseCreationConsumer: createSchemaAndFixtures home ready!' . "\r");

        $customEntityManager = $conteiner->get('new_entity_manager_connection');
        $resultCustomEntityManager = $customEntityManager->newEntityManagerConnection($dataOptionsRoot);

        /**
         * Variable $msg a utilizar en el twig
         */
        return $resultCustomEntityManager;
    }

    /**
     * Funcion para crear schema con fixtures para la base de datos enviada
     * en array de parameters
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $conteiner contenedor con el cual se puede acceder al metodo
     * get para llamar al servicio
     * @param type $dataOptionsRoot array con datos de conexion root y nombre
     * de base de datos a crear schema y fixtures
     * @return string
     */
    public static function configureInitialDataAndProcedures($conteiner, $licenseId, $info = null) {
        /**
         * Servicio que llama funcion para crear scheme de database segun
         * nombre de base de datos dado.
         */
        $logger = $conteiner->get('rabbitmq_logger');
        $logger->info('Ready to create fixtures!');

        $path = Utilx::getValidActiveLogByBaseName($conteiner, 'superXXXDatabaseCreationConsumer', $conteiner->getParameter('level_directory_data_android') . 'superXXXDatabaseCreationConsumer0.txt');
        static::createFileJson($path, 'DatabaseLicenseCreationConsumer: configureInitialDataAndProcedures home ready!' . "\r");

        $customEntityManager = $conteiner->get('new_entity_manager_connection');
        $resultCustomEntityManager = $customEntityManager->initialInsertConfigurationForLicenseService($licenseId, $info);

        /**
         * Variable $msg a utilizar en el twig
         */
        return $resultCustomEntityManager;
    }

    /**
     * Metodo generico para escribir un texto en la parte
     * final de un documento (log)
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $path ruta completa al documento en el que se escribira
     * @param type $text texto que se escribira en el final del documento
     */
    static public function createFileJson($path, $text) {
        $file = fopen($path, "a+");
        fwrite($file, $text);
        fclose($file);
    }

    /**
     * Util::replaceCharactersEspecials([Sting])
     * Esta funcion se encarga de reeplazar los caracteres especiales por
     * otros mas alfanumericos y los caracteres que son tipo Symbol
     * los retira de la cadena de texto
     * @author KJ-Hector Hdz <hhernandez@kijho.com> 19/07/2016
     * @param String $chain variable que contiene el texto a formatear
     * @return String
     */
    static public function replaceCharactersEspecials($chain, $strict = true) {
        $pattern = ["'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'",
            "'Ê'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'",
            "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'",
            "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'",
            "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'",
            "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'",
            "'ñ'", "'Ñ'"];

        $replace = ['e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a',
            'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o',
            'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I',
            'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y',
            'o', 'O', 'a', 'A', 'A', 'c', 'C', 'n', 'N'];

        $chain = preg_replace($pattern, $replace, $chain);

        if ($strict) {
            $chain = preg_replace("/[^A-Za-z0-9]/", "", $chain);
        }

        return $chain;
    }

    /**
     * Util::replaceCharactersEspecials([Sting])
     * Esta funcion se encarga de reeplazar los caracteres especiales por
     * otros mas alfanumericos y los caracteres que son tipo Symbol
     * los retira de la cadena de texto
     * @author KJ-Hector Hdz <hhernandez@kijho.com> 19/07/2016
     * @param String $chain variable que contiene el texto a formatear
     * @return String
     */
    static public function replacemtOfEspecialsCharactersPreJsonDecode($chain) {
        $pattern = ["\u00e2\u0080\u0099"];

        $replace = ['’'];

        $chain = str_replace($pattern, $replace, $chain);

        return $chain;
    }

    /**
     * Funcion que permite validar y setear los valores a la entidad
     * a la cual se le registraran la ciudad, el estado, zipcode del bloque
     * para formularios
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $userSession usuario en secion
     * @param type $em manager del orm
     * @param type $params parametros en donde se encuentran los valores
     * @param type $entity entidad a la cual se el seteara la informacion
     * correspondiente al bloque de ciudad, estado, zipcode
     * @param type $attrToSet array opcional que contiene de forma literal
     * los atributos a guardar (city, zipCode, country)
     * @return array con la entidad, status si fue erronea o no la insercion
     * y un mensaje en el caso de que no lo sea
     */
    static public function validateAndSaveCityZipcodeBlock($container, $em, $params, $entity, $attrToSet = "") {

        $isValidCityBlock = true;
        $isValidStateBlock = true;
        $isValidCountryBlock = true;
        $isValidZipcodeBlock = true;

        $statusMessage = '';

        $allBlockOk = true;
        $searchedByAddressAutocomp = false;
        $zipcode = [];

        $responseArray = [];

        if (isset($params['cityx']) && $params['cityx'] != '') {
            if (is_int($params['cityx'])) {
                $city = $em->getRepository('App:City')->find($params['cityx']);
            } else {
                $city = $em->getRepository('App:City')->findBy(['ciName' => $params['cityx']]);
                if (isset($city[0])) {
                    $city = $city[0];
                    $searchedByAddressAutocomp = true;
                    $state = $city->getCiState();
                    $country = $state->getStCountry();
                    $countryName = $country->getCoName();
                    if ($params['country'] != $countryName) {
                        $isValidCityBlock = false;
                    }
                }
            }

            if (!($city instanceof City)) {
                $isValidCityBlock = false;
                $statusMessage = 'Invalid parameter for city!';
            }
        } else {
            $isValidCityBlock = false;
            $statusMessage = 'Invalid city field!';
        }

        if (isset($params['state']) && $params['state'] != '' && !$searchedByAddressAutocomp) {
            $state = $em->getRepository('App:State')->find($params['state']);
            if (!($state instanceof State)) {
                $isValidStateBlock = false;
                $statusMessage = 'Invalid parameter for state!';
            }
        } elseif (!$searchedByAddressAutocomp) {
            $isValidStateBlock = false;
            $statusMessage = 'Empty state field!';
        }

        if (isset($params['country']) && $params['country'] != '') {
            if (!$searchedByAddressAutocomp && !is_int($params['country'])) {
                $country = $em->getRepository('App:Country')->find($params['country']);
            } else {
                $countryZ = $em->getRepository('App:Country')->findBy(['coName' => $params['country']]);
                if (isset($countryZ[0]) && $country->getCoId() == $countryZ[0]->getCoId()) {
                    $country = $countryZ[0];
                } else {
                    $country = null;
                }
            }

            if (!($country instanceof Country)) {
                $isValidCountryBlock = false;
                $statusMessage = 'Invalid parameter for country!';
            }
        } else {
            $isValidCountryBlock = false;
            $statusMessage = 'Empty country field!';
        }

        if (isset($params['zipcode']) && $params['zipcode'] != '') {
            $zipcode = $em->getRepository('App:Zipcode')->findBy(['zcName' => $params['zipcode']]);
            if (!empty($zipcode)) {
                if (!($zipcode[0] instanceof Zipcode)) {
                    $isValidZipcodeBlock = false;
                    $statusMessage = 'Invalid parameter for zipcode!';
                }
            } elseif (isset($city)) {
                $newZipcode = new Zipcode();
                $newZipcode->setZcName($params['zipcode']);
                $newZipcode->setZcLongitude(0);
                $newZipcode->setZcLatitude(0);
                $newZipcode->setZcCity($city);
                $newZipcode->setState($state->getStName());
                $newZipcode->setZcUserCreated(true);

                $em->persist($newZipcode);
                $em->flush();

                $zipcode[0] = $newZipcode;
            }
        } else {
            $isValidZipcodeBlock = false;
            $statusMessage = 'Empty zipcode field!';
        }

        if ($isValidCityBlock && $isValidStateBlock && $isValidCountryBlock && $isValidZipcodeBlock) {
            if ($city->getCiState()->getStId() != $state->getStId() || $state->getStCountry()->getCoId() != $country->getCoId()) {
                $allBlockOk = false;
                $statusMessage = 'Invalid parameters for city, state, country, zipcode!';
            }
        } else {
            $allBlockOk = false;
        }

        if ($allBlockOk) {
            if (!empty($attrToSet) && is_array($attrToSet)) {
                foreach ($attrToSet as $attribute) {
                    if ($attribute == 'city') {
                        $entity->setCity($city);
                    } elseif ($attribute == 'zipCode') {
                        $entity->setZipcode($zipcode[0]);
                    }
                }
            } else {
                try {
                    $entity->setCity($city);
                } catch (\Exception $e) {
                    $statusMessage = $e->getMessage();
                }

                try {
                    $entity->setZipcode($zipcode[0]);
                } catch (\Exception $e) {
                    $statusMessage = $e->getMessage();
                }
            }
        }

        $geocode = new Geocode($container->getParameter('gmg_api_key'), $container->getParameter('gmg_timezone_api_key'));

        $canAskToGeocode = true;

        if ($entity instanceof AccountLicense) {
            $addresInfo['street'] = $entity->getAlAddres();
            $statusMessage = "Invalid restaurant addres, this addres can't be located by Google Maps";
        } elseif ($entity instanceof Account) {
            $addresInfo['street'] = $entity->getAcAddress();
            $statusMessage = "Invalid account addres, this addres can't be located by Google Maps";
        } elseif ($entity instanceof Company) {
            $addresInfo['street'] = $entity->getCoAddress();
            $statusMessage = "Invalid company addres, this addres can't be located by Google Maps";
        } else {
            $canAskToGeocode = false;
        }

        $addresInfo['zip_code'] = $zipcode[0];

        if ($entity->getCity()) {
            $entityCity = $city;
            $entityState = $entityCity->getCiState();
            $addresInfo['city_name'] = $entityCity->getCiName();
            $addresInfo['state_name'] = $entityState->getStName();

            if ($entityState->getStCountry() && $canAskToGeocode) {
                $addresInfo['country_name'] = $entityState->getStCountry()->getCoName();
            } else {
                $canAskToGeocode = false;
            }
        } else {
            $canAskToGeocode = false;
        }

        if ($canAskToGeocode) {
            if (!isset($params['address'])) {
                $naturalAddress = static::getNaturalAddressFromRequest($addresInfo);
                $gmgLocateAddressResutl = $geocode->getBy(['address' => $naturalAddress]);
            } else {
                $entity->setAlAddres($params['address']);
                $gmgLocateAddressResutl = $geocode->getBy(['address' => $params['address']]);
            }

            if (!isset($gmgLocateAddressResutl->latitude) || !isset($gmgLocateAddressResutl->longitude)) {
                $allBlockOk = false;
            } else {
                $entity->setAlLongitude($gmgLocateAddressResutl->longitude);
                $entity->setAlLatitude($gmgLocateAddressResutl->latitude);

                $em->persist($entity);
                $em->flush();
            }
        }

        $responseArray['status'] = $allBlockOk;
        $responseArray['entity'] = $entity;
        $responseArray['message'] = $statusMessage;

        return $responseArray;
    }

    /**
     * Funcion que permite validar y setear los valores a la entidad
     * a la cual se le registraran la ciudad, el estado, zipcode del bloque
     * para formularios
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $userSession usuario en secion
     * @param type $em manager del orm
     * @param type $params parametros en donde se encuentran los valores
     * @param type $entity entidad a la cual se el seteara la informacion
     * correspondiente al bloque de ciudad, estado, zipcode
     * @param type $attrToSet array opcional que contiene de forma literal
     * los atributos a guardar (city, zipCode, country)
     * @return array con la entidad, status si fue erronea o no la insercion
     * y un mensaje en el caso de que no lo sea
     */
    static public function validateAndSaveAddressAutoComplete($container, $em, $params, $entity) {

        $isValidZipcodeBlock = true;

        $statusMessage = 'Valid address!';

        $allBlockOk = true;
        $searchedByAddressAutocomp = false;
        $zipcode = [];

        $responseArray = [];

        if (isset($params['address']) && $params['address'] !== "") {
            $geocode = new Geocode($container->getParameter('gmg_api_key'), $container->getParameter('gmg_timezone_api_key'));

            $gmgLocateAddressResutl = $geocode->getBy(['address' => $params['address']]);
            
            if (!isset($gmgLocateAddressResutl->latitude) || !isset($gmgLocateAddressResutl->longitude)) {
                $isValidZipcodeBlock = false;
                $allBlockOk = false;
            } elseif ($entity instanceof AccountLicense) {
                $entity->setAlLongitude($gmgLocateAddressResutl->longitude);
                $entity->setAlLatitude($gmgLocateAddressResutl->latitude);

                $em->persist($entity);
            } else {
                $em->persist($entity);
            }

            if(isset($params['zipcode']) && empty($params['zipcode'])) {
                $params['zipcode']  = $gmgLocateAddressResutl->getPostCode();
            }
            
            if(isset($params['street_number']) && empty($params['street_number'])) {
                $params['street_number']  = $gmgLocateAddressResutl->getStreetNumber();
            }

            if(isset($params['route']) && empty($params['route'])) {
                $params['route']  = $gmgLocateAddressResutl->getStreetAddress();
            }

            if(isset($params['state']) && empty($params['state'])) {
                $params['state']  = $gmgLocateAddressResutl->getDistrictShort();
            }

            if(isset($params['country']) && empty($params['country'])) {
                $params['country']  = $gmgLocateAddressResutl->getCountry();
            }
            
            if (isset($params['cityx']) && empty($params['cityx'])) {
                $params['cityx'] = $gmgLocateAddressResutl->getLocality();
            }

            if ((isset($params['cityx']) && empty($params['cityx'])) || (isset($params['country']) && empty($params['country'])) 
                || (isset($params['state']) && empty($params['state'])) || (isset($params['zipcode']) && empty($params['zipcode'])) 
                || (isset($params['address']) && empty($params['address'])) ) {
            
                $allBlockOk = false;
                $statusMessage = 'The address is ambiguous.';

                $responseArray['status'] = $allBlockOk;
                $responseArray['entity'] = $entity;
                $responseArray['message'] = $statusMessage;
                $responseArray;
                return $responseArray;

            }

        } else {
            $allBlockOk = false;
            $statusMessage = 'The address is invalid.';

            $responseArray['status'] = $allBlockOk;
            $responseArray['entity'] = $entity;
            $responseArray['message'] = $statusMessage;
            $responseArray;
            return $responseArray;
        }

        if (isset($params['zipcode'])) {

            $zipcode = $em->getRepository('App:Zipcode')->findBy(['zcName' => $params['zipcode']]);

            if (isset($zipcode[0])) {
                $cityName = $zipcode[0]->getZcCity();
                $stateName = $zipcode[0]->getState();

                $newZipcode = $zipcode[0];
            } else {
                $newZipcode = new Zipcode();
                $newZipcode->setZcName($params['zipcode']);
                $newZipcode->setZcLongitude($gmgLocateAddressResutl->longitude);
                $newZipcode->setZcLatitude($gmgLocateAddressResutl->latitude);
                $newZipcode->setZcUserCreated(true);

                $cityName = static::replaceCharactersEspecials($params['cityx']);
                $stateName = static::replaceCharactersEspecials($params['state']);
            }

            if ($entity instanceof AccountLicense) {
                $entity->setAlAddres(static::replaceCharactersEspecials($params['address'], false));
            } else {
                $entity->setAcAddress(static::replaceCharactersEspecials($params['address'], false));
            }

            $newZipcode->setZcCity($params['cityx']);
            $newZipcode->setState(static::replaceCharactersEspecials($params['state']));

            $em->persist($newZipcode);
            $em->flush();

            $zipcode[0] = $newZipcode;
        } else {
            $allBlockOk = false;
            $statusMessage = 'The address is ambiguous.';

            $responseArray['status'] = $allBlockOk;
            $responseArray['entity'] = $entity;
            $responseArray['message'] = $statusMessage;
            $responseArray;
            return $responseArray;
        }

        $city = $em->getRepository('App:AccountLicense')->findByNameAndStateName($cityName, $stateName);
        if (isset($city[0])) {
            $city = $city[0];
            $searchedByAddressAutocomp = true;
            $state = $city->getCiState();
            $country = $state->getStCountry();
        } else {
            $stateNew = false;
            $cityNew = false;

            $countryZ = $em->getRepository('App:Country')->findBy(['coName' => static::replaceCharactersEspecials($params['country'], false)]);
            if (isset($countryZ[0])) {
                $country = $countryZ[0];
            } else {
                $country = new Country();

                $country->setCoName($params['country']);

                $em->persist($country);
                $em->flush();
            }

            $state = $em->getRepository('App:State')->findBy(['stName' => static::replaceCharactersEspecials($params['state'], false)]);
            if (isset($state[0])) {
                $state = $state[0];
                if ($state->getStCountry()->getCoId() != $country->getCoId()) {
                    $stateNew = true;
                }
            } else {
                $stateNew = true;
            }

            if ($stateNew) {
                $state = new State();

                $state->setStName(static::replaceCharactersEspecials($params['state']));
                $state->setStCountry($country);

                $em->persist($state);
                $em->flush();
            }

            $city = $em->getRepository('App:City')->findBy(['ciName' => static::replaceCharactersEspecials($params['cityx'])]);
            if (isset($city[0])) {
                $city = $city[0];
                $searchedByAddressAutocomp = true;
                $statez = $city->getCiState();
                $countryx = $statez->getStCountry();
                if (static::replaceCharactersEspecials($params['country']) != $countryx->getCoName() || $statez->getStName() != static::replaceCharactersEspecials($params['state'])) {
                    $cityNew = true;
                }
            } else {
                $cityNew = true;
            }

            if ($cityNew) {
                $city = new City();

                $city->setCiName(static::replaceCharactersEspecials($params['cityx']));
                $city->setCiState($state);
                $city->setIsManuallyAdded(true);

                $em->persist($city);
                $em->flush();
            }
        }

        $entity->setCity($city);
        $entity->setZipcode($zipcode[0]);

        $responseArray['status'] = $allBlockOk;
        $responseArray['entity'] = $entity;
        $responseArray['message'] = $statusMessage;

        return $responseArray;
    }

    /**
     * Permite formatear la dirección que llega el el request, para luego consumir la API de Google
     * @author Luis Enrique Robledo Lopez - Oct. 05/2017
     * @param $requestData
     * @returns $naturalAddress
     */
    public static function getNaturalAddressFromRequest($requestData) {
        $naturalAddress = $requestData['street'];

        $naturalAddress .= isset($requestData['city_name']) ? ", " . $requestData['city_name'] : '';
        $naturalAddress .= isset($requestData['state_name']) ? ", " . $requestData['state_name'] : '';
        $naturalAddress .= isset($requestData['zip_code']) ? " " . $requestData['zip_code'] : '';
        $naturalAddress .= isset($requestData['country_name']) ? ", " . $requestData['country_name'] : '';
        return $naturalAddress;
    }

    /**
     * Metodo encargado de enviar emails con la plantilla generica oficial
     * de licensor
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param container $container contenedor
     * @param array $arrInfo array con la informacio basica para
     * enviar el correo. la estructura de este arreglo debe ser de
     * la manera:
     * array("to"=>"mail@mail.ml",
     *     "subject" => "subject",
     *     "message" => "message to send")
     * en este array en la posicion messaje puede ir un array() con otros
     * datos en los cuales se generara un <p> en el mensage por cada posicion
     * ejemplo: array("message line one", "other message")
     * @return array con la respuesta del envio del correo efectuado con los
     * parametros correspondientes de la aplicacion
     */
    static public function sendMailInfo($container, $arrInfo, $path = '', $body = '', $attachedFile = '') {

//        $arrRended = [
//            'header' => "Level Pos Team",
//            'message' => $arrInfo['message'],
//        ];
//
//        if ($body == '') {
//            $body = $container->get('templating')->render('Emails\emailTemplate.html.twig', $arrRended);
//        }
//
//        $response = ["msn" => "sended", "response" => "__OK__"];
//
//        $infoMail = $container->getParameter('mailer_user');
//        $infoPass = $container->getParameter('mailer_password');
//
//        if ($container->getParameter("mailer_host") == "mailhog") {
//            $encryption = null;
//        } else {
//            $encryption = $container->getParameter("mailer_encryption");
//        }
//
//        try {
//            if ($attachedFile != '') {
//                $documento = \Swift_Attachment::fromPath($attachedFile);
//            }
//
//            $transport = (new \Swift_SmtpTransport($container->getParameter("mailer_host"), $container->getParameter("mailer_port"), $encryption))
//                    ->setUsername($infoMail)
//                    ->setPassword($infoPass);
//
//            // Create the Mailer using your created Transport
//            $mailer = new \Swift_Mailer($transport);
//
//            $message = (new \Swift_Message())
//                    ->setSubject($arrInfo['subject'])
//                    ->setFrom($container->getParameter("mailer_user"), "Licensor Team")
//                    ->setTo($arrInfo['to'])
//                    ->setBody($body, 'text/html');
//
//            if (isset($arrInfo['emailCc'])) {
//                $message->setCC($arrInfo['emailCc']);
//            }
//
//            if ($attachedFile != '') {
//                $message->attach($documento);
//            }
//
//            if (is_string($path)) {
//                static::createFileJson($path, "Sending email from: " . $infoMail . " to: " . $arrInfo['to'] . " \r");
//            } else {
//                $path->addInfo('SyncCommonService', [
//                    'msg', "Sending email from: " . $infoMail . " to: " . $arrInfo['to']
//                ]);
//            }
//
//            $resultMail = $mailer->send($message);
//            $emailSuccess = true;
//
//            if (is_string($path)) {
//                static::createFileJson($path, "Send mail result -> " . json_encode($resultMail) . " \r");
//            } else {
//                $path->addInfo('SyncCommonService', [
//                    'msg', 'Send mail result -> ' . json_encode($resultMail)
//                ]);
//            }
//        } catch (\Exception $ex) {
//            $emailSuccess = false;
//            $response = ["msn" => "Error: " . $ex->getMessage(), "response" => "__KO__"];
//        }
//        $response['success'] = $emailSuccess;
//
//        return $response;



        $arrRended = [
            'header' => "Level Pos Team",
            'message' => $arrInfo['message'],
        ];

        if ($body == '') {
            $body = $container->get('templating')->render('Emails\emailTemplate.html.twig', $arrRended);
        }

        $response = ["msn" => "sended", "response" => "__OK__"];

        $infoMail = $container->getParameter('mailer_user');
        $infoPass = $container->getParameter('mailer_password');

        if ($container->getParameter("mailer_host") == "mailhog") {
            $encryption = null;
        } else {
            $encryption = $container->getParameter("mailer_encryption");
        }

        if ($path != '') {
            static::createFileJson($path, 'Util:sendMailInfo: encryption -> ' . $encryption . "\r");
        }

        $emailSuccess = false;

        try {
            if ($attachedFile != '') {
                $documento = \Swift_Attachment::fromPath($attachedFile);
            }

            static::createFileJson($path, 'mailer_host -> ' . $container->getParameter("mailer_host") . "\r");
            static::createFileJson($path, 'mailer_port -> ' . $container->getParameter("mailer_port") . "\r");

            $transport = (new \Swift_SmtpTransport($container->getParameter("mailer_host"), $container->getParameter("mailer_port"), $encryption))
                    ->setUsername($infoMail)
                    ->setPassword($infoPass);
            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            $message = (new \Swift_Message())
                    ->setSubject($arrInfo['subject'])
                    ->setFrom($container->getParameter("mailer_user"), "Level Pos Team")
                    ->setTo($arrInfo['to'])
                    ->setBody($body, 'text/html');

            if (isset($arrInfo['emailCc'])) {
                $message->setCC($arrInfo['emailCc']);
            }

            if ($attachedFile != '') {
                $message->attach($documento);
            }

            if ($path != '') {
                static::createFileJson($path, 'Sending email from -> ' . $infoMail . "\r");
            }

//            if (!((int) $container->getParameter('ignore_all_outgoin_emails'))) {
            $resultMail = $mailer->send($message);
            static::createFileJson($path, 'Send mail result -> ' . json_encode($resultMail) . "\r");

            $emailSuccess = true;
//            }
        } catch (\Exception $ex) {
            $response = ["msn" => "Error: " . $ex->getMessage(), "response" => "__KO__"];
            if ($path != '') {
                static::createFileJson($path, 'Util:sendMailInfo: ERROR -> ' . $ex->getMessage() . " TRACE -> " . $ex->getTraceAsString() . "\r");
            }
        }
        $response['success'] = $emailSuccess;

        return $response;
    }

    /**
     * Metodo generico para transformar un arreglo y enviarlo como
     * una respuesta synfony para los ajax utilizados en la aplicacion
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $responseToAjax array con la infromacion a enviar como
     * respuesta de synfony
     * @return Response que contiene la informacion dada en el arreglo pasado
     * como argumento junto con la codificacion y cabeceras correspondientes
     */
    public static function respondJson_Ajax($responseToAjax) {
        $r = new Response(json_encode($responseToAjax));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param string $dirPath
     * @throws InvalidArgumentException
     */
    public static function deleteDir($dirPath) {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
//                var_dump($file);
                static::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Metodo para convertir los caracteres especiales a su correspondencia
     * mas cercana segun la codificacion UTF-8 para evitar porblemas en la
     * aplicacion a la hora de generar urls para los archivos de sincronizacion
     * y login inicial
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $baseStr string base a verificar si contiene caracteres
     * especiales no soportados por la codificacion UTF-8, en caso de tenerlos
     * , estos seran pasados a su correspondecia exadecimal para posteriormente
     * pasarlos a su correspondecia en ASCII
     * @return string|boolean string con los caracteres especiales reemplazados
     * por su equivalente mas cercano en ASCII en caso de tenerlos o un boleano
     * (falso) indicando que hubo un error al reemplazar el caracter especial
     */
    public static function convertLatinCharactersForDB($baseStr) {

        $strlen = mb_strlen($baseStr);
        $strByCharactersArray = [];
        while ($strlen) {
            array_push($strByCharactersArray, mb_substr($baseStr, 0, 1, "UTF-8"));
            $baseStr = mb_substr($baseStr, 1, $strlen, "UTF-8");
            $strlen = mb_strlen($baseStr);
        }

        $auxHexArray = [];
        $theCount1 = count($strByCharactersArray);
        for ($i = 0; $i < $theCount1; ++$i) {
            array_push($auxHexArray, bin2hex($strByCharactersArray[$i]));
        }

        $theHexaLatinArray = ["c3a1", "c381", "c3a9", "c389", "c3ad", "c38d", "c3b3", "c393", "c3ba", "c39a", "c3b1", "c391", "c3bc"];

        $occurrenceMBCharacterArray = array_intersect($auxHexArray, $theHexaLatinArray);

        if (!empty($occurrenceMBCharacterArray)) {
            $arrayForDBName = $auxHexArray;

            foreach ($occurrenceMBCharacterArray as $key => $hexaValue) {
                switch ($occurrenceMBCharacterArray[$key]) {
                    case "c3a1":
                        $arrayForDBName[$key] = '61';
                        break;
                    case "c381":
                        $arrayForDBName[$key] = '41';
                        break;
                    case "c3a9":
                        $arrayForDBName[$key] = '65';
                        break;
                    case "c389":
                        $arrayForDBName[$key] = '45';
                        break;
                    case "c3ad":
                        $arrayForDBName[$key] = '69';
                        break;
                    case "c38d":
                        $arrayForDBName[$key] = '49';
                        break;
                    case "c3b3":
                        $arrayForDBName[$key] = '6f';
                        break;
                    case "c393":
                        $arrayForDBName[$key] = '4f';
                        break;
                    case "c3ba":
                    case "c3bc":
                        $arrayForDBName[$key] = '75';
                        break;
                    case "c39a":
                        $arrayForDBName[$key] = '55';
                        break;
                    case "c3b1":
                        $arrayForDBName[$key] = '6e';
                        break;
                    case "c391":
                        $arrayForDBName[$key] = '4e';
                        break;
                }
            }

            $nameToDB = '';
            foreach ($arrayForDBName as $key => $hexaValue) {
                $nameToDB .= static::hexToStr($hexaValue);
            }

            return $nameToDB;
        } else {
            return false;
        }
    }

    /**
     * Metodo para convertir hexadecimal en su caracteres equivalentes en string
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $hex hexadecimal equivalentes a acaracteres especiales
     * @return type string con los caracteres especiales equivalentes
     */
    public static function hexToStr($hex) {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }

    /**
     * Metodo que tiene como proposito la actualizacion de un registro de
     * sincronizacion con los errores encontrados durante dicho proceso, estos
     * errores quedaran asociados directamente al proceso de sincronizacion
     * implicado en el momento de obtener el error
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $em EntityManager con los parametros de conexion de
     * la base de datos de licensor
     * @param type $readFilesDataEntityByLicense ReadFilesData|ReadFilesDataWeb
     * registro de sinconizacion al que se registrara un error obtenido en
     * dicho proceso, este error sera truncado para evitar que sea una
     * transaccion muy grande y no entorpezca el proceso de sincronizacion
     * @param type $theBadError string con el texto del error obtenido en uno
     * de los registros de sincronizacion, ya sea de una sincronizacion de
     * abajo a arriba o una sincronizacion web
     * @param type $haveToFlush boleano que indica si ya se puede guardar los x
     * errores obtenidos en el proceso de sincronizacion el su registro
     * correspondiente
     */
    public static function updateSyncRecordError($em, $readFilesDataEntityByLicense, $theBadError, $haveToFlush = false) {

        if ($theBadError != '') {

            $theErrorArray = $readFilesDataEntityByLicense->getObtainedError();
            array_push($theErrorArray, substr($theBadError, -70));

            $readFilesDataEntityByLicense->setObtainedError($theErrorArray);
            $em->persist($readFilesDataEntityByLicense);
        }

        if ($haveToFlush) {
            $em->flush();
        }
    }

    /**
     * 
     * @param type $container
     * @param type $em
     * @param type $readFilesDataEntityByLicense
     * @param type $theBadError
     * @param type $haveToFlush
     */
    public static function theAwesomeUltimateUpdateRabbitRecordError($container, $em, $readFilesDataEntityByLicense, $theBadError, $haveToFlush = false, $repositoryName = 'App:PointRelatedRequest') {

        if (!$em->isOpen()) {
            $em->clear();
            $container->get('doctrine')->resetManager();
            $em = $container->get('doctrine')->getEntityManager();

            $readFilesDataEntityByLicense = $em->getRepository($repositoryName)->find($readFilesDataEntityByLicense->getId());
        }

        if ($theBadError != '') {

            $theErrorArray = $readFilesDataEntityByLicense->getObtainedError();
            array_push($theErrorArray, substr($theBadError, -70));

            $readFilesDataEntityByLicense->setObtainedError($theErrorArray);
            $em->persist($readFilesDataEntityByLicense);
        }

        if ($haveToFlush) {
            $em->flush();
        }
    }

    /**
     * Metodo que tiene como proposito la actualizacion de un registro de
     * sincronizacion con los errores encontrados durante dicho proceso, estos
     * errores quedaran asociados directamente al proceso de sincronizacion
     * implicado en el momento de obtener el error
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $em EntityManager con los parametros de conexion de
     * la base de datos de licensor
     * @param type $readFilesDataEntityByLicense ReadFilesData|ReadFilesDataWeb
     * registro de sinconizacion al que se registrara un error obtenido en
     * dicho proceso, este error sera truncado para evitar que sea una
     * transaccion muy grande y no entorpezca el proceso de sincronizacion
     * @param type $theBadError string con el texto del error obtenido en uno
     * de los registros de sincronizacion, ya sea de una sincronizacion de
     * abajo a arriba o una sincronizacion web
     * @param type $haveToFlush boleano que indica si ya se puede guardar los x
     * errores obtenidos en el proceso de sincronizacion el su registro
     * correspondiente
     */
    public static function updateSyncOMTRecordError($em, $readFilesDataEntityByLicense, $theBadError, $haveToFlush = false) {

        if ($theBadError != '') {
            $theErrorArray = $readFilesDataEntityByLicense->getObtainedOMTError();
            array_push($theErrorArray, substr($theBadError, -70));

            $readFilesDataEntityByLicense->setObtainedOMTError($theErrorArray);
            $em->persist($readFilesDataEntityByLicense);
        }

        if ($haveToFlush) {
            $em->flush();
        }
    }

    /**
     * 
     * @param type $arrayChoice
     */
    public static function choiceFlip($arrayChoice) {
        $version = "3.0.0";
        $symfonyVersion = Kernel::VERSION;
        /*         * * symfonyVersion  = version for symfony 2.8 */

        if (!(version_compare($symfonyVersion, $version) == '-1')) {
            $arrayResult = array_flip($arrayChoice);
        } else {
            $arrayResult = $arrayChoice;
        }

        return $arrayResult;
    }

    /**
     * Metodo generico creado para permitir la subida de archivos mediante uno
     * de los diversos WS que lo permiten en licensor
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $request la peticion enviada al WS con todos los parametros
     * incluidos en ella dentro de los que se encuentra el archivo que se
     * subira a licensor
     * @param type $theUploadDirectory string con la ruta del directorio a la
     * que se realizara la subida del archivo en cuestion
     * @param type $logPath ruta para el archvivo (log) en donde se escribira
     * las lineas de texto pertienetes al proceso de la subida del archivo
     * @return array que contiene el resultado de la subida del archivo ademas
     * de la ruta y nombre de este en caso de tener exito
     */
    public static function uploadFileTo($request, $theUploadDirectory, $logPath) {

        $respArray = [];

        $respArray['result'] = '__OK__';
        $respArray['msg'] = '';
        $respArray['fileName'] = '';

        // Temp file age in secondss
        $maxFileAge = 15 * 3600;

        $reqChunks = $request->request->get('chunks');

        $chunks = isset($reqChunks) ? intval($reqChunks) : 0;

        static::createFileJson($logPath, 'Ready to upload file!' . "\r");

        if (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $theUploadDirectory . '/' . $fileName;

        if (!is_dir($theUploadDirectory) || !$dir = opendir($theUploadDirectory)) {
            $respArray['result'] = '__KO__';
            $respArray['msg'] = 'Failed to open target directory';

            static::createFileJson($logPath, 'Error response ' . json_encode($respArray) . "\r");
            return $respArray;
        }

        while (($file = readdir($dir)) !== false) {

            $tmpfilePath = $theUploadDirectory . DIRECTORY_SEPARATOR . $file;

            // If temp file is current file proceed to the next
            if ($tmpfilePath == "{$filePath}.part") {
                continue;
            }
            // Remove temp file if it is older than the max age and is not the current file
            if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                unlink($tmpfilePath);
            }
        }
        closedir($dir);

        if (!$out = fopen("{$filePath}", $chunks ? "ab" : "wb")) {
            $respArray['result'] = '__KO__';
            $respArray['msg'] = 'Failed to open output stream';

            static::createFileJson($logPath, 'Error response ' . json_encode($respArray) . "\r");
            return $respArray;
        }

        static::createFileJson($logPath, json_encode($_FILES) . "\r");

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                $respArray['result'] = '__KO__';
                $respArray['msg'] = 'Failed to move uploaded file';
            }
            // Read binary input stream and append it to temp file
            if (!$in = fopen($_FILES["file"]["tmp_name"], "rb")) {
                $respArray['result'] = '__KO__';
                $respArray['msg'] = 'Failed to open input stream';
            }
        } else {
            if (!$in = fopen("php://input", "rb")) {
                $respArray['result'] = '__KO__';
                $respArray['msg'] = 'Failed to open input stream';
            }
        }

        while ($buff = fread($in, 12288)) {
            fwrite($out, $buff);
        }

        $respArray['fileName'] = $fileName;

        static::createFileJson($logPath, 'The response ' . json_encode($respArray) . "\r");

        fclose($out);
        fclose($in);

        static::createFileJson($logPath, 'File uploaded with name: ' . $fileName . "\r");

        return $respArray;
    }

    /**
     * funcion encargada de crear un registro en la tabla LicenseDataBase
     * con nombre de base de datos usuario y password con caracteristicas especiales
     * @param type $em
     * @param type $entity
     * @param type $user
     * @param type $isForConsume
     * @param type $info Esta variable trae un array con los datos para crear 
     * una cuenta de usuario en la base de datos de la licencia de level ademas de eso
     * se tiene el path del archivo que se debe usar para poblar la misma, con datos
     * obtenidos de OMT
     * @return boolean
     * @author KJ-Hector Hdz
     */
    public static function setDataLicense($em, $container, $entity, $user = null
    , $isForConsume = true, $info = null) {
        $insertId = $entity->getId();

        $customLog = new CustomLog($container);
        $channel = $customLog->createChannel('ResponseFromOMTS0');
        $customLog->setLogger($channel);

        $customLog->addInfo(
                'Util::setDataLicense', [
            'Content' => $insertId,
            'Info' => $info,
            'isForConsume' => $isForConsume
        ]);

        if ($insertId > 0) {
            $companyName = strtolower(static::replaceCharactersEspecials($entity->getAlRestaurantName()));

            /**
             * validaicon de longitud de nombre de restaurante
             */
            if (!(strlen($companyName) <= 10)) {
                $companyName = substr($companyName, 0, 5) . substr($companyName, -5);
            }

            $strDbName = "lvl_" . $entity->getAlAccountLicense()->getAcNickName() . "_$companyName" . "_$insertId";
            $strDbuser = $companyName . "_$insertId";
            $strDbPass = static::randomPassword(16);

            //nombre generado por codigo
            $databaseName = $strDbName;
            //variable en archivo parameter
            $databaseUser = $container->getParameter('user_databases_level');
            //variable en archivo parameter
            $databasePassword = $container->getParameter('pass_databases_level');
            //variable en archivo config
            $hostDatabases = $container->getParameter('level_host_databases');

            $licenseDataBase = new LicenseDataBase();
            $licenseDataBase->setDbhost($hostDatabases);
            $licenseDataBase->setDbname($strDbName);
            $licenseDataBase->setDbuser($strDbuser);
            $licenseDataBase->setDbpass($strDbPass);
            $licenseDataBase->setLicense($entity);
            $licenseDataBase->setIsDatabaseCreated(false);
            $licenseDataBase->setIsSchemaCreated(false);
            $licenseDataBase->setHasPersistentError(false);

            $em->persist($licenseDataBase);
            $em->flush();

            $consumerDatabaseCreationData = [];

            /**
             * arreglo con datos para crear base de datos en servidor establecido en
             * variable hostDatabases
             */
            $dataOptions = [
                'database' => $databaseName,
                //user para la base de datos creada
                'user' => $strDbuser,
                //pass para la base de datos creada
                'pass' => $strDbPass,
                'host' => $hostDatabases,
                'dbConnectionId' => $licenseDataBase->getId()
            ];
            $customLog->addInfo(
                    'Util::setDataLicense', [
                '$dataOptions' => $dataOptions
            ]);

            /**
             * arreglo de datos para crear el esquema de base de datos para aplicacion segun nombre base de datos
             * en host establecido en variable hostDatabases
             */
            $dataOptionsRoot = [
                'dbname' => $databaseName,
                'user' => $databaseUser,
                'password' => $databasePassword,
                'host' => $hostDatabases,
                'driver' => 'pdo_mysql',
                'rootId' => $licenseDataBase->getId()
            ];

            $customLog->addInfo(
                    'Util::setDataLicense', [
                'Content' => $insertId,
                'Info' => $info,
                '$consumerDatabaseCreationData' => $consumerDatabaseCreationData,
                '$isForConsume' => $isForConsume
            ]);

            $consumerDatabaseCreationData['dataOptions'] = $dataOptions;
            $consumerDatabaseCreationData['dataOptionsRoot'] = $dataOptionsRoot;
            $customLog->addInfo(
                    'Util::setDataLicense', [
                'Content' => $consumerDatabaseCreationData,
            ]);
            $consumerDatabaseCreationData['licenseDatabaseId'] = $licenseDataBase->getId();
            $consumerDatabaseCreationData['info'] = $info;
            $customLog->addInfo(
                    'Util::setDataLicense', [
                'Content' => $insertId,
                'Info' => $info,
                'consumerDatabaseCreationData' => $consumerDatabaseCreationData,
                'isForConsume' => $isForConsume
            ]);

            //crea cola para el rabbit y el comando level:license:create LicenseDatabaseCreationCommand
            $container->get('license_database_creation_producer')->setContentType('application/json');
            $container->get('license_database_creation_producer')->setDeliveryMode(2);
            $container->get('license_database_creation_producer')->publish(json_encode($consumerDatabaseCreationData));

            if ($isForConsume) {
                //crea cola para el rabbit y el comando level:license:create LicenseDatabaseCreationCommand
                ForcedAsynchronousCommandsUtil::consumeDataBaserCreationCommand($container, true);
            }

            return true;
        }
        return false;
    }

    /**
     * enviar correo cuando se crea la licencia. Se encía CC al contacto de
     * la cuenta
     * @param array $dataInfo datos necesarios para enviar los correos
     */
    public static function mailAccountLicense($container, $dataInfo) {

        $twig = $container->get('twig');
        $globals = $twig->getGlobals();

        $arrMessage = [
            "Dear " . $dataInfo['nameTo'] . ".",
            "We have created a license for the restaurant \"" . $dataInfo['nameLicense'] . "\". "
            . "Remember with this license you can access the online site "
            . "where the backup data is stored.",
            "&nbsp;",
            "URL: " . $globals['level_web'],
            "License's Nickname: " . $dataInfo['nickname'],
            "License's User: " . 'admin',
            "License's Password: " . 'admin123',
            "Qty Devices: " . $dataInfo['qtydevice'],
            "Price License: USD $" . $dataInfo['sumTotal'],
            "&nbsp;",
            "Thanks for choosing us."
        ];
        $arrInfo = [
            "to" => $dataInfo['emailTo'],
            "emailCc" => $dataInfo['emailCc'],
            "subject" => "License's Creation",
            "message" => $arrMessage
        ];
        static::sendMailInfo($container, $arrInfo);
    }

    /**
     * enviar correo cuando se crea la licencia. Se encía CC al contacto de
     * la cuenta
     * @param array $dataInfo datos necesarios para enviar los correos
     */
    public static function mailOMTDashboardDefaultUser($container, $dataInfo) {

        $arrMessage = [
            "Dear LEVEL Lite Customer.",
            "We have created a default user for your OpenMyTab '<strong>" . $dataInfo['name_license'] . "</strong>' account."
            . "Remember with this license you can access the OpenMyTab online site "
            . "from where you can manage your restaurant menu and other things related.",
            "&nbsp;",
            "LEVEL Lite App: <strong>Please download our LEVEL Lite app from the app store for IOS or Android!</strong>",
            "Restaurant Nickname: <strong>" . $dataInfo['omt_license'] . "</strong>",
            "Account's User: <strong>" . $dataInfo['omt_dashboard_user'] . "</strong>",
            "Account's Password: <strong>" . $dataInfo['omt_dashboard_pass'] . "</strong>",
            "&nbsp;",
            "Thanks for choosing us."
        ];

        $arrInfo = [
            "to" => $dataInfo['emailTo'],
            "subject" => "License's Creation",
            "message" => $arrMessage
        ];

        if (isset($dataInfo['emailCc'])) {
            $arrInfo['emailCc'] = $dataInfo['emailCc'];
        }

        static::sendMailInfo($container, $arrInfo);
    }

    /**
     * Funcion interna para validar si la estructura del menu en level es apropiada
     * Para la exportacion del restaurante a OMT, esta funcion corregira dicha
     * estructura de ser erronea
     * @param type $emLev
     * @param type $path
     */
    public static function createDefaultMenuCategoryItems($emLev, $path, $restaurant, $categoria = null ) {

        $dateNow = new DateTime('now');
        $dateCreation = $dateNow->format('Y-m-d H:i:s');

        static::createFileJson($path, 'createDefaultMenuCategoryItems: Starting!' . "\r");
        
        if ($categoria === null || $categoria === 'Pizza' ) {
            $conPizza = $emLev->getRepository('App\Structure\Categoria')->findBy(['catDefault' => 'Pizza']);

            $pos = static::validateIdFixMenu($conPizza) ;           

            if (!isset($conPizza) || $conPizza == [] || (count($conPizza) === 1 &&  strlen($conPizza[$pos]->getId() ) === 1) ) {
                $defaultCategoryPizza = new Categoria();
                
                $defaultCategoryPizza->setCatStatus(1);                
                $defaultCategoryPizza->setCatNombre('Pizza');
                $defaultCategoryPizza->setCatCreation($dateNow);
                $defaultCategoryPizza->setCatOrdenamiento(0);
                $defaultCategoryPizza->setCatEnabled(1);
                $defaultCategoryPizza->setCatColor1('#2550e0');
                $defaultCategoryPizza->setCatImg('pizza.png');
                static::createFileJson($path, 'createDefaultMenuCategoryItems: Pizza category created!' . "\r");
            } elseif (isset($conPizza[$pos])) {
                $defaultCategoryPizza = $conPizza[$pos];
            }

            
            $defaultCategoryPizza->setCatDefault('Pizza');
            $defaultCategoryPizza->setCatDescuento(0);
            $defaultCategoryPizza->setCatEstadodesc('inactivo');
            $defaultCategoryPizza->setCatPrecioventacomun('0.00');
            $defaultCategoryPizza->setCatPreciodomicilio('0.00');
            $defaultCategoryPizza->setCatModpordiviciones(1);
            $defaultCategoryPizza->setCatModgroup(1);
            $defaultCategoryPizza->setCatUsarmodificadores(0);
            $defaultCategoryPizza->setCatIsGiftCard(0);

            $emLev->persist($defaultCategoryPizza);
            $emLev->flush();

            static::defaultConfMultiplePrice($emLev, $restaurant, $defaultCategoryPizza, 1);
        }

        // if ($categoria === null || $categoria === 'Crust' ) {
        //     $conCrust = $emLev->getRepository('App\Structure\Categoria')->findBy(['catDefault' => 'Crust']);
        //     if (!isset($conCrust) || $conCrust == []) {
        //         $defaultCategoryCrust = new Categoria();

        //         $defaultCategoryCrust->setCatStatus(1);   
        //         $defaultCategoryCrust->setCatNombre('Crust');
        //         static::createFileJson($path, 'createDefaultMenuCategoryItems: Crust category created!' . "\r");
        //     } elseif ($conCrust[0]) {
        //         $defaultCategoryCrust = $conCrust[0];
        //     }

        //     $defaultCategoryCrust->setCatDefault('Crust');
        //     $defaultCategoryCrust->setCatDescuento(0);
        //     $defaultCategoryCrust->setCatEstadodesc('inactivo');
        //     $defaultCategoryCrust->setCatPrecioventacomun('0.00');
        //     $defaultCategoryCrust->setCatPreciodomicilio('0.00');
        //     $defaultCategoryCrust->setCatOrdenamiento(0);
        //     $defaultCategoryCrust->setCatModpordiviciones(0);
        //     $defaultCategoryCrust->setCatModgroup(2);
        //     $defaultCategoryCrust->setCatUsarmodificadores(0);
        //     $defaultCategoryCrust->setCatColor1('#ccc');
        //     $defaultCategoryCrust->setCatImg('trans.png');
        //     $defaultCategoryCrust->setCatIsGiftCard(0);
        //     $defaultCategoryCrust->setCatCreation($dateNow);

            // $emLev->persist($defaultCategoryCrust);
            // $emLev->flush();

            // static::defaultConfMultiplePrice($emLev, $restaurant, $defaultCategoryCrust, 1);
        // }

        if ($categoria === null || $categoria === 'Gift_Card' ) {
            $conGiftCard = $emLev->getRepository('App\Structure\Categoria')->findBy(['catDefault' => 'Gift Card',]);

            $pos = static::validateIdFixMenu($conGiftCard) ;  

            if (!isset($conGiftCard) || $conGiftCard == [] || strlen($conGiftCard[$pos]->getId()) === 1 ) {
                $defaultCategoryGiftCard = new Categoria();

                $defaultCategoryGiftCard->setCatNombre('Gift Card');
                $defaultCategoryGiftCard->setCatStatus(1);
                $defaultCategoryGiftCard->setCatCreation($dateNow);
                $defaultCategoryGiftCard->setCatColor1('#ccc');
                $defaultCategoryGiftCard->setCatImg('trans.png');
                $defaultCategoryGiftCard->setCatEnabled(1);
                $defaultCategoryGiftCard->setCatOrdenamiento(0);
                static::createFileJson($path, 'createDefaultMenuCategoryItems: Gift Card category created!' . "\r");
            } elseif (isset($conGiftCard[$pos])) {
                $defaultCategoryGiftCard = $conGiftCard[$pos];
            }

            $defaultCategoryGiftCard->setCatDefault('Gift Card');
            $defaultCategoryGiftCard->setCatDescuento(0);
            $defaultCategoryGiftCard->setCatEstadodesc('inactivo');
            $defaultCategoryGiftCard->setCatPrecioventacomun('0.00');
            $defaultCategoryGiftCard->setCatPreciodomicilio('0.00');
            $defaultCategoryGiftCard->setCatModpordiviciones(0);
            $defaultCategoryGiftCard->setCatModgroup(1);
            $defaultCategoryGiftCard->setCatUsarmodificadores(0);
            $defaultCategoryGiftCard->setCatIsGiftCard(1);

            $emLev->persist($defaultCategoryGiftCard);
            $emLev->flush();

            static::defaultConfMultiplePrice($emLev, $restaurant, $defaultCategoryGiftCard, 1);

            $prodGiftCard = $emLev->getRepository('App\Structure\Producto')->findBy(['prodNombre' => 'Gift Card', 'prodCategoria' => $defaultCategoryGiftCard->getId()]);
            
            $pos = static::validateIdFixMenu($prodGiftCard) ;  
            
            if (!isset($prodGiftCard) || $prodGiftCard == [] || (count($prodGiftCard) === 1 &&  strlen($prodGiftCard[$pos]->getId()) === 1) ) {
                $defaultProductGiftCard = new Producto();
                $defaultProductGiftCard->setProdCreation($dateNow);
            } elseif (isset($prodGiftCard[$pos])) {
                $defaultProductGiftCard = $prodGiftCard[$pos];
            }

            $defaultProductGiftCard->setProdNombre('Gift Card');
            $defaultProductGiftCard->setProdCategoria($defaultCategoryGiftCard->getId());
            $defaultProductGiftCard->setProdGiftCardEmpty(1);
            $defaultProductGiftCard->setProdIsGiftCard(1);
            $defaultProductGiftCard->setProdSaleType(2);
            $defaultProductGiftCard->setProdStatus(1);
            $defaultProductGiftCard->setProdDeleted(0);
            $defaultProductGiftCard->setProdEsventa(1);
            $defaultProductGiftCard->setProdIsUniqueSize(1);

            $emLev->persist($defaultProductGiftCard);
            $emLev->flush();

            static::defaultConfMultiplePrice($emLev, $restaurant, $defaultProductGiftCard, 2);

            static::createFileJson($path, 'createDefaultMenuCategoryItems: Gift Card product created!' . "\r");

            $categorySizeRegularGiftCard = $emLev->getRepository('App\Structure\CategoriaTalla')->findBy(['ptNombre' => 'Regular', 'ptProdcategoria' => $defaultCategoryGiftCard->getId()]);
            if (!isset($categorySizeRegularGiftCard) || $categorySizeRegularGiftCard == []) {
                $defaultCategorySizeGiftCard = new CategoriaTalla();
                $defaultCategorySizeGiftCard->setPtCreation($dateCreation);
            } elseif (isset($categorySizeRegularGiftCard[0])) {
                $defaultCategorySizeGiftCard = $categorySizeRegularGiftCard[0];
            }

            $defaultCategorySizeGiftCard->setPtNombre('Regular');
            $defaultCategorySizeGiftCard->setCsSorting(0);
            $defaultCategorySizeGiftCard->setPtProdcategoria($defaultCategoryGiftCard->getId());
            $defaultCategorySizeGiftCard->setPtInitialPrice(0);

            $emLev->persist($defaultCategorySizeGiftCard);
            $emLev->flush();
            
            static::createFileJson($path, 'createDefaultMenuCategoryItems: Gift Card category size created!' . "\r");
        

            $prodSizeGiftCard = $emLev->getRepository('App\Structure\ProductoTalla')->findBy(['prdtllaNombre' => 'Regular', 'prdtllaPrdcodigo' => $defaultProductGiftCard->getId()]);
            if (!isset($prodSizeGiftCard) || $prodSizeGiftCard == []) {
                $defaultProductSizeGiftCard = new ProductoTalla();
                $defaultProductSizeGiftCard->setPrdtllaCreation($dateCreation);
            } elseif (isset($prodSizeGiftCard[0])) {
                $defaultProductSizeGiftCard = $prodSizeGiftCard[0];
            }
            
            $defaultProductSizeGiftCard->setPrdtllaPrdcodigo($defaultProductGiftCard->getId());
            $defaultProductSizeGiftCard->setPrdtllaNombre('Regular');
            $defaultProductSizeGiftCard->setPrdtllaValor(0);
            $defaultProductSizeGiftCard->setPrdtllaStatus(1);

            $emLev->persist($defaultProductSizeGiftCard);
            $emLev->flush(); 

            static::createFileJson($path, 'createDefaultMenuCategoryItems: Gift Card product size created!' . "\r");
        }

        if ($categoria === null || $categoria === 'Pizza' ) {
            $categorySize10Pizza = $emLev->getRepository('App\Structure\CategoriaTalla')->findBy(['ptNombre' => '10', 'ptProdcategoria' => $defaultCategoryPizza->getId()]);
            $pos = static::validateIdFixMenu($categorySize10Pizza) ;
            if (!isset($categorySize10Pizza) || $categorySize10Pizza == [] || strlen($categorySize10Pizza[0]->getId()) === 1 ) {
                $defaultPizzaCategorySizeSmall = new CategoriaTalla();
                $defaultPizzaCategorySizeSmall->setPtCreation($dateCreation);
            } elseif (isset($categorySize10Pizza[$pos])) {
                $defaultPizzaCategorySizeSmall = $categorySize10Pizza[$pos];
            }

            $defaultPizzaCategorySizeSmall->setPtNombre('10');
            $defaultPizzaCategorySizeSmall->setCsSorting(1);
            $defaultPizzaCategorySizeSmall->setPtProdcategoria($defaultCategoryPizza->getId());
            $defaultPizzaCategorySizeSmall->setPtInitialPrice(0);

            $emLev->persist($defaultPizzaCategorySizeSmall);
            $emLev->flush();

            $categorySize14Pizza = $emLev->getRepository('App\Structure\CategoriaTalla')->findBy(['ptNombre' => '14', 'ptProdcategoria' => $defaultCategoryPizza->getId()]);
            
            $pos = static::validateIdFixMenu($categorySize14Pizza) ; 
            if (!isset($categorySize14Pizza) || $categorySize14Pizza == [] || strlen($categorySize14Pizza[$pos]->getId()) === 1) {
                $defaultPizzaCategorySizeMedium = new CategoriaTalla();
                $defaultPizzaCategorySizeMedium->setPtCreation($dateCreation);
            } elseif (isset($categorySize14Pizza[$pos])) {
                $defaultPizzaCategorySizeMedium = $categorySize14Pizza[$pos];
            }

            $defaultPizzaCategorySizeMedium->setPtNombre('14');
            $defaultPizzaCategorySizeMedium->setCsSorting(2);
            $defaultPizzaCategorySizeMedium->setPtProdcategoria($defaultCategoryPizza->getId());
            $defaultPizzaCategorySizeMedium->setPtInitialPrice(0);

            $emLev->persist($defaultPizzaCategorySizeMedium);
            $emLev->flush();

            $categorySize18Pizza = $emLev->getRepository('App\Structure\CategoriaTalla')->findBy(['ptNombre' => '18', 'ptProdcategoria' => $defaultCategoryPizza->getId()]);
            $pos = static::validateIdFixMenu($categorySize18Pizza) ; 
            if (!isset($categorySize18Pizza) || $categorySize18Pizza == [] || strlen($categorySize18Pizza[$pos]->getId()) === 1) {
                $defaultPizzaCategorySizeLarge = new CategoriaTalla();
                $defaultPizzaCategorySizeLarge->setPtCreation($dateCreation);
            } elseif (isset($categorySize18Pizza[$pos])) {
                $defaultPizzaCategorySizeLarge = $categorySize18Pizza[$pos];
            }

            $defaultPizzaCategorySizeLarge->setPtNombre('18');
            $defaultPizzaCategorySizeLarge->setCsSorting(3);
            $defaultPizzaCategorySizeLarge->setPtProdcategoria($defaultCategoryPizza->getId());
            $defaultPizzaCategorySizeLarge->setPtInitialPrice(0);

            $emLev->persist($defaultPizzaCategorySizeLarge);
            $emLev->flush(); 
        }

        static::createFileJson($path, 'createDefaultMenuCategoryItems: Pizza sizes created!' . "\r");

        if ($restaurant->getHasLogedOMT() || $restaurant->getHasAndroid()) {
            $arrayForEntitiesToRegistAction = [];
            if (isset($defaultCategoryPizza)) {
                $arrayForEntitiesToRegistAction[] = $defaultCategoryPizza;
            }
            if (isset($defaultCategoryCrust)) {
                $arrayForEntitiesToRegistAction[] = $defaultCategoryCrust;
            }
            if (isset($defaultCategoryGiftCard)) {
                $arrayForEntitiesToRegistAction[] = $defaultCategoryGiftCard;
            }
            if (isset($defaultProductGiftCard)) {
                $arrayForEntitiesToRegistAction[] = $defaultProductGiftCard;
            }
            if (isset($defaultProductSizeGiftCard)) {
                $arrayForEntitiesToRegistAction[] = $defaultProductSizeGiftCard;
            }
            if (isset($defaultPizzaCategorySizeSmall)) {
                $arrayForEntitiesToRegistAction[] = $defaultPizzaCategorySizeSmall;
            }
            if (isset($defaultPizzaCategorySizeMedium)) {
                $arrayForEntitiesToRegistAction[] = $defaultPizzaCategorySizeMedium;
            }
            if (isset($defaultPizzaCategorySizeLarge)) {
                $arrayForEntitiesToRegistAction[] = $defaultPizzaCategorySizeLarge;
            }

            foreach ($arrayForEntitiesToRegistAction as $entityToRegistAction) {
                if ($entityToRegistAction) {
                    $variablellorona = explode('\\', get_class($entityToRegistAction));
                    $androidTableName = Utilx::getRepoServerNameByAndroidTableName('\\' . end($variablellorona), true);

                    if ($androidTableName) {
                        $tableNameToRegistActionRecord = Utilx::getAndroidTableName($androidTableName, true);
                        if ($entityToRegistAction->getAndroidSync()) {
                            Utilx::saveUpdateRecordInRegistActions($emLev, $tableNameToRegistActionRecord, $entityToRegistAction, 'U');
                        } else {
                            Utilx::saveUpdateRecordInRegistActions($emLev, $tableNameToRegistActionRecord, $entityToRegistAction, 'I');
                        }

                        static::createFileJson($path, "createDefaultMenuCategoryItems: RegistAction with 'I' action for '$tableNameToRegistActionRecord' created!" . "\r");
                    } else {
                        static::createFileJson($path, "createDefaultMenuCategoryItems: No table traduction found for '$androidTableName' , skipping record!" . "\r");
                    }
                }
            }
        }

        static::defaultStartingServiceTypes($emLev, $path);

        return true;
    }

    /**
     * permite obtener la posicion del regitro que tenga el UID
     */
    public static function validateIdFixMenu($item){
        $pos = 0;
        if (count($item) >= 2) {                
            foreach($item AS $key => $dataItem) {
                if (strlen($dataItem->getId()) > 1) {
                    $pos = $key;
                }
            }
        }
        return $pos;
    }
    

    /**
     * Funcion que permite validar la configuracion de multiple price  por default
     */
    public static function defaultConfMultiplePrice($emLev, $restaurant, $categoriaOrProduct, $entity){
        $brandTypeMenu = $emLev->getRepository('App\Structure\BrandTypeMenu')->findAll();
        foreach($brandTypeMenu AS $brand) {                    
            $brandTypeMenu = $emLev->getRepository('App\Structure\BrandMenu')->findBy(["parenteId" => $categoriaOrProduct->getId(), "brandId" => $brand->getId()]);
            if (!isset($brandTypeMenu) || $brandTypeMenu === [] ) {
                $defaultBrandMenu =  new BrandMenu();
                $defaultBrandMenu->setCreate(new DateTime('now'));
                $defaultBrandMenu->setStatus(0);
            } elseif ($brandTypeMenu[0])  {
                $defaultBrandMenu = $brandTypeMenu[0];
            }
            $defaultBrandMenu->setEntityId($entity);
            $defaultBrandMenu->setParenteId($categoriaOrProduct->getId());
            $defaultBrandMenu->setBrandId($brand->getId());

            $emLev->persist($defaultBrandMenu);
            $emLev->flush();

            if ($entity === BrandMenu::ENTITY_CATEGORY) {
                $orderServicesTypes = $emLev->getRepository('App\Structure\OrderServiceTypes')->findBy(["ostStatus" => CategoryBrandService::STATUS_ACTIVE ]);
                foreach($orderServicesTypes AS $servicesTypes) {
                    $categoryBrandService = $emLev->getRepository('App\Structure\CategoryBrandService')->findBy(["ostId" => $servicesTypes->getId(), "categoryId" => $categoriaOrProduct->getId() ,"brandId" => $brand->getId()]);
                    if (!isset($categoryBrandService) || $categoryBrandService === [] ) {
                        $defaultCategoryBrandService =  new CategoryBrandService();
                        $defaultCategoryBrandService->setStatus(1);
                    } elseif ($categoryBrandService[0])  {
                        $defaultCategoryBrandService = $categoryBrandService[0];
                    }
                    $defaultCategoryBrandService->setOstId($servicesTypes->getId());
                    $defaultCategoryBrandService->setCategoryId($categoriaOrProduct->getId());
                    $defaultCategoryBrandService->setBrandId($brand->getId());

                    $emLev->persist($defaultCategoryBrandService);
                    $emLev->flush();
                }                    
            }
        }
    }

    /**
     * @param type $license
     * @param type $loyaltyConfNedded
     * @return array
     */
    public static function genericDefaultRecordsToLicensorDBByLicenseLoader($license, $loyaltyConfNedded = true) {
        $defaultLoyaltyConfigurationRecord = [
            [
                'setDateCreated' => static::getCurrentDate(),
                'setUseLocalPoints' => false,
                'setUseGlobalPoints' => false,
                'setMinimumPointAmountToExchange' => 20,
                'setLicense' => $license,
            ]
        ];

        $responseArray = [];

        // Se agregan los registros iniciales de codigos de aprobacion
        if ($loyaltyConfNedded) {
            $responseArray['RestaurantLoyaltyConfiguration'] = $defaultLoyaltyConfigurationRecord;
        }

        return $responseArray;
    }

    /**
     * @param type $emLic
     * @param type $license
     * @param type $path
     * @param type $loyaltyConfNedded
     * @return boolean
     */
    public static function defaultStartingLicensorRecordsByLicense($emLic, $license, $path, $loyaltyConfNedded = true) {
        $auxiliarStructureDir = "App:";
        $auxiliarDir = "\App\Entity\\";

        $defaultInsertsArray = static::genericDefaultRecordsToLicensorDBByLicenseLoader($license, $loyaltyConfNedded);

        foreach ($defaultInsertsArray as $entityName => $entitiesData) {
            static::createFileJson($path, "Starting $entityName default insert!" . "\r");

            foreach ($entitiesData as $singleEntityData) {
                if ($entityName == 'RestaurantLoyaltyConfiguration') {
                    $repoResultToValidate = $emLic->getRepository($auxiliarStructureDir . $entityName)->findBy(['license' => $license->getId()]);
                    if (isset($repoResultToValidate[0])) {
                        continue;
                    }
                }

                $entityRealDir = $auxiliarDir . $entityName;
                $newDefaultRecordToDB = new $entityRealDir();

                foreach ($singleEntityData as $methodName => $singleData) {
                    if (method_exists($newDefaultRecordToDB, $methodName)) {
                        $newDefaultRecordToDB->$methodName($singleData);
                    }
                }

                $emLic->persist($newDefaultRecordToDB);
                $emLic->flush();
            }
        }

        return true;
    }

    /**
     * @param type $typeServicesNedded
     * @param type $brandsNedded
     * @return type
     */
    public static function genericDefaultRecordsToWebDBLoader($typeServicesNedded = true, $brandsNedded = true) {

        $defaultServicesTypes = [
            [
                'setOstType' => \App\Structure\OrderServiceTypes::NO_PHONE,
                'setOstName' => 'No Phone',
                'setOstStatus' => \App\Structure\OrderServiceTypes::INACTIVE
            ],
            [
                'setOstType' => \App\Structure\OrderServiceTypes::PICKUP,
                'setOstName' => 'Pickup',
                'setOstStatus' => \App\Structure\OrderServiceTypes::ACTIVE
            ],
            [
                'setOstType' => \App\Structure\OrderServiceTypes::DELIVERY,
                'setOstName' => 'Delivery',
                'setOstStatus' => \App\Structure\OrderServiceTypes::ACTIVE
            ],
            [
                'setOstType' => \App\Structure\OrderServiceTypes::DINEIN,
                'setOstName' => 'Dine In',
                'setOstStatus' => \App\Structure\OrderServiceTypes::ACTIVE
            ],
            [
                'setOstType' => \App\Structure\OrderServiceTypes::TURN,
                'setOstName' => 'Turn',
                'setOstStatus' => \App\Structure\OrderServiceTypes::INACTIVE
            ],
            [
                'setOstType' => \App\Structure\OrderServiceTypes::TURN_AUTOMATIC,
                'setOstName' => 'Turn Automatic',
                'setOstStatus' => \App\Structure\OrderServiceTypes::INACTIVE
            ]
        ];

        $defaultBrands = [
            [
                'setName' => \App\Structure\BrandTypeMenu::LEVEL,
                'setAlias' => \App\Structure\BrandTypeMenu::ALIAS_LEVEL,
                'setOrder' => 1,
                'setStatus' => \App\Structure\BrandTypeMenu::STATUS_ACTIVE
            ],
            [
                'setName' => \App\Structure\BrandTypeMenu::WHITE_LABEL,
                'setAlias' => \App\Structure\BrandTypeMenu::ALIAS_WHITE_LABEL,
                'setOrder' => 4,
                'setStatus' => \App\Structure\BrandTypeMenu::STATUS_INACTIVE
            ],
            [
                'setName' => \App\Structure\BrandTypeMenu::OMT,
                'setAlias' => \App\Structure\BrandTypeMenu::ALIAS_OMT,
                'setOrder' => 2,
                'setStatus' => \App\Structure\BrandTypeMenu::STATUS_ACTIVE
            ],
            [
                'setName' => \App\Structure\BrandTypeMenu::MENU_ONLINE,
                'setAlias' => \App\Structure\BrandTypeMenu::ALIAS_MENU_ONLINE,
                'setOrder' => 3,
                'setStatus' => \App\Structure\BrandTypeMenu::STATUS_INACTIVE
            ]
        ];

        $defaultTypeReasonCodes = [
            [
                'setId' => '1',
                'setNameEs' => 'Cancelaciones',
                'setNameEn' => 'Cancellations'
            ],
            [
                'setId' => '2',
                'setNameEs' => 'Descuentos',
                'setNameEn' => 'Discounts'
            ],
            [
                'setId' => '3',
                'setNameEs' => 'Devoluciones',
                'setNameEn' => 'Voids'
            ],
            [
                'setId' => '4',
                'setNameEs' => 'Cortesias',
                'setNameEn' => 'Comps'
            ],
            [
                'setId' => '5',
                'setNameEs' => 'No Enviado a Cocina',
                'setNameEn' => 'Not Sent To Kitchen'
            ]
        ];

        $responseArray = [];

        // Se agregan los registros iniciales de codigos de aprobacion
        $responseArray['TypeReasonCodes'] = $defaultTypeReasonCodes;

        if ($typeServicesNedded) {
            $responseArray['OrderServiceTypes'] = $defaultServicesTypes;
        }

        if ($brandsNedded) {
            $responseArray['BrandTypeMenu'] = $defaultBrands;
        }

        return $responseArray;
    }

    /**
     * @param type $emLev
     * @param type $path
     * @param type $typeServicesNedded
     * @param type $brandsNedded
     * @return boolean
     */
    public static function defaultStartingServiceTypes($emLev, $path, $typeServicesNedded = true, $brandsNedded = true, $hasOMT = false) {
        $auxiliarStructureDir = "\App\Structure\\";
        $auxiliarStructureDirForRepo = "App\Structure\\";

        $defaultInsertsArray = static::genericDefaultRecordsToWebDBLoader($typeServicesNedded, $brandsNedded);

        foreach ($defaultInsertsArray as $entityName => $entitiesData) {
            static::createFileJson($path, "Starting $entityName default insert!" . "\r");

            foreach ($entitiesData as $singleEntityData) {
                $superAuxiliarEntityCosito = $auxiliarStructureDir . $entityName;

                if ($entityName == 'TypeReasonCodes') {
                    $repoResultToValidate = $emLev->getRepository($auxiliarStructureDirForRepo . $entityName)->find($singleEntityData['setId']);
                    if ($repoResultToValidate) {
                        continue;
                    }
                }
                $validateRegistOMt = false;
                if ($entityName == "OrderServiceTypes") {
                    $repoResultToValidate = $emLev->getRepository($superAuxiliarEntityCosito)->findBy(['ostName' => $singleEntityData['setOstName']]);
                    if (count($repoResultToValidate) > 0) {
                        $validateRegistOMt = true;
                    }
                } elseif ($entityName == "BrandTypeMenu") {
                    $repoResultToValidate = $emLev->getRepository($superAuxiliarEntityCosito)->findBy(['name' => $singleEntityData['setName']]);
                    if (count($repoResultToValidate) > 0) {
                        $validateRegistOMt = true;
                    }
                }
                if ($validateRegistOMt === false) {
                    $newDefaultRecordToDB = new $superAuxiliarEntityCosito();

                    foreach ($singleEntityData as $methodName => $singleData) {
                        if (method_exists($newDefaultRecordToDB, $methodName)) {
                            $newDefaultRecordToDB->$methodName($singleData);
                        }
                    }

                    $emLev->persist($newDefaultRecordToDB);
                    $emLev->flush();
                }


                if ($hasOMT) {
                    $omtTableName = Utilx::getOMTStructureEntityDir($entityName, true);
                    $regitActionName = Utilx::getOMTTableName($omtTableName, true);

                    Utilx::saveUpdateRecordInRegistActions($emLev, $regitActionName, $newDefaultRecordToDB, 'I');
                }
            }
        }

        $allBrandTypeMenu = $emLev->getRepository('App\Structure\BrandTypeMenu')->findAll();

        foreach ($allBrandTypeMenu as $entityToRegistAction) {
            Utilx::saveUpdateRecordInRegistActions($emLev, 'brand_type_menu', $entityToRegistAction, 'I');
        }

        $allOrderServiceTypes = $emLev->getRepository('App\Structure\OrderServiceTypes')->findAll();

        foreach ($allOrderServiceTypes as $entityToRegistAction) {
            Utilx::saveUpdateRecordInRegistActions($emLev, 'order_service_types', $entityToRegistAction, 'I');
        }

        return true;
    }

    /**
     * Funcion interna para validar si la estructura del menu en level es apropiada
     * Para la exportacion del restaurante a OMT, esta funcion corregira dicha
     * estructura de ser erronea
     * @param type $emLev
     * @param type $restaurant
     * @param type $path
     */
    public static function deleteOldDefaultMenuCategoryItems($emLev, $path, $restaurant) {

        $arrayEntitiesToDelete = [];
        $arrayToDeleteLogically = [];
        $arrayToDeletePhysically = [];

        $arrayToDeleteLogically[] = $arrayEntitiesToDelete[] = $categoryOldPizza = $emLev->getRepository('App\Structure\Categoria')->find(1);
        $arrayToDeleteLogically[] = $arrayEntitiesToDelete[] = $categoryOldCrust = $emLev->getRepository('App\Structure\Categoria')->find(2);
        $arrayToDeleteLogically[] = $arrayEntitiesToDelete[] = $categoryOldGiftCard = $emLev->getRepository('App\Structure\Categoria')->find(3);

        $arrayToDeleteLogically[] = $arrayEntitiesToDelete[] = $productOldGiftCard = $emLev->getRepository('App\Structure\Producto')->find(1);

        $arrayToDeleteLogically[] = $arrayEntitiesToDelete[] = $producSizeOldGiftCard = $emLev->getRepository('App\Structure\ProductoTalla')->find(1);

        $arrayToDeletePhysically[] = $arrayEntitiesToDelete[] = $sizeOldPizzaSmall = $emLev->getRepository('App\Structure\CategoriaTalla')->find(1);
        $arrayToDeletePhysically[] = $arrayEntitiesToDelete[] = $sizeOldPizzaMedium = $emLev->getRepository('App\Structure\CategoriaTalla')->find(2);
        $arrayToDeletePhysically[] = $arrayEntitiesToDelete[] = $sizeOldPizzaLarge = $emLev->getRepository('App\Structure\CategoriaTalla')->find(3);

        static::createFileJson($path, 'Old default menu items loaded!' . "\r");

        if ($categoryOldPizza) {
            $categoryOldPizza->setCatEnabled(0);
            $categoryOldPizza->setCatStatus(0);
        }

        if ($categoryOldCrust) {
            $categoryOldCrust->setCatEnabled(0);
            $categoryOldCrust->setCatStatus(0);
        }

        if ($categoryOldGiftCard) {
            $categoryOldGiftCard->setCatEnabled(0);
            $categoryOldGiftCard->setCatStatus(0);
        }

        if ($productOldGiftCard) {
            $productOldGiftCard->setProdDeleted('1');
            $productOldGiftCard->setProdStatus(0);
        }

        if ($producSizeOldGiftCard) {
            $producSizeOldGiftCard->setPrdtllaStatus(0);
        }

        foreach ($arrayToDeleteLogically as $logicallyDelete) {
            if ($logicallyDelete) {
                $emLev->persist($logicallyDelete);
            }
        }

        $emLev->flush();

        static::createFileJson($path, 'Logical delete for old default menu items done!' . "\r");

        if ($restaurant->getHasLogedOMT() || $restaurant->getHasAndroid()) {
            foreach ($arrayEntitiesToDelete as $entityToRegistAction) {
                if ($entityToRegistAction) {
                    $variablellorona = explode('\\', get_class($entityToRegistAction));
                    $androidTableName = Utilx::getRepoServerNameByAndroidTableName('\\' . end($variablellorona), true);

                    if ($androidTableName) {
                        $tableNameToRegistActionRecord = Utilx::getAndroidTableName($androidTableName, true);
                        Utilx::saveUpdateRecordInRegistActions($emLev, $tableNameToRegistActionRecord, $entityToRegistAction, 'D');

                        static::createFileJson($path, "deleteOldDefaultMenuCategoryItems: RegistAction with 'D' action for '$tableNameToRegistActionRecord' created!" . "\r");
                    } else {
                        static::createFileJson($path, "deleteOldDefaultMenuCategoryItems: No table traduction found for '$androidTableName' , skipping record!" . "\r");
                    }
                }
            }
        }

        foreach ($arrayToDeletePhysically as $physicallyDelete) {
            if ($physicallyDelete) {
                $emLev->remove($physicallyDelete);
            }
        }

        $emLev->flush();

        static::createFileJson($path, 'Physical delete for old default menu items done!' . "\r");
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 19/07/2016
     * @param type $em
     * @param type $params
     * @param type $path
     * @return boolean
     */
    public static function createCardAsLoyalClientRecord($em, $params, $omtClient, $path) {
        try {
            $cardAsClient = new CardAsClient();

            $cardAsClient->setLastFour($params['lastFour']);
            $cardAsClient->setFranchise($params['franchise']);
            $cardAsClient->setNameOnCard($params['nameOnCard']);
            $cardAsClient->setUniqueCombined($params['theCombined']);
            $cardAsClient->setExpirationDate($params['expirationDate']);

            $cardAsClient->setClient($omtClient);

            $em->persist($cardAsClient);

            $em->flush();

            static::createFileJson($path, 'CardAsClient record created! ' . "\r");
            return true;
        } catch (\Exception $ex) {
            static::createFileJson($path, 'Error updating processed status! ' . $ex->getMessage() . "\r");
            return false;
        }
    }

}
