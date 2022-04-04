<?php

    namespace App\Models\Core;

    use App\Mail\ResetPasswordEmail;
    use Hans\Alicia\Traits\AliciaRelationHandler;
    use Hans\Horus\HasRoles;
    use Hans\Horus\Models\Traits\HasRelations;
    use Hans\Kollector\Traits\KollectorTrait;
    use Hans\Sphinx\Traits\SphinxTrait;
    use Illuminate\Auth\Notifications\VerifyEmail;
    use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
    use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Spatie\Activitylog\LogOptions;
    use Spatie\Activitylog\Traits\LogsActivity;

    /**
 * @mixin IdeHelperUser
 */
    class User extends Authenticatable implements MustVerifyEmail, AuthenticatableContract {
        use HasFactory, Notifiable, LogsActivity;
        use AliciaRelationHandler, HasRoles, HasRelations;

        use SphinxTrait, SphinxTrait {
            SphinxTrait::booted as private handleCaching;
        }
        use KollectorTrait, KollectorTrait {
            KollectorTrait::booted as private handleWallet;
        }

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $fillable = [
            'name',
            'email',
            'password',
            'tokens',
            'version'
        ];

        /**
         * The attributes that should be hidden for serialization.
         *
         * @var array<int, string>
         */
        protected $hidden = [
            'password',
        ];

        /**
         * The attributes that should be cast.
         *
         * @var array<string, string>
         */
        protected $casts = [
            'email_verified_at' => 'datetime',
            'tokens'            => 'array',
            'version'           => 'integer'
        ];

        protected static function booted() {
            self::handleCaching();
            self::handleWallet();
        }

        public function getActivitylogOptions(): LogOptions {
            return LogOptions::defaults()->logFillable();
        }

        /**
         * Send the password reset notification.
         *
         * @param string $token
         *
         * @return void
         */
        public function sendPasswordResetNotification( $token ) {
            $this->notify( new ResetPasswordEmail( $token ) );
        }

        /**
         * Send the email verification notification.
         *
         * @return void
         */
        public function sendEmailVerificationNotification() {
            $this->notify( new VerifyEmail );
        }

        /**
         * count of logged-in users at a same time in one account
         *
         * @return int
         */
        public function getDeviceLimit(): int {
            return 2;
        }

        public function extract(): array {
            return [
                'name' => $this->name
            ];
        }

        public static function username(): string {
            return 'email';
        }
    }
