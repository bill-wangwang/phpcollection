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
        return trim($match[1]);
    }

    private function getContent($url) {
        $temp = iconv('gbk', 'utf-8', file_get_contents($url));
        return $temp;
    }

    private function getMainPic() {
        $pat = '/<img id="J_ImgBooth" alt="(.*)" src="(.*)"(.*)\/>/isU';
        preg_match($pat, $this->content, $match);
        $thumb_url = trim($match[2]);
        $url = $this->getRealUrl($thumb_url);
        return ['thumb_url' => $thumb_url, 'url' => $url];
    }

    private function getPicList() {
        $pat = '/<li class="(.*)">(.*)<a href="#"><img src="(.*)" \/><\/a>(.*)<\/li>/isU';
        preg_match_all($pat, $this->content, $match);
        $thumb_urls = $match[3];
        $urls = [];
        foreach($thumb_urls as $url){
            $urls[] = $this->getRealUrl($url);
        }
        return [
            'thumb_urls' => $thumb_urls,
            'urls'       => $urls
        ];
    }

    private function getDetail() {
        $pat = '/"descUrl":"(.*)",/isU';
        preg_match($pat, $this->content, $match);
        $descUrl = "http:" . trim($match[1]);
        $apiContent = iconv('gbk', 'utf-8', file_get_contents($descUrl));
        $patApiContent = "/var desc='(.*)';/isU";
        preg_match($patApiContent, $apiContent, $matchApiContent);
        return $matchApiContent[1];
    }

    private function getRealUrl($url) {
        if (stripos($url, '//') === 0) {
            $url = 'http:' . $url;
        }
        $temp = explode('.jpg_', $url);
        $new_url = $temp[0] . '.jpg';
        return $new_url;
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