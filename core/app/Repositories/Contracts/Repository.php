<?php

    namespace App\Repositories\Contracts;

    use Illuminate\Contracts\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Gate;

    abstract class Repository {
        abstract protected function getQueryBuilder(): Builder;

        abstract protected function getModelClassName(): string;

        public function all(): Builder {
            Gate::authorize( $this->policy( 'viewAny' ), $this->getModelClassName() );

            return $this->getQueryBuilder();
        }

        public function find( Model|int $model ): Model {
            $model = $this->resolveModel( $model );
            Gate::authorize( $this->policy( 'view' ), $model );

            return $model;
        }

        public function delete( Model|int $model ): bool {
            $model = $this->resolveModel( $model );
            Gate::authorize( $this->policy(), $model );

            return $model->delete();
        }

        public function create( array $data ): Model {
            Gate::authorize( $this->policy(), $this->getModelClassName() );

            return $this->getQueryBuilder()->create( $data );
        }

        public function update( Model|int $model, array $data ): bool {
            $model = $this->resolveModel( $model );
            Gate::authorize( $this->policy(), $model );

            return $model->update( $data );
        }

        protected function policy( string $ability = null ): string {
            return $ability ? : debug_backtrace()[ 1 ][ 'function' ];
        }

        protected function resolveModel( Model|int $model ): Model {
            return $model instanceof Model ? $model : $this->getQueryBuilder()->findOrFail( $model );
        }
    }
