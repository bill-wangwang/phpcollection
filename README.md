php 采集 天猫 京东 淘宝 商品详情页
===============
# 使用方法
``` 
# $url 为天猫、京东或淘宝的商品详情URL地址
$object = new CollectionService($url);
$info = $object->getInfo();
# info 格式为
{
    title    : '商品名称', 
    main_pic : { thumb_url: '缩略图地址', url: '原图地址'},
    pic_list : { thumb_urls: ['图集缩略图数组'], urls: ['图集原图数组'] }, 
    detail   : '商品详情'
}
```