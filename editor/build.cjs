#!/usr/bin/env node
'use strict';

const { execSync } = require('child_process');
const fs = require('fs');
const { name: pkgName, version: pkgVersion } = require('./package.json');

const commitHash = execSync('git rev-parse HEAD').toString().trim();
process.env.REACT_APP_COMMIT_HASH = commitHash;

execSync('rsbuild build', { stdio: 'inherit' });

if (process.platform !== 'win32' && fs.existsSync('postbuild.sh')) {
    execSync('bash postbuild.sh', { stdio: 'inherit' });
}