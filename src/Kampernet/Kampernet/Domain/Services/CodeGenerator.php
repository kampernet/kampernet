<?php

namespace Kampernet\Kampernet\Domain\Services;

use Packaged\Helpers\Strings;
use Jenssegers\Blade\Blade;

class CodeGenerator {

    /**
     * @var array
     */
    private $app = [];

    /**
     * @var string
     */
    private $root = '';

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $map = [
        'integer' => 'int',
        'float' => 'float',
        'string' => 'string',
        'boolean' => 'bool',
        'timestamp' => '\DateTime'
    ];

    /**
     * @var Blade
     */
    private $blade;

    /**
     * CodeGenerator constructor.
     * @param $app
     * @param $root
     */
    public function __construct($app, $root) {

        $this->app = $app;
        $this->root = $root;

        $root = $this->root . "/vendor/kampernet/kampernet";
        $this->blade = new Blade("$root/templates/src", "$root/templates/cache");

        $this->namespace = $this->parseNamespace();
        $this->makeDirectories();
        $this->makeBases();
    }

    /**
     * build and write the domain model class files from the application config
     *
     * @throws \Exception
     */
    public function writeBoilerPlate() {

        foreach ($this->app['application']['model'] as $name => $model) {
            if (!Strings::startsWith($name, '__')) {
                $parsed = $this->parseModel($name, $model);
                $this->writeModelFile($parsed);
                $this->writeRepositoryInterfaceFile($parsed['className'], $parsed['camelCaseClassName']);
                $this->writeRepositoryFile($parsed);
//                $this->writeValidationFile($parsed);
//                $this->writeControllerFile($parsed);
                print_r($parsed);
            }
        }
    }

    public function getNamespace() {

        return $this->namespace;
    }

    private function writeModelFile($parsed) {

        $className = $parsed['className'];
        $folder = str_replace("\\", "/", $this->namespace);
        file_put_contents($this->root . "/src/$folder/Domain/Model/$className.php", "<?php\n" . $this->blade->render("domain.models.model", $parsed));
    }

    private function writeRepositoryInterfaceFile($className, $camelCaseClassName) {

        $folder = str_replace("\\", "/", $this->namespace);
        file_put_contents(
            $this->root . "/src/$folder/Domain/Infrastructure/Repositories/$className"."RepositoryInterface.php",
            "<?php\n" . $this->blade->render("domain.infrastructure.repositories.extension", [
                'namespace' => $this->namespace,
                'className' => $className,
                'camelCaseClassName' => $camelCaseClassName
            ])
        );
    }

    private function writeRepositoryFile($parsed) {

        $className = $parsed['className'];
        $folder = str_replace("\\", "/", $this->namespace);
        file_put_contents(
            $this->root . "/src/$folder/Infrastructure/Repositories/MySql/$className"."Repository.php",
            "<?php\n" . $this->blade->render("infrastructure.repositories.mysql.extension", $parsed)
        );
    }

