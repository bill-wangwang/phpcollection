<?php

namespace app\common\service\collection;

use app\common\service\NetworkService;
use think\Exception;
use think\facade\Log;

class TaoBaoService {

    private $content = '';

    public function __construct($url) {
        $this->content = $this->getContent($url);
    }

    private function getTitle() {
        $pat = '/<h3 class="tb-main-title" data-title="(.*)">(.*)<\/h3>/isU';
        preg_match($pat, $this->content, $match);
        return trim(strip_tags($match[1]));
    }

    private function getContent($url) {
        $headers = [
            'user_agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36",
            'refer'      => 'https://www.taobao.com/'
        ];
        $headers = [];
        $content = NetworkService::curlGet($url, $headers);
        if (empty($content)) {
            throw new Exception("获取[{$url}]内容失败。");
        }
        $temp = mb_convert_encoding($content, 'UTF-8', 'GBK');
        return $temp;
    }

    private function getMainPic() {
        $pat = "/pic              : '(.*)',/isU";
        preg_match($pat, $this->content, $match);
        $origin_url = trim($match[1]);
        $url = $this->getRealUrl($origin_url);
        $thumb_url = $url . '_50x50.jpg';
        return [
            'thumb_url' => $thumb_url,
            'url'       => $url
        ];
    }

    private function getPicList() {
        $pat = "/auctionImages    : \[(.*)\]/isU";
        preg_match($pat, $this->content, $match);
        $data_urls = explode(',', $match[1]);
        $thumb_urls = [];
        $urls = [];
        foreach ($data_urls as $url) {
            $url = trim($url, '"');
            $u = $this->getRealUrl($url);
            $thumb_urls[] = $u . '_50x50.jpg' ;
            $urls[] = $u;
        }
        return [
            'thumb_urls' => $thumb_urls,
            'urls'       => $urls
        ];
    }

    private function getDetail() {
        $pat = "/location.protocol==='http:' \? '(.*)' : '(.*)'/isU";
        preg_match($pat, $this->content, $match);
        $descUrl = trim($match[2]);
        if (stripos($descUrl, '//') === 0) {
            $descUrl = 'https:' . $descUrl;
        }
        $apiContent = iconv('gbk', 'utf-8', NetworkService::curlGet($descUrl));
        $patApiContent = "/var desc='(.*)';/isU";
        preg_match($patApiContent, $apiContent, $matchApiContent);
        return trim($matchApiContent[1]);
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