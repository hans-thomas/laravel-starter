<?php

    namespace App\DTOs\Contracts;

    use Illuminate\Support\Collection;

    abstract class Dto {
        protected Collection $data;

        public function __construct( array $data ) {
            $this->data = $this->parse( $data );
        }

        abstract protected function parse( array $data ): Collection;

        public static function make( array $data ): static {
            return new static( $data );
        }

        public static function export( array $data ): Collection {
            return ( new static( $data ) )->getData();
        }

        public function getData(): Collection {
            return $this->data;
        }
    }
