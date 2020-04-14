<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function testList()
    {
        factory(Genre::class, 10)->create();
        $genreFields = [
            'id',
            'name',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at'
        ];
        $genres = Genre::all();
        $this->assertCount(10, $genres);
        $genreKeys =  array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing($genreFields, $genreKeys);
    }

    public function testCreate()
    {
        /** @var Genre $genre */
        $genre = Genre::query()->create([
            'name'  => 'test1'
        ]);

        $genre->refresh();

        $this->assertEquals(36, strlen($genre->id));
        $this->assertTrue(is_string($genre->id));
        $this->assertEquals('test1', $genre->name);
        $this->assertNull($genre->description);
        $this->assertTrue($genre->is_active);

        $genre = Genre::query()->create(['name'  => 'test1', 'description'  => null]);
        $this->assertNull($genre->description);

        $genre = Genre::query()->create(['name'  => 'test1']);
        $this->assertEquals('test1', $genre->name);

        $genre = Genre::query()->create(['name'  => 'test1', 'is_active'  => false]);
        $this->assertFalse($genre->is_active);

        $genre = Genre::query()->create(['name'  => 'test1', 'is_active'  => true]);
        $this->assertTrue($genre->is_active);

        try {
            Genre::query()->create(['name'  => null, 'is_active'  => true]);
        }catch (QueryException $queryException) {
            $this->assertTrue(strpos($queryException->getMessage(), 'Column \'name\' cannot be null') !== false);
        }
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'test_name_updated',
            'is_active' => true
        ];

        $genre->update($data);

        foreach ($data as $key => $value){
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        /** @var Genre $genre */
        $genre =factory(Genre::class)->create();
        $genre->delete();
        $this->assertNull(Genre::query()->find($genre->id));
        $genre->restore();
        $this->assertNotNull(Genre::query()->find($genre->id));
    }
}
