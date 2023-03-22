<?php
use think\migration\Migrator;

class MigrationCmfAuthPolicy extends Migrator
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
        $table = $this->table('auth_policy', ['comment' => "权限认证策略表"]);
        $table
            ->addColumn('ptype', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 50,
                'comment' => '策略类型'
            ])
            ->addColumn('v0', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => ''
            ])
            ->addColumn('v1', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => ''
            ])
            ->addColumn('v2', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => ''
            ])
            ->addColumn('v3', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => ''
            ])
            ->addColumn('v4', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => ''
            ])
            ->addColumn('v5', 'string', [
                'default' => '',
                'null'    => false,
                'limit'   => 100,
                'comment' => ''
            ])
            ->addIndex('ptype', ['limit' => 50])
            ->create();

    }
}
