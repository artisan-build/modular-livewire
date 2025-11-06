<?php

namespace ArtisanBuild\ModularLivewire\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use InterNACHI\Modular\Console\Commands\Modularize;
use InterNACHI\Modular\Support\ModuleConfig;
use Livewire\Features\SupportConsoleCommands\Commands\MakeCommand;
use Livewire\Finder\Finder;

class MakeLivewireCommand extends MakeCommand
{
    use Modularize;

    /**
     * Get the module configuration if --module option is provided.
     */
    protected function module(): ?ModuleConfig
    {
        if ($name = $this->option('module')) {
            $registry = $this->getLaravel()->make(\InterNACHI\Modular\Support\ModuleRegistry::class);

            if ($module = $registry->module($name)) {
                return $module;
            }

            throw new \Symfony\Component\Console\Exception\InvalidOptionException(sprintf('The "%s" module does not exist.', $name));
        }

        return null;
    }

    public function handle()
    {
        if ($module = $this->module()) {
            // Configure Livewire paths for the module
            $this->configureModulePaths($module);

            // Replace the Finder instance with one configured for the module
            $this->configureModuleFinder($module);
        }

        return parent::handle();
    }

    /**
     * Configure Livewire configuration paths for the module.
     */
    protected function configureModulePaths(ModuleConfig $module): void
    {
        // Set up the namespace for Livewire components
        Config::set('livewire.class_namespace', $module->qualify('Livewire'));

        // Set up the view path for Livewire components
        Config::set('livewire.view_path', $module->path('resources/views/livewire'));
    }

    /**
     * Configure the Finder instance for the module.
     */
    protected function configureModuleFinder(ModuleConfig $module): void
    {
        $finder = new Finder();

        // Add the module location to the finder
        $finder->addLocation(
            $module->path('resources/views/livewire'),
            $module->qualify('Livewire')
        );

        // Add the module namespace to support namespaced component names
        $finder->addNamespace(
            $module->name,
            $module->path('resources/views/livewire'),
            $module->qualify('Livewire')
        );

        // Replace the finder instance in the container
        $this->getLaravel()->instance('livewire.finder', $finder);
        $this->getLaravel()->instance(Finder::class, $finder);

        // Update the finder property on this command
        $this->finder = $finder;
    }

    protected function createClassBasedComponent(string $name): int
    {
        if ($module = $this->module()) {
            // Parse the component name into segments
            $segments = explode('.', $name);

            // Convert segments to StudlyCase for class name
            $classSegments = array_map(fn ($segment) => Str::studly($segment), $segments);
            $className = implode('\\', $classSegments);

            // Convert segments to kebab-case for view name
            $viewSegments = array_map(fn ($segment) => Str::kebab($segment), $segments);
            $viewName = implode('.', $viewSegments);

            // Build paths within the module
            $classPath = $module->path('src/Livewire/' . str_replace('\\', '/', $className) . '.php');
            $viewPath = $module->path('resources/views/livewire/' . str_replace('.', '/', $viewName) . '.blade.php');

            $this->ensureDirectoryExists(dirname($classPath));
            $this->ensureDirectoryExists(dirname($viewPath));

            $classContent = $this->buildClassBasedComponentClass($name);
            $viewContent = $this->buildClassBasedComponentView();

            $this->files->put($classPath, $classContent);
            $this->files->put($viewPath, $viewContent);

            $this->components->info(sprintf('Livewire component [%s] created successfully.', $classPath));

            return 0;
        }

        return parent::createClassBasedComponent($name);
    }

    protected function buildClassBasedComponentClass(string $name): string
    {
        if ($module = $this->module()) {
            $stub = $this->files->get($this->getStubPath('livewire.stub'));

            $segments = explode('.', $name);
            $className = Str::studly(end($segments));
            $namespaceSegments = array_slice($segments, 0, -1);

            $namespace = $module->qualify('Livewire');

            if (! empty($namespaceSegments)) {
                $namespace .= '\\' . collect($namespaceSegments)
                    ->map(fn ($segment) => Str::studly($segment))
                    ->implode('\\');
            }

            // Build the view name with module namespace
            $viewName = 'livewire.' . collect($segments)
                ->map(fn ($segment) => Str::kebab($segment))
                ->implode('.');

            $stub = str_replace('[namespace]', $namespace, $stub);
            $stub = str_replace('[class]', $className, $stub);
            $stub = str_replace('[view]', $viewName, $stub);

            return $stub;
        }

        return parent::buildClassBasedComponentClass($name);
    }

    public function call($command, array $arguments = [])
    {
        // Pass the --module flag on to subsequent commands
        if ($module = $this->option('module')) {
            $arguments['--module'] = $module;
        }

        return $this->runCommand($command, $arguments, $this->output);
    }
}
