<?php

    namespace App\Models\Contracts\Filtering;

    interface Loadable {
        public function getLoadableRelations(): array;
    }
