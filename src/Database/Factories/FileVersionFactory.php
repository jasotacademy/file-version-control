<?php

namespace Jasotacademy\FileVersionControl\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jasotacademy\FileVersionControl\Models\FileVersion;

class FileVersionFactory extends Factory
{

    protected $model = FileVersion::class;

    public function definition(): array
    {
        return [
            'file_id' => $this->faker->numberBetween(1, 10),
            'version_number' => $this->faker->numberBetween(1, 5),
            'path' => 'files/' . $this->faker->randomDigit() . '/v' . $this->faker->randomDigit() . '_document.txt',
            'filename' => $this->faker->word() . '.txt',
            'mime_type' => 'text/plain',
            'metadata' => ['author' => $this->faker->name()],
            'created_by' => null,
        ];
    }
}