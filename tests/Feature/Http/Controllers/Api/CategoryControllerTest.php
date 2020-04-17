<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, TestValidations;

    public function testIndex()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index', ['category' => $category->getAttribute('id')]));
        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testStore()
    {
        $response =  $this->postJson(route('categories.store'), [
            'name' => 'test'
        ]);
        $id = $response->json('id');
        $category = Category::query()->find($id);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response =  $this->postJson(route('categories.store'), [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false,
        ]);
        $id = $response->json('id');
        $category = Category::query()->find($id);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'description' => 'description',
                'is_active' => false,
            ]);
        $this->assertFalse($response->json('is_active'));
        $this->assertNotNull($response->json('description'));
    }

    public function testUpdate()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false,
        ]);

        $response =  $this->putJson(
            route('categories.update', ['category' => $category->getAttribute('id')]), [
            'name' => 'test',
            'description' => 'test',
            'is_active' => true
        ]);

        $id = $response->json('id');
        $category = Category::query()->find($id);

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'description' => 'test',
                'is_active' => true
            ]);

        $response =  $this->putJson(
            route('categories.update', ['category' => $category->getAttribute('id')]), [
            'name' => 'test',
            'description' => '',
            'is_active' => true
        ]);

        $response
            ->assertJsonFragment([
                'description' => null,
            ]);
    }

    public function testDestroy()
    {
        $category = factory(Category::class)->create();
        $response = $this->deleteJson(route(
            'categories.destroy',
            ['category' => $category->getAttribute('id')]
        ));
        $response->assertStatus(204);
        $this->assertSoftDeleted('categories'); // test if table is softDeleted
        $this->assertSoftDeleted('categories', $category->toArray()); // test if obect is softDeleted (I think....)
    }

    public function testInvalidationData()
    {
        $response = $this->postJson(route('categories.store'));
        $this->assertInvalidationRequired($response);

        $data = ['name' => str_repeat('a', 256), 'is_active' => 'a'];

        $response =  $this->postJson(route('categories.store'), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);

        $category = factory(Category::class)->create();

        $response =  $this->putJson(route('categories.update', ['category'  =>  $category->id]));
        $this->assertInvalidationRequired($response);

        $response =  $this->putJson(route('categories.update', ['category'  =>  $category->id]), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);
    }

}
