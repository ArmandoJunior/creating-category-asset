<?php
declare(strict_types=1);

namespace Tests\Traits;


use Exception;
use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves
{
    protected abstract function model();
    protected abstract function routeStore();
    protected abstract function routeUpdate();

    protected function assertStore(array $sendData, array $testDatabase, array $testJsonData = null): void
    {
        /** @var TestResponse $response */
        $response =  $this->postJson($this->routeStore(), $sendData);

        if ($response->status() !== 201) {
            throw new Exception("Response status must be 201, given {$response->status()}:\n {$response->content()}");
        }

        $response->assertStatus(201);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertInDatabase($response, $testDatabase);
        $this->assertJsonFragment($response, $testDatabase, $testJsonData);
    }

    protected function assertUpdate(array $sendData, array $testDatabase, array $testJsonData = null): void
    {
        /** @var TestResponse $response */
        $response =  $this->putJson($this->routeUpdate(), $sendData);

        if ($response->status() !== 200) {
            throw new Exception("Response status must be 200, given {$response->status()}:\n {$response->content()}");
        }

        $response->assertStatus(200);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertInDatabase($response, $testDatabase);
        $this->assertJsonFragment($response, $testDatabase, $testJsonData);
    }

    private function assertInDatabase(TestResponse $response, array $testDatabase)
    {
        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDatabaseHas($table, $testDatabase + ['id' => $response->json('id')]);
    }

    private function assertJsonFragment(TestResponse $response, array $testDatabase, array $testJsonData = null)
    {
        $testResponse = $testJsonData ?? $testDatabase;
        $response->assertJsonFragment($testResponse + ['id' => $response->json('id')]);
    }
}
