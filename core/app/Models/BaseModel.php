<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class BaseModel extends Model {
        public static function aliasForModelAttributes( Model $model, array $aliases ) {
            foreach ( $aliases as $raw => $alias ) {
                if ( $model->getAttributeFromArray( $alias ) ) {
                    $model->{$raw} = $model->getAttributeFromArray( $alias );
                    $model->offsetUnset( $alias );
                }
            }
        }
    }
