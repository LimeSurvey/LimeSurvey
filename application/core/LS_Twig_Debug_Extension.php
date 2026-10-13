<?php

/**
 * Replacement for Twig's own DebugExtension.
 *
 * Twig's dump() calls var_dump(), so with Xdebug the output is prefixed with the location of
 * that var_dump() call inside the Twig vendor code, which is useless for theme developers.
 * This dump() shows the Twig file and line where dump() was called instead.
 *
 * Like Twig's dump(), it only outputs something when Twig debug mode is enabled (LimeSurvey debug > 0).
 * Usage in any twig file: {{ dump(aSurveyInfo) }}, or {{ dump() }} to dump the whole context.
 */

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Template;
use Twig\TemplateWrapper;
use Twig\TwigFunction;

class LS_Twig_Debug_Extension extends AbstractExtension
{
    /** @var int Maximum depth when dumping nested arrays and objects */
    private const DUMP_DEPTH = 10;

    /**
     * Returns the dump() function
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'dump',
                [self::class, 'dump'],
                ['is_safe' => ['html'], 'needs_context' => true, 'needs_environment' => true, 'is_variadic' => true]
            ),
        ];
    }

    /**
     * Dumps the given variables (or the whole context if none given), prefixed with the Twig file and line of the call
     * @param Environment $env the Twig environment
     * @param array $context the current template context
     * @param mixed ...$vars the variables to dump
     * @return string HTML-encoded dump, empty if Twig debug mode is disabled
     */
    public static function dump(Environment $env, $context, ...$vars)
    {
        if (!$env->isDebug()) {
            return '';
        }

        if (!$vars) {
            $vars = [];
            foreach ($context as $key => $value) {
                if (!$value instanceof Template && !$value instanceof TemplateWrapper) {
                    $vars[$key] = $value;
                }
            }
            $vars = [$vars];
        }

        $output = self::getCallerLocation() . ":\n";
        foreach ($vars as $var) {
            $output .= CVarDumper::dumpAsString($var, self::DUMP_DEPTH) . "\n";
        }

        // ENT_SUBSTITUTE: Invalid UTF-8 data in the dump must not empty the whole output
        $output = htmlspecialchars($output, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<pre class="ls-twig-dump">' . $output . '</pre>';
    }

    /**
     * Finds the Twig file and line from which dump() was called
     * @return string the location as "file:line", or "unknown" if the calling template could not be found
     */
    private static function getCallerLocation()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($backtrace as $index => $trace) {
            if ($index === 0 || !isset($trace['object']) || !$trace['object'] instanceof Template) {
                continue;
            }
            // The call site inside the compiled template is recorded in the previous frame
            $template = $trace['object'];
            $callSite = $backtrace[$index - 1];
            $sourcePath = $template->getSourceContext()->getPath();
            $location = $sourcePath !== '' ? self::getRelativePath($sourcePath) : $template->getTemplateName();
            $compiledFile = (new ReflectionObject($template))->getFileName();
            if (!isset($callSite['file'], $callSite['line']) || $callSite['file'] !== $compiledFile) {
                return $location;
            }
            // Debug info maps compiled PHP lines to Twig lines, sorted by PHP line descending
            foreach ($template->getDebugInfo() as $codeLine => $templateLine) {
                if ($codeLine <= $callSite['line']) {
                    return $location . ':' . $templateLine;
                }
            }
            return $location;
        }
        return 'unknown';
    }

    /**
     * Strips the LimeSurvey root directory from a path
     * @param string $path the absolute path
     * @return string the path relative to the LimeSurvey root directory, or the path as is if outside of it
     */
    private static function getRelativePath($path)
    {
        $rootDir = rtrim((string) App()->getConfig('rootdir'), '/\\') . DIRECTORY_SEPARATOR;
        if (strpos($path, $rootDir) === 0) {
            return substr($path, strlen($rootDir));
        }
        return $path;
    }
}
