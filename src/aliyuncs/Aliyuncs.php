<?php
// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------

namespace xzncit\aliyuncs;

use OSS\OssClient;
use xzncit\OSS;
use xzncit\exception\ConfigNotFoundException;
use xzncit\exception\OSSException;

/**
 * @package xzncit\aliyuncs
 * @class Aliyuncs
 * @author xzncit 2023-08-26
 */
class Aliyuncs {

    /**
     * @var OssClient|null
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

    public function __construct($options=[]){
        if(empty($options["accessKeyId"])){
            throw new ConfigNotFoundException("AliyunOss【accessKeyId】字段不能为空");
        }

        if(empty($options["accessKeySecret"])){
            throw new ConfigNotFoundException("AliyunOss【accessKeySecret】字段不能为空");
        }

        if(empty($options["endpoint"])){
            throw new ConfigNotFoundException("AliyunOss【endpoint】字段不能为空");
        }

        if(empty($options["bucket"])){
            throw new ConfigNotFoundException("AliyunOss 存储空间不能为空");
        }

        $this->config = [
            "accessKeyId"        => $options["accessKeyId"],
            "accessKeySecret"    => $options["accessKeySecret"],
            "endpoint"           => "http://" . $options["endpoint"] . ".aliyuncs.com",
            "bucket"             => $options["bucket"],
            "domain"             => $options["domain"] ?? "",
            "protocol"           => $options["protocol"]?? "auto",
        ];

        $this->client = new OssClient($this->config["accessKeyId"], $this->config["accessKeySecret"], $this->config["endpoint"]);
    }

    /**
     * 上传
     * @param $file
     * @return mixed
     */
    public function upload($file){
        $this->info = $this->client->putObject($this->config["bucket"], $file, file_get_contents($file));
        return $this->info;
    }

    /**
     * 获取文件上传信息
     * @return mixed
     * @throws \Exception
     */
    public function getFileInfo(){
        if(empty($this->info["info"]["url"])){
            throw new OSSException("您要查找的内容不存在",0);
        }

        return $this->info["info"]["url"];
    }

    /**
     * 下载
     * @param $object
     * @return mixed
     */
    public function download($object){
        return $this->client->getObject($this->config["bucket"], $object);
    }

    /**
     * 删除
     * @param $object
     * @return mixed
     */
    public function remove($object){
        return $this->client->deleteObject($this->config["bucket"], $object);
    }

    /**
     * 添加CNAME记录
     * @param $domain
     * @return null
     * @throws \OSS\Core\OssException
     */
    public function addDomain($domain){
        return $this->client->addBucketCname($this->config["bucket"], $domain);
    }

    /**
     * 查看CNAME记录
     * @return \OSS\Model\CnameConfig
     * @throws \OSS\Core\OssException
     */
    public function getDomainList(){
        return $this->client->getBucketCname($this->config["bucket"]);
    }

    /**
     * 删除CNAME记录
     * @param $domain
     * @return null
     * @throws \OSS\Core\OssException
     */
    public function deleteDomain($domain){
        return $this->client->addBucketCname($this->config["bucket"], $domain);
    }

    /**
     * 获取文件路径
     * @param string $image
     * @return string
     */
    public function getUrl($image=""){
        return OSS::getUrl("aliyuncs",$image,[
            "domain"=>$this->config["domain"],
            "protocol"=>$this->config["protocol"],
            "bucket"=>$this->config["bucket"],
            "endpoint"=>$this->config["endpoint"],
        ]);
    }

}