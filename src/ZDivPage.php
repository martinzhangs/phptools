<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 分(翻)页计算
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZDivPage
{
    /**
     * 分(翻)页计算
     * @param  int $total 总条数
     * @param  int $pageSize 每页显示条数
     * @param  int $pageCurrent 当前第几页(默认第1页)
     * @param  int $blocks 水平摆放分页方块个数(默认10个)
     * @return array            返回数组
     * @author martinzhang
     */
    public static function divPage($total, $pageSize, $pageCurrent, $blocks = 10)
    {
        if (!is_numeric($total)) {
            return false;
        }
        if (!is_numeric($pageSize)) {
            return false;
        }
        if (!is_numeric($pageCurrent)) {
            $pageCurrent = 1;
        }
        if (!is_numeric($blocks)) {
            $blocks = 5;
        }
        $pageTotal = max(1, ceil($total / $pageSize));    //总页数
        $pageCurrent = max($pageCurrent, 1);
        $groupTotal = ceil($pageTotal / $blocks);         //总组数
        $offset = ($pageCurrent - 1) * $pageSize;         //起始条
        $length = min($pageSize, $total - $offset);       //当前页应查询显示条数 给limit $offset,$length用
        $length = max($length, 0);
        $end = min($offset + $pageSize, $total);          //结束条 给 for($i=$offset; $i<$end; $i++){...} 用
        $prePage = max(1, $pageCurrent - 1);              //上一页(页码值)
        $nextPage = min($pageTotal, $pageCurrent + 1);    //下一页(页码值)
        $groupNum = ceil($pageCurrent / $blocks);         //当前组(当前第几组)
        //////////////////////////////////////////////////////////////////////////////////////////////////
        $block_start = ceil($pageCurrent / $blocks) * $blocks - $blocks + 1;    //起始块
        $block_end = ceil($pageCurrent / $blocks) * $blocks;                    //结束块
        for ($i = $block_start; $i <= min($block_end, $pageTotal); $i++) {
            $pageblocks[] = $i;                                                 //水平摆放单元按钮方块
        }
        $leftBtnShow = 'n';
        $leftBtn2Page = max($block_start - 1, 1);           //水平摆放分页单元按钮 左组翻页导航按钮链接到的页码值
        $rightBtnShow = 'n';
        $rightBtn2Page = min($block_end + 1, $pageTotal);   //水平摆放分页单元按钮 右组翻页导航按钮链接到的页码值
        if ($groupNum > 1) {
            $leftBtnShow = 'y';                             //左组翻页导航按钮是否应该显示  y:应该显示; n:不应该显示;
        }
        if ($groupNum < $groupTotal) {
            $rightBtnShow = 'y';                            //右组翻页导航按钮是否应该显示  y:应该显示; n:不应该显示;
        }
        //////////////////////////////////////////////////////////////////////////////////////////////////
        //返回值
        return [
            'total' => $total,                  //总条数
            'pageSize' => $pageSize,            //每页显示条数
            'pageTotal' => $pageTotal,          //总页数
            'pageCurrent' => $pageCurrent,      //当前第几页
            'offset' => $offset,                //起始条(偏移量)
            'length' => $length,                //当前页应查询条数 给limit $offset,$length用
            'end' => $end,                      //结束条  给for($i=$offset;$i<$end;$i++){...}用
            'groupTotal' => $groupTotal,        //总组数(相当于是对页码分组)
            'groupNum' => $groupNum,            //当前第几组
            'prePage' => $prePage,              //上一页(链接到的页码值)
            'nextPage' => $nextPage,            //下一页(链接到的页码值)
            'leftBtnShow' => $leftBtnShow,      //水平摆放分页单元按钮 左组翻页导航按钮是否应该显示  y:应该显示; n:不应该显示;
            'leftBtn2Page' => $leftBtn2Page,    //水平摆放分页单元按钮 左组翻页导航按钮链接到的页码值
            'rightBtnShow' => $rightBtnShow,    //水平摆放分页单元按钮 右组翻页导航按钮是否应该显示  y:应该显示; n:不应该显示;
            'rightBtn2Page' => $rightBtn2Page,  //水平摆放分页单元按钮 右组翻页导航按钮链接到的页码值
            'blocks' => $pageblocks             //水平摆放分页方块列表
        ];
    }


    /**
     * 获取输出分页统计信息(根据实际修改样式)
     * @param array $divPage 上面计算出的分页参数
     * @return string        效果: 总条数:22, 3条/页, 当前第5/8页
     * @author martinzhang
     */
    public static function getStat4H5($divPage)
    {
        return "总条数:{$divPage['total']}, {$divPage['pageSize']}条/页, 当前第{$divPage['pageCurrent']}/{$divPage['pageTotal']}页";
    }


    /**
     * 获取输出分页按钮块(根据实际样式进行修改)
     * @param array $divPage 上面计算出的分页参数
     * @param string $route URL上的路由字符串(如:user/index/list)，获取方式: $this->routeName = Yii::$app->controller->getRoute();
     * @param array $param URL上的参数数组，获取方式: $this->reqArr = \Yii::$app->request->get();
     * @return string         效果: 上一组<<3  上一页<4   [4]  5  [6]   下一页>6   下一组>>7
     * @author martinzhang
     */
    public static function getBlocks4H5($divPage, $route, $param)
    {
        $route = trim($route, '/');
        $bolcks = '';

        //上一组
        if ($divPage['leftBtnShow'] == 'y') {
            $param['pageCurrent'] = $divPage['leftBtn2Page'];
            $href = '/' . $route . '?' . urldecode(http_build_query($param));
            $bolcks .= "上一组<< <a href='$href'>{$divPage['leftBtn2Page']}</a>\t";
        }

        //上一页
        if ($param['pageCurrent'] > 1) {
            $param['pageCurrent'] = $divPage['prePage'];
            $href = '/' . $route . '?' . urldecode(http_build_query($param));
            $bolcks .= "上一页<<a href='$href'>{$divPage['prePage']}</a>\t\t";
        } else {
            $bolcks .= "上一页<{$divPage['prePage']}\t\t";
        }

        //分页导航块
        foreach ($divPage['blocks'] as $page) {
            //循环输出分页导航块
            if ($page == $divPage['pageCurrent']) {
                $bolcks .= "{$page} \t";
            } else {
                $param['pageCurrent'] = $page;
                $href = '/' . $route . '?' . urldecode(http_build_query($param));
                $bolcks .= "<a href='$href'>[{$page}]</a>\t";
            }
        }

        //下一页
        if ($divPage['pageCurrent'] < $divPage['pageTotal']) {
            $param['pageCurrent'] = $divPage['nextPage'];
            $href = '/' . $route . '?' . urldecode(http_build_query($param));
            $bolcks .= "下一页><a href='$href'>{$divPage['nextPage']}</a>\t\t";
        } else {
            $bolcks .= "下一页>{$divPage['nextPage']}\t\t";
        }


        //下一组
        if ($divPage['rightBtnShow'] == 'y') {
            $param['pageCurrent'] = $divPage['rightBtn2Page'];
            $href = '/' . $route . '?' . urldecode(http_build_query($param));
            $bolcks .= "下一组>> <a href='$href'>{$divPage['rightBtn2Page']}</a>\t";
        }

        return $bolcks;
    }


}
