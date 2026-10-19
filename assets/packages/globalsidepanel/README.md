# globalsidepanel

LimeSurvey global side panel, bundled with webpack.

## Requirements

Node.js 20 or later, matching the CI workflow which uses Node.js 24.

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
