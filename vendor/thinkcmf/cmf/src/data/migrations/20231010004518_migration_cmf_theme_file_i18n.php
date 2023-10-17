<?php

use think\migration\Migrator;
use Phinx\Db\Adapter\MysqlAdapter;

class MigrationCmfThemeFileI18n extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $options=$this->getAdapter()->getOptions();
//        Array
//(
//    [adapter] => mysql
//    [host] => mysql
//    [name] => thinkcmf
//    [user] => root
//    [pass] => admin
//    [port] => 3306
//    [charset] => utf8mb4
//    [table_prefix] => cmf_
//    [version_order] => creation
//    [default_migration_table] => cmf_migration
//)
        $table = $this->table('theme_file_i18n', ['comment' => "模板文件多语言表"]);
        $table
            ->addColumn('file_id', 'integer', [
                'default' => 0,
                'null'    => false,
                'comment' => '模板文件ID',
            ])
            ->addColumn('theme', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 20,
                'comment' => '主题目录名，用于主题的维一标识'
            ])
            ->addColumn('lang', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 10,
                'comment' => '语言包'
            ])
            ->addColumn('action', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => '操作'
            ])
            ->addColumn('file', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => '模板文件，相对于模板根目录，如portal/index.html'
            ])
            ->addColumn('more', 'text', [
                'default' => null,
                'null'    => true,
                'comment' => '模板更多配置,用户自己后台设置的',
                'limit'   => MysqlAdapter::TEXT_LONG
            ])
            ->addColumn('draft_more', 'text', [
                'default' => null,
                'null'    => true,
                'comment' => '模板更多配置,用户临时保存的配置',
                'limit'   => MysqlAdapter::TEXT_LONG
            ])
            ->addIndex('file_id')
            ->addIndex('lang')
            ->create();

    }
}
