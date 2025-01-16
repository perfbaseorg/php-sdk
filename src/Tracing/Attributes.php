<?php

namespace Perfbase\SDK\Tracing;

use Perfbase\SDK\Utils\EnvironmentUtils;

class Attributes
{
    /**
     * The action that was performed to trigger this trace.
     * This could be a route name, cron name, or anything else.
     * Eg, "GET /users/:userId", "Cron:DailyReport", "Queue:ProcessEmails"
     * @var string|null
     */
    public ?string $action;

    /**
     * The unique user ID of the user that triggered this trace.
     * @var string|null
     */
    public ?string $userId;

    /**
     * The IP address of the user that triggered this trace.
     * @var string|null
     */
    public ?string $userIp;

    /**
     * The user agent of the user that triggered this trace.
     * @var string|null
     */
    public ?string $userAgent;

    /**
     * The hostname of the server that this trace was collected on.
     * @var string|null
     */
    public ?string $hostname;

    /**
     * The environment that this trace was collected in.
     * Eg, "production", "staging", "development"
     * @var string|null
     */
    public ?string $environment;

    /**
     * The version of the application that this trace was collected in.
     * This could be a git commit hash, a version number, or anything else.
     * @var string|null
     */
    public ?string $appVersion;

    /**
     * The version of PHP that this trace was collected in.
     * @var string|null
     */
    public ?string $phpVersion;

    /**
     * The HTTP method that was used to trigger this trace, if applicable.
     * Eg, "GET", "POST", "PUT", "DELETE"
     * @var string|null
     */
    public ?string $httpMethod;

    /**
     * The HTTP status code that was returned by the server, if applicable.
     * Eg, 200, 404, 500
     * @var int|null
     */
    public ?int $httpStatusCode;

    /**
     * The full URL that was requested to trigger this trace, if applicable.
     * @var string|null
     */
    public ?string $httpUrl;

    public function __construct()
    {
        // Attempt to grab some default values
        $this->hostname = gethostname() ?: null;
        $this->phpVersion = phpversion() ?: null;
        $this->userIp = EnvironmentUtils::getUserIp();
        $this->userAgent = EnvironmentUtils::getUserUserAgent();
        $this->environment = EnvironmentUtils::getHostname();
    }

}
