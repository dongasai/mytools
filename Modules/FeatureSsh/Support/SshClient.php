<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Support;

use Modules\FeatureSsh\Enums\AuthType;
use Modules\FeatureSsh\Enums\ServerStatus;
use Modules\FeatureSsh\Models\Authentication;
use Modules\FeatureSsh\Models\KeyPair;
use Modules\FeatureSsh\Models\Server;
use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Crypt\ECDSA;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SSH2;

/**
 * SSH客户端封装
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class SshClient
{
    /**
     * SSH连接实例
     */
    private ?SSH2 $ssh = null;

    /**
     * 服务器ID
     */
    private int $serverId;

    /**
     * 服务器模型
     */
    private Server $server;

    /**
     * 是否已连接
     */
    private bool $connected = false;

    /**
     * 最后活动时间
     */
    private int $lastActivity;

    /**
     * 构造函数
     *
     * @param Server $server 服务器模型
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->serverId = $server->id;
        $this->lastActivity = time();
    }

    /**
     * 获取服务器ID
     */
    public function getServerId(): int
    {
        return $this->serverId;
    }

    /**
     * 获取服务器模型
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * 连接服务器
     *
     * @param Authentication $authentication 认证方式
     * @return bool 是否连接成功
     */
    public function connect(Authentication $authentication): bool
    {
        if ($this->connected && $this->ssh !== null) {
            return true;
        }

        $this->ssh = new SSH2($this->server->host, $this->server->port);

        $credentials = $authentication->getDecryptedCredentials();
        $username = $credentials['username'] ?? 'root';

        $authenticated = match ($authentication->auth_type) {
            AuthType::KEY => $this->authenticateWithKey($username, $credentials),
            AuthType::PASSWORD => $this->authenticateWithPassword($username, $credentials),
            AuthType::CERTIFICATE => $this->authenticateWithCertificate($username, $credentials),
            AuthType::AGENT => $this->authenticateWithAgent($username),
            default => false,
        };

        if ($authenticated) {
            $this->connected = true;
            $this->lastActivity = time();
            $this->server->updateSystemInfo(['last_connected_at' => now()->toDateTimeString()]);

            return true;
        }

        return false;
    }

    /**
     * 使用密钥认证
     *
     * @param string $username 用户名
     * @param array $credentials 凭证信息
     * @return bool 是否认证成功
     */
    private function authenticateWithKey(string $username, array $credentials): bool
    {
        $keyPairId = $credentials['key_pair_id'] ?? null;

        if (!$keyPairId) {
            return false;
        }

        $keyPair = KeyPair::find($keyPairId);

        if (!$keyPair) {
            return false;
        }

        $privateKey = $keyPair->getDecryptedPrivateKey();
        $passphrase = $keyPair->getDecryptedPassphrase();

        $key = AsymmetricKey::load($privateKey, $passphrase);

        return $this->ssh->login($username, $key);
    }

    /**
     * 使用密码认证
     *
     * @param string $username 用户名
     * @param array $credentials 凭证信息
     * @return bool 是否认证成功
     */
    private function authenticateWithPassword(string $username, array $credentials): bool
    {
        $password = $credentials['password'] ?? '';

        if (!$password) {
            return false;
        }

        $decryptedPassword = decrypt($password);

        return $this->ssh->login($username, $decryptedPassword);
    }

    /**
     * 使用证书认证
     *
     * @param string $username 用户名
     * @param array $credentials 凭证信息
     * @return bool 是否认证成功
     */
    private function authenticateWithCertificate(string $username, array $credentials): bool
    {
        $certificate = $credentials['certificate'] ?? '';
        $keyPairId = $credentials['key_pair_id'] ?? null;

        if (!$certificate || !$keyPairId) {
            return false;
        }

        $keyPair = KeyPair::find($keyPairId);

        if (!$keyPair) {
            return false;
        }

        $privateKey = $keyPair->getDecryptedPrivateKey();
        $passphrase = $keyPair->getDecryptedPassphrase();

        $key = AsymmetricKey::load($privateKey, $passphrase);

        return $this->ssh->login($username, $key);
    }

    /**
     * 使用SSH Agent认证
     *
     * @param string $username 用户名
     * @return bool 是否认证成功
     */
    private function authenticateWithAgent(string $username): bool
    {
        return $this->ssh->login($username);
    }

    /**
     * 执行命令
     *
     * @param string $command 命令
     * @param int|null $timeout 超时时间（秒）
     * @return array 包含output、error、exitCode的数组
     */
    public function execute(string $command, ?int $timeout = null): array
    {
        if (!$this->connected) {
            return [
                'output' => '',
                'error' => 'Not connected',
                'exit_code' => -1,
            ];
        }

        $this->ssh->setTimeout($timeout ?? 30);

        $output = $this->ssh->exec($command);
        $exitCode = $this->ssh->getExitStatus();

        $this->lastActivity = time();

        return [
            'output' => $output ?: '',
            'error' => $exitCode !== 0 ? 'Command failed with exit code ' . $exitCode : '',
            'exit_code' => $exitCode ?? 0,
        ];
    }

    /**
     * 获取SFTP子系统
     *
     * @return \phpseclib3\Net\SFTP|null SFTP实例或null
     */
    public function getSftp(): ?\phpseclib3\Net\SFTP
    {
        if (!$this->connected) {
            return null;
        }

        $this->lastActivity = time();

        return $this->ssh->getSFTP();
    }

    /**
     * 检查连接是否活跃
     *
     * @return bool 是否活跃
     */
    public function isConnected(): bool
    {
        if (!$this->connected || $this->ssh === null) {
            return false;
        }

        return $this->ssh->isConnected();
    }

    /**
     * 获取连接空闲时间（秒）
     *
     * @return int 空闲秒数
     */
    public function getIdleTime(): int
    {
        return time() - $this->lastActivity;
    }

    /**
     * 断开连接
     */
    public function disconnect(): void
    {
        if ($this->ssh !== null) {
            $this->ssh->disconnect();
        }

        $this->connected = false;
        $this->ssh = null;
    }

    /**
     * 获取系统信息
     *
     * @return array 系统信息
     */
    public function getSystemInfo(): array
    {
        if (!$this->connected) {
            return [];
        }

        $info = [];

        $hostname = $this->execute('hostname')['output'] ?? '';
        $info['hostname'] = trim($hostname);

        $uname = $this->execute('uname -s')['output'] ?? 'Linux';
        $info['system_type'] = match (trim($uname)) {
            'Darwin' => 'macos',
            'MINGW', 'CYGWIN', 'MSYS' => 'windows',
            default => 'linux',
        };

        $kernel = $this->execute('uname -r')['output'] ?? '';
        $info['kernel'] = trim($kernel);

        $arch = $this->execute('uname -m')['output'] ?? '';
        $info['architecture'] = trim($arch);

        $osVersion = $this->execute('cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d \'"\'')['output'] ?? '';
        $info['os_version'] = trim($osVersion);

        $cpuInfo = $this->execute('nproc')['output'] ?? '1';
        $info['cpu_count'] = (int) trim($cpuInfo);

        $uptime = $this->execute('cat /proc/uptime | cut -d" " -f1')['output'] ?? '0';
        $info['uptime_seconds'] = (int) floatval(trim($uptime));

        return $info;
    }
}
