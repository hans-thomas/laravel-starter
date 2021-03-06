<?php


    use App\Helpers\Enums\CacheEnum;
    use App\Models\Contracts\ResourceCollectionable;
    use App\Models\Core\Preference;
    use App\Models\Core\User;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Optional;

    if ( ! function_exists( 'user' ) ) {
        function user(): User|Optional {
            return Auth::check() ? Auth::user() : optional();
        }
    }

    if ( ! function_exists( 'get_preference' ) ) {
        function get_preference( string $key, bool $fresh = false ) {
            if ( $fresh ) {
                try {
                    Cache::forever( CacheEnum::PREFIX->value . $key,
                        $value = Preference::firstWhere( 'key', $key )->value );

                    return $value;
                } catch ( Throwable $e ) {

                    return null;
                }
            }

            if ( $value = Cache::get( CacheEnum::PREFIX->value . $key ) ) {
                return $value;
            } else {
                try {
                    Cache::forever( CacheEnum::PREFIX->value . $key,
                        $value = Preference::firstWhere( 'key', $key )->value );

                    return $value;
                } catch ( Throwable $e ) {
                    return null;
                }
            }
        }
    }

    if ( ! function_exists( 'set_preference' ) ) {
        function set_preference( string $key, $value ): bool {
            try {
                $value = Preference::updateOrCreate( [ 'key' => $key ], [ 'value' => $value ] )->value;
                Cache::forever( CacheEnum::PREFIX->value . $key, $value );
            } catch ( Throwable $e ) {
                return false;
            }

            return true;
        }
    }

    if ( ! function_exists( 'set_batch_preferences' ) ) {
        function set_batch_preferences( array $preferences ): bool {
            try {
                foreach ( $preferences as $key => $value ) {
                    set_preference( $key, $value );
                }
            } catch ( Throwable $e ) {
                return false;
            }

            return true;
        }
    }

    if ( ! function_exists( 'generate_order' ) ) {
        // generates random order for factories
        function generate_order(): float {
            return rand( 111111, 999999 ) / 1000;
        }
    }

    if ( ! function_exists( 'resolveRelatedIdToModel' ) ) {
        function resolveRelatedIdToModel( int $related, string $entity, array $allowedEntities ): Model|false {
            if ( in_array( $entity, array_keys( $allowedEntities ) ) ) {
                return ( new $allowedEntities[ $entity ] )->findOrFail( $related );
            }

            return false;
        }
    }

    if ( ! function_exists( 'resolveMorphableToResource' ) ) {
        function resolveMorphableToResource( Model $morphable ): JsonResource {
            if ( $morphable instanceof ResourceCollectionable ) {
                return $morphable->toResource();
            }

            return JsonResource::make( $morphable );
        }
    }

    if ( ! function_exists( 'slugify' ) ) {
        function slugify( $string, $separator = '-' ) {

            $_transliteration = [
                "/??|??/" => "e",

                "/??/" => "e",

                "/??/" => "e",

                "/??/" => "e",

                "/??/" => "e",

                "/??|??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??|??|??/" => "",

                "/??|??|??|??|??/" => "",

                "/??|??|??/" => "",

                "/??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??|??/" => "",

                "/??|??|??|??/" => "",

                "/??|??/" => "",

                "/??|??/" => "",

                "/??|??|??|??|??|??| ??|??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??|??/" => "",

                "/??/" => "",

                "/??/" => "",

                "/??/" => "",

                "/??/" => "",

                "/??|??|??|??|??/" => "",

                "/??|??|??|??|??/" => "",

                "/??|??|??|??/" => "",

                "/??|??|??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??/" => "",

                "/??|??|??/" => "",

                "/??|??|??|??|??/" => "",

                "/??|??|??|??|??|??/" => "",

                "/??|??|??|??/" => "",

                "/??|??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??|??|??|??|??|??|??|??|??|??|??|??|??/" => "",

                "/??|??|??/" => "",

                "/??|??|??/" => "",

                "/??/" => "",

                "/??/" => "",

                "/??|??|??/" => "",

                "/??|??|??/" => "",

                "/??|??/" => "E",

                "/??/" => "s",

                "/??/" => "J",

                "/??/" => "j",

                "/??/" => "E",

                "/??/" => ""
            ];

            $quotedReplacement = preg_quote( $separator, '/' );

            $merge = [

                '/[^\s\p{Zs}\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',

                '/[\s\p{Zs}]+/mu' => $separator,

                sprintf( '/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement ) => '',

            ];

            $map = $_transliteration + $merge;

            unset( $_transliteration );

            return preg_replace( array_keys( $map ), array_values( $map ), $string );

        }
    }
