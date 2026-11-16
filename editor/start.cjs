#!/usr/bin/env node
'use strict';

const { execSync } = require('child_process');

const commitHash = execSync('git rev-parse HEAD').toString().trim();
process.env.REACT_APP_COMMIT_HASH = commitHash;

execSync('rsbuild dev', { stdio: 'inherit' });