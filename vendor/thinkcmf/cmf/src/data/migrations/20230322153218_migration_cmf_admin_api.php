<?php

use think\migration\Migrator;
use Phinx\Db\Adapter\MysqlAdapter;

class MigrationCmfAdminApi extends Migrator
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
        $table = $this->table('admin_api', ['comment' => "后台API列表"]);
        $table
            ->addColumn('parent_id', 'integer', [
                'default' => 0,
                'null'    => false,
                'comment' => '父级ID',
            ])
            ->addColumn('type', 'integer', [
                'default' => 0,
                'null'    => false,
                'limit'   => MysqlAdapter::INT_TINY,
                'comment' => '类型;1:纯API;2:父级节点',
            ])
            ->addColumn('url', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => '访问地址;1.界面路由,如:/users;2.API的路由,如:GET|admin/users/:id;'
            ])
            ->addColumn('name', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 50,
                'comment' => '名称'
            ])
            ->addColumn('tags', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => 'API标签列表，用于分组，以英文逗号分隔'
            ])
            ->addColumn('remark', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 255,
                'comment' => '备注'
            ])
            ->addIndex('parent_id')
            ->addIndex('url')
            ->create();

    }
}
