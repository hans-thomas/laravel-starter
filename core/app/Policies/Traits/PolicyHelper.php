<?php


    namespace App\Policies\Traits;


    use Illuminate\Support\Arr;

    trait PolicyHelper {
        private function makeAbility(): string {
            return $this->getModel() . '-' . debug_backtrace()[ 1 ][ 'function' ];
        }

        private function getModel(): string {
            return strtolower( substr( $string = Arr::last( explode( '\\', self::class ) ), 0,
                strpos( $string, 'Policy' ) ) );
        }
    }
