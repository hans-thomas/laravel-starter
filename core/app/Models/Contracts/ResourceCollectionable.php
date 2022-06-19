<?php

    namespace App\Models\Contracts;

    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Http\Resources\Json\ResourceCollection;

    interface ResourceCollectionable {
        public function getResource(): JsonResource;

        public function toResource(): JsonResource;

        public function getResourceCollection(): ResourceCollection;
    }
