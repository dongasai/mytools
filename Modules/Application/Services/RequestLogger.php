<?php

namespace Modules\Application\Services;

use DLaravel\Model\RequestLog;
use GameProtobuf\Request;
use Google\Protobuf\Internal\Message;
use Illuminate\Http\Request as HttpRequest;

class RequestLogger
{
    public static $di;

    /**
     * @var RequestLog
     */
    private $requestLog;

    private $httpRequest;

    private $response;

    public function __construct(HttpRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
        $this->requestLog = new RequestLog;

        // 初始化请求日志
        $this->requestLog->request_unid = RUN_UNIQID;
        $this->requestLog->run_unid = RUN_UNIQID;
        $this->requestLog->path = $httpRequest->path();
        $this->requestLog->method = $httpRequest->method();
        $this->requestLog->headers = json_encode($httpRequest->headers->all());
        $this->requestLog->ipaddress = $httpRequest->ip();
        $this->requestLog->host = $httpRequest->getHost();
        // 根据Content-Type决定如何记录post数据
        $contentType = $httpRequest->header('Content-Type', '');
        $rawContent = $httpRequest->getContent();

        if (str_contains($contentType, 'application/json') && ! empty($rawContent)) {
            // JSON请求直接记录JSON格式
            $this->requestLog->post = $rawContent;
        } elseif (str_contains($contentType, 'application/x-www-form-urlencoded') && ! empty($rawContent)) {
            // 表单请求解码后记录为JSON格式
            parse_str($rawContent, $formData);
            $this->requestLog->post = json_encode($formData);
        } else {
            // 其他类型使用base64编码
            $this->requestLog->post = base64_encode($rawContent);
        }
        $this->requestLog->sale_date = strtotime(date('Y-m-d'));

        $this->requestLog->save();
    }

    public static function getDi()
    {
        if (! self::$di) {
            self::$di = new RequestLogger(request());
        }

        return self::$di;
    }

    public function setResponse(Message $response)
    {
        $this->response = $response;
    }

    /**
     * 设置JSON响应数据
     *
     * @param  string  $jsonResponse  JSON格式的响应数据
     * @return void
     */
    public function setJsonResponse(string $jsonResponse)
    {
        $this->requestLog->response = $jsonResponse;
    }

    public function setError(string $error)
    {
        $this->requestLog->error = $error;
    }

    public function setRouter(string $router)
    {
        $this->requestLog->router = $router;
    }

    public function setUserId(int $user_id)
    {
        $this->requestLog->user_id = $user_id;
    }

    public function setRunTime(float $startTime)
    {
        $endTime = microtime(true);
        $this->requestLog->run_ms = intval(($endTime - $startTime) * 1000);
    }

    public function setProtobuf(Request $request)
    {
        $jsonData = $request->serializeToJsonString();
        $this->requestLog->protobuf_json = $jsonData;
        $this->requestLog->request_unid = $request->getRequestUnid();
    }

    public function __destruct()
    {

        if ($this->response) {
            $this->requestLog->response = $this->response->serializeToJsonString();
        }

        $this->requestLog->update();
    }
}
