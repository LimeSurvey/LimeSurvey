# adminsidepanel

LimeSurvey admin side panel, vanilla JavaScript bundled with webpack.

## Requirements

Node.js 20.19 or later. The CI workflow uses Node.js 24. Node.js 14 is no longer supported; `css-minimizer-webpack-plugin` 8 needs 20.9 and `sass` 1.104 needs 20.19.

## Project setup
```
yarn install --frozen-lockfile
```

### Build development and production bundles
```
yarn build
```

### Build only the development bundle (writes to `build/`)
```
yarn dev
```

### Build only the production bundle (writes to `build.min/`)
```
yarn prod
```

### Rebuild on change
```
yarn watch
```