    private function writeRepositoryFileOld($parsed) {

        $className = $parsed['className'];
        $camelCaseClassName = $parsed['camelCaseClassName'];
        $tableName = $parsed['tableName'];
        $namespace = $parsed['namespace'];
        $properties = $parsed['properties'];
        $objects = $this->hasObjects($properties);

        $root = $this->root . "/vendor/kampernet/kampernet";
        $folder = str_replace("\\", "/", $this->namespace);

        $templatesDir = "$root/templates/src/infrastructure/repositories/mysql";

        $content = str_replace(
            '%namespace%',
            $this->namespace,
            str_replace(
                '%class_name%',
                $className,
                str_replace(
                    '%camel_case_class_name%',
                    $camelCaseClassName,
                    file_get_contents("$templatesDir/02.a.open_file.phpt")
                )
            )
        );

        if ($objects) {
            foreach($properties as $property) {
                if ($property['isObject']) {
                    $content .= str_replace(
                        '%namespace%',
                        $namespace,
                        str_replace(
                            '%class_name%',
                            $property['propertyClassname'],
                            file_get_contents("$templatesDir/02.b.use_repos.phpt")
                        )
                    );
                }
            }
        }

        $content .= str_replace(
            '%class_name%',
            $className,
            str_replace(
                '%table_name%',
                $tableName,
                file_get_contents("$templatesDir/02.c.extend_base.phpt")
            )
        );

        if ($objects) {
            $injections = [];
            $params = [];
            foreach ($properties as $property) {
                if ($property['isObject']) {
                    $content .= str_replace(
                        '%camel_case_class_name%',
                        $property['objectPropertyCamelcase'],
                        str_replace(
                            '%class_name%',
                            $property['propertyClassname'],
                            file_get_contents("$templatesDir/02.d.declare_repos.phpt")
                        )
                    );
                    $injections []= str_replace(
                        '%class_name%',
                        $property['propertyClassname'],
                        str_replace(
                            '%camel_case_class_name%',
                            $property['objectPropertyCamelcase'],
                            file_get_contents("$templatesDir/02.e.prepare_injection.phpt")
                        )
                    );

                    $params []= str_replace(
                        '%class_name%',
                        $property['propertyClassname'],
                        str_replace(
                            '%camel_case_class_name%',
                            $property['objectPropertyCamelcase'],
                            file_get_contents("$templatesDir/03.a.b.params.phpt")
                        )
                    );
                }
            }

            $params = implode("\n", $params);
            $content .= str_replace(
                '%class_name%',
                $className,
                str_replace(
                    '%injection_params%',
                    $params,
                    file_get_contents("$templatesDir/03.a.a.constructor_docblock.phpt")
                )
            );

            $injections = implode(", ", $injections);
            $content .= str_replace(
                '%repo_injection%',
                $injections,
                str_replace(
                    '%class_name%',
                    $className,
                    file_get_contents("$templatesDir/03.a.open_constructor.phpt")
                )
            );

            foreach($properties as $property) {
                if ($property['isObject']) {
                    $content .= str_replace(
                        '%camel_case_class_name%',
                        $property['objectPropertyCamelcase'],
                        file_get_contents("$templatesDir/03.b.init_repo_fields.phpt")
                    );
                }
            }

            $content .= file_get_contents("$templatesDir/03.c.close_constructor.phpt");
        }

        $content .= str_replace(
            '%class_name%',
            $className,
            str_replace(
                '%camel_case_class_name%',
                $camelCaseClassName,
                file_get_contents("$templatesDir/04.save_open_hydrate.phpt")
            )
        );

        $content .= str_replace(
            '%class_name%',
            $className,
            str_replace(
                '%camel_case_class_name%',
                $camelCaseClassName,
                file_get_contents("$templatesDir/06.open_update.phpt")
            )
        );

        $content .= str_replace(
            '%class_name%',
            $className,
            str_replace(
                '%camel_case_class_name%',
                $camelCaseClassName,
                file_get_contents("$templatesDir/08.open_insert.phpt")
            )
        );

        $content .= str_replace(
            '%camel_case_class_name%',
            $camelCaseClassName,
            file_get_contents("$templatesDir/09.close_insert.phpt")
        );

        file_put_contents($this->root . "/src/$folder/Infrastructure/Repositories/MySql/$className"."Repository.php", $content);
    }

    private function writeValidationFile($parsed) {

        $templatesDir = $this->root . '/templates/src/application/validation';
        $content = str_replace(
            '%namespace%',
            $this->namespace,
            str_replace(
                '%class_name%',
                $parsed['className'],
                file_get_contents($templatesDir . '/validation.phpt')
            )
        );
        file_put_contents($this->root . '/app/src/Application/Validation/' . $parsed['className'] . 'Validation.php', $content);
    }

    private function writeCommandFiles($parsed) {

        $commands = [
            'Create',
            'Destroy',
            'Index',
            'Show',
        ];

        $templatesDir = $this->root . '/templates/src/application/actions/commands';
        if (!is_dir($this->root . '/app/src/Application/Actions/Commands/' . $parsed['className'])) {
            mkdir($this->root . '/app/src/Application/Actions/Commands/' . $parsed['className']);
        }

        foreach ($commands as $command) {
            $template = $templatesDir . '/' . strtolower($command) . '.phpt';
            $content = str_replace(
                '%namespace%',
                $this->namespace,
                str_replace(
                    '%class_name%',
                    $parsed['className'],
                    str_replace(
                        '%table_name%',
                        $parsed['tableName'],
                        str_replace(
                            '%snake_case_class_name%',
                            $parsed['snakeCaseClassName'],
                            str_replace(
                                '%camel_case_class_name%',
                                $parsed['camelCaseClassName'],
                                file_get_contents($template)
                            )
                        )
                    )
                )
            );
            file_put_contents($this->root . '/app/src/Application/Actions/Commands/' . $parsed['className'] . '/' . $command . '.php', $content);
        }

        $this->writeStoreCommandFile($templatesDir, $parsed);
        $this->writeUpdateCommandFile($templatesDir, $parsed);
    }

