<?php

namespace SkyVerge\WooCommerce\Memberships;

defined('ABSPATH') or exit;

/**
 * Generic fixed-window rate limiter, scoped per WC session + identifier.
 *
 * @since 1.30.0
 */
class FixedWindowRateLimiter
{
    protected const DEFAULT_MAX_ATTEMPTS = 5;
	protected const DEFAULT_WINDOW_SECONDS = 600;

	/** @var string the WC session key this limiter's counters are stored under */
	protected string $sessionKey;

    /**
     * @param string $sessionKey the WC session key this limiter's counters are stored under; also seeds this
     *                           instance's dynamic filter name, so each consumer gets its own hookable filter
     */
    public function __construct(string $sessionKey)
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * Determines whether attempts for an identifier are currently rate limited for this session.
     *
     * @since 1.30.0
     *
     * @param string $identifier the thing being rate limited (e.g. a profile field slug)
     * @return bool
     */
    public function isRateLimited(string $identifier): bool
    {
        WC()->initialize_session();

        // fail closed: without a session we can't count attempts, and WC's own session init can be
        // skipped by spoofing a REST request (see is_rest_api_request()), so treat "no session" as abuse, not as trusted.
        if (! isset(WC()->session) || ! WC()->session) {
            return true;
        }

        return $this->checkIfRateLimitedFromSession($identifier);
    }

	/**
	 * Determines whether attempts for an identifier are currently rate limited for this session.
	 * This method assumes that the session has already been initialized and confirmed loaded.
	 *
	 * @since 1.30.0
	 *
	 * @param string $identifier the thing being rate limited (e.g. a profile field slug)
	 * @return bool
	 */
	protected function checkIfRateLimitedFromSession(string $identifier) : bool
	{
		$rateLimit = $this->getRateLimit($identifier);
		$attempts = $this->getAttemptsFromSession();
		$now = $this->getCurrentTime();
		$entry = $attempts[$identifier] ?? ['count' => 0, 'windowStartedAt' => $now];

		if (($now - $entry['windowStartedAt']) >= $rateLimit['window']) {
			$entry = ['count' => 0, 'windowStartedAt' => $now];
		}

		$limited = $entry['count'] >= $rateLimit['max'];

		if (!$limited) {
			$entry['count']++;
		}

		$attempts[$identifier] = $entry;

		$this->updateSessionWithAttempts($attempts);

		return $limited;
	}

	/**
	 * Gets the current time.
	 * @codeCoverageIgnore
	 */
	protected function getCurrentTime() : int
	{
		return time();
	}

    /**
     * Gets the rate limit (max attempts + window in seconds) for an identifier.
     *
     * Filterable per session key + identifier, but the returned shape is always enforced
     * regardless of what the filter returns — a malformed/partial filter return falls back
     * to sane defaults per key.
     *
     * @since 1.30.0
     *
     * @param string $identifier the thing being rate limited
     * @return array{max: int, window: int}
     */
    protected function getRateLimit(string $identifier): array
    {
        $defaults = [
            'max' => static::DEFAULT_MAX_ATTEMPTS,
            'window' => static::DEFAULT_WINDOW_SECONDS,
        ];

        /**
         * Filters the rate limit for this limiter's session key.
         *
         * The filter name is dynamic per session key, so each consumer of this class gets
         * its own distinct, independently hookable filter.
         *
         * @since 1.30.0
         *
         * @param array $rateLimit ['max' => int, 'window' => int (seconds)]
         * @param string $identifier the thing being rate limited
         */
        $rateLimit = apply_filters("wc_memberships_rate_limit_{$this->sessionKey}", $defaults, $identifier);
        $rateLimit = is_array($rateLimit) ? $rateLimit : [];

        return [
            'max' => isset($rateLimit['max']) && is_numeric($rateLimit['max']) ? max(1, (int) $rateLimit['max']) : $defaults['max'],
            'window' => isset($rateLimit['window']) && is_numeric($rateLimit['window']) ? max(1, (int) $rateLimit['window']) : $defaults['window'],
        ];
    }

    /**
     * Gets the current attempt-tracking data from the WC session.
     *
     * @since 1.30.0
     *
     * @return array
     */
    protected function getAttemptsFromSession(): array
    {
        return (array) WC()->session->get($this->sessionKey, []);
    }

    /**
     * Persists attempt-tracking data back to the WC session.
     *
     * @since 1.30.0
     *
     * @param array $attempts
     * @return void
     */
    protected function updateSessionWithAttempts(array $attempts): void
    {
        WC()->session->set($this->sessionKey, $attempts);
    }
}
