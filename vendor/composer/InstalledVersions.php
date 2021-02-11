<?php











namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => '5.1.x-dev',
    'version' => '5.1.9999999.9999999-dev',
    'aliases' => 
    array (
    ),
    'reference' => '1c303e1a07ddc6273232520c8063701043fa115e',
    'name' => 'thinkcmf/thinkcmf',
  ),
  'versions' => 
  array (
    'electrolinux/phpquery' => 
    array (
      'pretty_version' => '0.9.6',
      'version' => '0.9.6.0',
      'aliases' => 
      array (
      ),
      'reference' => '6cb8afcfe8cd4ce45f2f8c27d561383037c27a3a',
    ),
    'ezyang/htmlpurifier' => 
    array (
      'pretty_version' => 'v4.13.0',
      'version' => '4.13.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '08e27c97e4c6ed02f37c5b2b20488046c8d90d75',
    ),
    'mindplay/annotations' => 
    array (
      'pretty_version' => '1.3.2',
      'version' => '1.3.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '7e1547259a6aa7e3abc3832207499943614e9d13',
    ),
    'phpmailer/phpmailer' => 
    array (
      'pretty_version' => 'v6.2.0',
      'version' => '6.2.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'e38888a75c070304ca5514197d4847a59a5c853f',
    ),
    'thinkcmf/cmf' => 
    array (
      'pretty_version' => 'v5.1.12',
      'version' => '5.1.12.0',
      'aliases' => 
      array (
      ),
      'reference' => '93fbca5a895bb5d59e18afc4b12831b9c55c0978',
    ),
    'thinkcmf/cmf-api' => 
    array (
      'pretty_version' => 'v5.1.7',
      'version' => '5.1.7.0',
      'aliases' => 
      array (
      ),
      'reference' => '40ee22857e2092d66e655c8890e330440b475875',
    ),
    'thinkcmf/cmf-app' => 
    array (
      'pretty_version' => 'v5.1.7',
      'version' => '5.1.7.0',
      'aliases' => 
      array (
      ),
      'reference' => 'bed179952b7554761348286e95cdabe1ad8c8ad9',
    ),
    'thinkcmf/cmf-extend' => 
    array (
      'pretty_version' => 'v5.1.1',
      'version' => '5.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '500ac89f30b9352dbac4f3f13c88212d8b2a9618',
    ),
    'thinkcmf/cmf-install' => 
    array (
      'pretty_version' => 'v5.1.5',
      'version' => '5.1.5.0',
      'aliases' => 
      array (
      ),
      'reference' => '4d0064bbaa7ab87e0b0e1edecffcac30f2b18c93',
    ),
    'thinkcmf/thinkcmf' => 
    array (
      'pretty_version' => '5.1.x-dev',
      'version' => '5.1.9999999.9999999-dev',
      'aliases' => 
      array (
      ),
      'reference' => '1c303e1a07ddc6273232520c8063701043fa115e',
    ),
    'topthink/framework' => 
    array (
      'pretty_version' => 'v5.1.41',
      'version' => '5.1.41.0',
      'aliases' => 
      array (
      ),
      'reference' => '7137741a323a4a60cfca334507cd1812fac91bb2',
    ),
    'topthink/think-captcha' => 
    array (
      'pretty_version' => 'v2.0.2',
      'version' => '2.0.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '54c8a51552f99ff9ea89ea9c272383a8f738ceee',
    ),
    'topthink/think-helper' => 
    array (
      'pretty_version' => 'v1.0.7',
      'version' => '1.0.7.0',
      'aliases' => 
      array (
      ),
      'reference' => '5f92178606c8ce131d36b37a57c58eb71e55f019',
    ),
    'topthink/think-image' => 
    array (
      'pretty_version' => 'v1.0.7',
      'version' => '1.0.7.0',
      'aliases' => 
      array (
      ),
      'reference' => '8586cf47f117481c6d415b20f7dedf62e79d5512',
    ),
    'topthink/think-installer' => 
    array (
      'pretty_version' => 'v2.0.5',
      'version' => '2.0.5.0',
      'aliases' => 
      array (
      ),
      'reference' => '38ba647706e35d6704b5d370c06f8a160b635f88',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
