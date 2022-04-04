<?php

    namespace Tests\Feature;

    use App\Models\Core\User;
    use Exception;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;

    class ApiAuthenticationTest extends TestCase {

        /**
         * @test
         *
         *
         * @return void
         */
        public function register() {
            $response = $this->postJson( route( 'api.register' ), [
                'name'                  => $name = 'hans',
                User::username()        => $username = 'hans@email.com',
                'password'              => 'password',
                'password_confirmation' => 'password'
            ] )->assertCreated()->assertJsonStructure( [
                'access_token',
                'refresh_token',
                'user'
            ] );
            $this->assertDatabaseHas( User::class, [ User::username() => $username ] );
            $data  = json_decode( $response->getContent() );
            $token = $data->access_token;
            request()->headers->set( 'Authorization', 'Bearer ' . $token );

            $unToken = $this->sphinx->session()->getInsideToken( $token );

            $this->assertTrue( $unToken->claims()->has( 'user' ) );
            $this->assertEquals( [
                'id'             => ( $user = User::where( User::username(), $username )->first() )->id,
                'name'           => $name,
                User::username() => $user->{$user->getUsername()}
            ], $unToken->claims()->get( 'user' ) );

        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function login() {
            $user     = UserFactory::createAUser();
            $response = $this->postJson( route( 'api.login' ), [
                User::username() => $user->{$user->getUsername()},
                'password'       => 'password', // default password
            ] )->assertOk()->assertJsonStructure( [
                'access_token',
                'refresh_token',
                'user'
            ] );

            $data  = json_decode( $response->getContent() );
            $token = $data->access_token;
            request()->headers->set( 'Authorization', 'Bearer ' . $token );

            $unToken = $this->sphinx->session()->getInsideToken( $token );

            $this->assertTrue( $unToken->claims()->has( 'user' ) );
            $this->assertEquals( [
                'id'             => $user->id,
                'name'           => $user->name,
                User::username() => $user->{$user->getUsername()}
            ], $unToken->claims()->get( 'user' ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function FailedIfRequestForRegistrationIsNotValid() {
            $this->postJson( route( 'api.register' ), [
                'name'                  => [ 1 ],
                'email'                 => $email = 'hans@@email.c4',
                'password'              => 'password',
                'password_confirmation' => 'passwordw',
            ] )->assertJsonValidationErrors( [
                'name',
                'email',
                'password',
            ] );

            $this->assertDatabaseMissing( User::class, [ 'email' => $email ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function FailedIfRequestForLoginIsNotValid() {
            $this->postJson( route( 'api.login' ), [
                'email'    => 'itsNotTheUserEmail.fail.ed',
                'password' => 'password5',
            ] )->assertJsonValidationErrors( [
                'email'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function FailedIfCredentialsForLoginIsNotValid() {
            $this->postJson( route( 'api.login' ), [
                'email'    => 'itsNotTheUserEmail@fail.ed',
                'password' => 'password5',
            ] )->assertJsonValidationErrors( [
                'email'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         * @throws Exception
         */
        public function refreshToken() {
            $user         = UserFactory::createNormalUserWithSession();
            $refreshToken = UserFactory::createRefreshToken( $user );

            $this->postJson( route( 'api.refresh.token' ), headers: [
                'Authorization' => 'Bearer ' . $refreshToken
            ] )->assertOk()->assertJsonStructure( [
                'access_token',
                'refresh_token',
                'user'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function refreshTokenExpired() {
            config()->set( 'sphinx.refreshExpired_at', '-4 second' );
            $user  = UserFactory::createNormalUserWithSession();
            $token = $this->sphinx->setConfig( config( 'sphinx' ) )
                                  ->session( $user->sessions()->first() )
                                  ->createRefreshToken( $user )
                                  ->refreshToken();

            $this->postJson( route( 'api.refresh.token' ), headers: [
                'Authorization' => 'Bearer ' . $token
            ] )->assertUnprocessable()->assertJsonValidationErrors( [
                'refresh_token' => 'Refresh token expired!'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function refreshTokenTokenRequired() {
            $this->postJson( route( 'api.refresh.token' ) )
                 ->assertUnprocessable()
                 ->assertJsonValidationErrors( [ 'token' ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function refreshTokenSend() {
            $user  = UserFactory::createNormalUserWithSession();
            $token = UserFactory::createAccessToken( $user );

            $this->postJson( route( 'api.refresh.token' ), headers: [
                'Authorization' => 'Bearer ' . $token
            ] )->assertUnprocessable()->assertJsonValidationErrors( [
                'token' => "Please send a refresh token!"
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function forgotPassword() {
            $user = UserFactory::createNormalUserWithSession();
            $this->postJson( route( 'api.forgot.password' ), [
                'email' => $user->email
            ] )->assertOk()->assertJson( [
                'message' => 'We have emailed your password reset link!'
            ] );
        }

    }
