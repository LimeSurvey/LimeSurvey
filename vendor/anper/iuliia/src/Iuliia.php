<?php

namespace Anper\Iuliia;

/**
 * Class Iuliia
 * @package Anper\Iuliia
 */
class Iuliia
{
    public const ALA_LC         = 'ala_lc';
    public const ALA_LC_ALT     = 'ala_lc_alt';
    public const BGN_PCGN       = 'bgn_pcgn';
    public const BGN_PCGN_ALT   = 'bgn_pcgn_alt';
    public const BS_2979        = 'bs_2979';
    public const BS_2979_ALT    = 'bs_2979_alt';
    public const GOST_16876     = 'gost_16876';
    public const GOST_16876_ALT = 'gost_16876_alt';
    public const GOST_52290     = 'gost_52290';
    public const GOST_52535     = 'gost_52535';
    public const GOST_7034      = 'gost_7034';
    public const GOST_779       = 'gost_779';
    public const GOST_779_ALT   = 'gost_779_alt';
    public const ICAO_DOC_9303  = 'icao_doc_9303';
    public const ISO_9_1954     = 'iso_9_1954';
    public const ISO_9_1968     = 'iso_9_1968';
    public const ISO_9_1968_ALT = 'iso_9_1968_alt';
    public const MOSMETRO       = 'mosmetro';
    public const MVD_310        = 'mvd_310';
    public const MVD_310_FR     = 'mvd_310_fr';
    public const MVD_782        = 'mvd_782';
    public const SCIENTIFIC     = 'scientific';
    public const TELEGRAM       = 'telegram';
    public const UNGEGN_1987    = 'ungegn_1987';
    public const WIKIPEDIA      = 'wikipedia';
    public const YANDEX_MAPS    = 'yandex_maps';
    public const YANDEX_MONEY   = 'yandex_money';

    public const SCHEMAS = [
        self::ALA_LC,
        self::ALA_LC_ALT,
        self::BGN_PCGN,
        self::BGN_PCGN_ALT,
        self::BS_2979,
        self::BS_2979_ALT,
        self::GOST_16876,
        self::GOST_16876_ALT,
        self::GOST_52290,
        self::GOST_52535,
        self::GOST_7034,
        self::GOST_779,
        self::GOST_779_ALT,
        self::ICAO_DOC_9303,
        self::ISO_9_1954,
        self::ISO_9_1968,
        self::ISO_9_1968_ALT,
        self::MOSMETRO,
        self::MVD_310,
        self::MVD_310_FR,
        self::MVD_782,
        self::SCIENTIFIC,
        self::TELEGRAM,
        self::UNGEGN_1987,
        self::WIKIPEDIA,
        self::YANDEX_MAPS,
        self::YANDEX_MONEY,
    ];

    /**
     * @param string $str
     * @param string $schema
     *
     * @return string
     */
    public static function translate(string $str, string $schema): string
    {
        return static::engine($schema)->translate($str);
    }

    /**
     * @param string $schema
     *
     * @return Engine
     */
    public static function engine(string $schema): Engine
    {
        return new Engine(static::schema($schema));
    }

    /**
     * @param string $schema
     *
     * @return Schema
     */
    public static function schema(string $schema): Schema
    {
        $schema = \mb_strtolower($schema);

        if (\in_array($schema, static::SCHEMAS, true) === false) {
            throw new \InvalidArgumentException("Schema '$schema' not found");
        }

        $filepath = __DIR__
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . 'build'
            . DIRECTORY_SEPARATOR
            . $schema
            . '.php';

        return Schema::createFromFile($filepath);
    }
}
