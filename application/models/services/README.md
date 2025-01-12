# README

The newly added service classes are expected to have a higher quality than older code in LimeSurvey. Especially:

* PSR-12 compliant
* Don't use Hungarian notation, but instead proper docblocks and @var annotations that can be checked by Psalm
* All methods are expected to be testable with unit-tests, which means that all dependencies should be injected _or_ side-effects isolated in small, mockable methods
* Don't use static methods unless you can prove that they don't need to be mocked. Maybe a separate function outside a class would be better?
* MessDetector might be used to enforce maximum class and method length
* A [dependency injection container](https://www.yiiframework.com/doc/guide/2.0/en/concept-di-container) will be used to coordinate dependencies in the future

For more information, please read the [code quality guide](https://manual.gitit-tech.com/Code_quality_guide).

Discussions about code quality guidelines can be had on the forum.
