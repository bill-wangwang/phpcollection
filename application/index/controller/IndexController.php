<?php

namespace app\index\controller;

use app\common\service\CollectionService;

class IndexController {
    public function index() {
        return view();
    }

    public function doCollection() {
        $url = input('param.object_url/s', '', 'trim');
        $res = [];
        if (empty($url)) {
            $res = [
                'code'    => 1,
                'message' => '参数不能为空'
            ];
        } else {
            $object = new CollectionService($url);
            $res = [
                'code'    => 0,
                'message' => 'success',
                'data'    => $object->getInfo()
            ];
        }
        exit(json_encode($res));
    }

    public function demo() {
        $url = '//img.alicdn.com/imgextra/i3/725677994/TB2s6z6eAfb_uJkHFJHXXb4vFXa_!!725677994.jpg_60x60q90.jpg';
        if(stripos($url, '//')===0){
            $url = 'http:' . $url;
        }
        $temp = explode('.jpg_', $url);
        $new_url = $temp[0] . '.jpg';
        return $new_url;
    }

}
