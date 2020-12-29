<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class ThemeModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'theme';

    /**
     * 获取插件列表
     */
    public function getList()
    {

    }

    public function installTheme($theme)
    {
        $manifest = WEB_ROOT . "themes/$theme/manifest.json";
        if (file_exists_case($manifest)) {
            $manifest           = file_get_contents($manifest);
            $themeData          = json_decode($manifest, true);
            $themeData['theme'] = $theme;

            $this->updateThemeFiles($theme);

            $this->save($themeData);
            return true;
        } else {
            return false;
        }
    }

    public function updateTheme($theme)
    {
        $manifest = WEB_ROOT . "themes/$theme/manifest.json";
        if (file_exists_case($manifest)) {
            $manifest  = file_get_contents($manifest);
            $themeData = json_decode($manifest, true);

            $this->updateThemeFiles($theme);

            $this->where('theme', $theme)->update($themeData);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取当前前台模板某操作下的模板文件
     * @param $action string 控制器操作
     * @return array|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getActionThemeFiles($action)
    {
        $theme = config('template.cmf_default_theme');

        return ThemeFileModel::where(['theme' => $theme, 'action' => $action])->select();
    }

    private function updateThemeFiles($theme, $suffix = 'html')
    {
        $dir                = 'themes/' . $theme;
        $themeDir           = $dir;
        $tplFiles           = [];
        $root_dir_tpl_files = cmf_scan_dir("$dir/*.$suffix");
        foreach ($root_dir_tpl_files as $root_tpl_file) {
            $root_tpl_file           = "$dir/$root_tpl_file";
            $configFile              = preg_replace("/\.$suffix$/", '.json', $root_tpl_file);
            $root_tpl_file_no_suffix = preg_replace("/\.$suffix$/", '', $root_tpl_file);
            if (is_file($root_tpl_file) && file_exists_case($configFile)) {
                array_push($tplFiles, $root_tpl_file_no_suffix);

            }
        }
        $subDirs = cmf_sub_dirs($dir);
        foreach ($subDirs as $dir) {
            $subDirTplFiles = cmf_scan_dir("$dir/*.$suffix");
            foreach ($subDirTplFiles as $tplFile) {
                $tplFile         = "$dir/$tplFile";
                $configFile      = preg_replace("/\.$suffix$/", '.json', $tplFile);
                $tplFileNoSuffix = preg_replace("/\.$suffix$/", '', $tplFile);
                if (is_file($tplFile) && file_exists_case($configFile)) {
                    array_push($tplFiles, $tplFileNoSuffix);
                }
            }
        }

        foreach ($tplFiles as $tplFile) {
            $configFile = $tplFile . ".json";
            $file       = preg_replace('/^themes\/' . $theme . '\//', '', $tplFile);
            $file       = strtolower($file);
            $config     = json_decode(file_get_contents($configFile), true);
            $findFile   = ThemeFileModel::where(['theme' => $theme, 'file' => $file])->find();
            $isPublic   = empty($config['is_public']) ? 0 : 1;
            $listOrder  = empty($config['order']) ? 0 : floatval($config['order']);
            $configMore = empty($config['more']) ? [] : $config['more'];
            $more       = $configMore;

            if (empty($findFile)) {
                ThemeFileModel::insert([
                    'theme'       => $theme,
                    'action'      => $config['action'],
                    'file'        => $file,
                    'name'        => $config['name'],
                    'more'        => json_encode($more),
                    'config_more' => json_encode($configMore),
                    'description' => $config['description'],
                    'is_public'   => $isPublic,
                    'list_order'  => $listOrder
                ]);
            } else { // 更新文件
                $moreInDb = $findFile['more'];
                $more     = $this->updateThemeConfigMore($configMore, $moreInDb);
                ThemeFileModel::where(['theme' => $theme, 'file' => $file])->update([
                    'theme'       => $theme,
                    'action'      => $config['action'],
                    'file'        => $file,
                    'name'        => $config['name'],
                    'more'        => json_encode($more),
                    'config_more' => json_encode($configMore),
                    'description' => $config['description'],
                    'is_public'   => $isPublic,
                    'list_order'  => $listOrder
                ]);
            }
        }

        // 检查安装过的模板文件是否已经删除
        $files = ThemeFileModel::where('theme', $theme)->select();

        foreach ($files as $themeFile) {
            $tplFile           = $themeDir . '/' . $themeFile['file'] . '.' . $suffix;
            $tplFileConfigFile = $themeDir . '/' . $themeFile['file'] . '.json';
            if (!is_file($tplFile) || !file_exists_case($tplFileConfigFile)) {
                ThemeFileModel::where(['theme' => $theme, 'file' => $themeFile['file']])->delete();
            }
        }
    }

    private function updateThemeConfigMore($configMore, $moreInDb)
    {

        if (!empty($configMore['vars'])) {
            foreach ($configMore['vars'] as $mVarName => $mVar) {
                if (isset($moreInDb['vars'][$mVarName]['value']) && $mVar['type'] == $moreInDb['vars'][$mVarName]['type']) {
                    $configMore['vars'][$mVarName]['value'] = $moreInDb['vars'][$mVarName]['value'];

                    if (isset($moreInDb['vars'][$mVarName]['valueText'])) {
                        $configMore['vars'][$mVarName]['valueText'] = $moreInDb['vars'][$mVarName]['valueText'];
                    }
                }
            }
        }

        if (!empty($configMore['widgets'])) {
            foreach ($configMore['widgets'] as $widgetName => $widget) {

                if (isset($moreInDb['widgets'][$widgetName]['title'])) {
                    $configMore['widgets'][$widgetName]['title'] = $moreInDb['widgets'][$widgetName]['title'];
                }

                if (isset($moreInDb['widgets'][$widgetName]['display'])) {
                    $configMore['widgets'][$widgetName]['display'] = $moreInDb['widgets'][$widgetName]['display'];
                }

                if (!empty($widget['vars'])) {
                    foreach ($widget['vars'] as $widgetVarName => $widgetVar) {

                        if (isset($moreInDb['widgets'][$widgetName]['vars'][$widgetVarName]['value']) && $widgetVar['type'] == $moreInDb['widgets'][$widgetName]['vars'][$widgetVarName]['type']) {
                            $configMore['widgets'][$widgetName]['vars'][$widgetVarName]['value'] = $moreInDb['widgets'][$widgetName]['vars'][$widgetVarName]['value'];

                            if (isset($moreInDb['widgets'][$widgetName]['vars'][$widgetVarName]['valueText'])) {
                                $configMore['widgets'][$widgetName]['vars'][$widgetVarName]['valueText'] = $moreInDb['widgets'][$widgetName]['vars'][$widgetVarName]['valueText'];
                            }
                        }

                    }
                }

            }
        }

        return $configMore;
    }


}
