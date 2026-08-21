<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Value object representing one Course Coach check result.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use coding_exception;
use moodle_url;

/**
 * Immutable result returned by every checker.
 */
final class result {
    /** Passed status. */
    public const STATUS_PASSED = 'passed';

    /** Warning status. */
    public const STATUS_WARNING = 'warning';

    /** Critical status. */
    public const STATUS_CRITICAL = 'critical';

    /** Not applicable status. */
    public const STATUS_NOT_APPLICABLE = 'not_applicable';

    /** Recommendation severity. */
    public const SEVERITY_RECOMMENDATION = 'recommendation';

    /** Important severity. */
    public const SEVERITY_IMPORTANT = 'important';

    /** Critical severity. */
    public const SEVERITY_CRITICAL = 'critical';

    /** @var string[] Valid result statuses. */
    private const STATUSES = [
        self::STATUS_PASSED,
        self::STATUS_WARNING,
        self::STATUS_CRITICAL,
        self::STATUS_NOT_APPLICABLE,
    ];

    /** @var string[] Valid severity levels. */
    private const SEVERITIES = [
        self::SEVERITY_RECOMMENDATION,
        self::SEVERITY_IMPORTANT,
        self::SEVERITY_CRITICAL,
    ];

    /** @var bool Whether this check contributes to readiness. */
    private bool $applicable;

    /** @var string Result status. */
    private string $status;

    /** @var string Result severity. */
    private string $severity;

    /** @var string User-facing result title. */
    private string $title;

    /** @var string User-facing explanation. */
    private string $explanation;

    /** @var string User-facing recommendation. */
    private string $recommendation;

    /** @var moodle_url|null Relevant Moodle settings URL. */
    private ?moodle_url $settingsurl;

    /** @var string|null Context-specific action label. */
    private ?string $actionlabel;

    /**
     * Constructor.
     *
     * @param bool $applicable Whether the check contributes to readiness.
     * @param string $status One of the STATUS_* constants.
     * @param string $severity One of the SEVERITY_* constants.
     * @param string $title User-facing title.
     * @param string $explanation User-facing explanation.
     * @param string $recommendation User-facing recommendation.
     * @param moodle_url|null $settingsurl Relevant Moodle settings URL.
     * @param string|null $actionlabel Context-specific action label.
     */
    public function __construct(
        bool $applicable,
        string $status,
        string $severity,
        string $title,
        string $explanation,
        string $recommendation,
        ?moodle_url $settingsurl = null,
        ?string $actionlabel = null
    ) {
        if (!in_array($status, self::STATUSES, true)) {
            throw new coding_exception('Invalid Course Coach check status.');
        }
        if (!in_array($severity, self::SEVERITIES, true)) {
            throw new coding_exception('Invalid Course Coach severity.');
        }
        if ($applicable === ($status === self::STATUS_NOT_APPLICABLE)) {
            throw new coding_exception('Course Coach applicability and status do not agree.');
        }

        $this->applicable = $applicable;
        $this->status = $status;
        $this->severity = $severity;
        $this->title = $title;
        $this->explanation = $explanation;
        $this->recommendation = $recommendation;
        $this->settingsurl = $settingsurl;
        $this->actionlabel = $actionlabel;
    }

    /**
     * Return whether the check contributes to readiness.
     *
     * @return bool Whether the check contributes to readiness.
     */
    public function is_applicable(): bool {
        return $this->applicable;
    }

    /**
     * Return the result status.
     *
     * @return string Result status.
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * Return the result severity.
     *
     * @return string Result severity.
     */
    public function get_severity(): string {
        return $this->severity;
    }

    /**
     * Return the user-facing title.
     *
     * @return string User-facing title.
     */
    public function get_title(): string {
        return $this->title;
    }

    /**
     * Return the user-facing explanation.
     *
     * @return string User-facing explanation.
     */
    public function get_explanation(): string {
        return $this->explanation;
    }

    /**
     * Return the user-facing recommendation.
     *
     * @return string User-facing recommendation.
     */
    public function get_recommendation(): string {
        return $this->recommendation;
    }

    /**
     * Return the relevant Moodle settings URL.
     *
     * @return moodle_url|null Relevant Moodle settings URL.
     */
    public function get_settings_url(): ?moodle_url {
        return $this->settingsurl;
    }

    /**
     * Return the context-specific action label.
     *
     * @return string|null Context-specific action label.
     */
    public function get_action_label(): ?string {
        return $this->actionlabel;
    }
}
