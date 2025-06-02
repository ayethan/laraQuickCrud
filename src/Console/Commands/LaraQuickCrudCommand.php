<?php

namespace Atk\LaraQuickCrud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class LaraQuickCrudCommand extends Command
{
    protected $signature = 'make:crud {name : The name of the model (e.g., Post)} {--fields= : Comma-separated fields for the model (e.g., title:string,body:text)} {--api : Generate API resources instead of web resources}';

    protected $description = 'Generate a full CRUD for a given model';

    protected $files;

    protected $stubsPath;

    protected $modelName;

    protected $modelNamePlural;

    protected $modelNameSingularLowercase;

    protected $modelNamePluralLowercase;

    protected $tableName;

    protected $fields = [];

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->stubsPath = resource_path('stubs/laraquickcrud'); // User can customize stubs here
        if (! $this->files->exists($this->stubsPath)) {
            $this->stubsPath = __DIR__ . '/../../../stubs'; // Fallback to package stubs
        }
    }

    public function handle()
    {
        $this->modelName = Str::studly($this->argument('name'));
        $this->modelNamePlural = Str::plural($this->modelName);
        $this->modelNameSingularLowercase = Str::snake($this->modelName);
        $this->modelNamePluralLowercase = Str::snake($this->modelNamePlural);
        $this->tableName = Str::plural($this->modelNameSingularLowercase);

        $fieldsArgument = $this->option('fields');
        if ($fieldsArgument) {
            $this->parseFields($fieldsArgument);
        }

        if ($this->option('api')) {
            $this->generateApiCrud();
        } else {
            $this->generateWebCrud();
        }

        $this->info("CRUD for {$this->modelName} generated successfully!");

        return 0;
    }

    /**
     * Parse the fields argument.
     *
     * @param string $fieldsArgument
     * @return void
     */
    protected function parseFields(string $fieldsArgument)
    {
        foreach (explode(',', $fieldsArgument) as $field) {
            list($name, $type) = explode(':', $field);
            $this->fields[] = ['name' => $name, 'type' => $type];
        }
    }

    /**
     * Generate web CRUD.
     *
     * @return void
     */
    protected function generateWebCrud()
    {
        $this->info("Generating web CRUD for {$this->modelName}...");
        $this->createModel();
        $this->createMigration();
        $this->createController();
        $this->createRequests();
        $this->createViews();
        $this->appendRoutes();
    }

    /**
     * Generate API CRUD.
     *
     * @return void
     */
    protected function generateApiCrud()
    {
        $this->info("Generating API CRUD for {$this->modelName}...");
        $this->createModel();
        $this->createMigration();
        $this->createApiController();
        $this->createApiRequests();
        $this->appendApiRoutes();
    }

    /**
     * Get stub file content.
     *
     * @param string $stubName
     * @return string
     */
    protected function getStub(string $stubName): string
    {
        $stubPath = $this->stubsPath . '/' . $stubName . '.stub';
        if (! $this->files->exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            exit(1);
        }
        return $this->files->get($stubPath);
    }

    /**
     * Replace placeholders in stub content.
     *
     * @param string $stub
     * @return string
     */
    protected function replacePlaceholders(string $stub): string
    {
        $replacements = [
            '{{modelName}}' => $this->modelName,
            '{{modelNamePlural}}' => $this->modelNamePlural,
            '{{modelNameSingularLowercase}}' => $this->modelNameSingularLowercase,
            '{{modelNamePluralLowercase}}' => $this->modelNamePluralLowercase,
            '{{tableName}}' => $this->tableName,
            '{{namespace}}' => config('laraquickcrud.namespaces.controllers'),
            '{{modelNamespace}}' => config('laraquickcrud.namespaces.models'),
            '{{requestNamespace}}' => config('laraquickcrud.namespaces.requests'),
            '{{fields}}' => $this->generateMigrationFields(),
            '{{fillable}}' => $this->generateModelFillable(),
            '{{rules}}' => $this->generateRequestValidationRules(),
            '{{formFields}}' => $this->generateViewFormFields(),
            '{{tableHeaders}}' => $this->generateViewTableHeaders(),
            '{{tableRows}}' => $this->generateViewTableRows(),
        ];

        // Add custom replacements from config
        foreach (config('laraquickcrud.replacements', []) as $key => $value) {
            if ($value !== null) { // Only replace if a value is provided in config
                $replacements[$key] = $value;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Create the model file.
     *
     * @return void
     */
    protected function createModel()
    {
        $modelPath = config('laraquickcrud.paths.models') . '/' . $this->modelName . '.php';
        $stub = $this->getStub('model');
        $content = $this->replacePlaceholders($stub);
        $this->createFile($modelPath, $content);
        $this->info("Model {$this->modelName} created at {$modelPath}");
    }

    /**
     * Create the migration file.
     *
     * @return void
     */
    protected function createMigration()
    {
        $timestamp = date('Y_m_d_His');
        $migrationName = 'create_' . $this->tableName . '_table';
        $migrationFileName = $timestamp . '_' . $migrationName . '.php';
        $migrationPath = config('laraquickcrud.paths.migrations') . '/' . $migrationFileName;

        $stub = $this->getStub('migration');
        $content = $this->replacePlaceholders($stub);
        $content = str_replace('{{migrationName}}', Str::studly($migrationName), $content);

        $this->createFile($migrationPath, $content);
        $this->info("Migration {$migrationFileName} created at {$migrationPath}");
    }

    /**
     * Create the controller file.
     *
     * @return void
     */
    protected function createController()
    {
        $controllerPath = config('laraquickcrud.paths.controllers') . '/' . $this->modelName . 'Controller.php';
        $stub = $this->getStub('controller');
        $content = $this->replacePlaceholders($stub);
        $this->createFile($controllerPath, $content);
        $this->info("Controller {$this->modelName}Controller created at {$controllerPath}");
    }

    /**
     * Create the API controller file.
     *
     * @return void
     */
    protected function createApiController()
    {
        $controllerPath = config('laraquickcrud.paths.controllers') . '/' . $this->modelName . 'ApiController.php';
        $stub = $this->getStub('api_controller'); // You'll need to create this stub
        $content = $this->replacePlaceholders($stub);
        $this->createFile($controllerPath, $content);
        $this->info("API Controller {$this->modelName}ApiController created at {$controllerPath}");
    }

    /**
     * Create the request files.
     *
     * @return void
     */
    protected function createRequests()
    {
        $requestStorePath = config('laraquickcrud.paths.requests') . '/' . $this->modelName . 'StoreRequest.php';
        $requestUpdatePath = config('laraquickcrud.paths.requests') . '/' . $this->modelName . 'UpdateRequest.php';

        $stubStore = $this->getStub('request_store');
        $contentStore = $this->replacePlaceholders($stubStore);
        $this->createFile($requestStorePath, $contentStore);
        $this->info("Request {$this->modelName}StoreRequest created at {$requestStorePath}");

        $stubUpdate = $this->getStub('request_update');
        $contentUpdate = $this->replacePlaceholders($stubUpdate);
        $this->createFile($requestUpdatePath, $contentUpdate);
        $this->info("Request {$this->modelName}UpdateRequest created at {$requestUpdatePath}");
    }

    /**
     * Create the API request files.
     *
     * @return void
     */
    protected function createApiRequests()
    {
        // For API, you might combine or simplify requests, or keep them separate.
        // For simplicity, let's just reuse the existing request stubs but you might want dedicated ones.
        $this->createRequests();
    }

    /**
     * Create the view files.
     *
     * @return void
     */
    protected function createViews()
    {
        $viewPath = config('laraquickcrud.paths.views') . '/' . $this->modelNamePluralLowercase;
        $this->files->makeDirectory($viewPath, 0755, true, true);

        $viewFiles = [
            'index' => 'view_index',
            'create' => 'view_create',
            'edit' => 'view_edit',
            'show' => 'view_show',
        ];

        foreach ($viewFiles as $fileName => $stubName) {
            $path = $viewPath . '/' . $fileName . '.blade.php';
            $stub = $this->getStub($stubName);
            $content = $this->replacePlaceholders($stub);
            $this->createFile($path, $content);
            $this->info("View {$fileName}.blade.php created at {$path}");
        }
    }

    /**
     * Append routes to web.php.
     *
     * @return void
     */
    protected function appendRoutes()
    {
        $routePath = config('laraquickcrud.paths.routes');
        $stub = $this->getStub('route');
        $content = $this->replacePlaceholders($stub);
        $this->files->append($routePath, "\n" . $content);
        $this->info("Routes for {$this->modelNamePlural} appended to {$routePath}");
    }

    /**
     * Append API routes to api.php (or web.php if api.php is not desired).
     *
     * @return void
     */
    protected function appendApiRoutes()
    {
        // For API, you might want to append to routes/api.php
        $routePath = base_path('routes/api.php');
        if (! $this->files->exists($routePath)) {
            $routePath = config('laraquickcrud.paths.routes'); // Fallback
        }
        $stub = $this->getStub('api_route'); // You'll need to create this stub
        $content = $this->replacePlaceholders($stub);
        $this->files->append($routePath, "\n" . $content);
        $this->info("API Routes for {$this->modelNamePlural} appended to {$routePath}");
    }

    /**
     * Generate migration fields.
     *
     * @return string
     */
    protected function generateMigrationFields(): string
    {
        if (empty($this->fields)) {
            return "// Add your table columns here (e.g., \$table->string('name');)";
        }

        $fields = '';
        foreach ($this->fields as $field) {
            $name = $field['name'];
            $type = $field['type'];

            // Basic type mapping for migration
            switch ($type) {
                case 'string':
                case 'varchar':
                    $fields .= "\$table->string('{$name}');\n";
                    break;
                case 'text':
                    $fields .= "\$table->text('{$name}');\n";
                    break;
                case 'integer':
                case 'int':
                    $fields .= "\$table->integer('{$name}');\n";
                    break;
                case 'bigint':
                    $fields .= "\$table->bigInteger('{$name}');\n";
                    break;
                case 'boolean':
                case 'bool':
                    $fields .= "\$table->boolean('{$name}');\n";
                    break;
                case 'date':
                    $fields .= "\$table->date('{$name}');\n";
                    break;
                case 'datetime':
                    $fields .= "\$table->dateTime('{$name}');\n";
                    break;
                case 'json':
                    $fields .= "\$table->json('{$name}');\n";
                    break;
                case 'float':
                    $fields .= "\$table->float('{$name}');\n";
                    break;
                case 'double':
                    $fields .= "\$table->double('{$name}');\n";
                    break;
                case 'decimal':
                    $fields .= "\$table->decimal('{$name}');\n";
                    break;
                default:
                    $fields .= "\$table->{$type}('{$name}');\n"; // Assume type is a valid schema builder method
                    break;
            }
            $fields .= str_repeat(' ', 12); // Indent for readability in the migration file
        }

        return rtrim($fields); // Remove trailing new line and spaces
    }

    /**
     * Generate model fillable property.
     *
     * @return string
     */
    protected function generateModelFillable(): string
    {
        if (empty($this->fields)) {
            return '';
        }
        $fillable = collect($this->fields)->map(fn($field) => "'{$field['name']}'")->implode(', ');
        return $fillable;
    }

    /**
     * Generate request validation rules.
     *
     * @return string
     */
    protected function generateRequestValidationRules(): string
    {
        if (empty($this->fields)) {
            return "// Add your validation rules here";
        }

        $rules = [];
        foreach ($this->fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $rule = "'required'"; // Default to required

            // Add basic type-based rules
            switch ($type) {
                case 'string':
                case 'varchar':
                    $rule .= ", 'string', 'max:255'";
                    break;
                case 'text':
                    $rule .= ", 'string'";
                    break;
                case 'integer':
                case 'int':
                    $rule .= ", 'integer'";
                    break;
                case 'boolean':
                case 'bool':
                    $rule .= ", 'boolean'";
                    break;
                case 'date':
                    $rule .= ", 'date'";
                    break;
                case 'datetime':
                    $rule .= ", 'date'"; // Use 'date' for both date and datetime
                    break;
                case 'email':
                    $rule .= ", 'email'";
                    break;
                case 'float':
                case 'double':
                case 'decimal':
                    $rule .= ", 'numeric'";
                    break;
            }
            $rules[] = "'{$name}' => [{$rule}]";
        }

        return implode(",\n            ", $rules);
    }

    /**
     * Generate view form fields.
     *
     * @return string
     */
    protected function generateViewFormFields(): string
    {
        if (empty($this->fields)) {
            return "";
        }

        $formFields = [];
        foreach ($this->fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $label = Str::title(str_replace('_', ' ', $name));

            $input = '';
            switch ($type) {
                case 'text':
                    $input = "<textarea class=\"form-control\" id=\"{$name}\" name=\"{$name}\" rows=\"3\">{{ old('{$name}', \${$this->modelNameSingularLowercase}->{$name} ?? '') }}</textarea>";
                    break;
                case 'boolean':
                    $input = "<input type=\"checkbox\" class=\"form-check-input\" id=\"{$name}\" name=\"{$name}\" value=\"1\" {{ (old('{$name}', \${$this->modelNameSingularLowercase}->{$name} ?? 0) == 1) ? 'checked' : '' }}>";
                    break;
                case 'date':
                    $input = "<input type=\"date\" class=\"form-control\" id=\"{$name}\" name=\"{$name}\" value=\"{{ old('{$name}', \${$this->modelNameSingularLowercase}->{$name} ?? '') }}\">";
                    break;
                case 'datetime':
                    $input = "<input type=\"datetime-local\" class=\"form-control\" id=\"{$name}\" name=\"{$name}\" value=\"{{ old('{$name}', \${$this->modelNameSingularLowercase}->{$name} ?? '') }}\">";
                    break;
                case 'password':
                    $input = "<input type=\"password\" class=\"form-control\" id=\"{$name}\" name=\"{$name}\">";
                    break;
                default:
                    $input = "<input type=\"{$type}\" class=\"form-control\" id=\"{$name}\" name=\"{$name}\" value=\"{{ old('{$name}', \${$this->modelNameSingularLowercase}->{$name} ?? '') }}\">";
                    break;
            }

            $formFields[] = <<<EOD
            <div class="mb-3">
                <label for="{$name}" class="form-label">{$label}</label>
                {$input}
                @error('{$name}')
                    <div class="text-danger">{{ \$message }}</div>
                @enderror
            </div>
            EOD;
        }

        return implode("\n\n", $formFields);
    }

    /**
     * Generate view table headers.
     *
     * @return string
     */
    protected function generateViewTableHeaders(): string
    {
        if (empty($this->fields)) {
            return "<th>ID</th>\n                <th>Created At</th>\n                <th>Updated At</th>";
        }

        $headers = collect($this->fields)->map(fn($field) => "<th>" . Str::title(str_replace('_', ' ', $field['name'])) . "</th>")->implode("\n                ");
        return "<th>ID</th>\n                " . $headers . "\n                <th>Created At</th>\n                <th>Updated At</th>";
    }

    /**
     * Generate view table rows.
     *
     * @return string
     */
    protected function generateViewTableRows(): string
    {
        if (empty($this->fields)) {
            return "<td>{{ \${$this->modelNameSingularLowercase}->id }}</td>\n                <td>{{ \${$this->modelNameSingularLowercase}->created_at }}</td>\n                <td>{{ \${$this->modelNameSingularLowercase}->updated_at }}</td>";
        }

        $rows = collect($this->fields)->map(fn($field) => "<td>{{ \${$this->modelNameSingularLowercase}->{$field['name']} }}</td>")->implode("\n                ");
        return "<td>{{ \${$this->modelNameSingularLowercase}->id }}</td>\n                " . $rows . "\n                <td>{{ \${$this->modelNameSingularLowercase}->created_at }}</td>\n                <td>{{ \${$this->modelNameSingularLowercase}->updated_at }}</td>";
    }

    /**
     * Create a file with content, creating directories if they don't exist.
     *
     * @param string $path
     * @param string $content
     * @return void
     */
    protected function createFile(string $path, string $content)
    {
        $directory = dirname($path);
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true, true);
        }
        $this->files->put($path, $content);
    }
}