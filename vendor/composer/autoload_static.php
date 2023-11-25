<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2063240401e5dcb37d1034dd9e89224d
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DiDom\\' => 6,
        ),
        'C' => 
        array (
            'Curl\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DiDom\\' => 
        array (
            0 => __DIR__ . '/..' . '/imangazaliev/didom/src/DiDom',
        ),
        'Curl\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-curl-class/php-curl-class/src/Curl',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2063240401e5dcb37d1034dd9e89224d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2063240401e5dcb37d1034dd9e89224d::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit2063240401e5dcb37d1034dd9e89224d::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2063240401e5dcb37d1034dd9e89224d::$classMap;

        }, null, ClassLoader::class);
    }
}
