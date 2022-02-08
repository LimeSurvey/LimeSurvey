module.exports = {
    presets: [
        ['@vue/app', { useBuiltIns: 'entry' }],
        ['@babel/preset-env', { targets: { node: 'current'}}],
    ],
    plugins: []
}