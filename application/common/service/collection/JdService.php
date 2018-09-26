<?php

namespace app\common\service\collection;

use app\common\service\NetworkService;
use think\Exception;

class JdService {

    private $content = '';

    public function __construct($url) {
        $this->content = $this->getContent($url);
    }

    private function getTitle() {
        $pat = '/<div class="sku-name">(.*)<\/div>/isU';
        preg_match($pat, $this->content, $match);
        return trim(strip_tags($match[1]));
    }

    private function getContent($url) {
        $headers = [
            'user_agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36",
            'refer'      => 'https://www.jd.com/'
        ];
        $headers = [];
        $content = NetworkService::curlGet($url, $headers);
        if (empty($content)) {
            throw new Exception("获取[{$url}]内容失败。");
        }
        $temp = iconv('gbk', 'utf-8', $content);
        return $temp;
    }

    private function getMainPic() {
        $pat = '/<img id="spec-img" width="350" data-origin="(.*)" alt="(.*)"\/>/isU';
        preg_match($pat, $this->content, $match);
        $origin_url = trim($match[1]);
        $url = $this->getRealUrl($origin_url);
        $thumb_url = str_replace('/n1/', '/n5/', $url);
        return [
            'thumb_url' => $thumb_url,
            'url' => $url
        ];
    }

    private function getPicList() {
        $pat = "/<img alt='(.*)' src='(.*)' data-url='(.*)' data-img='1' width='50' height='50'>/isU";
        preg_match_all($pat, $this->content, $match);
        $data_urls = $match[3];
        $thumb_urls = [];
        $urls = [];
        foreach ($data_urls as $url) {
            $thumb_urls[] = "http://img13.360buyimg.com/n5/" . $url;
            $urls[] = "http://img13.360buyimg.com/n1/" . $url;
        }
        return [
            'thumb_urls' => $thumb_urls,
            'urls'       => $urls
        ];
    }

    private function getDetail() {
        $pat = "/desc: '(.*)',/isU";
        preg_match($pat, $this->content, $match);
        $descUrl = trim($match[1]);
        if (stripos($descUrl, '//') === 0) {
            $descUrl = 'http:' . $descUrl;
        }
        $apiContent = iconv('gbk', 'utf-8', NetworkService::curlGet($descUrl));
        $json = json_decode($apiContent, 1);
        $content = $json['content'];
        $content = str_replace('data-lazyload', 'src', $content);
        return $content;
    }

    private function getRealUrl($url) {
        if (stripos($url, '//') === 0) {
            $url = 'http:' . $url;
        }
        return $url;
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