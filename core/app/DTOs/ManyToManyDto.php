<?php

    namespace App\DTOs;

    use App\DTOs\Contracts\Dto;
    use Illuminate\Support\Collection;

    class ManyToManyDto extends Dto {
        protected function parse( array $data ): Collection {
            $output = [];
            if ( ! isset( $data[ 'related' ] ) ) {
                return collect();
            }
            foreach ( $data[ 'related' ] as $item ) {
                if ( isset( $item[ 'pivot' ] ) ) {
                    $output[ $item[ 'id' ] ] = $item[ 'pivot' ];
                } else {
                    $output[] = $item[ 'id' ];
                }
            }

            return collect( $output );
        }

        public function withValues( array $values, bool $force = false ): Collection {
            foreach ( $this->data as $key => $value ) {
                if ( is_array( $value ) ) {
                    $this->data[ $key ] = $force ? array_merge( $this->data[ $key ], $values ) : array_merge( $values,
                        $this->data[ $key ] );
                }
                if ( is_int( $value ) ) {
                    unset( $this->data[ $key ] );
                    $this->data[ $value ] = $values;
                }
            }

            return $this->data;
        }
    }
