<?php
// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace xzncit;

use xzncit\exception\ClassNotFoundException;
use xzncit\exception\ConfigNotFoundException;
use xzncit\exception\OSSException;

/**
 * @package xzncit
 * @class OSS
 * @author xzncit 2023-08-26
 */
class OSS {

    /**
     * Current version of program
     * @var string
     */
    public static $version = "1.0.4";

    /**
     * @param $name
     * @param array $options
     * @return mixed
     * @throws ClassNotFoundException
     * @throws ConfigNotFoundException
     */
    public static function create($name,$options=[]){
        $obj = "\\xzncit\\" . strtolower($name) . "\\" . ucfirst($name);

        if(!class_exists($obj)){
            throw new ClassNotFoundException("class [$name] does not exist",0);
        }

        if(empty($options)){
            throw new ConfigNotFoundException("config does not exist",0);
        }

        return new $obj($options);
    }

    /**
     * 获取已上传云图片地址
     * @param $name
     * @param $image
     * @param array $options
     * @return string
     * @throws OSSException
     */
    public static function getUrl($name,$image,$options=[]){
        $image = trim(parse_url($image,PHP_URL_PATH),"/");
        // 七牛云
        if($name == "qiniu"){
            if(empty($options["domain"])){
                throw new OSSException("请配置七牛云域名",0);
            }

            return trim($options["domain"],"/") . "/" . $image;
        }

        // 腾讯COS
        if($name == "qcloud"){
            if(!empty($options["domain"])){
                return trim($options["domain"],"/") . "/" . $image;
            }

            $endpoint = "https://" . $options["bucket"] . ".cos." . $options["endpoint"] . ".myqcloud.com";
            return $endpoint . '/' . $image;
        }

        // 阿里云OSS
        if(!empty($options["domain"])){
            return trim($options["domain"],"/") . "/" . $image;
        }

        $protocol = $options["protocol"] ?? "auto";
        $url = "";
        if($protocol == "http" || $protocol == "https"){
            $url .= $protocol . "://";
        }else{
            $url .= "//";
        }

        $url .= $options["bucket"] . "." . $options["endpoint"] . ".aliyuncs.com/" . $image;
        return $url;
    }

    /**
     * 获取云配置文件
     * @param string $name
     * @return array|mixed
     */
    public static function config($name=""){
        if(empty($name)){
            return [];
        }

        $path = dirname(__FILE__) . '/' . $name . '/config.php';
        if(!file_exists($path)){
            return [];
        }

        $path = include $path;
        return $path;
    }
}