<?php

namespace PixlMint\CMS\Command;

use Exception;
use GetOptionKit\ContinuousOptionParser;
use GetOptionKit\OptionCollection;
use GetOptionKit\OptionPrinter\ConsoleOptionPrinter;
use Nacho\Nacho;
use ReflectionMethod;

class CLI
{
    private bool $specBuilt = false;
    private OptionCollection $appOptions;

    /** @var array{string: array{spec: OptionCollection, handler: string, descr: ?string}} $subcommands */
    private array $subcommands = [];

    public function buildSpec(array $commandConfig = [])
    {
        if ($this->specBuilt)
            return;
        $this->appOptions = new OptionCollection;

        if (isset($commandConfig['root'])) {
            foreach ($commandConfig['root'] as $rootConfig) {
                $this->addOption($this->appOptions, $rootConfig);
            }
        }

        if (isset($commandConfig['subcommands'])) {
            foreach ($commandConfig['subcommands'] as $baseCommand => $subcommand) {
                foreach ($subcommand as $subcommandName => $subcommandDefinition) {
                    $key = sprintf("%s:%s", $baseCommand, $subcommandName);

                    if (key_exists($key, $this->subcommands)) {
                        throw new Exception('Duplicate subcommand ' . $key);
                    } else {
                        $this->registerSubcommand($key, $subcommandDefinition);
                    }
                }
            }
        }

        $this->specBuilt = true;
    }

    public function run(array $args)
    {
        if (!$this->specBuilt)
            throw new Exception("Command spec not yet built");
        $parser = new ContinuousOptionParser($this->appOptions);

        $appOptions = $parser->parse($args);
        $arguments = [];
        $subcommandOptions = NULL;
        $selectedSubcommand = NULL;

        while (!$parser->isEnd()) {
            $subcommand = $parser->getCurrentArgument();
            if (key_exists($subcommand, $this->subcommands)) {
                if ($subcommandOptions !== NULL) {
                    throw new Exception("Subcommand already defined. There can only be one");
                }
                $parser->advance();
                $parser->setSpecs($this->subcommands[$subcommand]['spec']);
                $subcommandOptions = $parser->continueParse();
                $selectedSubcommand = $subcommand;
            } else {
                $arguments[] = $parser->advance();
            }
        }

        if ($appOptions->get('help') || $selectedSubcommand === NULL) {
            $this->printHelp();
        } else {
            $definition = $this->subcommands[$selectedSubcommand];

            $cls = Nacho::$container->get($definition['handler']);

            if (!method_exists($cls, 'call')) {
                throw new Exception("No method 'call' in " . $definition['handler']);
            } else {
                $refl = new ReflectionMethod($cls, 'call');
                
                $params = [];

                foreach ($refl->getParameters() as $param) {
                    $optionName = $param->getName();
                    $isRequired = $this->subcommands[$selectedSubcommand]['spec']->get($optionName)->required;
                    $optionValue = $subcommandOptions->get($optionName);
                    if (is_null($optionValue) && $isRequired) {
                        throw new Exception("$optionName is required");
                    }
                    $params[] = $optionValue;
                }

                $cls->call(...$params);
            }
        }
    }

    private function registerSubcommand(string $key, array $definition)
    {
        if (!isset($definition['handler'])) {
            throw new Exception('No handler defined for subcommand ' . $key);
        }

        $spec = new OptionCollection;
        if (isset($definition['args'])) {
            foreach ($definition['args'] as $arg) {
                $this->addOption($spec, $arg);
            }
        }

        $subcommandDescription = isset($definition['descr']) ? $definition['descr'] : '';

        $this->subcommands[$key] = [
            'spec' => $spec,
            'descr' => $subcommandDescription,
            'handler' => $definition['handler'],
        ];
    }

    private function addOption(OptionCollection $spec, array $definition)
    {
        $argumentDecription = isset($definition['descr']) ? $definition['descr'] : null;
        $argumentSpec = $spec->add($definition['spec'], $argumentDecription);
        if (isset($definition['type'])) {
            $argumentSpec->isa($definition['type']);
        }
        if (isset($definition['default'])) {
            $argumentSpec->defaultValue($definition['default']);
        }
    }

    private function printHelp()
    {
        $printer = new ConsoleOptionPrinter();
        print($printer->render($this->appOptions));

        foreach ($this->subcommands as $key => $definition) {
            printf("  %s\t%s\n", $key, $definition['descr']);
            printf("%s", $printer->render($definition['spec']));
        }
    }
}
