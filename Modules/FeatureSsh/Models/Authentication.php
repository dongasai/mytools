<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FeatureSsh\Enums\AuthType;

/**
 * SSH认证方式模型
 *
 * @property int $id
 * @property string $name 认证名称
 * @property int $server_id 服务器ID
 * @property AuthType $auth_type 认证类型
 * @property string $credentials 凭证信息（加密存储JSON）
 * @property bool $is_default 是否默认认证
 * @property string $status 状态
 * @property string|null $description 描述
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class Authentication extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 'fssh_authentications';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'name',
        'server_id',
        'auth_type',
        'credentials',
        'is_default',
        'status',
        'description',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'server_id' => 'integer',
            'auth_type' => AuthType::class,
            'is_default' => 'boolean',
        ];
    }

    /**
     * 所属服务器
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * 获取解密后的凭证
     */
    public function getDecryptedCredentials(): array
    {
        return json_decode(decrypt($this->credentials), true);
    }

    /**
     * 设置加密凭证
     */
    public function setEncryptedCredentials(array $data): void
    {
        $this->credentials = encrypt(json_encode($data));
    }

    /**
     * 获取凭证中的用户名
     */
    public function getUsername(): ?string
    {
        $credentials = $this->getDecryptedCredentials();
        return $credentials['username'] ?? null;
    }

    /**
     * 获取密钥对ID（仅key类型）
     */
    public function getKeyPairId(): ?int
    {
        if ($this->auth_type !== AuthType::KEY) {
            return null;
        }

        $credentials = $this->getDecryptedCredentials();
        return $credentials['key_pair_id'] ?? null;
    }
}