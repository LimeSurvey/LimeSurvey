// babel.config.js
module.exports = {
    exclude: 'node_modules/**',
    "presets": [
        [
            "@babel/preset-env",
            {
                targets: "> 0.25%, not dead",
                modules: 'false',
                useBuiltIns: "entry",
                corejs: 3,
            }
        ]
    ]
}
