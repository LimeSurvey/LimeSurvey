7.0.0 / 2016-04-03
==================
* **Breaking Change:** Restructured and refactored SASS source files to eliminate compass dependency and be more organized. Thanks to [Jacob van Mourik
 (jcbvm)](https://github.com/jcbvm)

6.1.7 / 2016-04-03
==================
* **Bug Fix:** Fixes issue where slider accidently scrolls when user taps on mobile device. Thanks to [Jerry (jerrylow)](https://github.com/jerrylow)

6.1.5 / 2016-03-12
==================
* **Bug Fix:** Call resize() before layout() within relayout() method, which enables intially hidden sliders to be revealed and behave appropriately. Thanks to [Peter (MaZderMind)](https://github.com/MaZderMind)

6.1.3 / 2016-03-07
==================
* **Bug Fix:** Fixed horizontal centering issue with labels. Thanks to [Josh Guffey](https://github.com/jguffey)

6.1.0 / 2016-02-28
==================
* **New Feature:** Auto-registering/intializing slider via `data-provide="slider"` attribute. Thanks to [MaZderMind](https://github.com/MaZderMind)
* Adding Github Templates for Issues, Pull Requeusts, and Contributions

6.0.16 / 2016-02-04
==================
* **Bug Fix:** Attempted Bug fix from 6.0.11 was refined to ensure so side effects.

6.0.15 / 2016-02-04
==================
* **Bug Fix:** _setText() defaults to `.textContent` vs `.innerHTML`. Thanks to [gio-js](https://github.com/gio-js)

6.0.13 / 2016-01-31
==================
* Reverted Bug fix from prior release

6.0.11 / 2016-01-31
==================
* **Bug fix:** Slider was not scrolling properly when nested inside of scrollable container. Thanks to [serbiant](https://github.com/serbiant)


6.0.9 / 2016-01-26
==================
* **Bug fix:** Race condition in `setValue()` where slider value was being set after `change` and `slide` events were being triggered. Thanks to [glaszig](https://github.com/glaszig)

6.0.7 / 2016-01-22
==================
* **Bug fix:** When `tooltip_position` option is set to `"bottom"` on a slider with multiple split handles, position both tooltips below the slider. Thanks to [Martin Hesslund](https://github.com/kesse)

6.0.5 / 2016-01-20
==================
* bower.json: changing "main" to reference /dist/bootstrap-slider.js

6.0.2 / 2015-12-31
==================
* package.json: changing "main" to point at proper file path

6.0.0 / 2015-12-30
==================
* Moving all source code to `/src` directory
* Transpiling JS with [Babel](https://babeljs.io/)
* Adding `Other Guidelines` section to CONTRIBUTING.MD
* Updating README with Grunt CLI tasks
* Update postpublish script to reference transpiled code
* Freezing dependency versions (this allows us to ensure the module and grunt tasks always have consistent/repeatable behavior)
* Adding an `.nvmrc` file for Node 5.x.x. This version of node comes with NPM 3.x.x, which creates a flat dependency tree for `node_modules`, which basically eliminates the need for bower as our client-side deps management solution

5.3.6 / 2015-12-27
==================
* Restoring bootstrap depedency to bower.json file (Fixes issue with `grunt prod` task)

5.3.4 / 2015-12-27
==================
* **Bug fix:** Ticks now reposition themselves during window resize - Thanks to [Zachary Siswick](https://github.com/zsiswick)
