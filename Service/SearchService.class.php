<?php

namespace Search\Service;


use System\Service\BaseService;

class SearchService extends BaseService {

    static function getSetting() {
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

    static function search($q = '', $page = 1) {
        if(empty($q)){
            return self::createReturn(true, null, '请输入关键字');
        }
        $config = self::getSetting();
        $cachetime = $config['cachetime'];
        $pagesize = $config['pagesize'];
        //排序
        $order = array("adddate" => "DESC", "searchid" => "DESC");
        //分词结果
        if ($config['dzsegment']) {
            $segment_q = D('Search/Search')->discuzSegment($q);
        } else {
            $segment_q = D('Search/Search')->segment($q);
        }
        if (!empty($segment_q[0]) && $config['segment']) {
            $words = $segment_q;
            $segment_q = implode(' ', $segment_q);
            $where['_string'] = " MATCH (`data`) AGAINST ('{$segment_q}' IN BOOLEAN MODE) ";
        } else {
            //这种搜索最不行
            $likeList = explode(' ', $q);
            if (count($likeList) > 1) {
                foreach ($likeList as $k => $rs) {
                    $likeList[$k] = "%{$rs}%";
                }
                $where['data'] = array('like', $likeList, 'or');
            } else {
                $where['data'] = array('like', "%{$q}%");
            }
            $words = array($q);
        }
        //查询结果缓存
        if ($cachetime) {
            //统计
            $count = M('Search')->where($where)->cache(true, $cachetime)->count();
            $page = page($count, $pagesize);
            $result = M('Search')->where($where)->cache(true, $cachetime)->page($page)->limit($pagesize)->order($order)->select();
        } else {
            $count = M('Search')->where($where)->count();
            $page = page($count, $pagesize);
            $result = M('Search')->where($where)->page($page)->limit($pagesize)->order($order)->select();
        }

        return self::createReturn(true, $result);
    }
} 