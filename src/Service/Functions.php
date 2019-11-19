<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Service\CustomLog;

class Functions {

    protected $container;

    /**
     * Este es de la clase version
     * @author- Luis Fernando Granados Kijho Technologies <lgranados@kijho.com> 03/05/2017
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    function __construct(ContainerInterface $container, CustomLog $customLog) {
        $this->container = $container;
        $this->custom = $customLog;
        $this->channel = $this->custom->createChannel('notify');
    }

    public function container() {
        $container = $this->container;
        return $container;
    }

    /**
     * Funcion para crear obtener el path del directorio donde esta el archivo 
     * con la informacion a sincronizar para cierto restaurante, 
     * el directorio tendra por nombre el nick del restaurante
     * @param type boolean|string
     */
    public function existOrCreateDir($name, $logChannel = null) {

        if ($logChannel) {
            $this->channel = $logChannel;
        }

        try {
            $path = $this->container->getParameter('app_directory_uploads') . $name;
            $this->custom->addInfo($this->channel
                    , 'Functions::existOrCreateDir', [
                'msg' => $name
            ]);

            $fs = new Filesystem();

            if (is_dir($path)) {
                $this->custom->addInfo($this->channel
                        , 'Functions::existOrCreateDir', [
                    'msg' => 'IS DIR'
                ]);
                if (is_writable($path)) {
                    $this->custom->addInfo($this->channel
                            , 'Functions::existOrCreateDir', [
                        'msg' => 'IS WRITE',
                        'path' => $path
                    ]);
                    return $path;
                } else {
                    $this->custom->addInfo($this->channel
                            , 'Functions::existOrCreateDir', [
                        'msg' => 'CHMOD'
                    ]);
                    $fs->chmod($path, 0755);

                    return $path;
                }
            } else {
                $this->custom->addInfo($this->channel
                        , 'Functions::existOrCreateDir', [
                    'msg' => 'ELSE'
                ]);
                if (!$fs->exists($path)) {
                    $this->custom->addInfo($this->channel
                            , 'Functions::existOrCreateDir', [
                        'msg' => '!$fs->exists($path)'
                    ]);
                    $fs->mkdir($path, 0755);
                    return $path;
                }
            }
            return false;
        } catch (\Exception $exc) {
            $this->custom->addInfo($this->channel
                    , 'existOrCreateDir', [
                'msg' => $exc->getMessage()
            ]);
            return false;
        }
    }

    /**
     * @param type $container
     */
    public function doOMTClientLoginToGetToken($container, $requestToWSLogin, $logChannel = null) {

        if ($logChannel) {
            $this->channel = $logChannel;
        }
        $urlLogin = $container->getParameter('app_scheme') . '://' . $container->getParameter('app_host') . '/cli/login';

        $agent = 'OMT/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';

        $this->custom->addInfo($this->channel
                , 'Functions::doOMTLoginToGetToken', [
            'msg' => "Ready to send login request to OM",
            'login' => $urlLogin,
            'array' => $requestToWSLogin
        ]);
        $chLogin = curl_init();
        curl_setopt($chLogin, CURLOPT_URL, $urlLogin);
        curl_setopt($chLogin, CURLOPT_POST, true);
        curl_setopt($chLogin, CURLOPT_USERAGENT, $agent);
        curl_setopt($chLogin, CURLOPT_HTTPHEADER, ["Accept: application/json", "Accept-Language: es", "Origin: omt"]);
        curl_setopt($chLogin, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chLogin, CURLOPT_POSTFIELDS, json_encode($requestToWSLogin));
        $loginResult = curl_exec($chLogin);

        $loginResultArray = json_decode($loginResult, true);
        $this->custom->addInfo($this->channel
                , 'Functions::doOMTLoginToGetToken', [
            'OMT login response' => $loginResult
        ]);

        if (isset($loginResultArray['data']) && isset($loginResultArray['data']['mobile_sessions']) && isset($loginResultArray['data']['mobile_sessions'][0]) && isset($loginResultArray['data']['mobile_sessions'][0]['apiKey'])) {
            return $loginResultArray;
        }
    }

}
