<?php

    namespace App\Http\Resources\Core\Resource;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Collection;

    class ResourcesResource extends JsonResource {
        private Collection $exports;

        /**
         * Transform the resource into an array.
         *
         * @param Request $request
         *
         * @return array
         */
        public function toArray( $request ) {
            $resource = [
                'id'           => $this->id,
                'title'        => $this->title,
                'extension'    => $this->extension,
                'options'      => $this->options,
                'url'          => $this->isExternal() ? $this->path : $this->url,
                'published_at' => $this->published_at,
            ];
            if ( isset( $this->exports ) ) {
                foreach ( $this->exports as $export ) {
                    $resource[ 'exports' ][] = self::make( $export );
                }
            } else {
                $this->resource->loadMissing( 'children' );
                foreach ( $this->resource->children as $child ) {
                    $resource[ 'exports' ][] = self::make( $child );
                }
            }

            return $resource;
        }

        public function setExports( Collection $exports ): self {
            $this->exports = $exports;

            return $this;
        }
    }
