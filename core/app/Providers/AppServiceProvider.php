<?php

    namespace App\Providers;

    use App\Services\Filtering\FilteringService;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\Relation;
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
            Model::preventLazyLoading();

            if ( env( 'ENABLE_DB_LOG', false ) ) {
                DB::listen( function( $query ) {
                    Log::info( $query->sql, $query->bindings //    $query->time
                    );
                } );
            }

            if ( ! Blueprint::hasMacro( 'order' ) ) {
                Blueprint::macro( 'order', function() {
                    return $this->unsignedDecimal( 'order', 6, 3 )->index();
                } );
            }

            if ( ! Route::hasMacro( 'belongsTo' ) ) {
                Route::macro( 'belongsTo', function( string $name, string $controller, array $options = [] ) {
                    $method    = ucfirst( Str::camel( $name ) );
                    $parameter = Str::lower( $name );

                    if ( isset( $options[ 'except' ] ) ) {
                        if ( ! in_array( 'view', $options[ 'except' ] ) ) {
                            Route::get( "{model}/" . $parameter, [ $controller, 'view' . $method ] );
                        }
                        if ( ! in_array( 'update', $options[ 'except' ] ) ) {
                            Route::post( "{model}/" . $parameter . "/{related}", [ $controller, 'update' . $method ] );
                        }
                    } else {
                        Route::get( "{model}/" . $parameter, [ $controller, 'view' . $method ] );
                        Route::post( "{model}/" . $parameter . "/{related}", [ $controller, 'update' . $method ] );
                    }
                } );
            }

            if ( ! Route::hasMacro( 'hasMany' ) ) {
                Route::macro( 'hasMany', function( string $name, string $controller, array $options = [] ) {
                    $method    = ucfirst( Str::camel( $name ) );
                    $parameter = Str::lower( $name );

                    if ( isset( $options[ 'except' ] ) ) {
                        if ( ! in_array( 'view', $options[ 'except' ] ) ) {
                            Route::get( "{model}/" . $parameter, [ $controller, 'view' . $method ] );
                        }
                        if ( ! in_array( 'update', $options[ 'except' ] ) ) {
                            Route::post( "{model}/" . $parameter, [ $controller, 'update' . $method ] );
                        }
                    } else {
                        Route::get( "{model}/" . $parameter, [ $controller, 'view' . $method ] );
                        Route::post( "{model}/" . $parameter, [ $controller, 'update' . $method ] );
                    }
                } );
            }

            if ( ! Route::hasMacro( 'morphTo' ) ) {
                Route::macro( 'morphTo', function( string $name, string $controller, array $options = [] ) {
                    Route::belongsTo( $name, $controller, $options = [] );
                } );
            }

            if ( ! Route::hasMacro( 'belongsToMany' ) ) {
                Route::macro( 'belongsToMany', function( string $name, string $controller, array $options = [] ) {
                    $method    = ucfirst( Str::camel( $name ) );
                    $parameter = Str::lower( $name );

                    if ( isset( $options[ 'except' ] ) ) {
                        if ( ! in_array( 'view', $options[ 'except' ] ) ) {
                            Route::get( '/{model}/' . $parameter, [ $controller, 'view' . $method ] );
                        }
                        if ( ! in_array( 'update', $options[ 'except' ] ) ) {
                            Route::post( '{model}/' . $parameter, [ $controller, 'update' . $method ] );
                        }
                        if ( ! in_array( 'attach', $options[ 'except' ] ) ) {
                            Route::patch( '{model}/' . $parameter, [ $controller, 'attach' . $method ] );
                        }
                        if ( ! in_array( 'detach', $options[ 'except' ] ) ) {
                            Route::delete( '{model}/' . $parameter, [ $controller, 'detach' . $method ] );
                        }
                    } else {
                        Route::get( '/{model}/' . $parameter, [ $controller, 'view' . $method ] );
                        Route::post( '{model}/' . $parameter, [ $controller, 'update' . $method ] );
                        Route::patch( '{model}/' . $parameter, [ $controller, 'attach' . $method ] );
                        Route::delete( '{model}/' . $parameter, [ $controller, 'detach' . $method ] );
                    }
                } );
            }

            if ( ! Route::hasMacro( 'morphToMany' ) ) {
                Route::macro( 'morphToMany', function( string $name, string $controller, array $options = [] ) {
                    Route::belongsToMany( $name, $controller, $options = [] );
                } );
            }

            if ( ! Route::hasMacro( 'morphedByMany' ) ) {
                Route::macro( 'morphedByMany', function( string $name, string $controller, array $options = [] ) {
                    Route::belongsToMany( $name, $controller, $options = [] );
                } );
            }
            if ( ! Route::hasMacro( 'hasOne' ) ) {
                Route::macro( 'hasOne', function( string $name, string $controller, array $options = [] ) {
                    Route::belongsTo( $name, $controller, $options = [] );
                } );
            }

            if ( ! Builder::hasGlobalMacro( 'applyFilters' ) ) {
                Builder::macro( 'applyFilters', function( array $options = [] ) {
                    return app( FilteringService::class )->apply( $this, $options );
                } );
            }

            if ( ! Relation::hasMacro( 'applyFilters' ) ) {
                Relation::macro( 'applyFilters', function( array $options = [] ) {
                    return app( FilteringService::class )->apply( $this, $options );
                } );
            }

            if ( ! Builder::hasGlobalMacro( 'whereLike' ) ) {
                Builder::macro( 'whereLike', function( $column, $value = null, $boolean = 'and' ) {
                    $this->where( $column, 'LIKE', "%{$value}%", $boolean );
                } );
            }

            if ( ! Builder::hasGlobalMacro( 'orWhereLike' ) ) {
                Builder::macro( 'orWhereLike', function( $column, $value = null, $boolean = 'and' ) {
                    $this->orWhere( $column, 'LIKE', "%{$value}%", $boolean );
                } );
            }


        }
    }
