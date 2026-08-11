# Course Readiness Coach

Course Readiness Coach (`local_coursecoach`) is a read-only Moodle course-readiness report for Moodle 4.5 through 5.2.

> Check whether your Moodle course is ready for learners.

Current release: **0.3.0**.

## Installation

Place this directory at `local/coursecoach` in a supported Moodle checkout, then complete the standard Moodle plugin upgrade process. Editing teachers and managers receive `local/coursecoach:view` by default.

## Scoring

Only applicable checks contribute to the 0-100 score. Passed checks receive full credit, warnings receive half credit, and critical issues receive no credit. The weights are: visibility 1, dates 1, course completion 3, activity completion coverage 2, required activity availability 3, quiz pass/completion 2, quiz randomisation 1, learner feedback 1, and incomplete content 1. Any critical result labels the course **Not ready** regardless of its numerical score.

Required activity availability is intentionally conservative: it reports only activities required for course completion that are hidden. Learner-specific availability restrictions are not evaluated.

## Development checks

From the Moodle root, run:

```text
vendor/bin/phpunit local/coursecoach/tests
vendor/bin/phpcs --standard=moodle local/coursecoach
```

Course Readiness Coach is licensed under the GNU GPL v3 or later.
