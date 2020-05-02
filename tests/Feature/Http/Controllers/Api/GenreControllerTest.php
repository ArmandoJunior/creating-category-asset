<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase, TestValidations, TestSaves;

    /**
     * @var Collection|Model|mixed
     */
    private $genre;
    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get($this->routeIndex());
        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get($this->routeIndexShow());
        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testStore()
    {
        $data = ['name' => 'test'];
        $testDatabase = $data + ['is_active' => true];
        $this->assertStore($data, $testDatabase);

        $data = $data + ['is_active' => false];
        $this->assertStore($data, $data);
    }

    public function testUpdate()
    {
        $this->genre = factory(Genre::class)->create([
            'is_active' => false,
        ]);

        $data = ['name' => 'test', 'is_active' => true];
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

        $data['is_active'] = false;
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

        $data['name'] = "teste_change";
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

    }

    public function testDestroy()
    {
        $response = $this->deleteJson($this->routeDestroy());
        $response->assertStatus(204);
        $this->assertSoftDeleted('genres'); // test if table is softDeleted
        $this->assertSoftDeleted('genres', $this->genre->toArray()); // test if obect is softDeleted (I think....)
    }

    public function testInvalidationData()
    {
        $response = $this->postJson($this->routeStore(), []);
        $this->assertInvalidationRequired($response);

        $data = ['name' => str_repeat('a', 256), 'is_active' => 'a'];

        $response =  $this->postJson($this->routeStore(), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);

        $response =  $this->putJson($this->routeUpdate(), []);
        $this->assertInvalidationRequired($response);

        $response =  $this->putJson($this->routeUpdate(), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function routeDestroy()
    {
        return route('genres.destroy', ['genre' => $this->genre->getAttribute('id')]);
    }

    protected function routeIndex()
    {
        return route('genres.index');
    }

    protected function routeIndexShow()
    {
        return route('genres.index', ['genre' => $this->genre->getAttribute('id')]);
    }

    protected function fieldsRequired()
    {
        return ['name'];
    }
}