    private function writeStoreCommandFile($templatesDir, $parsed) {

        $template = $templatesDir . '/store.1.phpt';
        $content = str_replace(
            '%namespace%',
            $this->namespace,
            str_replace(
                '%class_name%',
                $parsed['className'],
                str_replace(
                    '%table_name%',
                    $parsed['tableName'],
                    str_replace(
                        '%snake_case_class_name%',
                        $parsed['snakeCaseClassName'],
                        str_replace(
                            '%camel_case_class_name%',
                            $parsed['camelCaseClassName'],
                            file_get_contents($template)
                        )
                    )
                )
            )
        );

        $template = $templatesDir . '/store.2.phpt';
        foreach ($parsed['properties'] as $property) {
            if (!$property['isObject']) {
                $content .= str_replace(
                    '%camel_case_class_name%',
                    $parsed['camelCaseClassName'],
                    str_replace(
                        '%property_name%',
                        $property['propertyName'],
                        str_replace(
                            '%column_name%',
                            $property['columnName'],
                            file_get_contents($template)
                        )
                    )
                );
            }
        }

        $template = $templatesDir . '/store.3.phpt';
        $content .= str_replace(
            '%class_name%',
            $parsed['className'],
            str_replace(
                '%snake_case_class_name%',
                $parsed['snakeCaseClassName'],
                str_replace(
                    '%camel_case_class_name%',
                    $parsed['camelCaseClassName'],
                    file_get_contents($template)
                )
            )
        );

        file_put_contents($this->root . '/app/src/Application/Actions/Commands/' . $parsed['className'] . '/Store.php', $content);
    }

    private function writeUpdateCommandFile($templatesDir, $parsed) {

        $template = $templatesDir . '/update.1.phpt';
        $content = str_replace(
            '%namespace%',
            $this->namespace,
            str_replace(
                '%class_name%',
                $parsed['className'],
                str_replace(
                    '%table_name%',
                    $parsed['tableName'],
                    str_replace(
                        '%snake_case_class_name%',
                        $parsed['snakeCaseClassName'],
                        str_replace(
                            '%camel_case_class_name%',
                            $parsed['camelCaseClassName'],
                            file_get_contents($template)
                        )
                    )
                )
            )
        );

        $template = $templatesDir . '/update.2.phpt';
        foreach ($parsed['properties'] as $property) {
            if (!$property['isObject']) {
                $content .= str_replace(
                    '%camel_case_class_name%',
                    $parsed['camelCaseClassName'],
                    str_replace(
                        '%property_name%',
                        $property['propertyName'],
                        str_replace(
                            '%column_name%',
                            $property['columnName'],
                            file_get_contents($template)
                        )
                    )
                );
            }
        }

        $template = $templatesDir . '/update.3.phpt';
        $content .= str_replace(
            '%class_name%',
            $parsed['className'],
            str_replace(
                '%snake_case_class_name%',
                $parsed['snakeCaseClassName'],
                str_replace(
                    '%camel_case_class_name%',
                    $parsed['camelCaseClassName'],
                    file_get_contents($template)
                )
            )
        );

        file_put_contents($this->root . '/app/src/Application/Actions/Commands/' . $parsed['className'] . '/Update.php', $content);
    }

    private function writeActionFiles($parsed) {

        $actions = [
            'Create' => 'Create' . $parsed['className'],
            'Delete' => 'Delete' . $parsed['className'],
            'Get' => 'Get' . $parsed['className'],
            'Gets' => 'Get' . $parsed['classNamePlural'],
            'Insert' => 'Insert' . $parsed['className'],
            'Update' => 'Update' . $parsed['className']
        ];

        $templatesDir = $this->root . '/templates/src/application/actions';
        if (!is_dir($this->root . '/app/src/Application/Actions/' . $parsed['className'])) {
            mkdir($this->root . '/app/src/Application/Actions/' . $parsed['className']);
        }

        foreach ($actions as $action => $class) {
            $template = $templatesDir . '/' . strtolower($action) . '.phpt';
            $content = str_replace(
                '%namespace%',
                $this->namespace,
                str_replace(
                    '%class_name%',
                    $parsed['className'],
                    str_replace(
                        '%class_name_plural%',
                        $parsed['classNamePlural'],
                        file_get_contents($template)
                    )
                )
            );
            file_put_contents($this->root . '/app/src/Application/Actions/' . $parsed['className'] . '/' . $class . '.php', $content);
        }
    }

