<?php

namespace App\Service;

use stdClass;
use App\Service\CustomLog;
use App\Util\Util;
use App\Entity as Entity;
use App\Security\WebService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\SyncSendedMessage;
use App\Util\SyncUtil;

/**
 * Description of SyncCommonService
 * @author frcho
 */
class SyncCommonService {

    private $custom;
    private $channel;
    private $channelLevelOmt;

    public function __construct(CustomLog $customLog) {
        $this->custom = $customLog;
        $this->channel = $this->custom->createChannel('notify');
        $this->channelLevelOmt = $this->custom->createChannel('comming_level_data_consumer');
    }

    /**
     * Metodo para desencriptar un string segun los parametros comunes entre
     * android y licensor generalmente utilizado para los WS
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $container contenedor con el entorno de ejecucion de la
     * aplicacion
     * @param type $dataToDecrypt string con la infromacion a desencriptar
     * @return Array que indica internamente si el desencriptado fue exitoso
     * ademas de contener la infromacion desemcriptada o de lo contrario, contendria
     * el estatur indicando que hubo un error y el mensaje obtenido de este
     */
    public static function decryptDataForUploadsFiles($container, $dataToDecrypt) {
        $responseArray = [];
        $responseArray['data'] = '';
        $responseArray['status'] = '';

        try {
            $responseArray['data'] = $dataToDecrypt;

            $responseArray['status'] = WebService::HTTP_CODE_SUCCESS;

            return $responseArray;
        } catch (\Exception $ex) {
            $responseArray['status'] = WebService::CODE_INTERNAL_ERROR;
            $responseArray['msg'] = $ex->getMessage();
            return $responseArray;
        }
    }

    /**
     * @author frcho
     * @param type $em
     * @param type $registList
     * @return type
     */
    public function webSyncRegistActionsToFileTraductor($em, $registList, $isForAndroid = false, $channel = false) {

        /*
         * Traductor del nombre de las tablas
         */
        $arrTableName = [];
        $countLevelWebChangesForSync = count($registList);

        if ($channel) {
            $this->channel = $channel;
        }

        $this->custom->addInfo($this->channel
                , 'SyncCommonService', [
            'msg', 'LoadAndCreateWebAppSyncJson: sync records count: ' . $countLevelWebChangesForSync]);
        for ($i = 0; $i < $countLevelWebChangesForSync; ++$i) {
            $regTableName = $registList[$i]["tableName"];

            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'msg', "LoadAndCreateWebAppSyncJson: newName -> $regTableName"
            ]);

            $arrTableName[] = [
                "tname" => $regTableName,
                "reacId" => $registList[$i]["id"],
                "idcode" => $registList[$i]["id"],
                "idfrom" => $registList[$i]["tableId"],
                "level" => $registList[$i]["levelId"],
                "action" => $registList[$i]["tableAction"],
                "nickname" => $registList[$i]["restNickname"]
            ];

            $registList[$i]["reacTableName"] = $regTableName;
        }

        $arrDataExport = [];
        $arrExpectedDataBuild = [];

        /*
         * leyendo los resultados del regist action
         */
        foreach ($arrTableName as $tableName) {
            $tableRegistStd = new stdClass();
            $tName = $tableName['tname'];
            $tNameForFile = $this->getEntityForIncomignLevelOmtSync($tableName['tname'], true);
            if ($isForAndroid) {
                //Se llama el arreglo que traduce para las tablas android-omt
                $tNameForFile = $this->getAndroidTableNameForOutcomingSync($tableName['tname'], true);
            } else {
                $level = $tableName['level'];
            }
            $idFrom = $tableName['idfrom'];
            $reacId = $tableName['reacId'];
            $tAction = $tableName['action'];
            $iPetition = $tableName['idcode'];
            $nickname = $tableName['nickname'];
            $useEqual = " = ";

            if ($idFrom == '') {
                $this->custom->addInfo($this->channel
                        , 'SyncCommonService', [
                    'msg', "[ERROR] el IdFrom no es valido en RegistActions = $iPetition"
                ]);
                continue;
            }

            if ($tableName['action'] === "D") {
                $tableRegistStd->action = $tAction;
                $tableRegistStd->table = $tNameForFile;
                $tableRegistStd->id = $idFrom;
                $tableRegistStd->android = $idFrom;
                if (!$isForAndroid) {
                    $tableRegistStd->level = $level;
                } else {
                    $tableRegistStd->omt = $tableRegistStd->id;
                }

                if (!isset($arrExpectedDataBuild[$tNameForFile])) {
                    $arrExpectedDataBuild[$tNameForFile] = [$reacId => $idFrom];
                } else {
                    $arrExpectedDataBuild[$tNameForFile][$reacId] = $idFrom;
                }

                array_push($arrDataExport, $tableRegistStd);
                continue;
            }

            /*
             * traductor de los table_name de regist action para level
             */
            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'msg',
                "LoadAndCreateWebAndroidSyncJson: structureEntityDir => "
                . "$tName is for Android => "
                . (int) $isForAndroid . " idFrom => "
                . "$idFrom is equal => '" . $useEqual . "' nickname => $nickname"
            ]);
            /*
             * condicion que define que get usar para generar el json.
             * en este caso puede ser para android o para level, si entra al if es para level
             */
            if (!$isForAndroid) {
                $responseQuery = $em->getRepository('KijhoOMT:' . $tName)->getSyncData($idFrom, $useEqual, false, '', $nickname);
            } else {
                $responseQuery = $em->getRepository('KijhoOMT:' . $tName)->getAndroidSyncData($idFrom, $useEqual, false, '', $nickname);
            }

            if (!(count($responseQuery) > 0)) {
                $this->custom->addInfo($this->channel
                        , 'SyncCommonService', [
                    'msg', "[ALERTA] No se encontro datos para la tabla $tName en el registro $idFrom"
                ]);
                continue;
            } else {
                $this->custom->addInfo($this->channel
                        , 'SyncCommonService', [
                    'msg', "Data found!"
                ]);
            }

            $tableRegistStd->action = $tAction;
            $tableRegistStd->table = $tNameForFile;
            $tableRegistStd->id = $idFrom;
            $tableRegistStd->android = $idFrom;
            if (!$isForAndroid) {
                $tableRegistStd->level = $level;
                if (isset($responseQuery[0]['level']) && null != $responseQuery[0]['level']) {
                    if ($tAction != "I") {
                        if (empty($responseQuery[0]['level'])) {
                            $tableRegistStd->level = $idFrom;
                        } else {
                            $tableRegistStd->level = $responseQuery[0]['level'];
                        }
                    } else {
                        $this->custom->addInfo($this->channel
                                , 'SyncCommonService', [
                            'msg', ' ############ tableRegistStd => level: ' . $responseQuery[0]['level'] . ' $tName: ' . $tName . ' $idFrom: ' . $idFrom
                        ]);
                        $tableRegistStd->level = '';
                    }
                } else {
                    if ($tAction != "I") {
                        $tableRegistStd->level = $idFrom;
                    } else {
                        $tableRegistStd->level = '';
                    }
                }
            } else {
                $tableRegistStd->omt = $tableRegistStd->id;
                if (isset($responseQuery[0]['omt']) && null != $responseQuery[0]['omt']) {
                    if ($tAction != "I") {
                        $tableRegistStd->level = $idFrom;
                        $tableRegistStd->omt = $responseQuery[0]['omt'];
                    } else {
                        $this->custom->addInfo($this->channel
                                , 'SyncCommonService', [
                            'msg', ' ############ tableRegistStd => omt: ' . $responseQuery[0]['omt'] . ' $tName: ' . $tName . ' $idFrom: ' . $idFrom
                        ]);
                        $tableRegistStd->omt = '';
                        $tableRegistStd->level = '';
                    }
                } else {
                    $tableRegistStd->omt = '';
                    if ($tAction != "I") {
                        $tableRegistStd->level = $idFrom;
                    } else {
                        $tableRegistStd->level = '';
                    }
                }
            }

            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'TABLE_REGIST' => 'LoadAndCreateWebAndroidSyncJson: tableRegistStd => ' . json_encode($tableRegistStd),
                'RESPONSE' => 'LoadAndCreateWebAndroidSyncJson: responseQuery[0] => ' . json_encode($responseQuery[0])
            ]);

            if (!isset($arrExpectedDataBuild[$tNameForFile])) {
                $arrExpectedDataBuild[$tNameForFile] = [$reacId => $idFrom];
            } else {
                $arrExpectedDataBuild[$tNameForFile][$reacId] = $idFrom;
            }

            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'msg', '$tName: ' . $tName . ' -> ' . gettype($responseQuery[0]),
                'JSON', ' -----> ' . json_encode($responseQuery[0])
            ]);

            if ($tName == 'OmtRestaurantMenuCategory') {
                $responseQuery[0]['resmct_sizes'] = json_encode($responseQuery[0]['resmct_sizes']);
            }
