<?php

    namespace App\Services\Filtering\Filters;

    use App\Services\Contracts\Filters\Filter;
    use Illuminate\Contracts\Database\Eloquent\Builder;

    class WhereFilter extends Filter {

        public function apply( Builder $builder, $values = null ) {
            foreach ( $values ?? [] as $attribute => $where ) {
                $items = collect( explode( ',', $where ) )->map( fn( $value ) => filter_var( $value,
                    FILTER_SANITIZE_FULL_SPECIAL_CHARS ) )->filter( fn( $value ) => ! empty( $value ) );
                if ( in_array( $attribute,
                        $filterables = $this->getFilterables( $builder ) ) and $items->isNotEmpty() ) {
                    $builder->whereIn( $this->getTable( $builder ) . '.' . $this->resolveAttribute( $filterables,
                            $attribute ), $items );
                }
            }
        }

    }
