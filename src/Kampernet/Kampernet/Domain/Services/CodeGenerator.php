<?php

namespace Kampernet\Kampernet\Domain\Services;

use Packaged\Helpers\Strings;

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
     * CodeGenerator constructor.
     * @param $app
     * @param $root
     */
    public function __construct($app, $root) {

        $this->app = $app;
        $this->root = $root;

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
//                $this->writeModelFile($parsed);
//                $this->writeRepositoryInterfaceFile($parsed['className']);
//                $this->writeRepositoryFile($parsed['className']);
//                $this->writeValidationFile($parsed);
//                $this->writeControllerFile($parsed);
                print_r($parsed);
            }
        }
    }

    private function writeModelFile($parsed) {

        $namespace = $parsed['namespace'];
        $className = $parsed['className'];
        $tableName = $parsed['tableName'];
        $properties = $parsed['properties'];
        $collections = [];

        $hasCollections = $this->hasCollections($properties);

        $templatesDir = $this->root . '/templates/src/domain/models';
        $content = "";

        $templates = [
            $templatesDir . '/01.open_tag.phpt',
            $templatesDir . '/02.namespace.phpt',
            $templatesDir . '/03.use_collection.phpt',
            $templatesDir . '/04.open_class.phpt',
            $templatesDir . '/05.id.phpt',
            $templatesDir . '/06.property.phpt',
            $templatesDir . '/07.object_property.phpt',
            $templatesDir . '/08.object_collection.phpt',
            $templatesDir . '/09.open_constructor.phpt',
            $templatesDir . '/10.init_collections.phpt',
            $templatesDir . '/11.close_constructor.phpt',
            $templatesDir . '/12.close_class.phpt',
        ];

        $content .= file_get_contents($templates[0]); // open php tag
        $content .= str_replace('%namespace%', $namespace, file_get_contents($templates[1])); // namespace declaration
        $content .= ($hasCollections) ? file_get_contents($templates[2]) : ''; // whether or not to import / use doctrine collections
        $content .= str_replace(
            '%namespace%',
            $namespace,
            str_replace(
                '%class_name%',
                $className,
                str_replace(
                    '%table_name%',
                    $tableName,
                    file_get_contents($templates[3])
                )
            )
        ); // open the class
        $content .= file_get_contents($templates[4]); // declare the id property

        foreach ($properties as $property) {

            if ($property['isObject'] && $property['isCollection']) {
                $content .= str_replace(
                    '%namespace%',
                    $namespace,
                    str_replace(
                        '%property_classname%',
                        $property['propertyClassname'],
                        str_replace(
                            '%mapped_by%',
                            $property['mappedByPropertyName'],
                            str_replace(
                                '%plural_property_name%',
                                $property['pluralPropertyName'],
                                file_get_contents($templates[7]) // object collection property
                            )
                        )
                    )
                );

                $collections [] = [
                    'pluralPropertyName' => $property['pluralPropertyName']
                ];
            } elseif ($property['isObject']) {
                $content .= str_replace(
                    '%namespace%',
                    $namespace,
                    str_replace(
                        '%property_classname%',
                        $property['propertyClassname'],
                        str_replace(
                            '%object_property_snakecase%',
                            $property['objectPropertySnakecase'],
                            str_replace(
                                '%object_property_camelcase%',
                                $property['objectPropertyCamelcase'],
                                str_replace(
                                    '%inversed_by_property_name%',
                                    $property['inversedByPropertyName'],
                                    file_get_contents($templates[6]) // object property
                                )
                            )
                        )
                    )
                );
            } else {
                $content .= str_replace(
                    '%doctrine_type%',
                    $property['doctrineType'],
                    str_replace(
                        '%column_name%',
                        $property['columnName'],
                        str_replace(
                            '%php_type%',
                            $property['phpType'],
                            str_replace(
                                '%property_name%',
                                $property['propertyName'],
                                file_get_contents($templates[5]) // scalar property
                            )
                        )
                    )
                );
            }
        }

        if ($hasCollections) {
            $content .= str_replace('%class_name%', $className, file_get_contents($templates[8])); // open constructor
            foreach ($collections as $collection) {
                $content .= str_replace(
                    '%plural_property_name%',
                    $collection['pluralPropertyName'],
                    file_get_contents($templates[9]) // init collections inside constructor
                );
            }
            $content .= file_get_contents($templates[10]); // close constructor
        }

        $content .= file_get_contents($templates[11]); // close class

        file_put_contents($this->root . '/app/src/Domain/Model/' . $className . '.php', $content);
    }

    private function writeRepositoryInterfaceFile($className) {

        $templatesDir = $this->root . '/templates/src/domain/infrastructure/repositories';
        $content = str_replace(
            '%namespace%',
            $this->namespace,
            str_replace(
                '%class_name%',
                $className,
                file_get_contents($templatesDir . '/02.extension.phpt')
            )
        );

        file_put_contents($this->root . '/app/src/Domain/Infrastructure/Repositories/' . $className . 'RepositoryInterface.php', $content);
    }

    private function writeRepositoryFile($className) {

        $templatesDir = $this->root . '/templates/src/infrastructure/repositories/doctrine';
        $content = str_replace(
            '%namespace%',
            $this->namespace,
            str_replace(
                '%class_name%',
                $className,
                file_get_contents($templatesDir . '/02.extension.phpt')
            )
        );

        file_put_contents($this->root . '/app/src/Infrastructure/Repositories/Doctrine/' . $className . 'Repository.php', $content);
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

        $dirs = [
            $this->root . '/src',
            $this->root . '/src/Domain',
            $this->root . '/src/Domain/Infrastructure',
            $this->root . '/src/Domain/Infrastructure/Repositories',
            $this->root . '/src/Domain/Model',
            $this->root . '/src/Domain/Services',
            $this->root . '/src/Infrastructure',
            $this->root . '/src/Infrastructure/Repositories',
            $this->root . '/src/Infrastructure/Repositories/MySql',
            $this->root . '/src/Application',
            $this->root . '/src/Application/Auth',
            $this->root . '/src/Application/Validation',
            $this->root . '/src/Application/Http',
            $this->root . '/src/Application/Http/Controllers',
            $this->root . '/src/Application/Http/Controllers/Api',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) mkdir($dir);
        }
    }

    private function makeBases() {

        $bases = [
            ['/templates/src/domain/infrastructure/repositories', '/01.base.phpt', '/app/src/Domain/Infrastructure/Repositories/BaseRepositoryInterface.php'],
            ['/templates/src/domain/models', '/13.status.phpt', '/app/src/Domain/Model/Status.php'],
            ['/templates/src/infrastructure/repositories/mysql', '/01.base.phpt', '/app/src/Infrastructure/Repositories/MySql/BaseRepository.php'],
            ['/templates/src/application/auth', '/login.proxy.phpt', '/app/src/Application/Auth/LoginProxy.php'],
            ['/templates/src/application/auth', '/inactive.user.phpt', '/app/src/Application/Auth/InactiveUserException.php'],
            ['/templates/src/application/auth', '/invalid.credentials.phpt', '/app/src/Application/Auth/InvalidCredentialsException.php'],
            ['/templates/src/application/http', '/api.response.phpt', '/app/src/Application/Http/ApiResponse.php'],
            ['/templates/src/application/http/controllers', '/base.controller.phpt', '/app/src/Application/Http/Controllers/Controller.php'],
        ];

        foreach($bases as $base) {
            $templatesDir = $this->root . $base[0];
            $content = str_replace(
                '%namespace%',
                $this->namespace,
                file_get_contents($templatesDir . $base[1])
            );
            file_put_contents($this->root . $base[2], $content);
        }
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
}
