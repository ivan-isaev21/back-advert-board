<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\UsesUuid;
use Carbon\Carbon;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmail, UsesUuid;

    public const STATUS_WAIT = 'wait';
    public const STATUS_ACTIVE = 'active';

    public const ROLE_USER = 'user';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'verify_token', 'status', 'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'phone_verified' => 'boolean',
        'phone_verify_token_expire' => 'datetime',
        'phone_auth' => 'boolean',
        'password_reset_token_expire' => 'datetime'
    ];

    /**
     * Method return rolesList
     *
     * @return array
     */
    public static function rolesList(): array
    {
        return [
            self::ROLE_USER => 'User',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_ADMIN => 'Admin',
        ];
    }

    /**
     * Method register
     *
     * @param string $name 
     * @param string $email 
     * @param string $password 
     * @return self
     */
    public static function register(string $name, string $email, string $password): self
    {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'verify_token' => Str::uuid(),
            'role' => self::ROLE_USER,
            'status' => self::STATUS_WAIT,
        ]);
    }

    /**
     * Method return user isWait
     *
     * @return bool
     */
    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    /**
     * Method return user is Active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Method verifyEmail
     *
     * @return void
     */
    public function verifyEmail(string $hash): void
    {
        if ($this->hasVerifiedEmail()) {
            throw new \DomainException('User email is already verified.');
        }

        if ($this->verify_token !== $hash) {
            throw new \DomainException('Invalid hash');
        }

        if ($this->markEmailAsVerified()) {
            $this->verify();
        }
    }

    /**
     * Method verify user
     *
     * @return void
     */
    public function verify(): void
    {
        if (!$this->isWait()) {
            throw new \DomainException('User is already verified.');
        }

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'verify_token' => null,
        ]);
    }

    /**
     * Method changeRole
     *
     * @param string $role 
     *
     * @return void
     */
    public function changeRole(string $role): void
    {
        if (!array_key_exists($role, self::rolesList())) {
            throw new \InvalidArgumentException('Undefined role "' . $role . '"');
        }
        if ($this->role === $role) {
            throw new \DomainException('Role is already assigned.');
        }
        $this->update(['role' => $role]);
    }


    /**
     * Method unverifyPhone
     *
     * @return void
     */
    public function unverifyPhone(): void
    {
        $this->phone_verified = false;
        $this->phone_verify_token = null;
        $this->phone_verify_token_expire = null;
        $this->phone_auth = false;
        $this->saveOrFail();
    }

    /**
     * Method requestPhoneVerification
     *
     * @param Carbon $now 
     *
     * @return string
     */
    public function requestPhoneVerification(Carbon $now): string
    {
        if (empty($this->phone)) {
            throw new \DomainException('Phone number is empty.');
        }
        if (!empty($this->phone_verify_token) && $this->phone_verify_token_expire && $this->phone_verify_token_expire->gt($now)) {
            throw new \DomainException('Token is already requested.');
        }
        $this->phone_verified = false;
        $this->phone_verify_token = (string)random_int(10000, 99999);
        $this->phone_verify_token_expire = $now->copy()->addSeconds(300);
        $this->saveOrFail();

        return $this->phone_verify_token;
    }


    /**
     * Method requestPhoneVerifyToken
     *
     * @param Carbon $now
     *
     * @return string
     */
    public function requestPhoneVerifyToken(Carbon $now): string
    {
        if (empty($this->phone)) {
            throw new \DomainException('Phone number is empty.');
        }
        if (!empty($this->phone_verify_token) && $this->phone_verify_token_expire && $this->phone_verify_token_expire->gt($now)) {
            throw new \DomainException('Token is already requested.');
        }

        if (!$this->isPhoneAuthEnabled()) {
            throw new \DomainException('Phone auth is disabled.');
        }

        $this->phone_verify_token = (string)random_int(10000, 99999);
        $this->phone_verify_token_expire = $now->copy()->addSeconds(300);
        $this->saveOrFail();

        return $this->phone_verify_token;
    }


    /**
     * Method validatePhoneVerifyToken
     *
     * @param $token 
     * @param Carbon $now 
     *
     * @return void
     */
    public function validatePhoneVerifyToken($token, Carbon $now): void
    {
        if ($token !== $this->phone_verify_token) {
            throw new \DomainException('Incorrect verify token.');
        }
        if ($this->phone_verify_token_expire->lt($now)) {
            throw new \DomainException('Token is expired.');
        }

        $this->phone_verify_token = null;
        $this->phone_verify_token_expire = null;
        $this->saveOrFail();
    }

    /**
     * Method requestPasswordResetToken
     *
     * @param Carbon $now 
     *
     * @return string
     */
    public function requestPasswordResetToken(Carbon $now): string
    {
        if (!empty($this->password_reset_token) && $this->password_reset_token_expire && $this->password_reset_token_expire->gt($now)) {
            throw new \DomainException('Password reset token is already requested.');
        }

        $this->password_reset_token = (string)random_int(10000, 99999);
        $this->password_reset_token_expire = $now->copy()->addHour();
        $this->saveOrFail();

        return $this->password_reset_token;
    }

    /**
     * Method changePasswordByToken
     *
     * @param $token $token 
     * @param $password $password 
     * @return void
     */
    public function changePasswordByToken($token, $password, Carbon $now): void
    {
        if ($token !== $this->password_reset_token) {
            throw new \DomainException('Incorrect password reset token.');
        }
        if ($this->password_reset_token_expire->lt($now)) {
            throw new \DomainException('Password reset token is expired.');
        }

        $this->password = $password;
        $this->password_reset_token = null;
        $this->password_reset_token_expire = null;
        $this->saveOrFail();
    }

    /**
     * Method verifyPhone
     *
     * @param $token  
     * @param Carbon $now 
     *
     * @return void
     */
    public function verifyPhone($token, Carbon $now): void
    {
        if ($token !== $this->phone_verify_token) {
            throw new \DomainException('Incorrect verify token.');
        }
        if ($this->phone_verify_token_expire->lt($now)) {
            throw new \DomainException('Token is expired.');
        }
        $this->phone_verified = true;
        $this->phone_verify_token = null;
        $this->phone_verify_token_expire = null;
        $this->saveOrFail();
    }

    /**
     * Method enablePhoneAuth
     *
     * @return void
     */
    public function enablePhoneAuth(): void
    {
        if (!empty($this->phone) && !$this->isPhoneVerified()) {
            throw new \DomainException('Phone number is empty.');
        }
        $this->phone_auth = true;
        $this->saveOrFail();
    }

    /**
     * Method disablePhoneAuth
     *
     * @return void
     */
    public function disablePhoneAuth(): void
    {
        $this->phone_auth = false;
        $this->saveOrFail();
    }

    /**
     * Method isPhoneVerified
     *
     * @return bool
     */
    public function isPhoneVerified(): bool
    {
        return $this->phone_verified;
    }

    /**
     * Method isPhoneAuthEnabled
     *
     * @return bool
     */
    public function isPhoneAuthEnabled(): bool
    {
        return (bool)$this->phone_auth;
    }

    /**
     * Method return user is Moderator
     *
     * @return bool
     */
    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Method return user is Admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
