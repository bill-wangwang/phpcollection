<?php

namespace app\common\service;

use app\common\service\collection\JdService;
use app\common\service\collection\TaoBaoService;
use app\common\service\collection\TmallService;
use think\Exception;

class CollectionService {

    const TYPE_JD = 'jd';
    const TYPE_TB = 'tb';
    const TYPE_TM = 'tm';

    private $url = '';
    private $type = '';
    private $object = null;

    public function __construct($url) {
        $url = trim($url);
        if(empty($url)){
           throw new Exception("URL不能为空");
        }
        if(stripos($url, 'tmall.com')){
            $this->url = $url;
            $this->type = self::TYPE_TM;
            $this->object = new TmallService($url);
        } else  if(stripos($url, 'taobao.com')){
            $this->url = $url;
            $this->type = self::TYPE_TB;
            $this->object = new TaoBaoService($url);
        } else  if(stripos($url, 'jd.com')){
            $this->url = $url;
            $this->type = self::TYPE_JD;
            $this->object = new JdService($url);
        } else {
            throw new Exception("暂不支持该网站的采集");
        }
    }

    public function getInfo() {
        return $this->object->getInfo();
    }

}