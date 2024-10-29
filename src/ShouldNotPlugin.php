<?php

namespace Hanoii;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PrePoolCreateEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Semver\Semver;

class ShouldNotPlugin implements PluginInterface, EventSubscriberInterface {
  private $composer;
  private $io;
  private $config;

  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->config = $composer->getPackage()->getExtra()['should-not'] ?? [];
  }

  public static function getSubscribedEvents() {
    return [
      PluginEvents::PRE_POOL_CREATE => 'onPrePoolCreate',
    ];
  }

  public function onPrePoolCreate(PrePoolCreateEvent $event) {
    $packages = $event->getPackages();
    $filteredPackages = [];

    $processed = [];
    foreach ($packages as $package) {
      $packageName = $package->getName();

      // Check if this package has a constraint in the should-not configuration
      if (!empty($this->config[$packageName]) && !empty($this->config[$packageName]['version'])) {
        $versionConstraint = $this->config[$packageName]['version'];
        $packageVersion = $package->getVersion();

        if (strpos($packageVersion, 'dev-') !== 0) {
          // If the package version matches the blocked constraint, prevent it from being added
          if (Semver::satisfies($packageVersion, $versionConstraint)) {
            // Filter out versions that matches the should-not constraint
            $processed[$packageName]['deny'][] = $package->getPrettyVersion();
            $processed[$packageName]['reason'] = $this->config[$packageName]['reason'] ?? '';
          }
          else {
            // Add allowed versions back to the pool
            $filteredPackages[] = $package;
            $processed[$packageName]['allow'][] = $package->getPrettyVersion();
          }
        }
        else {
          // Always allow dev-* version
          $filteredPackages[] = $package;
          $processed[$packageName]['allow'][] = $package->getPrettyVersion();
        }
      }
      else {
        // Add allowed packages back to the pool
        $filteredPackages[] = $package;
      }
    }

    $event->setPackages($filteredPackages);

    // Compose warning message
    foreach ($processed as $name => $info) {
      if (!empty($info['deny'])) {
        $versions = implode(', ', $info['deny']);
        $reason = '';
        if (!empty($info['reason'])) {
          $reason = "\n    - {$info['reason']}";
        }
        $this->io->writeError(
          "<warning>Warning:\n  {$name} versions [{$versions}] where removed from the dependecy list as they should-not be installed.{$reason}</warning>",
          true,
          IOInterface::NORMAL
        );
      }
    }
  }

  public function deactivate(Composer $composer, IOInterface $io) {}
  public function uninstall(Composer $composer, IOInterface $io) {}
}
