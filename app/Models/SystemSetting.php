<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'is_secret',
    ];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    /**
     * Get decrypted value if secret, otherwise raw value.
     */
    public function getDecryptedValue(): ?string
    {
        if ($this->value === null || $this->value === '') {
            return null;
        }

        if (! $this->is_secret) {
            return $this->value;
        }

        try {
            return Crypt::decryptString($this->value);
        } catch (\Throwable $e) {
            return $this->value;
        }
    }

    /**
     * Set encrypted value if secret.
     */
    public function setEncryptedValue(?string $value, bool $isSecret = true): void
    {
        $this->is_secret = $isSecret;

        if ($value === null || $value === '') {
            $this->value = null;

            return;
        }

        if ($isSecret) {
            $this->value = Crypt::encryptString($value);
        } else {
            $this->value = $value;
        }
    }

    /**
     * Retrieve setting value by group and key.
     */
    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        /** @var self|null $setting */
        $setting = static::where('group', $group)->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getDecryptedValue() ?? $default;
    }

    /**
     * Save setting value by group and key.
     */
    public static function set(string $group, string $key, ?string $value, bool $isSecret = true): self
    {
        /** @var self $setting */
        $setting = static::firstOrNew([
            'group' => $group,
            'key' => $key,
        ]);

        $setting->setEncryptedValue($value, $isSecret);
        $setting->save();

        return $setting;
    }

    /**
     * Retrieve all decrypted key-value pairs for a specific group.
     *
     * @return array<string, string|null>
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getDecryptedValue();
        }

        return $result;
    }
}
