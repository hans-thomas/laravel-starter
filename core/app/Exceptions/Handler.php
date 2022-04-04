<?php

    namespace App\Exceptions;

    use Hans\Alicia\Exceptions\AliciaException;
    use Hans\Horus\Exceptions\HorusException;
    use Hans\Pollux\Exceptions\PolluxException;
    use Hans\Sphinx\Exceptions\SphinxException;
    use Illuminate\Auth\Access\AuthorizationException;
    use Illuminate\Database\QueryException;
    use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Validation\ValidationException;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Throwable;

    class Handler extends ExceptionHandler {
        /**
         * A list of the exception types that are not reported.
         *
         * @var array<int, class-string<Throwable>>
         */
        protected $dontReport = [//
        ];

        /**
         * A list of the inputs that are never flashed for validation exceptions.
         *
         * @var array<int, string>
         */
        protected $dontFlash = [
            'current_password',
            'password',
            'password_confirmation',
        ];

        /**
         * Register the exception handling callbacks for the application.
         *
         * @return void
         */
        public function register() {
            $this->reportable( function( Throwable $e ) {
                //
            } );
        }

        public function render( $request, Throwable $e ) {
            if ( env( 'RAW_ERROR', false ) ) {
                return parent::render( $request, $e );
            }

            return match ( true ) {
                $e instanceof ValidationException => parent::render( $request, $e ),
                $e instanceof AuthorizationException => self::throw( $e, 9998 ),
                $e instanceof NotFoundHttpException => self::throw( $e, 9997, 'Route not found!' ),
                $e instanceof QueryException => self::throw( $e, 9996, $e->getPrevious()->getMessage(), 500 ),
                $e instanceof SphinxException, $e instanceof PolluxException, $e instanceof HorusException, $e instanceof AliciaException => self::throw( $e ),
                default => self::throw( $e, responseCode: 500 )
            };
        }

        private static function throw( Throwable $e, int $defaultErrorCode = 9999, string $message = null, int $responseCode = null ): JsonResponse {
            $errorCode = method_exists( $e, $method = 'getErrorCode' ) ? $e->{$method}() : $defaultErrorCode;

            return BaseException::make( $message ? : $e->getMessage(), $errorCode, $responseCode ? : $e->getCode() )
                                ->render();
        }
    }
