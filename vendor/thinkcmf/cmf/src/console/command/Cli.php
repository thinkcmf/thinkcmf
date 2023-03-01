<?php

namespace cmf\console\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Cli extends Command
{
    private array $groups = [];
    private int $maxNameLength = 0;

    protected function configure()
    {
        $this->setName('cli')
            ->addArgument('name', Argument::OPTIONAL, 'the cli name,  e.g. <warning>demo:hello:hello</warning>')
            ->addArgument('params', Argument::OPTIONAL, 'the cli params', '')
            ->setDescription('List lightweight CLI commands');
    }

    /**
     * 获取用法介绍
     * @return array
     */
    public function getUsages(): array
    {
        return [
            'php think cli',
            'php think cli help'
        ];
    }

    /**
     * 获取简介
     * @param bool $short 是否简单的
     * @return string
     */
    public function getSynopsis(bool $short = false): string
    {
        return '';
    }

    protected function execute(Input $input, Output $output)
    {

        //https://www.seoxiehui.cn/article-137432-1.html
        //支持输出多种颜色风格的消息文本(info, comment, success, warning, danger, error ... )
        //常用的特殊格式信息显示(section, panel, padding, helpPanel, table, tree, title, list, multiList)
        //丰富的动态信息显示(pending/loading, pointing, spinner, counterTxt, dynamicText, progressTxt, progressBar)
        //常用的用户信息交互支持(select, multiSelect, confirm, ask/question, askPassword/askHiddenInput)
        $test = <<<EOT
The <info>migrate:run</info> command runs all available migrations, optionally up to a specific version
<error>error</error>
<info>info</info>
<comment>comment</comment>
<question> question</question>
<highlight>highlight </highlight>
<warning>danger 123</warning>
EOT;

        $isHelp = false;
        $url    = $input->getArgument('name');
        $params = $input->getArgument('params');
        if (empty($url) || ($url == 'help')) {
            if ($url == 'help') {
                if (empty($params)) {
                    $this->discoverCliGroups();
                    $this->describeHelp();
                    return;
                } else {
                    $isHelp = true;
                    $url    = $params;
                    $params = '';
                }
            } else {
                $this->discoverCliGroups();
                $output->writeln('<comment>Available commands:</> ');
                $output->writeln(' <info>help</info> ');
                $this->describeGroups();
                return;
            }
        }

        $url      = str_replace('/', ':', $url);
        $urlArr   = explode(':', $url);
        $countUrl = count($urlArr);

        if ($countUrl > 3) {
            $output->error('cli format is not right!');
            return;
        }

        if ($countUrl == 3) {
            $controller = cmf_parse_name($urlArr[1], 1);
            $action     = $urlArr[2];
            $appName    = $urlArr[0];
            $class      = "app\\{$appName}\\cli\\{$controller}Cli";

            if (class_exists($class)) {
                // 加载应用第三方库
                $appAutoLoadFile = $this->app->getAppPath() . $appName . '/vendor/autoload.php';
                if (file_exists($appAutoLoadFile)) {
                    require_once $appAutoLoadFile;
                }
                if (method_exists($class, $action)) {
                    $object = new $class();
                    if ($isHelp) {
                        $this->describeCliHelp($url, $class, $action);
                    } else {
                        $object->$action($input, $output);
                    }
                    return;
                }
            }
        }

        $this->discoverCliGroups();
        $this->describeCliSearch();
    }

    protected function discoverCliGroups()
    {
        $cliGroups = [];
        $appPath   = app_path();
        $apps      = cmf_scan_dir($appPath . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            $cliFiles = cmf_scan_dir($appPath . "$app/cli/*Cli.php");
            foreach ($cliFiles as $cliFile) {
                $cliClassName      = str_replace('.php', '', $cliFile);
                $cliControllerName = cmf_parse_name(str_replace('Cli', '', $cliClassName));
                $class             = "app\\$app\\cli\\$cliClassName";
                if (class_exists($class)) {
                    $reflect = new \ReflectionClass($class);
                    $methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);


                    foreach ($methods as $method) {

                        $methodName = $method->getName();
                        if ($method->isPublic()) {
                            $groupName = "{$app}[{$cliControllerName}]";
                            if (empty($cliGroups[$groupName])) {
                                $cliGroups[$groupName] = [];
                            }

                            try {
                                $attrs = $method->getAttributes(\cmf\console\Cli::class);
                                /* @var \cmf\console\Cli $mCli */
                                $mCli = $attrs[0]->newInstance();
                            } catch (\Exception $e) {
                                $mCli = new \cmf\console\Cli('');
                            }

                            $mCliName = "$app:$cliControllerName:$methodName";
                            $mCli->setName($mCliName);
                            $mCliNameLength = strlen($mCliName);
                            if ($mCliNameLength > $this->maxNameLength) {
                                $this->maxNameLength = $mCliNameLength;
                            }
                            $cliGroups[$groupName][] = $mCli;
                        }
                    }

                }
            }
        }

