<?php

    namespace App\Http\Resources\Core\User;

    use App\Http\Resources\Traits\InteractsWithPivots;
    use App\Http\Resources\Traits\InteractsWithRelations;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\ResourceCollection;

    class UserCollection extends ResourceCollection {
        use InteractsWithRelations, InteractsWithPivots;

        /**
         * Transform the resource collection into an array.
         *
         * @param Request $request
         *
         * @return array
         */
        public function toArray( $request ) {
            return parent::toArray( $request );
        }

        public function with( $request ) {
            return [
                'type' => 'users'
            ];
        }

    }
