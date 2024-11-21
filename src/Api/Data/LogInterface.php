<?php
declare(strict_types=1);

namespace OM\Nospam\Api\Data;

interface LogInterface
{
    const ENTITY_ID = 'entity_id';
    const IP = 'ip';
    const DATE = 'date';
    const COMMENT = 'comment';
    const USER_AGENT = 'user_agent';
    const REQUEST = 'request';
    const CACHE_TAG = 'om_nospam_log';

    public const ERROR_MSG_BLACKLISTED = 'You have been blacklisted due to multiple violations of our service. This ban will be lifted automatically after %1 hours. Please contact us if you have any questions.';

    /**
     * @return string
     */
    public function getIp(): string;

    /**
     * @param string $ip
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setIp(string $ip): LogInterface;

    /**
     * @return string
     */
    public function getDate(): string;

    /**
     * @param string $date
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setDate(string $date): LogInterface;

    /**
     * @return string
     */
    public function getComment(): string;

    /**
     * @param string $comment
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setComment(string $comment): LogInterface;

    /**
     * @return string
     */
    public function getUserAgent(): string;

    /**
     * @param string $userAgent
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setUserAgent(string $userAgent): LogInterface;

    /**
     * @return string
     */
    public function getRequest(): string;

    /**
     * @param string $request
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setRequest(string $request): LogInterface;
}