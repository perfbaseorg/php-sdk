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
    public ?string $action = null;

    /**
     * The unique user ID of the user that triggered this trace.
     * @var string|null
     */
    public ?string $userId = null;

    /**
     * The IP address of the user that triggered this trace.
     * @var string|null
     */
    public ?string $userIp = null;

    /**
     * The user agent of the user that triggered this trace.
     * @var string|null
     */
    public ?string $userAgent = null;

    /**
     * The hostname of the server that this trace was collected on.
     * @var string|null
     */
    public ?string $hostname = null;

    /**
     * The environment that this trace was collected in.
     * Eg, "production", "staging", "development"
     * @var string|null
     */
    public ?string $environment = null;

    /**
     * The version of the application that this trace was collected in.
     * This could be a git commit hash, a version number, or anything else.
     * @var string|null
     */
    public ?string $appVersion = null;

    /**
     * The version of PHP that this trace was collected in.
     * @var string|null
     */
    public ?string $phpVersion = null;

    /**
     * The HTTP method that was used to trigger this trace, if applicable.
     * Eg, "GET", "POST", "PUT", "DELETE"
     * @var string|null
     */
    public ?string $httpMethod = null;

    /**
     * The HTTP status code that was returned by the server, if applicable.
     * Eg, 200, 404, 500
     * @var int|null
     */
    public ?int $httpStatusCode = null;

    /**
     * The full URL that was requested to trigger this trace, if applicable.
     * @var string|null
     */
    public ?string $httpUrl = null;

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
