<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_callback_creates_a_new_user_and_logs_them_in(): void
    {
        Mail::fake();
        $this->mockSocialiteUser('google', new FakeSocialiteUser(
            id: 'google-123',
            name: 'Google Customer',
            email: 'google@example.com',
            avatar: 'https://example.com/google-avatar.jpg',
        ));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'google_id' => 'google-123',
            'role' => 'customer',
        ]);

        $user = User::where('email', 'google@example.com')->firstOrFail();

        $this->assertSame('Google Customer', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->password);
        Mail::assertQueued(WelcomeUserMail::class);
    }

    public function test_google_callback_sets_a_persistent_remember_cookie(): void
    {
        Mail::fake();
        $this->mockSocialiteUser('google', new FakeSocialiteUser(
            id: 'google-remembered',
            name: 'Remembered Customer',
            email: 'remembered@example.com',
        ));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $response->assertCookie(Auth::guard('web')->getRecallerName());
    }

    public function test_google_login_preserves_guest_cart_in_session(): void
    {
        Mail::fake();

        $guestCart = [
            'restaurant_id' => 1,
            'restaurant_name' => 'Test Restaurant',
            'items' => [
                '10||9.99' => [
                    'key' => '10||9.99',
                    'id' => 10,
                    'name' => 'Test Item',
                    'price' => 9.99,
                    'image' => null,
                    'quantity' => 2,
                    'variant' => null,
                ],
            ],
            'promo' => null,
        ];

        $this->withSession(['cart' => $guestCart]);

        $this->mockSocialiteUser('google', new FakeSocialiteUser(
            id: 'google-cart',
            name: 'Cart Customer',
            email: 'cart@example.com',
        ));

        $this->get('/auth/google/callback')->assertRedirect('/');

        $this->assertSame($guestCart, session('cart'));
    }

    public function test_facebook_callback_creates_a_new_user_and_logs_them_in(): void
    {
        Mail::fake();
        $this->mockSocialiteUser('facebook', new FakeSocialiteUser(
            id: 'facebook-123',
            name: 'Facebook Customer',
            email: 'facebook@example.com',
            avatar: 'https://example.com/facebook-avatar.jpg',
        ));

        $response = $this->get('/auth/facebook/callback');

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'facebook@example.com',
            'facebook_id' => 'facebook-123',
            'role' => 'customer',
        ]);

        $user = User::where('email', 'facebook@example.com')->firstOrFail();

        $this->assertSame('Facebook Customer', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->password);
        Mail::assertQueued(WelcomeUserMail::class);
    }

    protected function mockSocialiteUser(string $provider, SocialiteUserContract $socialUser): void
    {
        $providerMock = Mockery::mock();
        $providerMock->shouldReceive('stateless')->once()->andReturnSelf();
        $providerMock->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with($provider)
            ->andReturn($providerMock);
    }
}

class FakeSocialiteUser implements SocialiteUserContract
{
    public function __construct(
        protected string $id,
        protected ?string $name,
        protected ?string $email,
        protected ?string $avatar = null,
        protected ?string $nickname = null,
    ) {}

    public function getId()
    {
        return $this->id;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }
}
