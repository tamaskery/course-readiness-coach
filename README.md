# Course Readiness Coach

> Check whether your Moodle course is ready for learners.

Course Readiness Coach (`report_coursecoach`) is a teacher- and manager-facing, read-only Moodle course-readiness report. Version **0.6.1** supports Moodle 4.5 through 5.2.

## Installation and access

Install the plugin directory at `report/coursecoach`, then complete Moodle's standard plugin installation. The report is available in course navigation to users with `report/coursecoach:view`; editing teachers and managers receive this capability by default.

Moodle treats `report_coursecoach` as a different component from the former `local_coursecoach` plugin. Uninstall and remove the former local plugin before installing this report plugin to avoid duplicate navigation entries. Course Readiness Coach owns no database tables or stored report data.

The analysis never changes course settings, creates learner completion records, analyses learner data, or calls external APIs or AI services.

## Readiness checks

The report prioritises issues that need attention, provides a readiness score and overall status, and links to Moodle settings where applicable. It currently checks:

1. Course visibility
2. Course date sanity
3. Course completion configuration
4. Activity completion coverage
5. Required completion activity accessibility
6. Quiz pass/completion configuration
7. Quiz question randomisation
8. Feedback/evaluation presence
9. Incomplete course content
10. Activity date alignment

Only applicable checks contribute to the 0–100 score. Passed checks receive full credit, warnings half credit, and critical issues no credit. A critical issue always results in **Not ready**.

## Conservative scope and limitations

Course Readiness Coach flags configurations that are very likely accidental or problematic; it does not impose a pedagogical model. False positives are treated as worse than marginal missed recommendations.

- Activity completion coverage is deliberately conservative and does not treat passive view-trackable resources as needing completion.
- Accessibility detection covers deterministic hidden activities required for course completion, not learner-specific restrictions.
- Quiz randomisation detects Moodle random-question selection, not broader pedagogical variation.
- Feedback detection covers standard Moodle Feedback activities.
- Incomplete-content detection covers visible empty non-general sections.
- Activity date alignment checks only high-confidence Quiz and Assignment timeline mismatches. Course dates are not treated as hard access boundaries, relative Assignment dates are skipped, and learner-specific availability is not analysed.
- It does not crawl external links for breakage.

## Development checks

From the Moodle root, run:

```text
vendor/bin/phpunit report/coursecoach/tests
vendor/bin/phpcs --standard=moodle report/coursecoach
```

Course Readiness Coach is licensed under the GNU GPL v3 or later.

## Support

Report bugs or request improvements through the [GitHub issue tracker](https://github.com/tamaskery/course-readiness-coach/issues).
