<?php

namespace Sys;

use Faker;

abstract class FakeSeeder
{
    protected Faker\Generator $faker;
    protected array $actions;
    protected array $locales = [
        'en' => 'en_US',
        'de' => 'de_DE',
        'ru' => 'ru_RU',
    ];
    protected array $columns;

    public function __construct()
    {
        $this->actions = [
            'name' => fn() => $this->faker->name(),
            'email' => fn() => $this->faker->email(),
            'lang' => fn() => $this->faker->randomElement(['en', 'de', 'ru']),
            'status' => fn() => $this->faker->numberBetween(1, 100),
            'role' => fn() => $this->faker->numberBetween(0, 25) * 10,
            'created' => fn() => $this->faker->dateTimeBetween('-13 years')->format('Y-m-d h:i:s'),
        ];
    }

    public function generate()
    {
        foreach ($this->columns as $col) {
            if (array_key_exists($col, $this->actions)) {               
                    $data[$col] = $this->actions[$col]();  
            }
        }

        return $data;
    }

    protected function json(array $props)
    {
        foreach ($props as $key => $val) {
            if (is_numeric($val)) {
                $array[$key] = $this->faker->text($val);
            } else {
                $array[$key] = $val;
            }
        }

        return json_encode($array);
    }
}
