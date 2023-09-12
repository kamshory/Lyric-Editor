<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2219d434bd9928e433eb450fe0d2c608
{
    public static $files = array (
        '6e3fae29631ef280660b3cdad06f25a8' => __DIR__ . '/..' . '/symfony/deprecation-contracts/function.php',
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Ctype\\' => 23,
            'Symfony\\Component\\Yaml\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Symfony\\Component\\Yaml\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/yaml',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Pico\\' => 
            array (
                0 => __DIR__ . '/../..' . '/classes',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2219d434bd9928e433eb450fe0d2c608::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2219d434bd9928e433eb450fe0d2c608::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit2219d434bd9928e433eb450fe0d2c608::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2219d434bd9928e433eb450fe0d2c608::$classMap;

        }, null, ClassLoader::class);
    }
}
