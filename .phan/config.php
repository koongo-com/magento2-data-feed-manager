<?php
 
declare(strict_types=1);
 
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\RuleSet\Sets\PhpCsFixerSet;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
 
return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
 
     
    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/src',
    ]);
 
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
    ]);
 
    // Opravit cesty na use DateTime; místo new \DateTime();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
 
 
    // vícevláknový proces - zrychlí práci
    $parameters->set(Option::PARALLEL, true);
 
    // Cílová verze PHP se kterou budeme pracovat
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);
 
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::CODING_STYLE);
 
    $services = $containerConfigurator->services();
    // odstraní nepoužité improty
    $services->set(NoUnusedImportsFixer::class);
 
    // srovnání code aligment
    $services->set(PhpdocAlignFixer::class);
 
};
