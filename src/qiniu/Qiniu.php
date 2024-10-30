<?php
// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------

namespace xzncit\qiniu;

use xzncit\OSS;
use xzncit\exception\ConfigNotFoundException;
use xzncit\exception\OSSException;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Qiniu {

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
        if(empty($options["accessKey"])){
            throw new ConfigNotFoundException("Qiniu Kodo【accessKey】字段不能为空");
        }

        if(empty($options["secretKey"])){
            throw new ConfigNotFoundException("Qiniu Kodo【secretKey】字段不能为空");
        }

        $this->config    = [
            "accessKey"     => $options["accessKey"],
            "secretKey"     => $options["secretKey"],
            "domain"        => $options["domain"],
            "bucket"        => $options["bucket"],
        ];

        $this->client    = new Auth($options["accessKey"], $options["secretKey"]);
    }

    /**
     * 上传
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    public function upload($path){
        $token = $this->client->uploadToken($this->config["bucket"]);

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, trim($path,"/"), $path);

        if ($err !== null) {
            throw new OSSException($err,0);
        }

        $this->info = $this->config["domain"] . "/" . trim($ret["key"],"/");
        return $this->info;
    }

    /**
     * 获取文件路径
     * @return string
     * @throws \Exception
     */
    public function getFileInfo(){
        if(empty($this->info)){
            throw new OSSException("您要查找的内容不存在");
        }

        return $this->info;
    }

    /**
     * 下载
     * @param $object
     * @return string
     */
    public function download($object){
        return $this->client->privateDownloadUrl($object);
    }

    /**
     * 删除
     * @param $object
     * @return object
     */
    public function remove($object){
        $config = new Config();
        $bucketManager = new BucketManager($this->client, $config);
        return $bucketManager->delete($this->bucket, $object);
    }

    /**
     * 获取文件路径
     * @param string $image
     * @return string
     */
    public function getUrl($image=""){
        return OSS::getUrl("qiniu",$image,[
            "domain"    => $this->config["domain"]
        ]);
    }

    /**
     * @return string
     */
    public function getType(){
        return "qiniu";
    }

}