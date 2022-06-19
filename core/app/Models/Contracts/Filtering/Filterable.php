<?php

    namespace App\Models\Contracts\Filtering;

    interface Filterable {
        public function getFilterableAttributes(): array;
    }
