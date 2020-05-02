<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, TestValidations, TestSaves;

    /**
     * @var Collection|Model|mixed
     */
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get($this->routeIndex());
        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get($this->routeIndexShow());
        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testStore()
    {
        $data = ['name' => 'test'];
        $testDatabase = $data + ['description' => null, 'is_active' => true];
        $this->assertStore($data, $testDatabase);

        $data = ['name' => 'test', 'description' => 'description', 'is_active' => false];
        $this->assertStore($data, $data);
    }

    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false,
        ]);

        $data = ['name' => 'test', 'description' => 'test', 'is_active' => true];
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

        $data = ['name' => 'test', 'description' => ''];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, $data);

        $data['description'] = null;
        $this->assertUpdate($data, $data);
    }

    public function testDestroy()
    {
        $response = $this->deleteJson($this->routeDestroy());
        $response->assertStatus(204);
        $this->assertSoftDeleted('categories'); // test if table is softDeleted
        $this->assertSoftDeleted('categories', $this->category->toArray()); // test if obect is softDeleted (I think....)
    }

    public function testInvalidationData()
    {
        $response = $this->postJson($this->routeStore());
        $this->assertInvalidationRequired($response);

        $data = ['name' => str_repeat('a', 256), 'is_active' => 'a'];

        $response =  $this->postJson($this->routeStore(), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);

        $response =  $this->putJson($this->routeUpdate());
        $this->assertInvalidationRequired($response);

        $response =  $this->putJson($this->routeUpdate(), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }

    protected function routeDestroy()
    {
        return route('categories.destroy', ['category' => $this->category->getAttribute('id')]);
    }

    protected function routeIndex()
    {
        return route('categories.index');
    }

    protected function routeIndexShow()
    {
        return route('categories.index', ['category' => $this->category->getAttribute('id')]);
    }

    protected function fieldsRequired()
    {
        return ['name'];
    }
}