//            $JSON = '{"inv_billed_time":"2018-01-29 13:28:28","inv_status":0,"inv_disccounts":null,"inv_total":"1.000000","inv_subtotal":null,"ord_id":"6ebb6168-2f37-47d5-b626-a88f1f06ea69","inv_sub_id":null,"inv_taxes":null,"inv_chargers":null,"inv_uid_payment_station":null,"inv_pay_time":null,"inv_cus_name":null,"inv_is_tax_exempt":0,"inv_tax_exempt_comment":null,"inv_id":"122ce329-2e3d-41ec-abd6-79e3b0a127fd","use_id":"6713f947-9ca9-41ad-b1e3-d34a2964dae1","omt":"122ce329-2e3d-41ec-abd6-79e3b0a127fd"}';
//            $theObject = array_map("utf8_encode", $JSON);

            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'RESPONSE_QUERY' => $responseQuery[0]
            ]);

            $theObject = array_map("utf8_encode", $responseQuery[0]);

            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'ARRAY_OBJECT' => $theObject
            ]);
            if ($tName == 'OmtRestaurantMenuCategory') {
                $theObject['resmct_sizes'] = json_decode($theObject['resmct_sizes']);
            }

            $tableRegistStd->fields = [$theObject];

            array_push($arrDataExport, $tableRegistStd);
        }

        return [$arrDataExport, $arrExpectedDataBuild];
    }

    /**
     * Metodo utilizado para generar un archivo con todo el contenido de un json
     * ya sea que este encriptado o no, este metodo es comunmente utilizado en
     * los procesos de sincronizacion de licensor
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $container contenedor con el entorno de ejecucion de la
     * aplicacion
     * @param type $jsonToFile string con el json que sera almacenado en como un
     * archivo
     * @param type $pathFileJson string con la ruta y nombre con el que se
     * almacenara el archivo
     * @param type $appBoolWeb boleano que indica si se debe o no emcriptar el
     * contenido del archivo segun la regla comun entre licensor y omt
     * @return boolean boleano que indica si se genero con exito o no el archivo
     * con el json de sincronizacion
     */
    public function putAllEncryptedJsonContent($container
    , $jsonToFile, $pathFileJson, $appBoolWeb, $infoCommingFromLicensor = false) {

        $this->generateFile(str_replace("\r", "", $jsonToFile), $pathFileJson);
        return true;

//        if ($infoCommingFromLicensor) {
//            $this->generateFile($jsonToFile, $pathFileJson);
//            return true;
//        }
//
//        $theAuxiliarSplitedStrJsonArray = str_split($jsonToFile, 2000);
//
//        $numOfTextLines = count($theAuxiliarSplitedStrJsonArray);
//
//        $this->custom->addInfo($this->channelLevelOmt
//                , 'SyncCommonService', [
//            'msg', "Wutil: boolean mode->" . (int) $appBoolWeb
//        ]);
//
//        if ($numOfTextLines > 0) {
//            $splitedStrFull = '';
//            for ($i = 0; $i < $numOfTextLines; ++$i) {
//                $arrayEncryptResult = static::encryptDataForApp($container
//                                , $theAuxiliarSplitedStrJsonArray[$i], $appBoolWeb);
//
//                if ($arrayEncryptResult['status']) {
//                    $splitedStrFull .= $arrayEncryptResult['data'] . "\r";
//                } else {
//                    $this->custom->addInfo($this->channelLevelOmt
//                            , 'SyncCommonService', [
//                        'msg', "Wutil: Error encipting json lines for file!"
//                    ]);
//                    return false;
//                }
//            }
//
//            $this->generateFile($splitedStrFull, $pathFileJson);
//
//            return true;
//        } else {
//            if ($jsonToFile == '') {
//                $this->custom->addInfo($this->channelLevelOmt
//                        , 'SyncCommonService', [
//                    'msg', "Wutil: Empty json text"
//                ]);
//            } else {
//                $this->custom->addInfo($this->channelLevelOmt
//                        , 'SyncCommonService', [
//                    'msg', "Wutil: Error spliting text for json!" . $splitedStrFull
//                ]);
//            }
//
//            return false;
//        }
    }

    /**
     * Metodo para la escritura en un archivo (custom log) de licensor
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $text texto a colocar en el archivo
     * @param type $path rutal al archivo de escritura
     */
    public static function generateFileAlternate($text, $path) {
        $file = fopen($path, "a+");
        fwrite($file, $text);
        fclose($file);
    }

    /**
     * Funcion que genera un archivo con la informacion deseada segun se requiera
     * en el directorio upload
     * @param type $content
     */
    public function generateFile($content, $pathFilename) {
        $fs = new Filesystem();
        try {
            $fs->dumpFile($pathFilename, $content);
            $fs->chmod($pathFilename, 0755);
        } catch (\Exception $e) {
            $this->custom->addInfo($this->channelLevelOmt
                    , 'ValidatingRestaurantPath', [
                'Error', $e->getMessage()
            ]);
        }
    }

    /**
     * Metodo para encriptar un string segun los parametros comunes entre
     * android y licensor
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $container contenedor con el entorno de ejecucion de la
     * aplicacion
     * @param type $dataToEncrypt string con la informacion a encriptar
     * @param type $boolAppMode boleano que indica si se esta trabajando en
     * modo seguro o no, en caso de no hacerlo la aplicacion ignorara la
     * encriptacion para la informacion recivida
     * @return Array con la informacion del encriptado en donde se indica si el
     * proceso fue exitoso o no
     */
    public function encryptDataForApp($container, $dataToEncrypt, $boolAppMode = null, $log = null) {
        $responseArray = [];

        try {
            if (null != $boolAppMode && $boolAppMode == true) {
                if ($log) {
                    $log->addNotice('encryptDataForApp', ['encripting' => 'Aca llego un changuito ']);
                }
                $responseArray['data'] = static::encryptDataAppAlgorithm($dataToEncrypt, $log);
            } else {
                if ($log) {
                    $log->addNotice('encryptDataForApp', ['encripting' => 'El changuito no se perdio!']);
                }
                $responseArray['data'] = $dataToEncrypt;
            }

            if ($log) {
                $log->addNotice('encryptDataForApp', ['encripting' => 'Y aqui ya casi llego!']);
            }
            $responseArray['status'] = WebService::CODE_OK;
        } catch (\Exception $ex) {
            $responseArray['status'] = WebService::CODE_INTERNAL_ERROR;
            $responseArray['msg'] = $ex->getMessage();
        }

        return $responseArray;
    }

    /**
     * Metodo base que contiene y ejecuta el agoritmo de encriptacion que se
     * tiene entre android y licensor
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $dataToEncrypt string con la informacion a encriptar
     * @return type string con la informacion encriptada
     */
    public static function encryptDataAppAlgorithm($dataToEncrypt, $log = null) {
        $cipher = "rijndael-128";
        $modeCipher = "cbc";
        $textKeyMap = "cf6888522abb2316";
        $ivParameterSpec = "4e1925f9cf99259e";

        $textKeyMap = md5($textKeyMap);
        $mcryptModule = mcrypt_module_open($cipher, "", $modeCipher, $ivParameterSpec);
        mcrypt_generic_init($mcryptModule, $textKeyMap, $ivParameterSpec);
        $encryptText = mcrypt_generic($mcryptModule, $dataToEncrypt);
        mcrypt_generic_deinit($mcryptModule);
        mcrypt_module_close($mcryptModule);

        if ($log) {
            $log->addNotice('encryptDataForApp', ['encripting' => 'El chango fue por su banana!']);
        }

        return base64_encode(bin2hex($encryptText));
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
    public static function updateSyncRecordError($em
    , $readFilesDataEntityByLicense, $theBadError, $haveToFlush = false) {

        if ($theBadError != '') {
            $theErrorArray = $readFilesDataEntityByLicense->getObtainedError();
            array_push($theErrorArray, substr($theBadError, -70));

            $readFilesDataEntityByLicense->setObtainedError($theErrorArray);
            $em->merge($readFilesDataEntityByLicense);
        }

        if ($haveToFlush) {
            $em->flush();
        }
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
    public function sendMailInfo($container, $arrInfo, $path = '', $body = '', $attachedFile = '') {

        $arrRended = [
            'header' => "OMT Team",
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

        try {

            if ($attachedFile != '') {
                $documento = \Swift_Attachment::fromPath($attachedFile);
            }

            $transport = (new \Swift_SmtpTransport($container->getParameter("mailer_host"), $container->getParameter("mailer_port"), $encryption))
                    ->setUsername($infoMail)
                    ->setPassword($infoPass);

            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message())
                    ->setSubject($arrInfo['subject'])
                    ->setFrom($container->getParameter("mailer_user"), "OMT Team")
                    ->setTo($arrInfo['to'])
                    ->setBody($body, 'text/html');

            if (isset($arrInfo['emailCc'])) {
                $message->setCC($arrInfo['emailCc']);
            }

            if ($attachedFile != '') {
                $message->attach($documento);
            }

            if ($path != '') {
                $this->custom->addInfo($this->channel
                        , 'SyncCommonService', [
                    'msg', 'Sending email from -> ' . $infoMail
                ]);
            }

            $resultMail = $mailer->send($message);
            $emailSuccess = true;
            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'msg', 'Send mail result -> ' . json_encode($resultMail)
            ]);
        } catch (\Exception $ex) {
            $emailSuccess = false;
            $response = ["msn" => "Error: " . $ex->getMessage(), "response" => "__KO__"];
        }
        $response['success'] = $emailSuccess;

        return $response;
    }

    /**
     * Esta funcion permite crear registro en SyncMainRecord acorde a la situacion requerida
     * 
     * @param type $em entity manager
     * @param type $restaurant objeto restaurante
     * @param type $syncType el tipo de sincronizacin leve_omt, omt_android, omt_level
     * @param type $fileName Nombre del archivo
     * @param type $status estado de la sincronizacion
     * @param type $levelSyncId identificador syncmain
     * @return \App\Entity\SyncMainRecord
     */
    static public function createNewSyncRecord($em, $restaurant, $syncType, $fileName = null, $status = null, $levelSyncId = null) {

        $actualDate = Util::getCurrentDate();

        $entity = new Entity\SyncMainRecord();
        $entity->setDateSincAsk($actualDate);
        $entity->setRestaurant($restaurant);
        if ($fileName) {
            $entity->setInitialFileName($fileName);
        }
        if ($status) {
            $entity->setStatus($status);
        } else {
            $entity->setStatus(Entity\SyncMainRecord::STATUS_STANDBY);
        }
        if ($levelSyncId) {
            $entity->setSyncLevelId($levelSyncId);
        }
        $entity->setIsReadedByServer(false);
        $entity->setApplicationMode(false);
        $entity->setHasPersistentError(false);
        $entity->setApplicationMode(false);
        $entity->setSyncType($syncType);

        if ($syncType == Entity\SyncMainRecord::TYPE_SYNC_OMT_ANDROID) {
            $entity->setIsForAndroid(true);
        } else {
            $entity->setIsForAndroid(false);
        }

        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    /**
     * Metodo para obtener la informacion de un archivo de sincronizacion segun
     * su ubicacion y segun la manera en la que se requiere la informacion de
     * dicho archivo
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $container contenedor con el entorno de ejecucion de la
     * aplicacion
     * @param type $pathFileJson string con la ruta y nombre del archivo que se
     * consultara
     * @param type $appBoolWeb boleano que indica si se debe o no desencriptar
     * el contenido del archivo segun la regla comun entre licensor y android
     * @param type $getJson boleano que indica si es necesario retornar la
     * informacion del archivo justo como es obtenida o si se requiere la
     * decodificacion del string
     * @return Array|string la infromacion obtenida del archivo especificado ya
     * sea que se haya pedido decodificada o sin decodificacion
     */
    public function getAllDecryptedJsonContent($container, $pathFileJson, $appBoolWeb, $getJson = false, $getAsArray = true) {
        $jsn = file_get_contents($pathFileJson);

        $auxEncriptedByLinesArray = explode("\r", $jsn);
        $numEncriptedLines = count($auxEncriptedByLinesArray);

        $arrData = null;

        $this->custom->addInfo($this->channelLevelOmt
                , 'SyncLevelCommingDataConsumer', ['GettingJsonData', "Count plain lines -> " . $numEncriptedLines]);

        if ($numEncriptedLines > 0) {
            $splitedStrFull = "";
            for ($i = 0; $i < $numEncriptedLines; ++$i) {
                if (!empty($auxEncriptedByLinesArray[$i])) {
                    $arrayEncryptResult = $this->decryptDataForAndroid($container, $auxEncriptedByLinesArray[$i], $appBoolWeb);

                    if ($arrayEncryptResult['status']) {
                        $splitedStrFull .= $arrayEncryptResult['data'];
                    } else {
                        $this->custom->addInfo($this->channelLevelOmt
                                , 'SyncLevelCommingDataConsumer', ['GettingJsonData', "Error decripting json lines for file!"]);
                    }
                } else {
                    $this->custom->addInfo($this->channelLevelOmt
                            , 'SyncLevelCommingDataConsumer', ['GettingJsonData', "Continue sentence!"]);
                    continue;
                }
            }

            try {
                $r = new Response($splitedStrFull);
                $r->headers->set('Content-Type', 'application/json');

                if (!$getJson) {
                    $arrData = json_decode($r->getContent(), $getAsArray);
                } else {
                    $arrData = $r->getContent();
                }
            } catch (\Exception $e) {
                $this->custom->addInfo($this->channelLevelOmt
                        , 'SyncLevelCommingDataConsumer', ['GettingJsonData', 'Error: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine()]);
                return null;
            }
        }

        return $arrData;
    }

    /**
     * Metodo para desencriptar un string segun los parametros comunes entre
     * android y licensor
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $container contenedor con el entorno de ejecucion de la
     * aplicacion
     * @param type $dataToDecrypt string con la infromacion a desencriptar
     * @param type $boolAppMode boleano que indica el modo de ejecucion del
     * metodo, si es (false) entonces se ignorara el desencriptado del string
     * pasado por argumento
     * @param type $keepMultipleLines boleano que indica si es necesario
     * conservar las multiples lineas del string desencriptado como es el caso
     * de los logs
     * @return Array que indica internamente si el desencriptado fue exitoso
     * ademas de contener la infromacion desemcriptada o de lo contrario, contendria
     * el estatur indicando que hubo un error y el mensaje obtenido de este
     */
    public function decryptDataForAndroid($container, $dataToDecrypt, $boolAppMode = null, $keepMultipleLines = false) {
        $responseArray = [];
        $responseArray['data'] = '';
        $responseArray['status'] = '';

        try {
            if ($boolAppMode === true) {
                $responseArray['data'] = $this->decryptDataAndroidAlgorithm($dataToDecrypt, $keepMultipleLines);
            } else {
                $responseArray['data'] = $dataToDecrypt;
            }

            $responseArray['status'] = WebService::HTTP_CODE_SUCCESS;

            return $responseArray;
        } catch (\Exception $ex) {
            $responseArray['status'] = WebService::HTTP_CODE_SERVICE_UNAVAILABLE;
            $responseArray['msg'] = $ex->getMessage();
            return $responseArray;
        }
    }

    /**
     * Metodo base que contiene y ejecuta el agoritmo de desencriptado que se
     * tiene entre android y licensor con una funcion de autodeteccion de un
     * string no encriptado (un json) en cuyo caso se omitira la
     * desencriptacion, generalmente este metodo se usa en los WS de licensor
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $dataToDecrypt string con la informacion a desencriptar
     * @param type $keepMultipleLines booleano que indica si se desea conservar
     * los saltos de linea o no
     * @return type string con la informacion desencriptada
     */
    public function decryptDataAndroidAlgorithm($dataToDecrypt, $keepMultipleLines) {
        if ($dataToDecrypt[0] == '{') {
            return $dataToDecrypt;
        } else {
            $cipher = "rijndael-128";
            $modeCipher = "cbc";
            $textKeyMap = "D4:6E:AC:3F:F0:BE";
            $ivParameterSpec = "fedcba9876543210";

            // Make sure the key length should be 16 bytes 
            $textKeyMap = $this->sureOf16BytesKey($textKeyMap);

//            $td = mcrypt_module_open($cipher, "", $mode, $iv); 
//            mcrypt_generic_init($td, $secret_key, $iv); 
//            $decrypted_text = mdecrypt_generic($td, hex2bin("444e6969a269829a3e59a86300614fc5")); 
//            mcrypt_generic_deinit($td); 
//            mcrypt_module_close($td); 
            //////
//            $cipher = "rijndael-128";
//            $modeCipher = "cbc";
//            $textKeyMap = "cf6888522abb2316";
//            $ivParameterSpec = "4e1925f9cf99259e";
//
//            $textKeyMap = md5($textKeyMap);
//            $dataToDecryptBase = hex2bin(base64_decode($dataToDecrypt));
            $dataToDecryptBase = hex2bin($dataToDecrypt);
            $mcryptModule = mcrypt_module_open($cipher, "", $modeCipher, $ivParameterSpec);
            mcrypt_generic_init($mcryptModule, $textKeyMap, $ivParameterSpec);
            $decryptText = mdecrypt_generic($mcryptModule, $dataToDecryptBase);
            mcrypt_generic_deinit($mcryptModule);
            mcrypt_module_close($mcryptModule);

            if ($keepMultipleLines) {
                $theDecriptedData = nl2br($decryptText);
                return trim(rtrim($theDecriptedData, "\0"));
            }

            return trim(rtrim($decryptText, "\0"));
        }
    }

    /**
     * Funcion encargada en recibir el nombre de la tabla android para retornarR
     * el array con la posicion del nombre android el cual contiene
     * el string traducido del bundle donde se encuentra esa entidad
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $tableName
     * @return string
     */
    public static function getEntityForIncomignLevelOmtSync($tableName, $getTableNameForLicensor = false) {
        $structureBaseDir = "KijhoOMT:";
        $structureEntityDirByTableMap = [];

////// Tablas login inicial omt, tambien son tablas comunes de sincronizacion bidirecciones entre level y omt
        $structureEntityDirByTableMap['omt_menu_category'] = "OmtRestaurantMenuCategory";
        $structureEntityDirByTableMap['omt_combo_product_size'] = "OmtComboProductSize";
        $structureEntityDirByTableMap['omt_conf_rangedelivery'] = "OmtDeliveryRange";
        $structureEntityDirByTableMap['omt_restaurant_menu'] = "OmtRestaurantMenu";
        $structureEntityDirByTableMap['omt_relational_restaurant_menu_category'] = "OmtRelationalRestaurantMenuCategory";
        $structureEntityDirByTableMap['omt_restaurant_menu_schedule'] = "OmtRestaurantMenuSchedule";
        $structureEntityDirByTableMap['omt_relational_restaurant_menu_schedule'] = "OmtRelationalRestaurantMenuSchedule";
        $structureEntityDirByTableMap['omt_restaurant_category_service_status_time'] = "OmtRestaurantCategoryServiceStatusTime";
        $structureEntityDirByTableMap['omt_restaurant_order_service_status_types'] = "OmtRestaurantOrderServiceStatusTypes";
        $structureEntityDirByTableMap['omt_restaurant_merchant_account'] = "OmtRestaurantMerchantAccount";
        $structureEntityDirByTableMap['omt_product'] = "OmtProduct";
        $structureEntityDirByTableMap['omt_product_modifier'] = "OmtProductModifier";
        $structureEntityDirByTableMap['omt_product_size'] = "OmtProductSize";
        $structureEntityDirByTableMap['omt_product_tax'] = "OmtProductTax";
        $structureEntityDirByTableMap['omt_tax'] = "OmtTax";
        $structureEntityDirByTableMap['omt_modifiers_configuration'] = "OmtModifiersConfiguration";
        $structureEntityDirByTableMap['omt_opening_date'] = "OmtOpeningDate";
        $structureEntityDirByTableMap['omt_opening_hour'] = "OmtOpeningHour";
        $structureEntityDirByTableMap['omt_restaurant'] = "OmtRestaurant";
        $structureEntityDirByTableMap['omt_restaurant_phone'] = "OmtRestaurantPhone";
        $structureEntityDirByTableMap['omt_restaurant_email'] = "OmtRestaurantEmail";
        $structureEntityDirByTableMap['omt_restaurant_address'] = "OmtRestaurantAddress";
        $structureEntityDirByTableMap['omt_restaurant_configuration'] = "OmtRestaurantConfiguration";
        $structureEntityDirByTableMap['omt_account'] = "OmtAccount";
        $structureEntityDirByTableMap['omt_account_address'] = "OmtAccountAddress";
        $structureEntityDirByTableMap['omt_coupon_buy_product'] = "OmtCouponBuyProduct";
        $structureEntityDirByTableMap['omt_coupon_discount_product'] = "OmtCouponDiscountProduct";
        $structureEntityDirByTableMap['omt_order_status'] = "OmtOrderServiceStatus";
////// TABLAS COMUNES DE SINCRONIZACION BIDIRECCIONES ENTRE LEVEL Y OMT RELACIONADAS A LAS VENTAS
////// TABLAS COMUNES DE SINCRONIZACION BIDIRECCIONES ENTRE LEVEL Y OMT RELACIONADAS A LAS VENTAS
////// TABLAS COMUNES DE SINCRONIZACION BIDIRECCIONES ENTRE LEVEL Y OMT RELACIONADAS A LAS VENTAS
////// TABLAS COMUNES DE SINCRONIZACION BIDIRECCIONES ENTRE LEVEL Y OMT RELACIONADAS A LAS VENTAS
////// TABLAS COMUNES DE SINCRONIZACION BIDIRECCIONES ENTRE LEVEL Y OMT RELACIONADAS A LAS VENTAS
////// TABLAS COMUNES DE SINCRONIZACION BIDIRECCIONES ENTRE LEVEL Y OMT RELACIONADAS A LAS VENTAS
////// TABLAS COMUNES DE SINCRONIZACION BIDIRECCIONES ENTRE LEVEL Y OMT RELACIONADAS A LAS VENTAS
        $structureEntityDirByTableMap['omt_inv_regispays'] = "OmtInvoiceRegistPays"; //
        $structureEntityDirByTableMap['omt_invoice_typepay'] = "OmtInvoiceTypePay";
        $structureEntityDirByTableMap['omt_invoice_taxes'] = "OmtInvoiceTaxes";
        $structureEntityDirByTableMap['omt_order_detail_modifier'] = "OmtOrderDetailModifier";
        $structureEntityDirByTableMap['omt_invoice'] = "OmtInvoice"; //verificar
        $structureEntityDirByTableMap['omt_delivery'] = "OmtDelivery";
        $structureEntityDirByTableMap['omt_order'] = "OmtOrder"; //
        $structureEntityDirByTableMap['omt_order_detail'] = "OmtOrderDetail"; //
        $structureEntityDirByTableMap['omt_order_detail_taxes'] = "OmtOrderDetailTax";
        $structureEntityDirByTableMap['omt_order_detail_combo'] = "OmtOrderDetailCombo";
        $structureEntityDirByTableMap['omt_order_detail_refund'] = "OmtOrderDetailRefund";
        $structureEntityDirByTableMap['omt_order_detail_tax_refund'] = "OmtOrderDetailTaxRefund";
        $structureEntityDirByTableMap['omt_item_type'] = "OmtItemType";
        $structureEntityDirByTableMap['omt_void_refund'] = "OmtOrderRefund";
        $structureEntityDirByTableMap['omt_adminapprove'] = "OmtAdminApprove";
        $structureEntityDirByTableMap['omt_modifiers_configuration'] = "OmtModifiersConfiguration";
        $structureEntityDirByTableMap['omt_cancelation_codes'] = "OmtCancelationCodes";
        $structureEntityDirByTableMap['omt_coupon'] = "OmtCoupon";
        $structureEntityDirByTableMap['omt_coupon_discount'] = "OmtCouponDiscount";
        $structureEntityDirByTableMap['omt_service_price'] = "OmtServicePrice";
        $structureEntityDirByTableMap['omt_brand_type_menu'] = "OmtBrandTypeMenu";
        $structureEntityDirByTableMap['omt_order_service_types'] = "OmtOrderServiceType";
        $structureEntityDirByTableMap['omt_brand_menu'] = "OmtBrandMenu";
        $structureEntityDirByTableMap['omt_category_brand_service'] = "OmtCategoryBrandService";
////////EN ESTE BLOQUE SOLO PONER TABLAS QUE SEAN PARA VENTAS
////////EN ESTE BLOQUE SOLO PONER TABLAS QUE SEAN PARA VENTAS
////////EN ESTE BLOQUE SOLO PONER TABLAS QUE SEAN PARA VENTAS
////////EN ESTE BLOQUE SOLO PONER TABLAS QUE SEAN PARA VENTAS
////////EN ESTE BLOQUE SOLO PONER TABLAS QUE SEAN PARA VENTAS
        if ($getTableNameForLicensor) {
            $structureEntityDirByTableMap = array_flip($structureEntityDirByTableMap);
        }

        if (isset($structureEntityDirByTableMap[$tableName])) {
            if (!$getTableNameForLicensor) {
                return $structureBaseDir . $structureEntityDirByTableMap[$tableName];
            } else {
                return $structureEntityDirByTableMap[$tableName];
            }
        } else {
            return null;
        }
    }

    /**
     * Funcion encargada en recibir el nombre de la tabla android para retornar
     * el array con la posicion del nombre omt el cual contiene
     * el string traducido del bundle donde se encuentra esa entidad
     * @author frcho
     * @param type $tableName
     * @return string
     */
    public static function getAndroidTableNameForOutcomingSync($tableName, $getTableNameForLicensor = false) {
        $structureBaseDir = "KijhoOMT:";
        $structureEntityDirByTableMap = [];

        /**
         *  solo se tienen las tablas a sincronizar con android
         */
        $structureEntityDirByTableMap['invoice_pay_record'] = "OmtInvoiceRegistPays";
        $structureEntityDirByTableMap['invoice_type_pay'] = "OmtInvoiceTypePay";
        $structureEntityDirByTableMap['invoice_tax'] = "OmtInvoiceTaxes";
        $structureEntityDirByTableMap['order_detail_modifier'] = "OmtOrderDetailModifier"; ///
        $structureEntityDirByTableMap['invoice'] = "OmtInvoice";
        $structureEntityDirByTableMap['delivery'] = "OmtDelivery";
        $structureEntityDirByTableMap['orders'] = "OmtOrder";
        $structureEntityDirByTableMap['order_detail'] = "OmtOrderDetail";
        $structureEntityDirByTableMap['order_detail_taxes'] = "OmtOrderDetailTax";
        $structureEntityDirByTableMap['order_detail_combo'] = "OmtOrderDetailCombo"; ///
        $structureEntityDirByTableMap['order_detail_refund'] = "OmtOrderDetailRefund"; ///
        $structureEntityDirByTableMap['order_detail_tax_refund'] = "OmtOrderDetailTaxRefund"; ///
//        $structureEntityDirByTableMap['item_type'] = "OmtItemType";
        $structureEntityDirByTableMap['void_refund'] = "OmtOrderRefund"; ///
        $structureEntityDirByTableMap['approval'] = "OmtAdminApprove"; ///
//        $structureEntityDirByTableMap['config_modifiers'] = "OmtModifiersConfiguration";
        $structureEntityDirByTableMap['customer'] = "OmtClient"; ///
        $structureEntityDirByTableMap['cancelation_codes'] = "OmtCancelationCodes";
        $structureEntityDirByTableMap['coupon'] = "OmtCoupon";
        $structureEntityDirByTableMap['coupon_discount'] = "OmtCouponDiscount";

        if ($getTableNameForLicensor) {
            $structureEntityDirByTableMap = array_flip($structureEntityDirByTableMap);
        }

        if (isset($structureEntityDirByTableMap[$tableName])) {
            if (!$getTableNameForLicensor) {
                return $structureBaseDir . $structureEntityDirByTableMap[$tableName];
            } else {
                return $structureEntityDirByTableMap[$tableName];
            }
        } else {
            return null;
        }
    }

    /**
     * @param type $secret_key
     */
    private function sureOf16BytesKey($secret_key) {
        $key_len = strlen($secret_key);
        if ($key_len < 16) {
            $addS = 16 - $key_len;
            for ($i = 0; $i < $addS; ++$i) {
                $secret_key .= " ";
            }
        } else {
            $secret_key = substr($secret_key, 0, 16);
        }

        return $secret_key;
    }

    /**
     * @param type $em
     * @param type $arrName
     * @param type $needExpectedFile
     * @param type $nickname
     * @return type
     */
    public function fileJsonFromSelectEntities($em, $arrName, $needExpectedFile = false, $nickname = '') {
        $arrayResponse = [];

        $contador = 0;

        $arrayResponse['msg'] = '';
        $arrayResponse['error'] = false;
        $arrayResponse['status'] = WebService::CODE_OK;
        $arrayResponse['urlBase'] = null;

        array_unshift($arrName, ['tname' => 'x', 'idfrom' => null]);

        $arrDataExport = [];
        $expectedLoginResponse = [];

        /*
         * se recorren todos los nombres BD-omt solicitados
         * y se pasan a nombres BD-Level
         */
        while (next($arrName) !== false) {

            /*
             * se analiza si se desea buscar registros superiores a un Id dado
             */
            $responseQuery = [];

            $tName = '';
            $idFrom = null;

            if (isset(current($arrName)['tname']) && (current($arrName)['tname'] != '' &&
                    current($arrName)['tname'] != null)) {
                $tName = current($arrName)['tname'];
                $this->custom->addInfo($this->channel
                        , 'SyncCommonService', [
                    'CONDITIONAL TNAME NULL' => $tName,
                ]);
                $tNameForLicensor = $this->getEntityForIncomignLevelOmtSync(
                        current($arrName)['tname'], true);
            } else {
                $arrayResponse['msg'] = 'Error in json format, tname mising!';
                $arrayResponse['status'] = WebService::CODE_INTERNAL_ERROR;
                $arrayResponse['urlBase'] = null;

                return $arrayResponse;
            }

            if (isset(current($arrName)['idfrom']) && (current($arrName)['idfrom'] != '' || current($arrName)['idfrom'] == null)) {
                $idFrom = current($arrName)['idfrom'];
            }
            $this->custom->addInfo($this->channel
                    , 'SyncCommonService', [
                'Table' => $tName,
                'idFrom' => $idFrom,
                'nickname' => $nickname
            ]);
            if ($tName) {

                $actualRepo = $em->getRepository('KijhoOMT:' . $tName);
                $responseQuery = $actualRepo->getSyncData($idFrom, '=', true, '', $nickname);
                $this->custom->addInfo($this->channel
                        , 'SyncCommonService', [
                    'resutl' => json_encode($responseQuery)
                ]);
            }

            $contador += count($responseQuery);

            foreach ($responseQuery as $registToInsertInit) {
                $tableRegistStd = new stdClass();

                if ($needExpectedFile && isset($expectedLoginResponse[$tNameForLicensor])) {
                    $expectedLoginResponse[$tNameForLicensor][$registToInsertInit['id']] = $registToInsertInit['id'];
                } elseif ($needExpectedFile) {
                    $expectedLoginResponse[$tNameForLicensor] = [$registToInsertInit['id'] => $registToInsertInit['id']];
                }

                if ($tNameForLicensor) {
                    $tableRegistStd->action = 'I';
                    $tableRegistStd->table = $tNameForLicensor;
                    if (isset($registToInsertInit['level']) && !empty($registToInsertInit['level'])) {
                        $tableRegistStd->level = $registToInsertInit['level'];
                    } else {
                        $tableRegistStd->level = $registToInsertInit['id'];
                    }
                    $tableRegistStd->id = $registToInsertInit['id'];

                    $theObject = array_map("utf8_encode", $registToInsertInit);
                    $tableRegistStd->fields = [$theObject];

                    array_push($arrDataExport, $tableRegistStd);
                }
            }
        }

        return [$arrDataExport, $expectedLoginResponse];
    }

    /**
     * funcionalidad que crea array con las entidades que se van a usar
     * para sincronizacion y login inicial
     * @param type $isForInitialExprot si esta variable se envia como true
     * es para exportar a level, de lo contrario es para sincronizacion.
     * @return array
     */
    public function syncArray($isForInitialExprot = false) {
        /**
         * tablas de menu y configuracion de restaurante
         */
        $object = [
            ['tname' => "OmtRestaurantMenuCategory", 'idfrom' => null],
            ['tname' => "OmtComboProductSize", 'idfrom' => null],
            ['tname' => "OmtDeliveryRange", 'idfrom' => null],
            ['tname' => "OmtRestaurant", 'idfrom' => null],
            ['tname' => "OmtRestaurantMenu", 'idfrom' => null],
            ['tname' => "OmtRestaurantPhone", 'idfrom' => null],
            ['tname' => "OmtRestaurantEmail", 'idfrom' => null],
            ['tname' => "OmtRestaurantAddress", 'idfrom' => null],
            ['tname' => "OmtRestaurantConfiguration", 'idfrom' => null],
            ['tname' => "OmtRestaurantMerchantAccount", 'idfrom' => null],
            ['tname' => "OmtRelationalRestaurantMenuCategory", 'idfrom' => null],
            ['tname' => "OmtProduct", 'idfrom' => null],
            ['tname' => "OmtCoupon", 'idfrom' => null],
            ['tname' => "OmtCouponDiscount", 'idfrom' => null],
            ['tname' => "OmtProductModifier", 'idfrom' => null],
            ['tname' => "OmtProductSize", 'idfrom' => null],
            ['tname' => "OmtProductTax", 'idfrom' => null],
            ['tname' => "OmtTax", 'idfrom' => null],
            ['tname' => "OmtOpeningDate", 'idfrom' => null],
            ['tname' => "OmtOpeningHour", 'idfrom' => null],
            ['tname' => "OmtItemType", 'idfrom' => null],
            ['tname' => "OmtModifiersConfiguration", 'idfrom' => null],
            ['tname' => "OmtServicePrice", 'idfrom' => null],
            ['tname' => "OmtBrandTypeMenu", 'idfrom' => null],
            ['tname' => "OmtBrandMenu", 'idfrom' => null],
            ['tname' => "OmtCategoryBrandService", 'idfrom' => null],
            ['tname' => "OmtOrderServiceType", 'idfrom' => null],
            ['tname' => "OmtAccount", 'idfrom' => null],
            ['tname' => "OmtAccountAddress", 'idfrom' => null]
        ];
        /**
         * Esas son solo las tablas relacionadas con ventas 
         * si se requiere sincronizar una tabla relacionada con ventas, favorr
         * ponerla en este arreglo.
         */
        if (!$isForInitialExprot) {
            $object = $this->syncArrayAndroidTables($object);
        }

        return $object;
    }

    public function syncArrayAndroidTables($object = []) {
        array_push($object, ['tname' => "OmtInvoiceRegistPays", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtInvoiceTypePay", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtInvoiceTaxes", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrderDetailModifier", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtInvoice", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtDelivery", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrder", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrderDetail", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrderDetailTax", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrderDetailCombo", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrderDetailRefund", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrderDetailTaxRefund", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtOrderRefund", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtAdminApprove", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtClient", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtCouponDiscount", 'idfrom' => null]);
        array_push($object, ['tname' => "OmtCancelationCodes", 'idfrom' => null]);

        return $object;
    }

    /**
     * Lo ultimo en guarachas!
     * @author Aealan Z <lrobledo@kijho.com> 02/09/2016
     * @param type $container
     * @param type $em
     * @param type $dataInMessage
     * @param type $syncRecord
     * @param type $log
     * @param type $messagePurpose
     * @param type $messageType
     * @param type $sendingAgain
     * @return type
     */
    public static function sendSuperMessage(
    $container, $em, $dataInMessage, $syncRecord, $log, $messagePurpose, $messageType, $sendingAgain = null, $justSaveCurlMessage = true, $path = ''
    ) {

        $log->addNotice('SendSuperPush', ['Initialazing' => 'Starting validations to send message...']);

        $theMessageKey = static::getValidCodeForPush($em, $sendingAgain);

        if (!$sendingAgain) {
            $messageEntity = new SyncSendedMessage();
        } else {
            $messageEntity = $sendingAgain;
            $theMessageKey = $messageEntity->getVerificationCode();
        }

        if ($syncRecord instanceof Entity\SyncMainRecord && $syncRecord->getApplicationMode() && $messageType == SyncSendedMessage::MESSAGE_TYPE_IS_PUSH) {
            $pushVerificationCode = static::createRegistPushSend($em, $messageEntity, $messagePurpose, $theMessageKey, $syncRecord, true, false);
        } elseif ($syncRecord instanceof Entity\SyncMainRecord && $syncRecord->getApplicationMode() && $messageType == SyncSendedMessage::MESSAGE_TYPE_IS_CURL) {
            $pushVerificationCode = static::createRegistPushSend($em, $messageEntity, $messagePurpose, $theMessageKey, $syncRecord, true, true);
        } elseif ($messageType == SyncSendedMessage::MESSAGE_TYPE_IS_PUSH) {
            $pushVerificationCode = static::createRegistPushSend($em, $messageEntity, $messagePurpose, $theMessageKey, $syncRecord, false, false);
        } else {
            $pushVerificationCode = static::createRegistPushSend($em, $messageEntity, $messagePurpose, $theMessageKey, $syncRecord, false, true);
        }

        if ($pushVerificationCode && $pushVerificationCode != '') {
            $log->addNotice('sendSuperPush', ['Initialazing' => 'Push send record created!']);
            if (is_array($dataInMessage) && $messageEntity->getVerificationCode()) {
                $dataInMessage["code"] = $messageEntity->getVerificationCode();
                $dataInMessage["omt"] = 1;
            }
        } else {
            $log->addNotice('sendSuperPush', ['ERROR' => $pushVerificationCode]);
        }

        if (is_array($dataInMessage)) {
            $dataToSend = json_encode($dataInMessage);
        } else {
            $dataToSend = $dataInMessage;
        }

        $log->addNotice('sendSuperPush', ['Verificating' => 'Unencripted data to push: ' . $dataToSend]);

        if ($syncRecord instanceof Entity\SyncMainRecord && $syncRecord->getApplicationMode()) {
            $log->addNotice('encryptDataForApp', ['encripting' => 'Aca llego un changuito ']);
            $theEncriptedArray['data'] = static::encryptDataAppAlgorithm($dataToSend, $log);
        } else {
            $log->addNotice('encryptDataForApp', ['encripting' => 'El changuito no se perdio!']);
            $theEncriptedArray['data'] = $dataToSend;
        }

        $log->addNotice('encryptDataForApp', ['encripting' => 'Y aqui ya casi llego!']);

        if (is_array($dataInMessage)) {
            $dataInMessage = json_encode($theEncriptedArray);
        } else {
            $dataInMessage = $dataInMessage;
            $theEncriptedArray = $dataInMessage;
        }

        $log->addNotice('sendSuperPush', ['Preparing' => 'Data in push: ' . $dataInMessage]);

        $dataSetted = static::setDataInMessageRegist($em, $dataInMessage, $pushVerificationCode);

        $log->addNotice('sendSuperPush', ['Preparing' => 'Data in push setted result: ' . $dataSetted]);
        $log->addNotice('sendSuperPush', ['Preparing' => 'Encripted data to push: ' . json_encode($theEncriptedArray)]);

        if ($syncRecord instanceof Entity\SyncMainRecord && null != $syncRecord->getRestaurant()->getPushyKey() && $syncRecord->getRestaurant()->getPushyKey() != '' && $messageType == SyncSendedMessage::MESSAGE_TYPE_IS_PUSH) {
            $pushyKeyToPush = $syncRecord->getRestaurant()->getPushyKey();
        } elseif ($syncRecord instanceof Entity\OmtRestaurant) {
            $pushyKeyToPush = $syncRecord->getPushyKey();
        } else {
            $pushyKeyToPush = null;
        }

        $wasAPushyPushSend = false;
        if ($pushyKeyToPush) {
            $data = ['kijho-level-data' => $dataInMessage];
            $log->addNotice('sendSuperPush', ['Sending' => 'Sending to Pushy with key: ' . $syncRecord->getRestaurant()->getPushyKey()]);

            $wasAPushyPushSend = true;
            SyncUtil::sendPushyPushNotification($container, $data, $pushyKeyToPush, $syncRecord->getRestaurant()->getLevelDbNickname(), $log, true);

            if ($path != '') {
                static::createFileJson($path, "Async call to Pushy: " . json_encode($data) . "\r");
            }
        } elseif ($syncRecord instanceof Entity\SyncMainRecord && $messageType == SyncSendedMessage::MESSAGE_TYPE_IS_PUSH) {
            $log->addNotice('sendSuperPush', ['Sending' => 'Sending to PubNub with channel: ' . $syncRecord->getRestaurant()->getLevelDbNickname()]);
            SyncUtil::sendPubNubPushNotification($container, $syncRecord->getRestaurant()->getLevelDbNickname(), $theEncriptedArray, $log, true);

            if ($path != '') {
                static::createFileJson($path, "Async call to PubNub: " . json_encode($data) . "\r");
            }

            $syncRecord->setSyncMessage($messageEntity);

            $em->persist($syncRecord);
            $em->flush();
        } elseif ($syncRecord instanceof Entity\OmtRestaurant && $messageType == SyncSendedMessage::MESSAGE_TYPE_IS_PUSH) {
            $log->addNotice('sendSuperPush', ['Sending' => 'Sending to PubNub with channel: ' . $syncRecord->getLevelDbNickname()]);
            SyncUtil::sendPubNubPushNotification($container, $syncRecord->getLevelDbNickname(), $theEncriptedArray, $log, true);
        }
        
//        if ($wasAPushyPushSend) {
//            sleep(rand(3, 7));
//        }

//        if ($syncRecord instanceof Entity\SyncMainRecord && $messageType == SyncSendedMessage::MESSAGE_TYPE_IS_PUSH) {
//            $log->addNotice('sendSuperPush', ['Sending' => 'Sending to PubNub with channel: ' . $syncRecord->getRestaurant()->getLevelDbNickname()]);
//
//            SyncUtil::sendPubNubPushNotification($container, $syncRecord->getRestaurant()->getLevelDbNickname(), $theEncriptedArray, $log, true);
//
//            if ($path != '') {
//                static::createFileJson($path, "Async call to PubNub: " . json_encode($data) . "\r");
//            }
//
//            $syncRecord->setSyncMessage($messageEntity);
//
//            $em->persist($syncRecord);
//            $em->flush();
//        } elseif ($syncRecord instanceof Entity\OmtRestaurant && $messageType == SyncSendedMessage::MESSAGE_TYPE_IS_PUSH) {
//            $log->addNotice('sendSuperPush', ['Sending' => 'Sending to PubNub with channel: ' . $syncRecord->getLevelDbNickname()]);
//            SyncUtil::sendPubNubPushNotification($container, $syncRecord->getLevelDbNickname(), $theEncriptedArray, $log, true);
//        } elseif (!$justSaveCurlMessage) {
//            //////// comando asincrono para enviar los curl de sincronizacion
//        }

        return ['messageInPush' => $dataInMessage, 'entityId' => $theMessageKey, 'messageEntity' => $messageEntity];
    }

    /**
     * @param type $path
     * @param type $text
     */
    private function createFileJson($path, $text) {
        $file = fopen($path, "a+");
        fwrite($file, $text);
        fclose($file);
    }

    /**
     * @param type $em
     * @param type $sendingAgain
     * @return type
     */
    public static function getValidCodeForPush($em, $sendingAgain) {
        $pushWithSameKey = ['foo' => 'bar'];
        while (!empty($pushWithSameKey) && !$sendingAgain) {
            $theMessageKey = static::randomPushKey();
            $pushWithSameKey = $em->getRepository('KijhoOMT:SyncSendedMessage')->findBy(['verificationCode' => $theMessageKey]);
        }

        return $theMessageKey;
    }

    /**
     * @param type $em
     * @param type $sendedMessage
     * @return type
     * Servicio que refresca el codigo de un push para un reenvio forzado
     */
    public static function refreshVerificationCodeForPushResend($em, $sendedMessage) {
        $messageData = json_decode($sendedMessage->getDataInMessage(), true);

        if (!isset($messageData['data'])) {
            return null;
        } else {
            $messageData = json_decode($messageData['data'], true);
        }

        if (isset($messageData['code'])) {
            $newMessageCodeHistory = new Entity\SendedMessageForcedPushCodeHistory();

            $newMessageCodeHistory->setSyncMessage($sendedMessage);
            $newMessageCodeHistory->setCreatedAndReplacedDate(Util::getCurrentDate());
            $newMessageCodeHistory->setReplacedVerificationCode($sendedMessage->getVerificationCode());

            $em->persist($newMessageCodeHistory);
            $em->flush();

            $theMessageKey = static::getValidCodeForPush($em, null);

            $messageData['code'] = $theMessageKey;

            $sendedMessage->setVerificationCode($theMessageKey);
            $sendedMessage->setDataInMessage(json_encode(['data' => json_encode($messageData)]));

            $em->persist($sendedMessage);
            $em->flush();

            return $sendedMessage;
        }

        return null;
    }

    /**
     * Metodo con el cual se especifica en el registro de la base de datos de
     * licensor los datos enviados en determinado push y si este fue reenviado
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $em EntityManager con los datos de conexion de la base de
     * datos de licensor
     * @param type $messageData string con el json de los datos que se enviaron
     * en el push
     * @param type $messageId string con el codigo de verificacion
     * del push enviado
     * @param type $isResendBool boleano que determina si es el reenvio de un
     * push no respondido por parte de licensor
     * @param type $path string opcional para registrar en los logs el codigo
     * del push
     * @return boolean boleano que indica si se tuvo exito en el registro del
     * push
     */
    public static function setDataInMessageRegist($em, $messageData, $messageId, $isResendBool = null) {

        $messageRecord = $em->getRepository('KijhoOMT:SyncSendedMessage')->find($messageId);

        if ($messageRecord) {
            $messageRecord->setDataInMessage($messageData);

            if ($isResendBool) {
                $messageRecord->setResendTimes(((int) $messageRecord->getResendTimes()) + 1);
            }

            $em->merge($messageRecord);

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
     * Metodo para la generacion del codigo de verificacion de un push el cual
     * por defecto es de 16 caracteres alfanumericos
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param type $number cantidad de caracteres alfanumericos para el codigo
     * @return string codigo de verificacion generado aleatoriamente
     */
    public static function randomPushKey($number = 16) {
        $alphabet = "abcdefghijkmnpqrstuwxyzABCDEFGHIJKLMNPQRSTUWXYZ23456789";
        /*
         * remember to declare $pass as an array
         */
        $pass = [];
        /*
         * put the length -1 in cache
         */
        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < $number; ++$i) {
            $n = mt_rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        /*
         * turn the array into a string
         */
        return implode($pass);
    }

    /**
     * Metodo para crear el registro de un push enviado por licensor en la
     * base de datos, este metodo requiere que se le proporcione la entidad
     * del registro del push ya creada ademas de que se le envien los diversos
     * parametros que especifican toda la informacion necesaria a registrar en
     * el envio del push
     * @param type $em
     * @param SyncSendedMessage $messageEntity
     * @param type $messagePurpose
     * @param type $theMessageKey
     * @param type $syncRecord
     * @param type $boolAppMode
     * @param type $isCurl
     * @return type
     */
    public static function createRegistPushSend($em, $messageEntity, $messagePurpose, $theMessageKey, $syncRecord, $boolAppMode = false, $isCurl = true) {

        $actualDate = Util::getCurrentDate();

        $messageEntity->setSentDate($actualDate);
        $messageEntity->setApplicationMode($boolAppMode);
        $messageEntity->setMessagePurpose($messagePurpose);
        $messageEntity->setVerificationCode($theMessageKey);
        if (is_string($actualDate)) {
            $messageEntity->setRestaurant($syncRecord);
        } else {
            $messageEntity->setRestaurant($syncRecord->getRestaurant());
        }

        if ($isCurl) {
            $messageEntity->setMessageType(SyncSendedMessage::MESSAGE_TYPE_IS_CURL);
        } else {
            $messageEntity->setMessageType(SyncSendedMessage::MESSAGE_TYPE_IS_PUSH);
        }
        $messageEntity->setMessageStatus(SyncSendedMessage::STATUS_MESSAGE_PENDING);

        $em->persist($messageEntity);
        try {
            $em->flush();
            return $messageEntity->getId();
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

}
