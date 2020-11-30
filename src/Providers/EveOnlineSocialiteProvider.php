<?php
namespace nullx27\Socialite\EveOnline\Providers;

use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class EveOnlineSocialiteProvider extends AbstractProvider implements ProviderInterface
{

    protected $scopeSeparator = ' ';
    protected $baseUrl;

    public function __construct(){
        $this->baseUrl = env('EVEONLINE_AUTH_BASE_URL', 'https://login.eveonline.com/');
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            $this->baseUrl.'oauth/authorize', $state
        );
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl.'oauth/token';
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
             $this->baseUrl.'oauth/verify', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['CharacterID'],
            'name' => $user['CharacterName'],
            'owner_hash' => $user['CharacterOwnerHash'],
            'avatar' => 'https://image.eveonline.com/Character/' . $user['CharacterID'] . '_128.jpg',
        ]);
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
