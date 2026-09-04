<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Services;

use Modules\FeatureSsh\Enums\KeyType;
use Modules\FeatureSsh\Models\KeyPair;
use phpseclib3\Crypt\DSA;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\RSA;

/**
 * SSH密钥对管理服务
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class KeyPairService
{
    /**
     * 生成密钥对
     *
     * @param string $name 密钥名称
     * @param string $type 密钥类型（rsa, ed25519, ecdsa）
     * @param string|null $passphrase 私钥密码
     * @return KeyPair 密钥对模型
     * @throws \InvalidArgumentException 参数错误
     * @throws \RuntimeException 生成失败
     */
    public static function generate(string $name, string $type = 'ed25519', ?string $passphrase = null): KeyPair
    {
        $keyType = KeyType::tryFrom($type) ?? KeyType::ED25519;

        $privateKey = self::generatePrivateKey($keyType);
        $publicKey = $privateKey->getPublicKey();

        $privateKeyPem = $privateKey->toString('PKCS8');
        $publicKeyRaw = $publicKey->toString('Raw');
        $fingerprint = self::calculateFingerprint($publicKeyRaw);

        $keyPair = new KeyPair([
            'name' => $name,
            'type' => $keyType,
            'public_key' => $publicKeyRaw,
        ]);

        $keyPair->setEncryptedPrivateKey($privateKeyPem);

        if ($passphrase) {
            $keyPair->setEncryptedPassphrase($passphrase);
        }

        $keyPair->fingerprint = $fingerprint;
        $keyPair->save();

        return $keyPair;
    }

    /**
     * 导入密钥对
     *
     * @param string $name 密钥名称
     * @param string $privateKey 私钥内容
     * @param string|null $publicKey 公钥内容（可选，不提供则从私钥推导）
     * @param string|null $passphrase 私钥密码
     * @return KeyPair 密钥对模型
     * @throws \InvalidArgumentException 密钥格式错误
     * @throws \RuntimeException 导入失败
     */
    public static function import(string $name, string $privateKey, ?string $publicKey = null, ?string $passphrase = null): KeyPair
    {
        $keyType = self::detectKeyType($privateKey);

        $privateKeyObj = self::loadPrivateKey($privateKey, $passphrase);

        if ($publicKey === null) {
            $publicKeyObj = $privateKeyObj->getPublicKey();
            $publicKeyRaw = $publicKeyObj->toString('Raw');
        } else {
            $publicKeyRaw = $publicKey;
        }

        $fingerprint = self::calculateFingerprint($publicKeyRaw);

        $keyPair = new KeyPair([
            'name' => $name,
            'type' => $keyType,
            'public_key' => $publicKeyRaw,
            'fingerprint' => $fingerprint,
        ]);

        $keyPair->setEncryptedPrivateKey($privateKey);

        if ($passphrase) {
            $keyPair->setEncryptedPassphrase($passphrase);
        }

        $keyPair->save();

        return $keyPair;
    }

    /**
     * 导出公钥
     *
     * @param int $keyPairId 密钥对ID
     * @param string $format 输出格式（openssh, rfc4716, pem）
     * @return string 公钥内容
     * @throws \InvalidArgumentException 密钥不存在
     */
    public static function exportPublicKey(int $keyPairId, string $format = 'openssh'): string
    {
        $keyPair = KeyPair::find($keyPairId);

        if (!$keyPair) {
            throw new \InvalidArgumentException('Key pair not found: ' . $keyPairId);
        }

        if ($format === 'openssh') {
            return $keyPair->getPublicKeyForAuthorizedKeys();
        }

        return $keyPair->public_key;
    }

    /**
     * 验证密钥对
     *
     * @param int $keyPairId 密钥对ID
     * @return bool 是否有效
     */
    public static function validate(int $keyPairId): bool
    {
        $keyPair = KeyPair::find($keyPairId);

        if (!$keyPair) {
            return false;
        }

        $privateKey = $keyPair->getDecryptedPrivateKey();
        $passphrase = $keyPair->getDecryptedPassphrase();

        $publicKeyRaw = $keyPair->public_key;

        $calculatedFingerprint = self::calculateFingerprint($publicKeyRaw);

        return $calculatedFingerprint === $keyPair->fingerprint;
    }

    /**
     * 获取密钥指纹
     *
     * @param int $keyPairId 密钥对ID
     * @return string 密钥指纹
     * @throws \InvalidArgumentException 密钥不存在
     */
    public static function getFingerprint(int $keyPairId): string
    {
        $keyPair = KeyPair::find($keyPairId);

        if (!$keyPair) {
            throw new \InvalidArgumentException('Key pair not found: ' . $keyPairId);
        }

        return $keyPair->fingerprint;
    }

    /**
     * 删除密钥对
     *
     * @param int $keyPairId 密钥对ID
     * @return bool 是否删除成功
     */
    public static function delete(int $keyPairId): bool
    {
        $keyPair = KeyPair::find($keyPairId);

        if (!$keyPair) {
            return false;
        }

        return $keyPair->delete();
    }

    /**
     * 生成私钥
     *
     * @param KeyType $type 密钥类型
     * @return \phpseclib3\Crypt\Common\AsymmetricKey 私钥对象
     */
    private static function generatePrivateKey(KeyType $type): \phpseclib3\Crypt\Common\AsymmetricKey
    {
        return match ($type) {
            KeyType::RSA => RSA::createKey(4096),
            KeyType::ED25519 => EC::createCurve('Ed25519'),
            KeyType::ECDSA => EC::createKey('secp384r1'),
        };
    }

    /**
     * 加载私钥
     *
     * @param string $privateKey 私钥内容
     * @param string|null $passphrase 私钥密码
     * @return \phpseclib3\Crypt\Common\AsymmetricKey 私钥对象
     */
    private static function loadPrivateKey(string $privateKey, ?string $passphrase = null): \phpseclib3\Crypt\Common\AsymmetricKey
    {
        return \phpseclib3\Crypt\Common\AsymmetricKey::load($privateKey, $passphrase);
    }

    /**
     * 检测密钥类型
     *
     * @param string $privateKey 私钥内容
     * @return KeyType 密钥类型
     */
    private static function detectKeyType(string $privateKey): KeyType
    {
        if (str_contains($privateKey, 'BEGIN OPENSSH PRIVATE KEY')) {
            return KeyType::ED25519;
        }

        if (str_contains($privateKey, 'BEGIN EC PRIVATE KEY') || str_contains($privateKey, 'BEGIN EC PARAMETERS')) {
            return KeyType::ECDSA;
        }

        return KeyType::RSA;
    }

    /**
     * 计算密钥指纹
     *
     * @param string $publicKey 公钥内容
     * @return string 指纹（SHA256）
     */
    private static function calculateFingerprint(string $publicKey): string
    {
        $hash = hash('sha256', $publicKey, true);
        $base64 = base64_encode($hash);

        return 'SHA256:' . substr($base64, 0, 43);
    }
}
