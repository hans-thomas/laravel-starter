<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Models\Core\User;
    use DateTimeImmutable;
    use Hans\Sphinx\Contracts\SphinxContract;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\ValidationException;

    class RefreshTokenController extends Controller {
        /**
         * @throws ValidationException
         */
        public function __invoke( Request $request, SphinxContract $jwt ): JsonResponse {
            $this->validationRequest( $request );

            $token = $jwt->extract( $request->bearerToken() );
            if ( ! $token->headers()->get( 'refresh', false ) ) {
                throw ValidationException::withMessages( [
                    'token' => 'Please send a refresh token!'
                ] );
            }
            $diff = ( new DateTimeImmutable( 'UTC' ) )->diff( $token->claims()->get( 'exp' ) );
            if ( '-' == $diff->format( '%R' ) ) {
                throw ValidationException::withMessages( [
                    'refresh_token' => 'Refresh token expired!'
                ] );
            }
            $insideToken = $jwt->getInsideToken( $request->bearerToken() );
            $credentials = [
                'id'             => $insideToken->claims()->get( 'user' )[ 'id' ],
                User::username() => $insideToken->claims()->get( 'user' )[ User::username() ],
            ];
            $user        = User::whereId( $credentials[ 'id' ] )
                               ->where( User::username(), $credentials[ User::username() ] )
                               ->first();
            if ( $user ) {
                return new JsonResponse( [
                    'access_token'  => $jwt->create( $user )->accessToken(),
                    'refresh_token' => $jwt->createRefreshToken( $user )->refreshToken(),
                    'user'          => $user->extract()
                ], 200 );
            }
            throw ValidationException::withMessages( [
                'refresh_token' => 'Refresh token is invalid!'
            ] );
        }

        private function validationRequest( Request $request ) {
            Validator::make( array_merge( $request->all(), [
                'token' => $request->bearerToken()
            ] ), [
                'token'                => [ 'required', 'string' ],
                'g-recaptcha-response' => env( 'RECAPTCHAV3_ENABLE',
                    false ) ? 'required|recaptchav3:login,0.5' : 'nullable'
            ] )->validate();
        }
    }
