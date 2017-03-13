<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class ThemeModel extends Model
{

    /**
     * 获取插件列表
     */
    public function getList()
    {

    }

    public function installTheme($theme)
    {
        $manifest = "themes/$theme/manifest.json";
        if (file_exists_case($manifest)) {
            $manifest           = file_get_contents($manifest);
            $themeData          = json_decode($manifest, true);
            $themeData['theme'] = $theme;

            $this->updateThemeFiles($theme);

            $this->data($themeData)->save();
            return true;
        } else {
            return false;
        }
    }

    private function updateThemeFiles($theme, $suffix = 'html')
    {
        $dir                = 'themes/' . $theme;
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
            $findFile   = Db::name('theme_file')->where(['theme' => $theme, 'file' => $file])->find();
            $isPublic   = empty($config['is_public']) ? 0 : 1;
            $listOrder  = empty($config['order']) ? 0 : floatval($config['order']);
            $more       = empty($config['more']) ? [] : json_encode($config['more']);
            $oldMore    = $more;

            if (empty($findFile)) {
                Db::name('theme_file')->insert(
                    [
                        'theme'       => $theme,
                        'action'      => $config['action'],
                        'file'        => $file,
                        'name'        => $config['name'],
                        'more'        => $more,
                        'config_more' => $oldMore,
                        'description' => $config['description'],
                        'is_public'   => $isPublic,
                        'list_order'  => $listOrder
                    ]);
            } else {
                Db::name('theme_file')->where(['theme' => $theme, 'file' => $file])->update(
                    [
                        'theme'       => $theme,
                        'action'      => $config['action'],
                        'file'        => $file,
                        'name'        => $config['name'],
                        'more'        => $more,
                        'config_more' => $oldMore,
                        'description' => $config['description'],
                        'is_public'   => $isPublic,
                        'list_order'  => $listOrder
                    ]);
            }
        }
    }

}