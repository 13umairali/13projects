<?php

namespace Database\Seeders;

use App\Services\ModuleManagerService;
use Illuminate\Database\Seeder;

class SystemCoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoRestrictionSeeder::class,
            SystemSettingSeeder::class,
        ]);

        app(ModuleManagerService::class)->syncFromNwidart();
    }
}