    private function writeControllerFile($parsed) {

        $templatesDir = $this->root . '/templates/app/http/controllers/api/v1';

        $template = $templatesDir . '/controllers.1.phpt';
        $content = str_replace(
            '%namespace%',
            $this->namespace,
            str_replace(
                '%class_name%',
                $parsed['className'],
                str_replace(
                    '%class_name_plural%',
                    $parsed['classNamePlural'],
                    file_get_contents($template)
                )
            )
        );

        $template = $templatesDir . '/controllers.2.phpt';
        foreach ($parsed['properties'] as $property) {
            if (!$property['isObject']) {
                $content .= str_replace(
                    '%column_name%',
                    $property['columnName'],
                    file_get_contents($template)
                );
            } elseif ($property['isObject'] && !$property['isCollection']) {
                $content .= str_replace(
                    '%column_name%',
                    $property['columnName'] . '_id',
                    file_get_contents($template)
                );
            }
        }

        $template = $templatesDir . '/controllers.3.phpt';
        $content .= file_get_contents($template);

        $template = $templatesDir . '/controllers.4.phpt';
        foreach ($parsed['properties'] as $property) {
            if (!$property['isObject']) {
                $content .= str_replace(
                    '%column_name%',
                    $property['columnName'],
                    file_get_contents($template)
                );
            } elseif ($property['isObject'] && !$property['isCollection']) {
                $content .= str_replace(
                    '%column_name%',
                    $property['columnName'] . '_id',
                    file_get_contents($template)
                );
            }
        }

        $template = $templatesDir . '/controllers.5.phpt';
        $content .= file_get_contents($template);

        file_put_contents($this->root . '/app/app/Http/Controllers/Api/V1/' . $parsed['className'] . 'Controller.php', $content);
    }

