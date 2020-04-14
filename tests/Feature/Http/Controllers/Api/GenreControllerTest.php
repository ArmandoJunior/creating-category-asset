<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index', ['genre' => $genre->getAttribute('id')]));
        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testInvalidationData()
    {
        $response = $this->postJson(route('genres.store'), []);
        $this->assertInvalidationRequired($response);

        $response =  $this->postJson(route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);

        /** @var Genre $genre */
        $genre = factory(Genre::class)->create();
        $response =  $this->putJson(route('genres.update', [
            'genre'  =>  $genre->getAttribute('id')
        ]), []);
        $this->assertInvalidationRequired($response);

        $response =  $this->putJson(route('genres.update', [
            'genre'  =>  $genre->getAttribute('id')
        ]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBolean($response);
    }

    public function testStore()
    {
        $response =  $this->postJson(route('genres.store'), [
            'name' => 'test'
        ]);
        $id = $response->json('id');
        $genre = Genre::query()->find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        $response =  $this->postJson(route('genres.store'), [
            'name' => 'test',
            'is_active' => false,
        ]);
        $id = $response->json('id');
        $genre = Genre::query()->find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'is_active' => false,
            ]);
        $this->assertFalse($response->json('is_active'));
    }

    public function testUpdate()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create([
            'is_active' => false,
        ]);

        $response =  $this->putJson(
            route('genres.update', ['genre' => $genre->getAttribute('id')]), [
            'name' => 'test',
            'is_active' => true
        ]);

        $id = $response->json('id');
        $genre = Genre::query()->find($id);

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'is_active' => true
            ]);

        $response =  $this->putJson(
            route('genres.update', ['genre' => $genre->getAttribute('id')]), [
            'name' => 'test',
            'is_active' => true
        ]);

        $response
            ->assertJsonFragment([
                'name' => 'test',
            ]);
    }

    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->deleteJson(route('genres.destroy', ['genre' => $genre->getAttribute('id')]));
        $response->assertStatus(204);
        $this->assertSoftDeleted('genres'); // test if table is softDeleted
        $this->assertSoftDeleted('genres', $genre->toArray()); // test if obect is softDeleted (I think....)
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
