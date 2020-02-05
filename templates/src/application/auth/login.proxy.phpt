<?php
namespace %namespace%\Application\Auth;

use Auth;
use Illuminate\Foundation\Application;
use %namespace%\Domain\Infrastructure\Repositories\UserRepositoryInterface;
use %namespace%\Domain\Model\Status;
use %namespace%\Domain\Model\User;
use Optimus\ApiConsumer\Router;
use Request;

class LoginProxy {

    const REFRESH_TOKEN = 'refresh_token';

    /**
     * @var Router
     */
    private $apiConsumer;

    /**
     * @var \Illuminate\Cookie\CookieJar|mixed
     */
    private $cookie;

    /**
     * @var \Illuminate\Database\DatabaseManager|mixed
     */
    private $db;

    /**
     * @var mixed|\Request
     */
    private $request;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * LoginProxy constructor.
     * @param Application $app
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(Application $app, UserRepositoryInterface $userRepository) {

        $this->userRepository = $userRepository;

        $this->apiConsumer = $app->make('apiconsumer');
        $this->cookie = $app->make('cookie');
        $this->db = $app->make('db');
        $this->request = $app->make('request');
    }

    /**
     * Attempt to create an access token using user credentials
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws InactiveUserException
     * @throws InvalidCredentialsException
     */
    public function attemptLogin($email, $password) {

        $user = $this->userRepository->filter(['email' => $email, 'status' => Status::INACTIVE], true);
        if (!is_null($user)) {
            throw new InactiveUserException();
        }

        $user = $this->userRepository->filter(['email' => $email, 'status' => Status::ACTIVE], true);

        if (!is_null($user)) {
            $tokens = $this->proxy('password', [
                'username' => $email,
                'password' => $password
            ]);

            // if we got here, credentials should be valid, otherwise it would have thrown an exception
            $user = \App\User::find($user->id); // hackers gonna hack
            Auth::login($user);

            return $tokens;
        }

        throw new InvalidCredentialsException();
    }

    /**
     * Attempt to refresh the access token used a refresh token that
     * has been saved in a cookie
     */
    public function attemptRefresh() {

        $refreshToken = Request::fromCookieSuperGlobal(self::REFRESH_TOKEN);

        return $this->proxy('refresh_token', [
            'refresh_token' => $refreshToken
        ]);
    }

    /**
     * Proxy a request to the OAuth server.
     *
     * @param string $grantType what type of grant type should be proxied
     * @param array $data the data to send to the server
     * @return array
     * @throws InvalidCredentialsException
     */
    public function proxy($grantType, array $data = []) {

        $row = $this->userRepository->getOauthPasswordClient();

        $data = array_merge($data, [
            'client_id' => $row->id,
            'client_secret' => $row->secret,
            'grant_type' => $grantType
        ]);

        $response = $this->apiConsumer->post('/oauth/token', $data);

        if (!$response->isSuccessful()) {
            throw new InvalidCredentialsException();
        }

        $data = json_decode($response->getContent());

        // Create a refresh token cookie
        $this->cookie->queue(
            self::REFRESH_TOKEN,
            $data->refresh_token,
            14400, // how many minutes in 10 days
            null,
            null,
            false,
            true // HttpOnly
        );

        return [
            'access_token' => $data->access_token,
            'access_expires' => time() + $data->expires_in
        ];
    }

    /**
     * Logs out the user. We revoke access token and refresh token.
     * Also instruct the client to forget the refresh cookie.
     */
    public function logout() {

        $accessToken = Auth::user()->token();

        if ($accessToken) {
            $refreshToken = $this->db
                ->table('oauth_refresh_tokens')
                ->where('access_token_id', $accessToken->id)
                ->update([
                    'revoked' => true
                ]);

            $accessToken->revoke();

            $this->cookie->queue($this->cookie->forget(self::REFRESH_TOKEN));

            auth('web')->logout();
        }
    }
}
