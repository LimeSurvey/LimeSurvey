// run "yarn install" first
//
// for bootstrap 5:
// yarn gulp build_bootstrap
//
// for admintheme:
// yarn gulp build_admintheme
//
// for survey_theme_fruity:
// yarn gulp build_survey_theme_fruity
//
// for survey_theme_fruity_twentythree:
// yarn gulp build_survey_theme_fruity_twentythree
//
// you can also use watch instead of build for every command
// e.g. yarn gulp watch_bootstrap

const { watch, series, parallel } = require("gulp");
const { src, dest } = require("gulp");
const uglify = require("gulp-uglify");
const rename = require("gulp-rename");
const sass = require("gulp-sass")(require("sass"));
const gulppostcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");
const cssnano = require("cssnano");
const rtlcss = require("gulp-rtlcss");
const replace = require("gulp-replace");
const merge = require("merge-stream");
const babelify = require("babelify");
const source = require("vinyl-source-stream");
const buffer = require("vinyl-buffer");
const browserify = require("browserify");
const fs = require("fs");

function js_minify() {
    // browserify package handler
    return (
        browserify({
            entries: ["assets/bootstrap_5/js/bootstrap_5.js"],
        })
            // transform babelify ES6 to ES5 [@babel/preset-env]
            .transform(babelify, {
                presets: ["@babel/preset-env"],
                retainLines: false,
                compact: false,
                global: true,
            })
            // bundle the transformed code
            .bundle()
            // sourcemap
            .pipe(source("assets/bootstrap_5/js/bootstrap_5.js"))
            // rename
            .pipe(rename("bootstrap_5.js"))
            // buffer
            .pipe(buffer())
            // destination
            .pipe(dest("assets/bootstrap_5/build/js/"))
            .pipe(uglify())
            .pipe(rename({ extname: ".min.js" }))
            .pipe(dest("assets/bootstrap_5/build/js/"))
    );
}

function scss_transpile() {
    return src("assets/bootstrap_5/scss/bootstrap_5.scss").pipe(sass());
}

function scss_minify() {
    let plugins = [
        autoprefixer(),
        cssnano({
            preset: [
                "default",
                {
                    colormin: false,
                },
            ],
        }),
    ];
    return scss_transpile()
        .pipe(dest("assets/bootstrap_5/build/css"))
        .pipe(gulppostcss(plugins))
        .pipe(rename({ extname: ".min.css" }))
        .pipe(dest("assets/bootstrap_5/build/css"));
}

function scss_minify_rtl() {
    let plugins = [
        autoprefixer(),
        cssnano({
            preset: [
                "default",
                {
                    colormin: false,
                },
            ],
        }),
    ];
    return scss_transpile()
        .pipe(rtlcss())
        .pipe(rename({ suffix: "-rtl" }))
        .pipe(dest("assets/bootstrap_5/build/css"))
        .pipe(gulppostcss(plugins))
        .pipe(rename({ extname: ".min.css" }))
        .pipe(dest("assets/bootstrap_5/build/css"));
}

exports.watch_bootstrap = function () {
    watch("assets/bootstrap_5/js/**/*.js", js_minify);
    watch(
        "assets/bootstrap_5/scss/**/*.scss",
        parallel(scss_minify, scss_minify_rtl),
    );
};

exports.build_bootstrap = parallel(js_minify, scss_minify, scss_minify_rtl);

// Admin themes: Sea Green and its variants share the SCSS in assets/admin_themes/Sea_Green.
// The theme tokens (the colours as CSS variables) are loaded by the React editor.
// "dark" is the dark mode of a theme, it is loaded after the CSS of "source".
const adminThemes = [
    {
        source: "assets/admin_themes/Sea_Green/sea_green.scss",
        dark: "assets/admin_themes/Sea_Green/sea_green_dark.scss",
        tokens: "assets/admin_themes/Sea_Green/theme_tokens.scss",
        target: "themes/admin/Sea_Green/css",
    },
    {
        source: "assets/admin_themes/Sea_Green_WCAG_AA/sea_green_wcag_aa.scss",
        dark: "assets/admin_themes/Sea_Green_WCAG_AA/sea_green_wcag_aa_dark.scss",
        tokens: "assets/admin_themes/Sea_Green_WCAG_AA/theme_tokens.scss",
        target: "themes/admin/Sea_Green_WCAG_AA/css",
    },
];

function adminThemeTokensTask(adminTheme) {
    const task = function () {
        let plugins = [
            cssnano({
                preset: [
                    "default",
                    {
                        colormin: false,
                    },
                ],
            }),
        ];
        return src([adminTheme.tokens])
            .pipe(sass())
            .pipe(dest(adminTheme.target))
            .pipe(gulppostcss(plugins))
            .pipe(rename({ extname: ".min.css" }))
            .pipe(dest(adminTheme.target));
    };
    task.displayName = "admintheme:" + adminTheme.target + ":tokens";
    return task;
}

function adminThemeTask(adminTheme, rtl, dark = false) {
    const task = function () {
        let plugins = [
            autoprefixer(),
            cssnano({
                preset: [
                    "default",
                    {
                        colormin: false,
                    },
                ],
            }),
        ];
        let stream = src([dark ? adminTheme.dark : adminTheme.source]).pipe(sass());
        if (rtl) {
            stream = stream.pipe(rtlcss()).pipe(rename({ suffix: "-rtl" }));
        }
        return stream
            .pipe(dest(adminTheme.target))
            .pipe(gulppostcss(plugins))
            .pipe(rename({ extname: ".min.css" }))
            .pipe(dest(adminTheme.target));
    };
    task.displayName = "admintheme:" + adminTheme.target + (dark ? ":dark" : "") + (rtl ? ":rtl" : "");
    return task;
}

