<?php

namespace PublishPress\StripePhp;
/**
 * Based on the ActionScheduler_Versions class from Action Scheduler library.
 */
class Versions
{
    /**
     * @var Versions
     */
    private static $instance = null;

    private $versions = array();

    public function register($version_string, $initialization_callback): bool
    {
        if (isset($this->versions[$version_string])) {
            return false;
        }

        $this->versions[$version_string] = $initialization_callback;

        return true;
    }

    public function getVersions(): array
    {
        return $this->versions;
    }

    public function latestVersion()
    {
        $keys = array_keys($this->versions);
        if (empty($keys)) {
            return false;
        }
        uasort($keys, 'version_compare');
        return end($keys);
    }

    public function latestVersionCallback()
    {
        $latest = $this->latestVersion();
        if (empty($latest) || ! isset($this->versions[$latest])) {
            return '__return_null';
        }

        return $this->versions[$latest];
    }

    /**
     * @return Versions
     * @codeCoverageIgnore
     */
    public static function getInstance(): ?Versions
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function initializeLatestVersion(): void
    {
        $self = self::getInstance();

        call_user_func($self->latestVersionCallback());
    }
}
