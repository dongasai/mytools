<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FeatureSsh\Enums\KeyType;

/**
 * SSH密钥对模型
 *
 * @property int $id
 * @property string $name 密钥名称
 * @property KeyType $type 密钥类型
 * @property string $public_key 公钥
 * @property string $private_key 私钥（加密存储）
 * @property string|null $passphrase 私钥密码（加密存储）
 * @property string $fingerprint 指纹
 * @property string|null $comment 注释
 * @property string|null $description 描述
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class KeyPair extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 'fssh_key_pairs';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'name',
        'type',
        'public_key',
        'private_key',
        'passphrase',
        'fingerprint',
        'comment',
        'description',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'type' => KeyType::class,
        ];
    }

    /**
     * 获取解密后的私钥
     */
    public function getDecryptedPrivateKey(): string
    {
        return decrypt($this->private_key);
    }

    /**
     * 设置加密私钥
     */
    public function setEncryptedPrivateKey(string $privateKey): void
    {
        $this->private_key = encrypt($privateKey);
    }

    /**
     * 获取解密后的密码
     */
    public function getDecryptedPassphrase(): ?string
    {
        return $this->passphrase ? decrypt($this->passphrase) : null;
    }

    /**
     * 设置加密密码
     */
    public function setEncryptedPassphrase(?string $passphrase): void
    {
        $this->passphrase = $passphrase ? encrypt($passphrase) : null;
    }

    /**
     * 获取公钥内容（用于authorized_keys）
     */
    public function getPublicKeyForAuthorizedKeys(): string
    {
        $parts = [
            $this->type->value,
            base64_encode($this->public_key),
        ];

        if ($this->comment) {
            $parts[] = $this->comment;
        }

        return implode(' ', $parts);
    }
}