const adminThemeTasks = adminThemes.flatMap((adminTheme) => [
    adminThemeTask(adminTheme, false),
    adminThemeTask(adminTheme, true),
    ...(adminTheme.dark ? [adminThemeTask(adminTheme, false, true), adminThemeTask(adminTheme, true, true)] : []),
    adminThemeTokensTask(adminTheme),
]);

exports.watch_admintheme = function () {
    watch("assets/admin_themes/**/*.scss", parallel(...adminThemeTasks));
};

exports.build_admintheme = parallel(...adminThemeTasks);

function survey_theme_fruity() {
    let variations = [
        ["apple_blossom", "#AA4340"],
        ["bay_of_many", "#214F7E"],
        ["black_pearl", "#071630"],
        ["free_magenta", "#C63678"],
        ["purple_tentacle", "#993399"],
        ["sea_green", "#328637"],
        ["sunset_orange", "#FE5B35"],
        ["skyline_blue", "#91dcff"],
    ];
    let plugins = [
        autoprefixer(),
        cssnano({
            preset: [
                "default",
                {
                    colormin: false,
                },
            ],
        }),
    ];
    let variationsFiles = variations.map((variation) => {
        let variationName = variation[0];
        let variationColor = variation[1];
        return src(["assets/survey_themes/fruity/fruityThemeTemplate.scss"])
            .pipe(
                replace(
                    "$base-color: #ffffff;",
                    "$base-color: " + variationColor + ";",
                ),
            )
            .pipe(sass())
            .pipe(gulppostcss(plugins))
            .pipe(rename(variationName + ".css"))
            .pipe(dest("themes/survey/fruity/css/variations"));
    });
    return merge(variationsFiles);
}

exports.build_survey_theme_fruity = parallel(survey_theme_fruity);

exports.watch_survey_theme_fruity = function () {
    watch("assets/survey_themes/fruity/src/**/*.scss", survey_theme_fruity);
};

function survey_theme_ls6() {
    let variations = [
        ["apple", "#14AE5C"],
        ["blueberry", "#5076FF"],
        ["grape", "#8146F6"],
        ["mango", "#ED5046"],
    ];
    let plugins = [
        autoprefixer(),
        // cssnano({
        //     preset: ['default', {
        //         colormin: false
        //     }]
        // })
    ];

    let variationsFiles = variations.map((variation) => {
        let variationName = variation[0];
        let variationColor = variation[1];
        return src([
            "assets/survey_themes/fruity_twentythree/theme_template.scss",
        ])
            .pipe(
                replace(
                    "$base-color: #ffffff;",
                    "$base-color: " + variationColor + ";",
                ),
            )
            .pipe(sass())
            .pipe(gulppostcss(plugins))
            .pipe(rename("theme_" + variationName + ".css"))
            .pipe(dest("themes/survey/fruity_twentythree/css/variations"));
    });
    return merge(variationsFiles);
}

function survey_theme_ls6_rtl() {
    let variations = [
        ["apple", "#0F8545"],
        ["blueberry", "#3D67FF"],
        ["grape", "#8146F6"],
        ["mango", "#E32416"],
    ];
    let plugins = [
        autoprefixer(),
        // cssnano({
        //     preset: ['default', {
        //         colormin: false
        //     }]
        // })
    ];

    let variationsFiles = variations.map((variation) => {
        let variationName = variation[0];
        let variationColor = variation[1];
        return src([
            "assets/survey_themes/fruity_twentythree/theme_template.scss",
        ])
            .pipe(
                replace(
                    "$base-color: #ffffff;",
                    "$base-color: " + variationColor + ";",
                ),
            )
            .pipe(sass())
            .pipe(rtlcss())
            .pipe(gulppostcss(plugins))
            .pipe(rename("theme_" + variationName + "-rtl.css"))
            .pipe(dest("themes/survey/fruity_twentythree/css/variations"));
    });
    return merge(variationsFiles);
}

function survey_theme_ls6_js() {
    // browserify package handler
    return (
        browserify({
            entries: [
                "assets/survey_themes/fruity_twentythree/theme_js_modules.js",
            ],
        })
            // transform babelify ES6 to ES5 [@babel/preset-env]
            .transform(babelify, {
                presets: ["@babel/preset-env"],
                retainLines: false,
                compact: false,
                global: true,
            })
            // bundle the transformed code
            .bundle()
            // sourcemap
            .pipe(
                source(
                    "assets/survey_themes/fruity_twentythree/theme_js_modules.js",
                ),
            )
            // rename
            .pipe(rename("theme.js"))
            // buffer
            .pipe(buffer())
            // destination
            .pipe(
                replace(
                    /^/,
                    fs.readFileSync(
                        "assets/survey_themes/fruity_twentythree/theme_js_disclaimer.js",
                    ),
                ),
            )
            .pipe(dest("themes/survey/fruity_twentythree/scripts/"))
    );
}

exports.build_survey_theme_fruity_twentythree = parallel(
    survey_theme_ls6,
    survey_theme_ls6_rtl,
    survey_theme_ls6_js,
);

exports.watch_survey_theme_fruity_twentythree = function () {
    watch(
        "assets/survey_themes/fruity_twentythree/**/*.scss",
        survey_theme_ls6,
    );
    watch(
        "assets/survey_themes/fruity_twentythree/**/*.scss",
        survey_theme_ls6_rtl,
    );
    watch(
        "assets/survey_themes/fruity_twentythree/**/*.js",
        survey_theme_ls6_js,
    );
};
