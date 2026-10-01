# RobThree\TwoFactorAuth changelog

# Version 2.x

## Breaking changes

### PHP Version

Version 2.x requires at least PHP 8.1.

### Constructor signature

With version 2.x, the `algorithm` parameter of `RobThree\Auth\TwoFactorAuth` constructor is now an `enum`.

On version 1.x:

~~~php
use RobThree\Auth\TwoFactorAuth;

$lib = new TwoFactorAuth('issuer-name', 6, 30, 'sha1');
~~~

On version 2.x, simple change the algorithm from a `string` to the correct `enum`:

~~~php
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Algorithm;

$lib = new TwoFactorAuth('issuer-name', 6, 30, Algorithm::Sha1);
~~~

See the [Algorithm.php](./lib/Algorithm.php) file to see available algorithms.
