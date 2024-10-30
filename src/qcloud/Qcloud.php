<?php
// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------

namespace xzncit\qcloud;

use Qcloud\Cos\Client;

use xzncit\exception\ConfigNotFoundException;
use xzncit\exception\OSSException;
use xzncit\OSS;

/**
 * @package xzncit\qcloud
 * @class Qcloud
 * @author xzncit 2023-08-26
 */
class Qcloud {

    /**
     * @var Client|null
     */
    private $client = null;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $info = [];

    /**
     * Qcloud constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct($options=[]){
        if(empty($options["secretId"])){
            throw new ConfigNotFoundException("TencentCos【secretId】字段不能为空");
        }

        if(empty($options["secretKey"])){
            throw new ConfigNotFoundException("TencentCos【secretKey】字段不能为空");
        }

        $this->config = [
            "secretId"      => $options["secretId"],
            "secretKey"     => $options["secretKey"],
            "region"        => $options["endpoint"],
            "bucket"        => $options["bucket"],
            "endpoint"      => "https://" . $options["bucket"] . ".cos." . $options["endpoint"] . ".myqcloud.com"
        ];

        $this->client    = new Client([
            'region'        => $options["endpoint"],
            // 'schema'        => 'https', //协议头部，默认为http
            'credentials'   => [
                'secretId'  => $options["secretId"],
                'secretKey' => $options["secretKey"]
            ]
        ]);
    }

    /**
     * 上传
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    public function upload($path){
        $file = fopen($path, "rb");
        if(!$file){
            throw new OSSException("文件不存在",0);
        }

        $this->info = $this->client->putObject([
            'Bucket' => $this->config["bucket"],
            'Key'    => trim($path,"/"),
            'Body'   => $file
        ]);

        return $this->info["Location"];
    }

    /**
     * 获取文件路径
     * @return string
     * @throws \Exception
     */
    public function getFileInfo(){
        if(empty($this->info["Location"])){
            throw new OSSException("您要查找的内容不存在",0);
        }

        return "https://".str_replace(["https://","http://"],"",$this->info["Location"]);
    }

    /**
     * 下载
     * @param $object
     * @return object|void
     */
    public function download($object){
        $printbar = function($totalSize, $downloadedSize) {
            printf("downloaded [%d/%d]\n", $downloadedSize, $totalSize);
        };

        $result = $this->client->download(
            $bucket     = $this->config["bucket"], //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
            $key        = $object["key"],
            $saveAs     = $object["path"],
            $options    = [
                "Progress"              => $printbar, //指定进度条
                "PartSize"              => 10 * 1024 * 1024, //分块大小
                "Concurrency"           => 5, //并发数
                "ResumableDownload"     => true, //是否开启断点续传，默认为false
                "ResumableTaskFile"     => "tmp.cosresumabletask" //断点文件信息路径，默认为<localpath>.cosresumabletask
            ]
        );

        return $result;
    }

    /**
     * 删除
     * @param $object
     * @return object
     */
    public function remove($object){
        return $this->client->deleteObject([
            'Bucket' => $this->config["bucket"],
            'Key'    => $object
        ]);
    }

    /**
     * 获取文件路径
     * @param string $image
     * @return string
     */
    public function getUrl($image=""){
        return OSS::getUrl("qcloud",$image,[
            "domain"    => $this->config["domain"],
            "bucket"    => $this->config["bucket"],
            "endpoint"  => $this->config["endpoint"]
        ]);
    }

    /**
     * @return string
     */
    public function getType(){
        return "qcloud";
    }

}