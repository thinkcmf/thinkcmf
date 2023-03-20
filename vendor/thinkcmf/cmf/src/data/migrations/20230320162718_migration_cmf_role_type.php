<?php

use think\migration\Migrator;

class MigrationCmfRoleType extends Migrator
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
        $table = $this->table('role');
        $table->addColumn('type', 'string', [
            'default' => 'admin', // 默认值
            'null'    => false, // 不能为空
            'comment' => '角色类型', // 字段注释
            'limit'   => 50,
            'after'   => 'name'
        ])
            ->update();
    }
}
