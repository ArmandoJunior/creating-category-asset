<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoryStub extends Model
{
//    use SoftDeletes, Uuid;

    protected $table = "categories_stubs";
    protected $fillable = ['name', 'description'];

    public static function createTable()
    {
        Schema::create('categories_stubs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        Schema::dropIfExists('categories_stubs');
    }


//    protected $dates = ['deleted_at'];
//    protected $casts = [
//        'id' => 'string',
//        'is_active' => 'boolean',
//    ];
//    public $incrementing = false;
}
