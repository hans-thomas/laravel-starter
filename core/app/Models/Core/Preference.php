<?php

    namespace App\Models\Core;

    use App\Models\BaseModel;
    use App\Models\Contracts\Filtering\Filterable;
    use App\Models\Traits\Paginatable;
    use Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * @mixin IdeHelperPreference
     */
    class Preference extends BaseModel implements Filterable {
        use HasFactory;
        use Paginatable;

        protected $fillable = [ 'key', 'value' ];

        protected $casts = [
            'value' => 'array'
        ];

        public function getFilterableAttributes(): array {
            return [
                'id',
                'key',
                'value',
            ];
        }
    }
