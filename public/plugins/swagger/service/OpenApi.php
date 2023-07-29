<?php

namespace plugins\swagger\service;

class OpenApi
{

    public static function generate()
    {
        $paths = [WEB_ROOT . 'plugins/swagger/swagger', CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/swagger'];
        $apps  = cmf_scan_dir(CMF_ROOT . 'api/*', GLOB_ONLYDIR);

        foreach ($apps as $app) {
            $dir = CMF_ROOT . 'api/' . $app . '/controller';
            if (is_dir($dir)) {
                $paths[] = $dir;
            }

            $dir = CMF_ROOT . 'api/' . $app . '/swagger';
            if (is_dir($dir)) {
                $paths[] = $dir;
            }
        }

        $apps = cmf_scan_dir(CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            $dir = CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/' . $app . '/controller';
            if (is_dir($dir)) {
                $paths[] = $dir;
            }

            $dir = CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/' . $app . '/swagger';
            if (is_dir($dir)) {
                $paths[] = $dir;
            }
        }
        return \OpenApi\Generator::scan($paths,
            [
                'aliases'  => [
                    'oa' => 'OpenApi\\Annotations'
                ],
//                    'namespaces' => ['OpenApi\\Annotations\\'],
                'validate' => false
            ]
        );
    }
}