<?php
declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Lang;

trait TestValidations
{
    protected abstract function model();
    protected abstract function routeStore();
    protected abstract function routeUpdate();
    protected abstract function fieldsRequired();

    protected function assertInvalidationRequired(TestResponse $response) : void
    {
        $this->assertInvalidationFields($response, $this->fieldsRequired(), 'required');
        $response->assertJsonMissingValidationErrors(['is_active']);
    }

    protected function assertInvalidationMax(TestResponse $response) : void
    {
        $this->assertInvalidationFields($response, ['name'], 'max.string', ['max' => 255]);
    }

    protected function assertInvalidationBolean(TestResponse $response) : void
    {
        $this->assertInvalidationFields($response, ['is_active'], 'boolean');
    }

    private function assertInvalidationFields(
        TestResponse $response,
        array   $fields,
        string  $rule,
        array   $ruleParams = [])
    {
        $response->assertStatus(422)->assertJsonValidationErrors($fields);

        foreach ($fields as $field)  {
            $fieldName = str_replace('_', ' ', $field);
            $response->assertJsonFragment([
                Lang::get("validation.{$rule}", ['attribute' => $fieldName] + $ruleParams)
            ]);
        }
    }

}