        $this->groups = $cliGroups;
    }

    protected function describeCliHelp($cliName, $cliClass, $action)
    {
        $reflect = new \ReflectionMethod($cliClass, $action);
        $attrs   = $reflect->getAttributes(\cmf\console\Cli::class);

        foreach ($attrs as $attr) {
            /**
             * @var \cmf\console\Cli $mCli
             */
            $mCli           = $attr->newInstance();
            $mCliName       = $cliName;
            $mCliDescrition = $mCli->getDescription();
            $mCli->setName($mCliName);

            if (!empty($mCliDescrition)) {
                $this->output->writeln($mCliDescrition);
                $this->output->writeln('');
            }

            $this->output->writeln('<comment>Usage:</comment>');
            $mCliUsages = $mCli->getUsages();
            $this->output->writeln(" $cliName");
            if (!empty($mCliUsages)) {
                foreach ($mCliUsages as $mCliUsage) {
                    $this->output->writeln(" $mCliUsage");
                }
            }
            $this->output->writeln('');
            $mCliExamples = $mCli->getExamples();

            if (!empty($mCliExamples)) {
                $this->output->writeln('<comment>Examples:</comment>');
                foreach ($mCliExamples as $mCliExample) {
                    $this->output->writeln(" $mCliExample");
                }
            }
            break;
        }
    }

    protected function describeHelp()
    {
        $this->output->writeln('<comment>Usage:</comment>');
        $this->output->writeln(" help  [<cli_name>]");
        $this->output->writeln('');
        if ($this->maxNameLength < 9) {
            $this->maxNameLength = 9;
        }

        $this->output->writeln('<comment>Arguments:</comment>');
        $space = str_repeat(' ', $this->maxNameLength - 9);
        $this->output->writeln("<info>cli_name</info>$space  The cli name e.g. <warning>demo:hello:hello</warning>");
        $this->output->writeln('');

        if (!empty($this->groups)) {
            $this->output->writeln('<comment>Examples:</comment>');
            foreach ($this->groups as $groupName => $clis) {
                /**
                 * @var \cmf\console\Cli $cli
                 */
                foreach ($clis as $cli) {
                    $cliName        = $cli->getName();
                    $cliDescription = $cli->getDescription();
                    $space          = str_repeat(' ', $this->maxNameLength - strlen($cliName));
                    $this->output->writeln(" <info>php think cli help $cliName</info>$space  $cliDescription");
                }
            }
        }
    }

    protected function describeGroups()
    {
        foreach ($this->groups as $groupName => $clis) {
            $this->output->writeln("<comment>$groupName:</comment>");
            /**
             * @var \cmf\console\Cli $cli
             */
            foreach ($clis as $cli) {
                $cliName        = $cli->getName();
                $cliDescription = $cli->getDescription();
                $space          = str_repeat(' ', $this->maxNameLength - strlen($cliName));
                $this->output->writeln(" <info>$cliName</info>$space   $cliDescription");
            }
        }
    }

    protected function describeCliSearch()
    {
        $keyword = $this->input->getArgument('name');
        $this->output->writeln('<comment>Search Result:</comment>');
        foreach ($this->groups as $groupName => $clis) {
            $groupNameShowed = false;
            /**
             * @var \cmf\console\Cli $cli
             */
            foreach ($clis as $cli) {
                $cliName        = $cli->getName();
                $cliDescription = $cli->getDescription();

                if (strpos($cliName, $keyword) !== false || strpos($cliDescription, $keyword) !== false) {
                    if (!$groupNameShowed) {
                        $this->output->writeln("<comment>$groupName:</comment>");
                        $groupNameShowed = true;
                    }
                    $space = str_repeat(' ', $this->maxNameLength - strlen($cliName));

                    $cliName        = str_replace($keyword, "<error>$keyword</error>", $cliName);
                    $cliDescription = str_replace($keyword, "<error>$keyword</error>", $cliDescription);
                    $this->output->writeln(" <info>$cliName</info>$space   $cliDescription");
                }
            }
        }
    }
}
