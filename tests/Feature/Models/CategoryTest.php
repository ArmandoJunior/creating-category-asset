<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use mysql_xdevapi\DocResult;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Category::class, 10)->create();
        $categoryFields = [
            'id',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at'
        ];
        $categories = Category::all();
        $this->assertCount(10, $categories);
        $categoryKeys =  array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing($categoryFields, $categoryKeys);
    }

    public function testCreate()
    {
        $category = Category::create([
            'name'  => 'test1'
        ]);

        $category->refresh();

        $this->assertEquals(36, strlen($category->id));
        $this->assertTrue(is_string($category->id));
        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create(['name'  => 'test1', 'description'  => null]);
        $this->assertNull($category->description);

        $category = Category::create(['name'  => 'test1', 'description'  => 'test description']);
        $this->assertEquals('test description', $category->description);

        $category = Category::create(['name'  => 'test1', 'is_active'  => false]);
        $this->assertFalse($category->is_active);

        $category = Category::create(['name'  => 'test1', 'is_active'  => true]);
        $this->assertTrue($category->is_active);

        try {
            Category::create(['name'  => null, 'is_active'  => true]);
        }catch (QueryException $queryException) {
            $this->assertTrue(strpos($queryException->getMessage(), 'Column \'name\' cannot be null') !== false);
        }
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'test_description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active' => true
        ];

        $category->update($data);

        foreach ($data as $key => $value){
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDelete()
    {
        /** @var Category $category */
        $category =factory(Category::class)->create();
        $category->delete();
        $this->assertNull(Category::query()->find($category->id));
        $category->restore();
        $this->assertNotNull(Category::query()->find($category->id));
    }
}
