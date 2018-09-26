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

}
