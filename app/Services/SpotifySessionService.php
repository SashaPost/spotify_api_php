<?php

namespace App\Services;

use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;

use GuzzleHttp\Client;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

use App\Models\User;
use App\Models\SpotifyToken;

use App\Services\CreateIfNotService;

class SpotifySessionService {

    // '$options' was replaced by '$scope'
    // public array $options;
    public array $authQueryParameters;
    private string $state;
    private string $oAuthBaseUri;
    private array $scope;
    private string $tokenBaseUri;
    // private array $tokenRequestParameters;
    protected $createIfNotService;


    public function __construct(
        CreateIfNotService $createIfNotService,
        private bool $autoRefresh = true,
        private bool $autoRetry = true,
    ) {
        $this->scope = [
            'playlist-read-private',
            'user-read-private',
            'user-read-email',
            'playlist-read-collaborative',
            'user-follow-read',
            'user-library-read',
        ];
        $this->state = bin2hex(random_bytes(62));
        $this->authQueryParameters = [
            'response_type' => 'code',
            'client_id' => env('SPOTIFY_CLIENT_ID'),
            'scope' => $this->scope,   // not sure if this will work properly; check
            'redirect_uri' => env('REDIRECT_URI'),
            'state' => $this->state,   // fill this value in the method
        ];
        $this->oAuthBaseUri = env('SPOTIFY_OAUTH_BASE_URI');
        $this->tokenBaseUri = env('SPOTIFY_ACCESS_TOKEN_URI');
        $this->createIfNotService = $createIfNotService;
    }

    public function buildOAuthUri() {
        return $this->oAuthBaseUri . http_build_query($this->authQueryParameters);
    }

    public function getTokens(
        Request $request, 
        User $user = null,
    ) {
        $user = $user ?? User::where('id', auth()->user()->id)->first();
        $code = $request->get('code');
        $state = $request->get('state');

        // need to perform such checks at every step:
        if($state === null) {
            return "debyl, ty proyibav 'state'";
        } 

        $credentials = base64_encode(env('SPOTIFY_CLIENT_ID') . ':' . env('SPOTIFY_CLIENT_SECRET'));
        $headers = [
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $tokenRequestParameters = [
            'code' => $code,
            'redirect_uri' => env('REDIRECT_URI'),
            'grant_type' => 'authorization_code',
            'client_id' => env('SPOTIFY_CLIENT_ID'),
            'code_verifier' => $state,
        ];
        $guzzleClient = new Client();
        $result = $guzzleClient->request('POST', $this->tokenBaseUri, [
            'headers' => $headers,
            'form_params' => $tokenRequestParameters,
        ]);
        $statusCode = $result->getStatusCode();
        $responseBody = $result->getBody()->getContents();
        $responseBodyArray = json_decode($responseBody, true);

        $token = $this->createIfNotService->token(
            $responseBodyArray,
            $user->id,
            $code,
        );
    }

    public function refreshAccessToken(User $user = null) {
        
    }

    public function session() {
        return new Session(
            env('SPOTIFY_CLIENT_ID'),
            env('SPOTIFY_CLIENT_SECRET'),
            env('REDIRECT_URI')
        );
    }
    


    public function getAccessToken(User $user = null) {

    }



    // if will be used later rename to 'apiInstance' or something like that.
    // creates a Spotify session:
    public function instantiateSession(User $user = null) {
        // $spotifyTokens = SpotifyToken::latest()->first();
        // $user = $user ?? auth()->user();   
        
        $user = $user ?? User::where('id', auth()->user()->id)->first();
        
        // $user = User::where('id', $this->authUser->id)->first();
        $spotifyTokens = $user->spotify_tokens;
        $accessToken = $spotifyTokens->access_token;
        $refreshToken = $spotifyTokens->refresh_token;

        $options = [
            'auto_refresh' => $this->autoRefresh, 
            'auto_retry' => $this->autoRetry,
        ];

        $spotifySession = new Session(
            env('SPOTIFY_CLIENT_ID'),
            env('SPOTIFY_CLIENT_SECRET'),
            env('REDIRECT_URI')
        );

        if (now()->timestamp < $spotifyTokens->expiration) {
            $spotifySession->setAccessToken($accessToken);
            $spotifySession->setRefreshToken($refreshToken);
        } else {
            // проверить 'refreshAccessToken' - не работал
            $spotifySession->refreshAccessToken($refreshToken);
            // 
            // $spotifySession->getAccessToken();
            // $spotifySession->getRefreshToken();

            $newAccessToken = $spotifySession->getAccessToken();
            $newRefreshToken = $spotifySession->getRefreshToken();
            // эта дрочь какого-то, сука, хуя возвращает 0. Заебала нахуй хуета ебаная
            $newTokenExpiration = $spotifySession->getTokenExpiration();

            $spotifyTokens->update([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                // 'expiration' => $spotifyTokens->expiration,
            ]);
        }
        $spotifyApi = new SpotifyWebAPI($options, $spotifySession);

        
        return $spotifyApi;
    }

    // gets all user's playlists from the Spotify API:
    public function getAllPlaylists(User $user = null)
    {
        $api = $this->instantiateSession($user);
        $playlists = $api->getMyPlaylists();
        $total = $playlists->total;
        
        $limit = 50;
        $offset = 0;
        $all_playlists = [];

        while ($playlists = $api->getMyPlaylists([
            'limit' => $limit,
            'offset' => $offset
        ])) 
        {
            $all_playlists = array_merge($all_playlists, $playlists->items);
            $offset += $limit;

            if ($offset > $playlists->total)
            {
                break;
            }
        }

        return $all_playlists;
    }
}