    private function makeDirectories() {

        $namespace = str_replace("\\", "/", $this->namespace);
        $dirs = [
            $this->root . "/src/$namespace",
            $this->root . "/src/$namespace/Domain/Infrastructure/Repositories",
            $this->root . "/src/$namespace/Domain/Model",
            $this->root . "/src/$namespace/Domain/Validation",
            $this->root . "/src/$namespace/Domain/Services",
            $this->root . "/src/$namespace/Infrastructure/Repositories/MySql",
            $this->root . "/src/$namespace/Application/Validation/Rules",
            $this->root . "/src/$namespace/Application/Policies",
            $this->root . "/src/$namespace/Application/Providers",
            $this->root . "/src/$namespace/Application/Http/Controllers/Api",
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) mkdir($dir, "0777", true);
        }
    }

    private function makeBases() {

        $folder = str_replace("\\", "/", $this->namespace);

        file_put_contents(
            $this->root . "/src/$folder/Domain/Infrastructure/Repositories/BaseRepositoryInterface.php",
            "<?php\n" . $this->blade->render("domain.infrastructure.repositories.base", ['namespace' => $this->namespace])
        );

        file_put_contents(
            $this->root . "/src/$folder/Domain/Model/Status.php",
            "<?php\n" . $this->blade->render("domain.models.status", ['namespace' => $this->namespace])
        );

        file_put_contents(
            $this->root . "/src/$folder/Infrastructure/Repositories/MySql/BaseRepository.php",
            "<?php\n" . $this->blade->render("infrastructure.repositories.mysql.base", ['namespace' => $this->namespace])
        );

        file_put_contents(
            $this->root . "/src/$folder/Application/Http/ApiResponse.php",
            "<?php\n" . $this->blade->render("application.http.api_response", ['namespace' => $this->namespace])
        );

        file_put_contents(
            $this->root . "/src/$folder/Application/Http/Controllers/Controller.php",
            "<?php\n" . $this->blade->render("application.http.controllers.base_controller", ['namespace' => $this->namespace])
        );

    }

    /**
     * @return array|string
     */
    private function parseNamespace() {

        $namespace = explode('.', $this->app['application']['model']['__namespace']);
        foreach ($namespace as &$ns) {
            $ns = Strings::stringToPascalCase($ns);
        }
        $namespace = implode('\\', $namespace);

        return $namespace;
    }

    /**
     * @param $name
     * @param $model
     * @return array
     * @throws \Exception
     */
    private function parseModel($name, $model) {

        $properties = [];

        list(
            $className,
            $tableName,
            $snakeCaseClassName,
            $camelCaseClassName,
            $classNamePlural
            ) = $this->parseClass($name, $model);

        foreach ($model as $propertyName => $details) {

            if (!Strings::startsWith($propertyName, '__')) {
                $properties [] = $this->parseProperties($name, $propertyName, $details);
            }
        }

        return [
            'namespace' => $this->namespace,
            'className' => $className,
            'snakeCaseClassName' => $snakeCaseClassName,
            'camelCaseClassName' => $camelCaseClassName,
            'classNamePlural' => $classNamePlural,
            'tableName' => $tableName,
            'properties' => $properties
        ];
    }

    /**
     * @param $name
     * @param $model
     * @return array
     */
    private function parseClass($name, $model) {

        $className = Strings::stringToPascalCase($name);
        $snakeCaseClassName = Strings::stringToUnderScore($name);
        $camelCaseClassName = Strings::stringToCamelCase($name);
        $tableName = Strings::stringToUnderScore($model['__plural']);
        $classNamePlural = Strings::stringToPascalCase($tableName);

        return [
            $className, $tableName, $snakeCaseClassName, $camelCaseClassName, $classNamePlural
        ];
    }

    /**
     * @param $name
     * @param $propertyName
     * @param $details
     * @return array
     * @throws \Exception
     */
    private function parseProperties($name, $propertyName, $details) {

        $propertyClassname = $objectPropertySnakecase = $objectPropertyCamelcase = $pluralPropertyName = "";

        $doctrineType = $details['type'];
        $object = false;
        $phpType = $this->mapDoctrineTypeToPhp($this->map, $doctrineType);
        if ($doctrineType == 'timestamp') $doctrineType = 'integer'; // we want to store datetime as unix timestamps
        $phpTypes = array_values($this->map);
        if (!in_array($phpType, $phpTypes)) {
            // assume it's another object in dis file
            if (!isset($this->app['application']['model'][$phpType])) {
                throw new \Exception("Look, this isn't working...");
            }

            $object = true;
            $model = $this->app['application']['model'][$phpType];
            $propertyClassname = Strings::stringToPascalCase($phpType);
            $objectPropertySnakecase = Strings::stringToUnderScore($propertyClassname);
            $objectPropertyCamelcase = Strings::stringToCamelCase($objectPropertySnakecase);
            $pluralPropertyName = Strings::stringToCamelCase($model['__plural']);
        }

        $columnName = Strings::stringToUnderScore($propertyName);
        if ($object) {
            $columnName .= "_id";
        }
        $collection = isset($details['collection']) ? true : false;
        $mappedByPropertyName = isset($details['mappedBy']) ? $details['mappedBy'] : $name;
        $inversedByPropertyName = Strings::stringToCamelCase(isset($details['inversedBy']) ? $details['inversedBy'] : $this->app['application']['model'][$name]['__plural']);

        return [
            'doctrineType' => $doctrineType,
            'phpType' => $phpType,
            'isCollection' => $collection,
            'isObject' => $object,
            'columnName' => $columnName,
            'propertyName' => $propertyName,
            'propertyClassname' => $propertyClassname,
            'objectPropertySnakecase' => $objectPropertySnakecase,
            'objectPropertyCamelcase' => $objectPropertyCamelcase,
            'mappedByPropertyName' => $mappedByPropertyName,
            'inversedByPropertyName' => $inversedByPropertyName,
            'pluralPropertyName' => $pluralPropertyName,
        ];
    }

    private function mapDoctrineTypeToPhp($map, $doctrineType) {

        return (isset($map[$doctrineType])) ? $map[$doctrineType] : $doctrineType;
    }

    /**
     * @param $properties
     * @return bool
     */
    private function hasCollections($properties) {

        foreach ($properties as $property) {
            if ($property['isCollection']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $properties
     * @return bool
     */
    private function hasObjects($properties) {

        foreach ($properties as $property) {
            if ($property['isObject']) {
                return true;
            }
        }

        return false;
    }
}
