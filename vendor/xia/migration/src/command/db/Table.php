<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\migration\command\db;


use Phinx\Db\Table\Index;

class Table extends \Phinx\Db\Table
{
    /**
     * 设置表注释
     * @param $comment
     * @return $this
     */
    public function setComment($comment): Table
    {
        $this->changeComment($comment);
        return $this;
    }

    /**
     * @return $this
     * @author : 小夏
     * @date   : 2021-04-28 13:55:54
     */
    public function addSoftDelete(): Table
    {
        $this->addColumn(Column::TIMESTAMP('delete_time')->setNullable());
        return $this;
    }

    /**
     * @param $name
     * @param null $indexName
     * @return $this
     * @author : 小夏
     * @date   : 2021-04-28 13:55:45
     */
    public function addMorphs($name, $indexName = null): Table
    {
        $this->addColumn(Column::unsignedInteger("{$name}_id"));
        $this->addColumn(Column::string("{$name}_type"));
        $this->addIndex(["{$name}_id", "{$name}_type"], ['name' => $indexName]);
        return $this;
    }

    /**
     * @param $name
     * @param null $indexName
     * @return $this
     * @author : 小夏
     * @date   : 2021-04-28 13:55:38
     */
    public function addNullableMorphs($name, $indexName = null): Table
    {
        $this->addColumn(Column::unsignedInteger("{$name}_id")->setNullable());
        $this->addColumn(Column::string("{$name}_type")->setNullable());
        $this->addIndex(["{$name}_id", "{$name}_type"], ['name' => $indexName]);
        return $this;
    }

    /**
     * @param string $createdAt
     * @param string $updatedAt
     * @param false $withTimezone 是否在添加的列上设置时区选项
     * @return Table
     * @author : 小夏
     * @date   : 2021-04-28 13:54:58
     */
    public function addTimestamps($createdAt = 'create_time', $updatedAt = 'update_time', $withTimezone = false): Table
    {
        return parent::addTimestamps($createdAt, $updatedAt, $withTimezone);
    }

    /**
     * @param \Phinx\Db\Table\Column|string $columnName
     * @param null $type
     * @param array $options
     * @return \Phinx\Db\Table|Table
     */
    public function addColumn($columnName, $type = null, $options = [])
    {
        if ($columnName instanceof Column && $columnName->getUnique()) {
            $index = new Index();
            $index->setColumns([$columnName->getName()]);
            $index->setType(Index::UNIQUE);
            $this->addIndex($index);
        }
        return parent::addColumn($columnName, $type, $options);
    }

    /**
     * @param string $columnName
     * @param null $newColumnType
     * @param array $options
     * @return \Phinx\Db\Table|Table
     */
    public function changeColumn($columnName, $newColumnType = null, array $options = [])
    {
        if ($columnName instanceof \Phinx\Db\Table\Column) {
            return parent::changeColumn($columnName->getName(), $columnName, $options);
        }
        return parent::changeColumn($columnName, $newColumnType, $options);
    }
}
