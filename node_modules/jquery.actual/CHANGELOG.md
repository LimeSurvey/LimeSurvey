# jQuery Actual Plugin CHANGELOG

## 1.0.19

- Add `main` property to `package.json`



## 1.0.18

- Depend on jQuery as a peer



## 1.0.17

- Added display inline support



## 1.0.16

- [refactoring] Keep original element styles as far as possible



## 1.0.15

- [bug fix] Replaced jQuery version detection with feature detection. Previously this would not properly detect jQuery 1.10+



## 1.0.14

- Added support for jQuery 1.9.0



## 1.0.13

- [bug fix] Local variable `style` not initialized, thanks to [Searle](https://github.com/Searle)



## 1.0.12

- [refactoring] Use `.eq( 0 )` instead of `.filter( ':first' )` for better performance, thanks to Matt Hinchliffe



## 1.0.11

- [refactoring] Select only the first hidden element to improve the perfomance



## 1.0.10

- [bug fix] Override `!imporant` css declarations



## 1.0.9

- [bug fix] jQuery 1.8.0 compatibility



## 1.0.8

- [bug fix] Inverted code lines



## 1.0.7

- [refactoring] Save/restore element style rather than individual CSS attributes( thanks to Jon Tara )



## 1.0.6

- [bug fixed] Pass `configs.includeMargin` to only `outerWidth` and `outerHeight` so it does not break in $ 1.7.2



## 1.0.5

- Add package.json for new jquery plugin site



## 1.0.4

- Add `includeMargin` for `outerWidth`( thanks to Erwin Derksen )



## 1.0.3

- [bug fixed] `$` namespace conflict



## 1.0.2

- [bug fixed] Typo



## 1.0.1

- [bug fixed] Typo



## 1.0.0

- First stable release
