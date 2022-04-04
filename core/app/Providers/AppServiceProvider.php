<?php

    namespace App\Providers;

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Support\Str;

    class AppServiceProvider extends ServiceProvider {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register() {
            //
        }

        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot() {
            if ( env( 'DB_LOG_STATUS', false ) ) {
                DB::listen( function( $query ) {
                    Log::info( $query->sql, $query->bindings //    $query->time
                    );
                } );
            }

            if ( ! Blueprint::hasMacro( 'order' ) ) {
                Blueprint::macro( 'order', function() {
                    $this->unsignedDecimal( 'order', 6, 3 )->index();
                } );
            }

            if ( ! Route::hasMacro( 'belongsTo' ) ) {
                // one to one and one to many relations
                Route::macro( 'belongsTo', function( string $name, string $controller ) {
                    $method    = ucfirst( Str::camel( $name ) );
                    $parameter = Str::lower( $name );

                    Route::get( "{model}/" . $parameter, [ $controller, 'view' . $method ] );
                    Route::post( "{model}/" . $parameter . "/{related}", [ $controller, 'update' . $method ] );
                } );
            }

            if ( ! Route::hasMacro( 'hasMany' ) ) {
                // one to one and one to many relations
                Route::macro( 'hasMany', function( string $name, string $controller ) {
                    $method    = ucfirst( Str::camel( $name ) );
                    $parameter = Str::lower( $name );

                    Route::get( "{model}/" . $parameter, [ $controller, 'view' . $method ] );
                    Route::post( "{model}/" . $parameter, [ $controller, 'update' . $method ] );
                } );
            }

            if ( ! Route::hasMacro( 'belongsToMany' ) ) {
                // many to many relations
                Route::macro( 'belongsToMany', function( string $name, string $controller ) {
                    $method    = ucfirst( Str::camel( $name ) );
                    $parameter = Str::lower( $name );

                    Route::get( '/{model}/' . $parameter, [ $controller, 'view' . $method ] );
                    Route::post( '{model}/' . $parameter, [ $controller, 'update' . $method ] );
                    Route::patch( '{model}/' . $parameter, [ $controller, 'attach' . $method ] );
                    Route::delete( '{model}/' . $parameter, [ $controller, 'detach' . $method ] );
                } );
            }

        }
    }
