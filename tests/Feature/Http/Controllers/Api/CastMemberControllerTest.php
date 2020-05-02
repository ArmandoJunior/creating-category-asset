<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use RefreshDatabase, TestValidations, TestSaves;

    /**
     * @var Collection|Model|mixed
     */
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    public function testIndex()
    {
        $response = $this->get($this->routeIndex());
        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get($this->routeIndexShow());
        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testStore()
    {
        $data = ['name' => 'test', 'type'  => CastMember::TYPE_DIRECTOR];
        $this->assertStore($data, $data);

        $data = ['name' => 'test', 'type'  => CastMember::TYPE_ACTOR];
        $this->assertStore($data, $data);
    }

    public function testUpdate()
    {
        $data = ['name' => 'test', 'type' =>  CastMember::TYPE_DIRECTOR];
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

        $data['type'] = CastMember::TYPE_ACTOR;
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

        $data['name'] = "teste_change";
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

    }

    public function testDestroy()
    {
        $response = $this->deleteJson($this->routeDestroy());
        $response->assertStatus(204);
        $this->assertSoftDeleted('cast_members'); // test if table is softDeleted
        $this->assertSoftDeleted('cast_members', $this->castMember->toArray()); // test if obect is softDeleted (I think....)
    }

    public function testInvalidationData()
    {
        $response = $this->postJson($this->routeStore(), []);
        $this->assertInvalidationRequired($response);

        $data = ['name' => str_repeat('a', 256), 'type' => CastMember::TYPE_DIRECTOR];

        $response =  $this->postJson($this->routeStore(), $data);
        $this->assertInvalidationMax($response);

        $response =  $this->putJson($this->routeUpdate(), []);
        $this->assertInvalidationRequired($response);

        $response =  $this->putJson($this->routeUpdate(), $data);
        $this->assertInvalidationMax($response);
    }

    protected function model()
    {
        return CastMember::class;
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function routeDestroy()
    {
        return route('cast_members.destroy', ['cast_member' => $this->castMember->getAttribute('id')]);
    }

    protected function routeIndex()
    {
        return route('cast_members.index');
    }

    protected function routeIndexShow()
    {
        return route('cast_members.index', ['cast_member' => $this->castMember->getAttribute('id')]);
    }

    protected function fieldsRequired()
    {
        return ['name', 'type'];
    }
}
