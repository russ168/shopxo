<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\service\SearchService;
use app\service\BrandService;
use app\service\SeoService;
use app\service\GoodsService;

/**
 * 搜索
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Search extends Common
{
    private $params;

    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-02-22T16:50:32+0800
     */
    public function Index()
    {
        $keywords = input('post.wd');
        if(!empty($keywords))
        {
            return redirect(MyUrl('index/search/index', ['wd'=>StrToAscii($keywords)]));
        } else {
            // 参数初始化
            $this->ParamsInit();

            // 品牌列表
            $this->assign('brand_list', BrandService::CategoryBrandList(['category_id'=>$this->params['category_id'], 'keywords'=>$this->params['wd']]));

            // 商品分类
            $this->assign('category_list', SearchService::GoodsCategoryList(['category_id'=>$this->params['category_id']]));

            // 筛选价格区间
            $this->assign('screening_price_list', SearchService::ScreeningPriceList(['field'=>'id,name']));

            // 参数
            $this->assign('params', $this->params);

            // 浏览器名称
            if(!empty($this->params['category_id']))
            {
                $seo_name = GoodsService::GoodsCategoryValue($this->params['category_id'], 'name', '商品搜索');
            } else {
                $seo_name = empty($this->params['wd']) ? '商品搜索' : $this->params['wd'];
            }
            $this->assign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_name, 1));

            return $this->fetch();
        }
    }

    /**
     * 参数初始化
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-02T01:38:27+0800
     */
    private function ParamsInit()
    {
        // 参数
        $params = input();

        // 品牌id
        $this->params['brand_id'] = isset($params['brand_id']) ? intval($params['brand_id']) : 0;

        // 分类id
        $this->params['category_id'] = isset($params['category_id']) ? intval($params['category_id']) : 0;

        // 筛选价格id
        $this->params['screening_price_id'] = isset($params['screening_price_id']) ? intval($params['screening_price_id']) : 0;

        // 搜索关键字
        $this->params['wd'] = empty($params['wd']) ? '' : (IS_AJAX ? trim($params['wd']) : AsciiToStr($params['wd']));

        // 排序方式
        $this->params['order_by_field'] = empty($params['order_by_field']) ? 'default' : $params['order_by_field'];
        $this->params['order_by_type'] = empty($params['order_by_type']) ? 'desc' : $params['order_by_type'];

        // 用户信息
        $this->params['user_id'] = isset($this->user['id']) ? $this->user['id'] : 0;
    }

    /**
     * 获取商品列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-07
     * @desc    description
     */
    public function GoodsList()
    {
        // 参数初始化
        $this->ParamsInit();

        // 获取商品列表
        $this->params['keywords'] = $this->params['wd'];
        $ret = SearchService::GoodsList($this->params);
        if(empty($ret['data']['data']))
        {
            $msg = '没有相关数据';
            $code = -100;
        } else {
            $msg = '操作成功';
            $code = 0;
        }

        // 搜索记录
        SearchService::SearchAdd($this->params);

        // 返回
        return DataReturn($msg, $code, $ret['data']);
    }
}
?>