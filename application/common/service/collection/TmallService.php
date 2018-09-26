<?php

namespace app\common\service\collection;

use app\common\service\NetworkService;
use think\Exception;

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
        $headers = [
            'user_agent'=>"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36",
            'refer'=>'https://www.tmall.com/'
        ];
        $headers = [];
        $content =  NetworkService::curlGet($url, $headers);
        if(empty($content)){
            throw new Exception("获取[{$url}]内容失败。");
        }
        $temp = iconv('gbk', 'utf-8',$content);
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
        $descUrl = trim($match[1]);
        if (stripos($descUrl, '//') === 0) {
            $descUrl = 'http:' . $descUrl;
        }
        $apiContent = iconv('gbk', 'utf-8', NetworkService::curlGet($descUrl));
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