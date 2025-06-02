# Laravel CRUD Generator Package

## Developer Usage Guide

The `atk/laraquickcrud` package accelerates Laravel development by automating the creation of standard CRUD (Create, Read, Update, Delete) components. With a single Artisan command, you can generate:

* Eloquent Models
* Resourceful Controllers (for web or API)
* Form Request classes for validation
* Database Migrations
* Blade Views (for web UI)
* Resourceful Routes

This package saves you significant time by generating boilerplate code, allowing you to focus on your application's unique logic.

---

## Table of Contents

1. [Installation](#installation)
2. [Usage](#usage)

   * [Basic Generation](#basic-generation)
   * [Generating with Fields](#generating-with-fields)
   * [API-Only Generation](#api-only-generation)
3. [Step-by-Step Usage Guide](#step-by-step-usage-guide)
4. [Configuration](#configuration)

   * [Default Paths](#default-paths)
   * [Namespaces](#namespaces)
   * [Primary Key Type](#primary-key-type)
   * [Custom Replacements](#custom-replacements)
5. [Customizing Generated Code (Stubs)](#customizing-generated-code-stubs)

   * [Publishing Stubs](#publishing-stubs)
   * [Modifying Stub Files](#modifying-stub-files)
   * [Available Placeholders](#available-placeholders)
6. [Troubleshooting and Tips](#troubleshooting-and-tips)
7. [License](#license)

---

## Installation

### Step 1: Install the Package

Run the following Composer command in your Laravel project's root directory:

```bash
composer require atk/laraquickcrud
```

*Note: If you are developing this package locally or using it from a private repository, you might need to add a `repositories` entry to your `composer.json` before running the `composer require` command.*

### Step 2: Register the Service Provider (Laravel 11+)

For Laravel 11 and above, package service providers are typically auto-discovered. However, if you encounter issues, you can explicitly register the service provider in your `config/app.php` file:

```php
'providers' => [
    // ...
    Atk\LaraQuickCrud\CrudGeneratorServiceProvider::class,
],
```

### Step 3: Publish Configuration and Stubs (Optional)

To customize the default settings and stub files, publish them to your application's `config` and `resources/stubs` directories:

```bash
php artisan vendor:publish --tag=crud-generator-config
php artisan vendor:publish --tag=crud-generator-stubs
```

This will create:

* `config/crud-generator.php`
* `resources/stubs/crud-generator/` (containing all stub files)

---

## Usage

The package provides a single Artisan command: `make:crud`.

### Basic Generation

To generate a full CRUD for a model named `Post`:

```bash
php artisan make:crud Post
```

This command will generate:

* `app/Models/Post.php`
* `app/Http/Controllers/PostController.php`
* `app/Http/Requests/PostStoreRequest.php`
* `app/Http/Requests/PostUpdateRequest.php`
* A migration file for the `posts` table (with `id` and timestamps).
* Blade views:

  * `resources/views/posts/index.blade.php`
  * `resources/views/posts/create.blade.php`
  * `resources/views/posts/edit.blade.php`
  * `resources/views/posts/show.blade.php`
* A resourceful route `Route::resource('posts', PostController::class);` appended to `routes/web.php`.

### Generating with Fields

You can specify fields for your model using the `--fields` option. Fields should be comma-separated, with each field defined as `name:type`.

**Example:**

```bash
php artisan make:crud Product --fields=name:string,description:text,price:decimal,stock:integer,is_active:boolean
```

This will:

* Add fields to the `$fillable` array in the `Product` model.
* Generate corresponding columns in the migration.
* Add basic validation rules to Form Requests.
* Generate form fields and table columns in the Blade views.

### API-Only Generation

To generate only API resources (model, API controller, requests, migration, and API routes) without any Blade views, use the `--api` option:

```bash
php artisan make:crud User --api --fields=name:string,email:string,password:string
```

This will:

* Generate `UserApiController.php`.
* Add API route to `routes/api.php`.
* Skip Blade view generation.

---

## Step-by-Step Usage Guide

1. **Generate the CRUD:**

   ```bash
   php artisan make:crud MyModel --fields=field1:string,field2:text
   ```
2. **Run Database Migrations:**

   ```bash
   php artisan migrate
   ```
3. **Review and Customize Files:**

   * Modify controllers, models, and views as needed.
   * Ensure your `layouts/app.blade.php` file exists and includes Bootstrap 5 or other styles.
4. **Test the CRUD:**

   ```bash
   php artisan serve
   ```

   Navigate to `http://127.0.0.1:8000/mymodels` in your browser.

---

## Configuration

Published config file: `config/crud-generator.php`

You can customize:

* Default namespaces
* Path settings for models, controllers, views, etc.
* Default validation rules
* Field type-to-validation mappings

---

## Customizing Generated Code (Stubs)

### Publishing Stubs

```bash
php artisan vendor:publish --tag=crud-generator-stubs
```

This publishes stubs to: `resources/stubs/crud-generator/`

### Modifying Stub Files

You can customize any part of the scaffolding. For example:

* `controller.stub`
* `model.stub`
* `form-request.stub`
* `index.blade.stub`

### Available Placeholders

You can use the following in stubs:

* `{{ modelName }}`
* `{{ modelVariable }}`
* `{{ modelPlural }}`
* `{{ modelTitle }}`
* `{{ fields }}`
* `{{ validationRules }}`

---

## Troubleshooting and Tips

* Make sure to run `php artisan config:clear` after changing the config file.
* If views don't render, verify `layouts/app.blade.php` exists and uses `@yield('content')`.
* Always review generated code for security, especially validation logic.
* Use the `--api` flag for SPA or mobile backend APIs.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
