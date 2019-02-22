<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Binder extends Model implements INestedSet
{
    //

    protected $table = 'Binders_interview';
    protected $fillable = ['idtblBinder', 'lft', 'rgt', 'idtblDatabaseIndexU', 'Parent', 'Deleted', 'BinderName'];
    public $timestamps = false;


    /**
     * @return array|mixed
     */
    public function getColumns()
    {
        return [
            'id' => 'idtblBinder',
            'lft' => 'lft',
            'rgt' => 'rgt',
            'dbId' => 'idtblDatabaseIndexU',
            'parentId' => 'Parent',
            'deleted' => 'Deleted',
            'name' => 'BinderName'
        ];
    }

}
