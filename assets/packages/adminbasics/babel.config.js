// babel.config.js
module.exports = function(api) {
    if(api.env(["production", "development"])) {
        return {
            exclude: "node_modules/**",
            presets: [
                [
                    "@babel/preset-env",
                    {
                        targets: "> 0.25%, not dead",
                        modules: "false",
                        useBuiltIns: "entry",
                        corejs: 3
                    }
                ]
            ]
        };
    } else {
        return {
            exclude: "node_modules/**",
            presets: [
                [
                    "@babel/preset-env",
                    {
                        targets: "> 0.25%, not dead",
                        modules: "auto",
                        useBuiltIns: "entry",
                        corejs: 3
                    }
                ]
            ]
        };
    }
};
