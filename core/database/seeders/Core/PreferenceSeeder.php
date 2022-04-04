<?php

    namespace Database\Seeders\Core;

    use Illuminate\Database\Seeder;

    class PreferenceSeeder extends Seeder {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run() {
            set_batch_preferences( [
                'name' => 'Starter',
            ] );
        }
    }
