<?php

    namespace App\Services\Filtering;

    use App\Services\Filtering\Filters\ApplyCategoriesFilter;
    use App\Services\Filtering\Filters\ApplyValuesFilter;
    use App\Services\Filtering\Filters\IncludeFilter;
    use App\Services\Filtering\Filters\LikeFilter;
    use App\Services\Filtering\Filters\OrderFilter;
    use App\Services\Filtering\Filters\OrWhereRelationFilter;
    use App\Services\Filtering\Filters\OrWhereRelationLikeFilter;
    use App\Services\Filtering\Filters\WhereFilter;
    use App\Services\Filtering\Filters\WherePivotFilter;
    use App\Services\Filtering\Filters\WhereRelationFilter;
    use App\Services\Filtering\Filters\WhereRelationLikeFilter;
    use Illuminate\Contracts\Database\Eloquent\Builder;
    use Illuminate\Support\Arr;

    class FilteringService {
        private array $registered = [
            'apply_values_filter'           => ApplyValuesFilter::class,
            'apply_categories_filter'       => ApplyCategoriesFilter::class,
            'include_filter'                => IncludeFilter::class,
            'like_filter'                   => LikeFilter::class,
            'order_filter'                  => OrderFilter::class,
            'where_filter'                  => WhereFilter::class,
            'where_pivot_filter'            => WherePivotFilter::class,
            'where_relation_filter'         => WhereRelationFilter::class,
            'where_relation_like_filter'    => WhereRelationLikeFilter::class,
            'or_where_relation_filter'      => OrWhereRelationFilter::class,
            'or_where_relation_like_filter' => OrWhereRelationLikeFilter::class,
        ];

        public function apply( Builder $builder, array $options ): Builder {
            foreach ( $this->scopeActions( $options ) as $key => $filter ) {
                if ( request()->has( $key ) ) {
                    call_user_func( [ new $filter(), 'apply' ], $builder, request()->input( $key ) );
                }
            }

            return $builder;
        }

        private function scopeActions( array $options ): array {
            $actions = $this->registered;
            if ( isset( $options[ 'only' ] ) ) {
                $actions = array_intersect( $actions, Arr::wrap( $options[ 'only' ] ) );
            }

            if ( isset( $options[ 'except' ] ) ) {
                $actions = array_diff( $actions, Arr::wrap( $options[ 'except' ] ) );
            }

            return $actions;
        }

    }
