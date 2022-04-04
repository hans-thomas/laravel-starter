<?php

    namespace App\Models\Core;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    /**
 * @mixin IdeHelperPreference
 */
    class Preference extends Model {
        use HasFactory;

        protected $fillable = [ 'key', 'value' ];

        protected $casts = [
            'value' => 'array'
        ];
    }
