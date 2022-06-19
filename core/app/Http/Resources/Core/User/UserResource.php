<?php

    namespace App\Http\Resources\Core\User;

    use App\Http\Resources\Traits\InteractsWithPivots;
    use App\Http\Resources\Traits\InteractsWithRelations;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class UserResource extends JsonResource {
        use InteractsWithRelations, InteractsWithPivots;

        /**
         * Transform the resource into an array.
         *
         * @param Request $request
         *
         * @return array
         */
        public function toArray( $request ) {
            $data = [
                'id'         => $this->id,
                'name'       => $this->name,
                'email'      => $this->email,
                'created_at' => $this->created_at
            ];
            $data = $this->loadedRelations( $data );
            $data = $this->loadedPivots( $data );

            return $data;
        }

        public function with( $request ) {
            return [
                'type' => 'users'
            ];
        }

    }
