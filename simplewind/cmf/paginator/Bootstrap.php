<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\paginator;

use think\Paginator;

class Bootstrap extends Paginator
{
    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getPreviousButton($text = "")
    {

        if (empty($text)) {
            if (empty($this->options['prev'])) {
                $text = "&laquo;";
            } else {
                $text = $this->options['prev'];
            }
        }

        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url(
            $this->currentPage() - 1
        );

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getNextButton($text = '')
    {
        if (empty($text)) {
            if (empty($this->options['next'])) {
                $text = "&raquo;";
            } else {
                $text = $this->options['next'];
            }
        }

        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url($this->currentPage() + 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getSimplePreviousButton($text = "")
    {

        if (empty($text)) {
            if (empty($this->options['prev'])) {
                $text = "&larr;";
            } else {
                $text = $this->options['prev'];
            }

            if (!empty($this->options['simple_prev'])) {
                $text = $this->options['simple_prev'];
            }
        }

        if ($this->currentPage() <= 1) {
            return '<li class="disabled previous page-item"><span class="page-link">' . $text . '</span></li>';
        }

        $url = $this->url(
            $this->currentPage() - 1
        );

        return '<li class="previous page-item"><a class="page-link" href="' . htmlentities($url) . '">' . $text . '</a></li>';
    }

    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getSimpleNextButton($text = '')
    {
        if (empty($text)) {
            if (empty($this->options['next'])) {
                $text = "&rarr;";
            } else {
                $text = $this->options['next'];
            }

            if (!empty($this->options['simple_next'])) {
                $text = $this->options['simple_next'];
            }

        }

        if (!$this->hasMore) {
            return '<li class="disabled next page-item"><span class="page-link">' . $text . '</span></li>';
        }

        $url = $this->url($this->currentPage() + 1);

        return '<li class="next page-item"><a class="page-link" href="' . htmlentities($url) . '">' . $text . '</a></li>';
    }

    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks()
    {
        if ($this->simple)
            return '';

        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null
        ];

        $side   = 2;
        $window = $side * 2;

        if ($this->lastPage < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last']  = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last']  = $this->getUrlRange($this->lastPage - ($window + 0), $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        }

        $html = '';

        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }

        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }

        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }

        return $html;
    }

    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
        if ($this->hasPages()) {
            $request = request();

            if ($this->simple || $request->isMobile()) {
                return sprintf(
                    '%s %s',
                    $this->getSimplePreviousButton(),
                    $this->getSimpleNextButton()
                );
            } else {
                return sprintf(
                    '%s %s %s',
                    $this->getPreviousButton(),
                    $this->getLinks(),
                    $this->getNextButton()
                );
            }
        }
    }

    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<li class="page-item"><a class="page-link" href="' . htmlentities($url) . '">' . $page . '</a></li>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<li class="page-item disabled"><span class="page-link">' . $text . '</span></li>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<li class="active page-item disabled" ><span class="page-link">' . $text . '</span></li>';
    }

    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 批量生成页码按钮.
     *
     * @param  array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls)
    {
        $html = '';

        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }

        return $html;
    }

    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  int $page
     * @return string
     */
    protected function getPageLinkWrapper($url, $page)
    {
        if ($page == $this->currentPage()) {
            return $this->getActivePageWrapper($page);
        }

        return $this->getAvailablePageWrapper($url, $page);
    }


}
