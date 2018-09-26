<?php

namespace app\common\service\collection;

use think\facade\Log;

/**
 * Class TmallService
 * @package app\common\service\collection
 * TODO
 * 获取原始图片和缩略图
 * 下载远程图片到本地
 * 入库
 */
class TmallService {

    private $content = '';

    public function __construct($url) {
        $this->content = $this->getContent($url);
    }

    private function getTitle() {
        $pat = '/<h1 data-spm="1000983">(.*)<\/h1>/isU';
        preg_match($pat, $this->content, $match);
        return  trim($match[1]);
    }

    private function getContent($url) {
        $temp =  iconv('gbk', 'utf-8',file_get_contents($url));
        return $temp;
    }

    private function getMainPic() {
        $pat = '/<img id="J_ImgBooth" alt="(.*)" src="(.*)"(.*)\/>/isU';
        preg_match($pat, $this->content, $match);
        return  trim($match[2]);
    }

    private function getPicList() {
        $pat = '/<li class="(.*)">(.*)<a href="#"><img src="(.*)" \/><\/a>(.*)<\/li>/isU';
        preg_match_all($pat, $this->content, $match);
        return   $match[3] ;
    }

    private function getDetail() {
        $pat = '/"descUrl":"(.*)",/isU';
        preg_match($pat, $this->content, $match);
        $descUrl = "http:"  . trim($match[1]);
        $apiContent = iconv('gbk', 'utf-8',file_get_contents($descUrl));
        $patApiContent = "/var desc='(.*)';/isU";
        preg_match($patApiContent, $apiContent, $matchApiContent);
        return $matchApiContent[1];
    }

    public function getInfo() {
        return [
            'title'    => $this->getTitle(),
            'main_pic' => $this->getMainPic(),
            'pic_list' => $this->getPicList(),
            'detail'   => $this->getDetail(),
        ];
    }
}