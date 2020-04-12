<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

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

    public function testInvalidationData()
    {
        $response = $this->postJson(route('categories.store'), []);
        $this->assertInvalidationRequired($response);

        $response =  $this->postJson(route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);

        /** @var Category $category */
        $category = factory(Category::class)->create();
        $response =  $this->putJson(route('categories.update', [
            'category'  =>  $category->getAttribute('id')
        ]), []);
        $this->assertInvalidationRequired($response);

        $response =  $this->putJson(route('categories.update', [
            'category'  =>  $category->getAttribute('id')
        ]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);
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

    private function assertInvalidationRequired(TestResponse $response) : void
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    private function assertInvalidationMax(TestResponse $response) : void
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    private function assertInvalidationBolean(TestResponse $response) : void
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }
}
