# Modular Livewire

Livewire 4 component generator with `internachi/modular` support.

## Overview

This package extends the Livewire 4 `make:livewire` command to add support for creating components within modules when using `internachi/modular`. It adds a `--module` option that allows you to specify which module the component should be created in.

## Installation

```bash
composer require artisan-build/modular-livewire
```

The package will auto-register via Laravel's package discovery.

## Usage

Use the standard `make:livewire` command with the `--module` option:

### Single-File Component (SFC)

```bash
php artisan make:livewire test-component --module=your-module --sfc
```

This creates: `scalpels/your-module/resources/views/livewire/⚡test-component.blade.php`

### Class-Based Component

```bash
php artisan make:livewire file-manager --module=your-module --class
```

This creates:
- `scalpels/your-module/src/Livewire/FileManager.php`
- `scalpels/your-module/resources/views/livewire/file-manager.blade.php`

### Multi-File Component (MFC)

```bash
php artisan make:livewire uploader --module=your-module --mfc --test
```

This creates:
- `scalpels/your-module/resources/views/livewire/⚡uploader/uploader.php`
- `scalpels/your-module/resources/views/livewire/⚡uploader/uploader.blade.php`
- `scalpels/your-module/resources/views/livewire/⚡uploader/uploader.test.php` (with --test flag)

## How It Works

The package:

1. Overrides Livewire 4's `MakeCommand` using Laravel's service container
2. Detects the `--module` option and configures paths accordingly
3. Creates components in the module's directory structure with correct namespaces
4. Maintains full compatibility with all Livewire 4 component types

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Livewire 4.0+
- internachi/modular 2.0+

## License

MIT
