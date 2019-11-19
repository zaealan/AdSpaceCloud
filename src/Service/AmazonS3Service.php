<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;

/**
 * Description of AmazonS3Service
 * @author zaealan
 */
class AmazonS3Service {

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var S3Client
     */
    private $client;

    /**
     * Constructor de la clase
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param \App\ServicesClasses\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        if (!($container instanceof ContainerInterface)) {
            $container = $container->getServiceContainerService();
        }

        $this->setContainer($container);

        $s3Client = new S3Client([
            'version' => $this->getContainer()->getParameter('amazon_api_version'),
            'region' => $this->getContainer()->getParameter('amazon_bucket_region'),
            'credentials' => [
                'key' => $this->getContainer()->getParameter('amazon_key'),
                'secret' => $this->getContainer()->getParameter('amazon_secret'),
            ],
        ]);

        $this->setClient($s3Client);
    }

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $fileName
     * @param type $filePath
     */
    public function uploadFile($fileName, $filePath) {
        $uploader = new MultipartUploader($this->getClient(), $filePath, [
            'bucket' => $this->getContainer()->getParameter('amazon_bucket_name'),
            'key' => $fileName
        ]);

        do {
            try {
                $result = $uploader->upload();
                $presignedUrl = $this->getObjectS3AndRenewPresignedUrl($this->getContainer()->getParameter('amazon_bucket_name'), $fileName);
            } catch (MultipartUploadException $e) {
                $uploader = new MultipartUploader($this->getClient(), $filePath, [
                    'state' => $e->getState(),
                ]);
            }
        } while (!isset($result));

        return $presignedUrl;
    }

    /**
     * Funcion que genera un nuevo link actualizado por una semana para el file que ya esta en s3
     * @param type $bucket
     * @param type $keyname
     * @return type
     */
    public function getObjectS3AndRenewPresignedUrl($bucket, $keyname) {

        $getObject = $this->getClient()->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $keyname,
        ]);
        
        $request = $this->getClient()->createPresignedRequest($getObject, '+1 week');
        
        // Get the actual presigned-url
        $presignedUrl = (string) $request->getUri();

        return $presignedUrl;
    }
    
    /**
     * @param type $bucket
     * @param type $key
     * @return type
     */
    public function getObjectUrl($bucket, $key) {
        $command = $this->getClient()->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key'    => $key
        ]);

        return (string) \Aws\serialize($command)->getUri();
    }
    
    /**
     * Getter of client
     * @return S3Client
     */
    protected function getClient() {
        return $this->client;
    }

    /**
     * Setter of client
     * @param S3Client $client
     * @return $this
     */
    private function setClient(S3Client $client) {
        $this->client = $client;
        return $this;
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
     * @param \App\ServicesClasses\ContainerInterface $container
     */
    private function setContainer(ContainerInterface $container) {
        $this->container = $container;
    }

}
