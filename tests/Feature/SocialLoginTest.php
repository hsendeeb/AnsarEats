<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect('owner/dashboard');
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

    public function test_facebook_callback_links_an_existing_account_by_email(): void
    {
        Mail::fake();
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'email_verified_at' => null,
            'facebook_id' => null,
            'avatar' => null,
        ]);

        $this->mockSocialiteUser('facebook', new FakeSocialiteUser(
            id: 'facebook-456',
            name: 'Existing User',
            email: 'existing@example.com',
            avatar: 'https://example.com/facebook-avatar.jpg',
        ));

        $response = $this->get(route('social.callback', 'facebook'));

        $response->assertRedirect('owner/dashboard');
        $this->assertAuthenticatedAs($existingUser->fresh());
        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'facebook_id' => 'facebook-456',
            'email' => 'existing@example.com',
        ]);
        $this->assertNotNull($existingUser->fresh()->email_verified_at);
        $this->assertSame('https://example.com/facebook-avatar.jpg', $existingUser->fresh()->avatar);
        Mail::assertNothingSent();
    }

    protected function mockSocialiteUser(string $provider, SocialiteUserContract $socialUser): void
    {
        $providerMock = Mockery::mock();
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
    ) {
    }

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
