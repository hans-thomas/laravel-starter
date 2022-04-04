<?php

    namespace App\Helpers\Enums;

    use App\Helpers\Traits\EnumHelper;

    enum StatusEnum: string {
        use EnumHelper;

        case ACCEPTED = 'accepted';
        case DENIED = 'denied';
        case PENDING = 'pending';
    }
