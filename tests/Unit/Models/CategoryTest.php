<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{

    private $category;

    protected function setUp() :void
    {
        parent::setUp();
        $this->category = new Category();
    }

    public function testFillableAttribute()
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->category->getDates());
        }

        $this->assertCount(count($dates), $this->category->getDates());
    }

    public function testCast()
    {
        $cast = ['id' =>  'string', 'is_active' => 'boolean'];
        $this->assertEquals($cast, $this->category->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->category->getIncrementing());
    }

    public function testIfUseTraits()
    {
        $traits = [SoftDeletes::class, Uuid::class];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }
}
