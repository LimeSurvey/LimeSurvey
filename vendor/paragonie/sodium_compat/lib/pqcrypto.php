<?php

/* Guard each entry independently for partially supported ext-sodium builds. */
if (!function_exists('sodium_crypto_kem_mlkem768_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_mlkem768_keypair()
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_mlkem768_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_kem_mlkem768_keypair();
    }
}
if (!function_exists('sodium_crypto_kem_mlkem768_seed_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_mlkem768_seed_keypair()
     * @param string $seed
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_mlkem768_seed_keypair(
        #[\SensitiveParameter]
        $seed
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($seed, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_mlkem768_seed_keypair($seed);
    }
}
if (!function_exists('sodium_crypto_kem_mlkem768_secretkey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_mlkem768_secretkey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_mlkem768_secretkey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_mlkem768_secretkey($keypair);
    }
}
if (!function_exists('sodium_crypto_kem_mlkem768_publickey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_mlkem768_publickey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_mlkem768_publickey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_mlkem768_publickey($keypair);
    }
}
if (!function_exists('sodium_crypto_kem_mlkem768_enc')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_mlkem768_enc()
     * @param string $publicKey
     * @return array
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_mlkem768_enc($publicKey)
    {
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_mlkem768_enc($publicKey);
    }
}
if (!function_exists('sodium_crypto_kem_mlkem768_enc_deterministic')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_mlkem768_enc_deterministic()
     * @param string $publicKey
     * @param string $seed
     * @return array
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_mlkem768_enc_deterministic(
        $publicKey,
        #[\SensitiveParameter]
        $seed
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($seed, 'string', 2);
        return ParagonIE_Sodium_Compat::crypto_kem_mlkem768_enc_deterministic($publicKey, $seed);
    }
}
if (!function_exists('sodium_crypto_kem_mlkem768_dec')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_mlkem768_dec()
     * @param string $ciphertext
     * @param string $secretKey
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_mlkem768_dec(
        $ciphertext,
        #[\SensitiveParameter]
        $secretKey
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($ciphertext, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($secretKey, 'string', 2);
        return ParagonIE_Sodium_Compat::crypto_kem_mlkem768_dec($ciphertext, $secretKey);
    }
}
if (!function_exists('sodium_crypto_kem_xwing_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_xwing_keypair()
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_xwing_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_kem_xwing_keypair();
    }
}
if (!function_exists('sodium_crypto_kem_xwing_seed_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_xwing_seed_keypair()
     * @param string $seed
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_xwing_seed_keypair(
        #[\SensitiveParameter]
        $seed
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($seed, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_xwing_seed_keypair($seed);
    }
}
if (!function_exists('sodium_crypto_kem_xwing_secretkey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_xwing_secretkey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_xwing_secretkey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_xwing_secretkey($keypair);
    }
}
if (!function_exists('sodium_crypto_kem_xwing_publickey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_xwing_publickey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_xwing_publickey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_xwing_publickey($keypair);
    }
}
if (!function_exists('sodium_crypto_kem_xwing_enc')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_xwing_enc()
     * @param string $publicKey
     * @return array
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_xwing_enc($publicKey)
    {
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_xwing_enc($publicKey);
    }
}
if (!function_exists('sodium_crypto_kem_xwing_enc_deterministic')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_xwing_enc_deterministic()
     * @param string $publicKey
     * @param string $seed
     * @return array
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_xwing_enc_deterministic(
        $publicKey,
        #[\SensitiveParameter]
        $seed
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($seed, 'string', 2);
        return ParagonIE_Sodium_Compat::crypto_kem_xwing_enc_deterministic($publicKey, $seed);
    }
}
if (!function_exists('sodium_crypto_kem_xwing_dec')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_xwing_dec()
     * @param string $ciphertext
     * @param string $secretKey
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_xwing_dec(
        $ciphertext,
        #[\SensitiveParameter]
        $secretKey
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($ciphertext, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($secretKey, 'string', 2);
        return ParagonIE_Sodium_Compat::crypto_kem_xwing_dec($ciphertext, $secretKey);
    }
}
if (!function_exists('sodium_crypto_kem_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_keypair()
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_kem_keypair();
    }
}
if (!function_exists('sodium_crypto_kem_seed_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_seed_keypair()
     * @param string $seed
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_seed_keypair(
        #[\SensitiveParameter]
        $seed
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($seed, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_seed_keypair($seed);
    }
}
if (!function_exists('sodium_crypto_kem_secretkey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_secretkey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_secretkey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_secretkey($keypair);
    }
}
if (!function_exists('sodium_crypto_kem_publickey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_publickey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_publickey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_publickey($keypair);
    }
}
if (!function_exists('sodium_crypto_kem_enc')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_enc()
     * @param string $publicKey
     * @return array
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_enc($publicKey)
    {
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_kem_enc($publicKey);
    }
}
if (!function_exists('sodium_crypto_kem_enc_deterministic')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_enc_deterministic()
     * @param string $publicKey
     * @param string $seed
     * @return array
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_enc_deterministic(
        $publicKey,
        #[\SensitiveParameter]
        $seed
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($seed, 'string', 2);
        return ParagonIE_Sodium_Compat::crypto_kem_enc_deterministic($publicKey, $seed);
    }
}
if (!function_exists('sodium_crypto_kem_dec')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_kem_dec()
     * @param string $ciphertext
     * @param string $secretKey
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_kem_dec(
        $ciphertext,
        #[\SensitiveParameter]
        $secretKey
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($ciphertext, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($secretKey, 'string', 2);
        return ParagonIE_Sodium_Compat::crypto_kem_dec($ciphertext, $secretKey);
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_keypair()
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_keypair();
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_seed_keypair')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_seed_keypair()
     * @param string $seed
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_seed_keypair(
        #[\SensitiveParameter]
        $seed
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($seed, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_seed_keypair($seed);
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_secretkey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_secretkey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_secretkey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_secretkey($keypair);
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_publickey')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_publickey()
     * @param string $keypair
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_publickey(
        #[\SensitiveParameter]
        $keypair
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($keypair, 'string', 1);
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_publickey($keypair);
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_detached')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_detached()
     * @param string $message
     * @param string $secretKey
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_detached(
        $message,
        #[\SensitiveParameter]
        $secretKey
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($message, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($secretKey, 'string', 2);
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_detached($message, $secretKey);
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_detached_ctx')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_detached_ctx()
     * @param string $message
     * @param string $context
     * @param string $secretKey
     * @return string
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_detached_ctx(
        $message,
        $context,
        #[\SensitiveParameter]
        $secretKey
    ) {
        ParagonIE_Sodium_Core_Util::declareScalarType($message, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($context, 'string', 2);
        ParagonIE_Sodium_Core_Util::declareScalarType($secretKey, 'string', 3);
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_detached_ctx($message, $context, $secretKey);
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_verify_detached')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_verify_detached()
     * @param string $signature
     * @param string $message
     * @param string $publicKey
     * @return bool
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_verify_detached($signature, $message, $publicKey)
    {
        ParagonIE_Sodium_Core_Util::declareScalarType($signature, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($message, 'string', 2);
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 3);
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_verify_detached($signature, $message, $publicKey);
    }
}
if (!function_exists('sodium_crypto_sign_mldsa44_verify_detached_ctx')) {
    /**
     * @see ParagonIE_Sodium_Compat::crypto_sign_mldsa44_verify_detached_ctx()
     * @param string $signature
     * @param string $message
     * @param string $context
     * @param string $publicKey
     * @return bool
     * @throws \SodiumException
     * @throws \TypeError
     */
    function sodium_crypto_sign_mldsa44_verify_detached_ctx($signature, $message, $context, $publicKey)
    {
        ParagonIE_Sodium_Core_Util::declareScalarType($signature, 'string', 1);
        ParagonIE_Sodium_Core_Util::declareScalarType($message, 'string', 2);
        ParagonIE_Sodium_Core_Util::declareScalarType($context, 'string', 3);
        ParagonIE_Sodium_Core_Util::declareScalarType($publicKey, 'string', 4);
        return ParagonIE_Sodium_Compat::crypto_sign_mldsa44_verify_detached_ctx($signature, $message, $context, $publicKey);
    }
}
if (!defined('SODIUM_CRYPTO_KEM_MLKEM768_PUBLICKEYBYTES')) {
    define('SODIUM_CRYPTO_KEM_MLKEM768_PUBLICKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_MLKEM768_PUBLICKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_MLKEM768_SECRETKEYBYTES')) {
    define('SODIUM_CRYPTO_KEM_MLKEM768_SECRETKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_MLKEM768_SECRETKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_MLKEM768_SEEDBYTES')) {
    define('SODIUM_CRYPTO_KEM_MLKEM768_SEEDBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_MLKEM768_SEEDBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_MLKEM768_CIPHERTEXTBYTES')) {
    define('SODIUM_CRYPTO_KEM_MLKEM768_CIPHERTEXTBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_MLKEM768_CIPHERTEXTBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_MLKEM768_SHAREDSECRETBYTES')) {
    define('SODIUM_CRYPTO_KEM_MLKEM768_SHAREDSECRETBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_MLKEM768_SHAREDSECRETBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_MLKEM768_KEYPAIRBYTES')) {
    define('SODIUM_CRYPTO_KEM_MLKEM768_KEYPAIRBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_MLKEM768_KEYPAIRBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_XWING_PUBLICKEYBYTES')) {
    define('SODIUM_CRYPTO_KEM_XWING_PUBLICKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_XWING_PUBLICKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_XWING_SECRETKEYBYTES')) {
    define('SODIUM_CRYPTO_KEM_XWING_SECRETKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_XWING_SECRETKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_XWING_SEEDBYTES')) {
    define('SODIUM_CRYPTO_KEM_XWING_SEEDBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_XWING_SEEDBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_XWING_CIPHERTEXTBYTES')) {
    define('SODIUM_CRYPTO_KEM_XWING_CIPHERTEXTBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_XWING_CIPHERTEXTBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_XWING_SHAREDSECRETBYTES')) {
    define('SODIUM_CRYPTO_KEM_XWING_SHAREDSECRETBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_XWING_SHAREDSECRETBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_XWING_KEYPAIRBYTES')) {
    define('SODIUM_CRYPTO_KEM_XWING_KEYPAIRBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_XWING_KEYPAIRBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_PUBLICKEYBYTES')) {
    define('SODIUM_CRYPTO_KEM_PUBLICKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_PUBLICKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_SECRETKEYBYTES')) {
    define('SODIUM_CRYPTO_KEM_SECRETKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_SECRETKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_SEEDBYTES')) {
    define('SODIUM_CRYPTO_KEM_SEEDBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_SEEDBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_CIPHERTEXTBYTES')) {
    define('SODIUM_CRYPTO_KEM_CIPHERTEXTBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_CIPHERTEXTBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_SHAREDSECRETBYTES')) {
    define('SODIUM_CRYPTO_KEM_SHAREDSECRETBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_SHAREDSECRETBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_KEYPAIRBYTES')) {
    define('SODIUM_CRYPTO_KEM_KEYPAIRBYTES', ParagonIE_Sodium_Compat::CRYPTO_KEM_KEYPAIRBYTES);
}
if (!defined('SODIUM_CRYPTO_KEM_PRIMITIVE')) {
    define('SODIUM_CRYPTO_KEM_PRIMITIVE', ParagonIE_Sodium_Compat::CRYPTO_KEM_PRIMITIVE);
}
if (!defined('SODIUM_CRYPTO_SIGN_MLDSA44_PUBLICKEYBYTES')) {
    define('SODIUM_CRYPTO_SIGN_MLDSA44_PUBLICKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_SIGN_MLDSA44_PUBLICKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_SIGN_MLDSA44_SECRETKEYBYTES')) {
    define('SODIUM_CRYPTO_SIGN_MLDSA44_SECRETKEYBYTES', ParagonIE_Sodium_Compat::CRYPTO_SIGN_MLDSA44_SECRETKEYBYTES);
}
if (!defined('SODIUM_CRYPTO_SIGN_MLDSA44_SEEDBYTES')) {
    define('SODIUM_CRYPTO_SIGN_MLDSA44_SEEDBYTES', ParagonIE_Sodium_Compat::CRYPTO_SIGN_MLDSA44_SEEDBYTES);
}
if (!defined('SODIUM_CRYPTO_SIGN_MLDSA44_BYTES')) {
    define('SODIUM_CRYPTO_SIGN_MLDSA44_BYTES', ParagonIE_Sodium_Compat::CRYPTO_SIGN_MLDSA44_BYTES);
}
if (!defined('SODIUM_CRYPTO_SIGN_MLDSA44_KEYPAIRBYTES')) {
    define('SODIUM_CRYPTO_SIGN_MLDSA44_KEYPAIRBYTES', ParagonIE_Sodium_Compat::CRYPTO_SIGN_MLDSA44_KEYPAIRBYTES);
}
if (!defined('SODIUM_CRYPTO_SIGN_MLDSA44_CONTEXTBYTES_MAX')) {
    define('SODIUM_CRYPTO_SIGN_MLDSA44_CONTEXTBYTES_MAX', ParagonIE_Sodium_Compat::CRYPTO_SIGN_MLDSA44_CONTEXTBYTES_MAX);
}
if (!defined('SODIUM_CRYPTO_SIGN_MLDSA44_MESSAGEBYTES_MAX')) {
    define('SODIUM_CRYPTO_SIGN_MLDSA44_MESSAGEBYTES_MAX', ParagonIE_Sodium_Compat::CRYPTO_SIGN_MLDSA44_MESSAGEBYTES_MAX);
}
