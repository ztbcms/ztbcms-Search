<?php

namespace Search\Service;


use System\Service\BaseService;

class SearchService extends BaseService {

    static function getSetting(){
        $Search = M('Module')->where(array('module' => 'Search'))->find();
        $now_config = unserialize($Search['setting']);
        $setting = [
            //是否启用相关搜索
            'relationenble' => isset($now_config['relationenble']) ? $now_config['relationenble'] : 1,
            //是否启用PHP简易分词
            'segment' => isset($now_config['segment']) ? $now_config['segment'] : 1,
            //搜索结果每页显示条数
            'pagesize' => isset($now_config['pagesize']) ? $now_config['pagesize'] : 10,
            //搜索结果缓存时间
            'cachetime' => isset($now_config['cachetime']) ? $now_config['cachetime'] : 0,
            //是否使用DZ在线分词接口
            'dzsegment' => isset($now_config['dzsegment']) && $now_config['dzsegment'] ? $now_config['dzsegment'] : true,
            //是否启用sphinx全文索引
            'sphinxenable' => isset($now_config['sphinxenable']) ? $now_config['sphinxenable'] : 0,
            //sphinx服务器主机地址
            'sphinxhost' => isset($now_config['sphinxhost']) ? $now_config['sphinxhost'] : '',
            //sphinx服务器端口号
            'sphinxport' => isset($now_config['sphinxport']) ? $now_config['sphinxport'] : '',
        ];

        cache('Search_config', $setting);
        return $setting;
    }


} 