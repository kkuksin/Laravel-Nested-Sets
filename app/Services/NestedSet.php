<?php

namespace App\Services;

use DB;

class NestedSet
{
    const PARAM_NAME = 'name';
    const PARAM_DB_ID = 'dbID';
    const PARAM_PARENT_ID = 'parentId';
    const PARAM_ID = 'id';
    const NAME = 'Master';
    const NAMESPACE = "App\\Model\\";

    protected $model;
    protected $columns;
    protected $id;
    protected $parentId;
    protected $dbId;
    protected $name;
    protected $isRoot = false;

    public function __construct($model = 'Binder')
    {
        $model = static::NAMESPACE . $model;
        $this->model = new $model();
        $this->columns = $this->model->getColumns();

    }

    /**
     * @param array $options
     * @throws \Exception
     */
    public function add(array $options = [])
    {
        $this->parserParams($options);
        if ($this->isRoot) {
            $this->model->create([
                $this->columns['lft'] => 1,
                $this->columns['rgt'] => 2,
                $this->columns['dbId'] => $this->dbId,
                $this->columns['parentId'] => $this->parentId,
                $this->columns['deleted'] => false,
                $this->columns['name'] => $this->name,
            ]);
            return;
        }

        $parentNode = $this->getNode($this->parentId);

        $lftName = $this->columns['lft'];
        $rgtName = $this->columns['rgt'];
        $dbIdName = $this->columns['dbId'];

        DB::beginTransaction();
        try {

            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $this->dbId)
                ->where($lftName, '>', $parentNode->rgt)
                ->update([
                    $lftName => DB::raw($lftName . " +2"),
                    $rgtName => DB::raw($rgtName . " +2"),
                ]);

            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $this->dbId)
                ->where($lftName, '<', $parentNode->rgt)
                ->where($this->columns['rgt'], '>=', $parentNode->rgt)
                ->update([$rgtName => DB::raw($rgtName . " +2")]);

            $this->model->create([
                $lftName => $parentNode->rgt,
                $rgtName => $parentNode->rgt + 1,
                $dbIdName => $this->dbId,
                $this->columns['parentId'] => $parentNode->id,
                $this->columns['name'] => $this->name
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();

    }

    public function delete($dbId)
    {
        $this->dbId = $dbId;
        $node = $this->getIdMoreParent();
        while ($node) {
            $this->deleteNode($node);
            $node = $this->getIdMoreParent();
        }

    }

    public function deleteNode($node)
    {

        DB::beginTransaction();
        try {

            $diff = $node->rgt - $node->lft + 1;
            //delete current node
            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $node->dbId)
                ->where($this->columns['lft'], '>=', $node->lft)
                ->where($this->columns['rgt'], '<=', $node->rgt)
                ->delete();

            //update parent node
            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $node->dbId)
                ->where($this->columns['rgt'], '>', $node->rgt)
                ->where($this->columns['lft'], '<', $node->lft)
                ->update([$this->columns['rgt']=> DB::raw($this->columns['rgt'] . " - ".$diff)]);

            //update all nodes
            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $node->dbId)
                ->where($this->columns['lft'], '>', $node->rgt)
                ->update([
                    $this->columns['lft']=> DB::raw($this->columns['lft'] . " - ".$diff),
                    $this->columns['rgt']=> DB::raw($this->columns['rgt'] . " - ".$diff)
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();
    }


    public function move($id, $parentId)
    {
        $node = $this->getNode($id);
        $parentNode = $this->getNode($parentId);
        $diff = $node->rgt - $node->lft + 1;
        $offset = $parentNode->rgt - $node->lft;

        DB::beginTransaction();
        try {

            //update rows new
            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $parentNode->dbId)
                ->where($this->columns['lft'], '>', $parentNode->rgt)
                ->update([
                    $this->columns['lft']=> DB::raw($diff." + ".$this->columns['lft']),
                    $this->columns['rgt']=> DB::raw($diff." + ".$this->columns['rgt']),
                ]);

            //move from old to new
            DB::table($this->model->getTable())
                ->where($this->columns['id'], $node->id)
                ->update([$this->columns['parentId']=>$parentNode->id]);

            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $node->dbId)
                ->where($this->columns['lft'], '>=' ,$node->lft)
                ->where($this->columns['rgt'], '<=', $node->rgt)
                ->update([
                    $this->columns['dbId'] => $parentNode->dbId,
                    $this->columns['lft'] => DB::raw($this->columns['lft']."+".$offset),
                    $this->columns['rgt'] => DB::raw($this->columns['rgt']."+".$offset)
                ]);

            //update old
            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $node->dbId)
                ->where($this->columns['lft'], '>', $node->rgt)
                ->update([
                    $this->columns['lft']=> DB::raw($this->columns['lft'] . " - ".$diff),
                    $this->columns['rgt']=> DB::raw($this->columns['rgt'] . " - ".$diff)
                ]);

            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $node->dbId)
                ->where($this->columns['rgt'], '>', $node->rgt)
                ->where($this->columns['lft'], '<', $node->lft)
                ->update([$this->columns['rgt']=> DB::raw($this->columns['rgt'] . " - ".$diff)]);


            DB::table($this->model->getTable())
                ->where($this->columns['dbId'], $parentNode->dbId)
                ->where($this->columns['rgt'], '>=', $parentNode->rgt)
                ->where($this->columns['lft'], '<=', $parentNode->lft)
                ->update([$this->columns['rgt'] => DB::raw($diff."+".$this->columns['rgt'])]);

        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();


    }

    private function parserParams(array $options)
    {
        if (count($options) == 0 ||
            ($options[static::PARAM_DB_ID] == null
                && $options[static::PARAM_PARENT_ID] == null
            )
        ) {
            $this->name = static::NAME;
            $this->parentId = null;
            $this->isRoot = true;
            $this->dbId = $this->model->max($this->columns['dbId'])+1;
            return;
        }

        if ($options[static::PARAM_NAME] == null && $options[static::PARAM_PARENT_ID] !== null) {
            $this->name = mb_strtoupper(str_random(12));
        }

        $this->parentId = $options[static::PARAM_PARENT_ID];
        $this->dbId = $options[static::PARAM_DB_ID];

    }

    private function getNode($id)
    {
        $obj = $this->model
            ->where($this->columns['id'], $id)
            ->where($this->columns['deleted'], false)
            ->first();
        if (!$obj) {
            throw new \Exception('Not exists node');
        }

        return $this->convertNode($obj);
    }

    private function getIdMoreParent()
    {
        $selectedColumns = "";
        foreach ($this->columns as $value) {
            $selectedColumns .= $value . ", ";
        }

        $obj = $this->model
            ->select(DB::raw($selectedColumns . "(" . $this->columns['rgt'] . "-" . $this->columns['lft'] . ") as diff"))
            ->where($this->columns['dbId'], $this->dbId)
            ->where($this->columns['deleted'], true)
            ->orderBy('diff', 'desc')
            ->first();

        if (!$obj) {
            return null;
        }
        return $this->convertNode($obj);
    }

    private function convertNode($obj)
    {
        $node = new \stdClass();
        foreach ($this->columns as $key => $value) {
            $node->$key = $obj->$value;
        }
        return $node;
    }
}