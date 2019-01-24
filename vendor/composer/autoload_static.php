<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita9b2a98493fc49d88d01bb38365afaca
{
    public static $files = array (
        '5255c38a0faeba867671b61dfda6d864' => __DIR__ . '/..' . '/paragonie/random_compat/lib/random.php',
        '72579e7bd17821bb1321b87411366eae' => __DIR__ . '/..' . '/illuminate/support/helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Mimey\\' => 6,
        ),
        'I' => 
        array (
            'Illuminate\\Support\\' => 19,
            'Illuminate\\Contracts\\' => 21,
        ),
        'D' => 
        array (
            'Drupal\\loft_core\\' => 17,
        ),
        'A' => 
        array (
            'AKlump\\LoftLib\\' => 15,
            'AKlump\\Data\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Mimey\\' => 
        array (
            0 => __DIR__ . '/..' . '/ralouphie/mimey/src',
        ),
        'Illuminate\\Support\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/support',
        ),
        'Illuminate\\Contracts\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/contracts',
        ),
        'Drupal\\loft_core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
            1 => __DIR__ . '/../..' . '/tests/src',
        ),
        'AKlump\\LoftLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib',
        ),
        'AKlump\\Data\\' => 
        array (
            0 => __DIR__ . '/..' . '/aklump/data',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Sunra\\PhpSimple\\HtmlDomParser' => 
            array (
                0 => __DIR__ . '/..' . '/sunra/php-simple-html-dom-parser/Src',
            ),
        ),
        'D' => 
        array (
            'Doctrine\\Common\\Inflector\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/inflector/lib',
            ),
        ),
    );

    public static $classMap = array (
        'AKlump\\Data\\CallTestObject' => __DIR__ . '/..' . '/aklump/data/Tests/DataTest.php',
        'AKlump\\Data\\Data' => __DIR__ . '/..' . '/aklump/data/Data.php',
        'AKlump\\Data\\DataInterface' => __DIR__ . '/..' . '/aklump/data/DataInterface.php',
        'AKlump\\Data\\DataTest' => __DIR__ . '/..' . '/aklump/data/Tests/DataTest.php',
        'AKlump\\LoftLib\\Bash\\Bash' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Bash/Bash.php',
        'AKlump\\LoftLib\\Bash\\Color' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Bash/Color.php',
        'AKlump\\LoftLib\\Bash\\Configuration' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Bash/Configuration.php',
        'AKlump\\LoftLib\\Bash\\FailedExecException' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Bash/FailedExecException.php',
        'AKlump\\LoftLib\\Bash\\Output' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Bash/Output.php',
        'AKlump\\LoftLib\\Code\\Arrays' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/Arrays.php',
        'AKlump\\LoftLib\\Code\\Cache' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/Cache.php',
        'AKlump\\LoftLib\\Code\\Dataset' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/Dataset.php',
        'AKlump\\LoftLib\\Code\\DatasetInterface' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/DatasetInterface.php',
        'AKlump\\LoftLib\\Code\\Dates' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/Dates.php',
        'AKlump\\LoftLib\\Code\\Grammar' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/Grammar.php',
        'AKlump\\LoftLib\\Code\\InfiniteSubset' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/InfiniteSubset.php',
        'AKlump\\LoftLib\\Code\\LoftXmlElement' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/LoftXmlElement.php',
        'AKlump\\LoftLib\\Code\\Markdown' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/Markdown.php',
        'AKlump\\LoftLib\\Code\\ObjectCacheTrait' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/ObjectCacheTrait.php',
        'AKlump\\LoftLib\\Code\\PersistentSequence' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/PersistentSequence.php',
        'AKlump\\LoftLib\\Code\\StandardPhpErrorException' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/StandardPhpErrorException.php',
        'AKlump\\LoftLib\\Code\\Strings' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/Strings.php',
        'AKlump\\LoftLib\\Code\\ThrowableErrorsTrait' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Code/ThrowableErrorsTrait.php',
        'AKlump\\LoftLib\\Config\\Config' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Config/Config.php',
        'AKlump\\LoftLib\\Config\\ConfigBash' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Config/ConfigBash.php',
        'AKlump\\LoftLib\\Config\\ConfigFileBasedStorage' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Config/ConfigFileBasedStorage.php',
        'AKlump\\LoftLib\\Config\\ConfigInterface' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Config/ConfigInterface.php',
        'AKlump\\LoftLib\\Storage\\FilePath' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Storage/FilePath.php',
        'AKlump\\LoftLib\\Storage\\FilePathCollection' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Storage/FilePathCollection.php',
        'AKlump\\LoftLib\\Storage\\PersistentInterface' => __DIR__ . '/..' . '/aklump/loft-lib/src/AKlump/LoftLib/Storage/PersistentInterface.php',
        'Doctrine\\Common\\Inflector\\Inflector' => __DIR__ . '/..' . '/doctrine/inflector/lib/Doctrine/Common/Inflector/Inflector.php',
        'Drupal\\loft_core\\Attribute' => __DIR__ . '/../..' . '/src/Attribute.php',
        'Drupal\\loft_core\\CoreBase' => __DIR__ . '/../..' . '/src/CoreBase.php',
        'Drupal\\loft_core\\CoreInterface' => __DIR__ . '/../..' . '/src/CoreInterface.php',
        'Drupal\\loft_core\\Drupal7' => __DIR__ . '/../..' . '/tests/src/Entity/ExtractorTest.php',
        'Drupal\\loft_core\\Entity\\ExtractorTrait' => __DIR__ . '/../..' . '/src/Entity/ExtractorTrait.php',
        'Drupal\\loft_core\\ExtractorTest' => __DIR__ . '/../..' . '/tests/src/Entity/ExtractorTest.php',
        'Drupal\\loft_core\\Redirect' => __DIR__ . '/../..' . '/src/Redirect.php',
        'Drupal\\loft_core\\StaticContentStreamWrapper' => __DIR__ . '/../..' . '/src/StaticContentStreamWrapper.php',
        'Drupal\\loft_core\\TestableCore' => __DIR__ . '/../..' . '/tests/src/Entity/ExtractorTest.php',
        'Drupal\\loft_core\\TestableExtractor' => __DIR__ . '/../..' . '/tests/src/Entity/ExtractorTest.php',
        'Illuminate\\Contracts\\Auth\\Access\\Authorizable' => __DIR__ . '/..' . '/illuminate/contracts/Auth/Access/Authorizable.php',
        'Illuminate\\Contracts\\Auth\\Access\\Gate' => __DIR__ . '/..' . '/illuminate/contracts/Auth/Access/Gate.php',
        'Illuminate\\Contracts\\Auth\\Authenticatable' => __DIR__ . '/..' . '/illuminate/contracts/Auth/Authenticatable.php',
        'Illuminate\\Contracts\\Auth\\CanResetPassword' => __DIR__ . '/..' . '/illuminate/contracts/Auth/CanResetPassword.php',
        'Illuminate\\Contracts\\Auth\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Auth/Factory.php',
        'Illuminate\\Contracts\\Auth\\Guard' => __DIR__ . '/..' . '/illuminate/contracts/Auth/Guard.php',
        'Illuminate\\Contracts\\Auth\\PasswordBroker' => __DIR__ . '/..' . '/illuminate/contracts/Auth/PasswordBroker.php',
        'Illuminate\\Contracts\\Auth\\PasswordBrokerFactory' => __DIR__ . '/..' . '/illuminate/contracts/Auth/PasswordBrokerFactory.php',
        'Illuminate\\Contracts\\Auth\\StatefulGuard' => __DIR__ . '/..' . '/illuminate/contracts/Auth/StatefulGuard.php',
        'Illuminate\\Contracts\\Auth\\SupportsBasicAuth' => __DIR__ . '/..' . '/illuminate/contracts/Auth/SupportsBasicAuth.php',
        'Illuminate\\Contracts\\Auth\\UserProvider' => __DIR__ . '/..' . '/illuminate/contracts/Auth/UserProvider.php',
        'Illuminate\\Contracts\\Broadcasting\\Broadcaster' => __DIR__ . '/..' . '/illuminate/contracts/Broadcasting/Broadcaster.php',
        'Illuminate\\Contracts\\Broadcasting\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Broadcasting/Factory.php',
        'Illuminate\\Contracts\\Broadcasting\\ShouldBroadcast' => __DIR__ . '/..' . '/illuminate/contracts/Broadcasting/ShouldBroadcast.php',
        'Illuminate\\Contracts\\Broadcasting\\ShouldBroadcastNow' => __DIR__ . '/..' . '/illuminate/contracts/Broadcasting/ShouldBroadcastNow.php',
        'Illuminate\\Contracts\\Bus\\Dispatcher' => __DIR__ . '/..' . '/illuminate/contracts/Bus/Dispatcher.php',
        'Illuminate\\Contracts\\Bus\\QueueingDispatcher' => __DIR__ . '/..' . '/illuminate/contracts/Bus/QueueingDispatcher.php',
        'Illuminate\\Contracts\\Cache\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Cache/Factory.php',
        'Illuminate\\Contracts\\Cache\\Repository' => __DIR__ . '/..' . '/illuminate/contracts/Cache/Repository.php',
        'Illuminate\\Contracts\\Cache\\Store' => __DIR__ . '/..' . '/illuminate/contracts/Cache/Store.php',
        'Illuminate\\Contracts\\Config\\Repository' => __DIR__ . '/..' . '/illuminate/contracts/Config/Repository.php',
        'Illuminate\\Contracts\\Console\\Application' => __DIR__ . '/..' . '/illuminate/contracts/Console/Application.php',
        'Illuminate\\Contracts\\Console\\Kernel' => __DIR__ . '/..' . '/illuminate/contracts/Console/Kernel.php',
        'Illuminate\\Contracts\\Container\\BindingResolutionException' => __DIR__ . '/..' . '/illuminate/contracts/Container/BindingResolutionException.php',
        'Illuminate\\Contracts\\Container\\Container' => __DIR__ . '/..' . '/illuminate/contracts/Container/Container.php',
        'Illuminate\\Contracts\\Container\\ContextualBindingBuilder' => __DIR__ . '/..' . '/illuminate/contracts/Container/ContextualBindingBuilder.php',
        'Illuminate\\Contracts\\Cookie\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Cookie/Factory.php',
        'Illuminate\\Contracts\\Cookie\\QueueingFactory' => __DIR__ . '/..' . '/illuminate/contracts/Cookie/QueueingFactory.php',
        'Illuminate\\Contracts\\Database\\ModelIdentifier' => __DIR__ . '/..' . '/illuminate/contracts/Database/ModelIdentifier.php',
        'Illuminate\\Contracts\\Debug\\ExceptionHandler' => __DIR__ . '/..' . '/illuminate/contracts/Debug/ExceptionHandler.php',
        'Illuminate\\Contracts\\Encryption\\DecryptException' => __DIR__ . '/..' . '/illuminate/contracts/Encryption/DecryptException.php',
        'Illuminate\\Contracts\\Encryption\\EncryptException' => __DIR__ . '/..' . '/illuminate/contracts/Encryption/EncryptException.php',
        'Illuminate\\Contracts\\Encryption\\Encrypter' => __DIR__ . '/..' . '/illuminate/contracts/Encryption/Encrypter.php',
        'Illuminate\\Contracts\\Events\\Dispatcher' => __DIR__ . '/..' . '/illuminate/contracts/Events/Dispatcher.php',
        'Illuminate\\Contracts\\Filesystem\\Cloud' => __DIR__ . '/..' . '/illuminate/contracts/Filesystem/Cloud.php',
        'Illuminate\\Contracts\\Filesystem\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Filesystem/Factory.php',
        'Illuminate\\Contracts\\Filesystem\\FileNotFoundException' => __DIR__ . '/..' . '/illuminate/contracts/Filesystem/FileNotFoundException.php',
        'Illuminate\\Contracts\\Filesystem\\Filesystem' => __DIR__ . '/..' . '/illuminate/contracts/Filesystem/Filesystem.php',
        'Illuminate\\Contracts\\Foundation\\Application' => __DIR__ . '/..' . '/illuminate/contracts/Foundation/Application.php',
        'Illuminate\\Contracts\\Hashing\\Hasher' => __DIR__ . '/..' . '/illuminate/contracts/Hashing/Hasher.php',
        'Illuminate\\Contracts\\Http\\Kernel' => __DIR__ . '/..' . '/illuminate/contracts/Http/Kernel.php',
        'Illuminate\\Contracts\\Logging\\Log' => __DIR__ . '/..' . '/illuminate/contracts/Logging/Log.php',
        'Illuminate\\Contracts\\Mail\\MailQueue' => __DIR__ . '/..' . '/illuminate/contracts/Mail/MailQueue.php',
        'Illuminate\\Contracts\\Mail\\Mailable' => __DIR__ . '/..' . '/illuminate/contracts/Mail/Mailable.php',
        'Illuminate\\Contracts\\Mail\\Mailer' => __DIR__ . '/..' . '/illuminate/contracts/Mail/Mailer.php',
        'Illuminate\\Contracts\\Notifications\\Dispatcher' => __DIR__ . '/..' . '/illuminate/contracts/Notifications/Dispatcher.php',
        'Illuminate\\Contracts\\Notifications\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Notifications/Factory.php',
        'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator' => __DIR__ . '/..' . '/illuminate/contracts/Pagination/LengthAwarePaginator.php',
        'Illuminate\\Contracts\\Pagination\\Paginator' => __DIR__ . '/..' . '/illuminate/contracts/Pagination/Paginator.php',
        'Illuminate\\Contracts\\Pipeline\\Hub' => __DIR__ . '/..' . '/illuminate/contracts/Pipeline/Hub.php',
        'Illuminate\\Contracts\\Pipeline\\Pipeline' => __DIR__ . '/..' . '/illuminate/contracts/Pipeline/Pipeline.php',
        'Illuminate\\Contracts\\Queue\\EntityNotFoundException' => __DIR__ . '/..' . '/illuminate/contracts/Queue/EntityNotFoundException.php',
        'Illuminate\\Contracts\\Queue\\EntityResolver' => __DIR__ . '/..' . '/illuminate/contracts/Queue/EntityResolver.php',
        'Illuminate\\Contracts\\Queue\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Queue/Factory.php',
        'Illuminate\\Contracts\\Queue\\Job' => __DIR__ . '/..' . '/illuminate/contracts/Queue/Job.php',
        'Illuminate\\Contracts\\Queue\\Monitor' => __DIR__ . '/..' . '/illuminate/contracts/Queue/Monitor.php',
        'Illuminate\\Contracts\\Queue\\Queue' => __DIR__ . '/..' . '/illuminate/contracts/Queue/Queue.php',
        'Illuminate\\Contracts\\Queue\\QueueableCollection' => __DIR__ . '/..' . '/illuminate/contracts/Queue/QueueableCollection.php',
        'Illuminate\\Contracts\\Queue\\QueueableEntity' => __DIR__ . '/..' . '/illuminate/contracts/Queue/QueueableEntity.php',
        'Illuminate\\Contracts\\Queue\\ShouldQueue' => __DIR__ . '/..' . '/illuminate/contracts/Queue/ShouldQueue.php',
        'Illuminate\\Contracts\\Redis\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Redis/Factory.php',
        'Illuminate\\Contracts\\Routing\\BindingRegistrar' => __DIR__ . '/..' . '/illuminate/contracts/Routing/BindingRegistrar.php',
        'Illuminate\\Contracts\\Routing\\Registrar' => __DIR__ . '/..' . '/illuminate/contracts/Routing/Registrar.php',
        'Illuminate\\Contracts\\Routing\\ResponseFactory' => __DIR__ . '/..' . '/illuminate/contracts/Routing/ResponseFactory.php',
        'Illuminate\\Contracts\\Routing\\UrlGenerator' => __DIR__ . '/..' . '/illuminate/contracts/Routing/UrlGenerator.php',
        'Illuminate\\Contracts\\Routing\\UrlRoutable' => __DIR__ . '/..' . '/illuminate/contracts/Routing/UrlRoutable.php',
        'Illuminate\\Contracts\\Session\\Session' => __DIR__ . '/..' . '/illuminate/contracts/Session/Session.php',
        'Illuminate\\Contracts\\Support\\Arrayable' => __DIR__ . '/..' . '/illuminate/contracts/Support/Arrayable.php',
        'Illuminate\\Contracts\\Support\\Htmlable' => __DIR__ . '/..' . '/illuminate/contracts/Support/Htmlable.php',
        'Illuminate\\Contracts\\Support\\Jsonable' => __DIR__ . '/..' . '/illuminate/contracts/Support/Jsonable.php',
        'Illuminate\\Contracts\\Support\\MessageBag' => __DIR__ . '/..' . '/illuminate/contracts/Support/MessageBag.php',
        'Illuminate\\Contracts\\Support\\MessageProvider' => __DIR__ . '/..' . '/illuminate/contracts/Support/MessageProvider.php',
        'Illuminate\\Contracts\\Support\\Renderable' => __DIR__ . '/..' . '/illuminate/contracts/Support/Renderable.php',
        'Illuminate\\Contracts\\Translation\\Translator' => __DIR__ . '/..' . '/illuminate/contracts/Translation/Translator.php',
        'Illuminate\\Contracts\\Validation\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/Validation/Factory.php',
        'Illuminate\\Contracts\\Validation\\ValidatesWhenResolved' => __DIR__ . '/..' . '/illuminate/contracts/Validation/ValidatesWhenResolved.php',
        'Illuminate\\Contracts\\Validation\\Validator' => __DIR__ . '/..' . '/illuminate/contracts/Validation/Validator.php',
        'Illuminate\\Contracts\\View\\Factory' => __DIR__ . '/..' . '/illuminate/contracts/View/Factory.php',
        'Illuminate\\Contracts\\View\\View' => __DIR__ . '/..' . '/illuminate/contracts/View/View.php',
        'Illuminate\\Support\\AggregateServiceProvider' => __DIR__ . '/..' . '/illuminate/support/AggregateServiceProvider.php',
        'Illuminate\\Support\\Arr' => __DIR__ . '/..' . '/illuminate/support/Arr.php',
        'Illuminate\\Support\\Collection' => __DIR__ . '/..' . '/illuminate/support/Collection.php',
        'Illuminate\\Support\\Composer' => __DIR__ . '/..' . '/illuminate/support/Composer.php',
        'Illuminate\\Support\\Debug\\Dumper' => __DIR__ . '/..' . '/illuminate/support/Debug/Dumper.php',
        'Illuminate\\Support\\Debug\\HtmlDumper' => __DIR__ . '/..' . '/illuminate/support/Debug/HtmlDumper.php',
        'Illuminate\\Support\\Facades\\App' => __DIR__ . '/..' . '/illuminate/support/Facades/App.php',
        'Illuminate\\Support\\Facades\\Artisan' => __DIR__ . '/..' . '/illuminate/support/Facades/Artisan.php',
        'Illuminate\\Support\\Facades\\Auth' => __DIR__ . '/..' . '/illuminate/support/Facades/Auth.php',
        'Illuminate\\Support\\Facades\\Blade' => __DIR__ . '/..' . '/illuminate/support/Facades/Blade.php',
        'Illuminate\\Support\\Facades\\Broadcast' => __DIR__ . '/..' . '/illuminate/support/Facades/Broadcast.php',
        'Illuminate\\Support\\Facades\\Bus' => __DIR__ . '/..' . '/illuminate/support/Facades/Bus.php',
        'Illuminate\\Support\\Facades\\Cache' => __DIR__ . '/..' . '/illuminate/support/Facades/Cache.php',
        'Illuminate\\Support\\Facades\\Config' => __DIR__ . '/..' . '/illuminate/support/Facades/Config.php',
        'Illuminate\\Support\\Facades\\Cookie' => __DIR__ . '/..' . '/illuminate/support/Facades/Cookie.php',
        'Illuminate\\Support\\Facades\\Crypt' => __DIR__ . '/..' . '/illuminate/support/Facades/Crypt.php',
        'Illuminate\\Support\\Facades\\DB' => __DIR__ . '/..' . '/illuminate/support/Facades/DB.php',
        'Illuminate\\Support\\Facades\\Event' => __DIR__ . '/..' . '/illuminate/support/Facades/Event.php',
        'Illuminate\\Support\\Facades\\Facade' => __DIR__ . '/..' . '/illuminate/support/Facades/Facade.php',
        'Illuminate\\Support\\Facades\\File' => __DIR__ . '/..' . '/illuminate/support/Facades/File.php',
        'Illuminate\\Support\\Facades\\Gate' => __DIR__ . '/..' . '/illuminate/support/Facades/Gate.php',
        'Illuminate\\Support\\Facades\\Hash' => __DIR__ . '/..' . '/illuminate/support/Facades/Hash.php',
        'Illuminate\\Support\\Facades\\Input' => __DIR__ . '/..' . '/illuminate/support/Facades/Input.php',
        'Illuminate\\Support\\Facades\\Lang' => __DIR__ . '/..' . '/illuminate/support/Facades/Lang.php',
        'Illuminate\\Support\\Facades\\Log' => __DIR__ . '/..' . '/illuminate/support/Facades/Log.php',
        'Illuminate\\Support\\Facades\\Mail' => __DIR__ . '/..' . '/illuminate/support/Facades/Mail.php',
        'Illuminate\\Support\\Facades\\Notification' => __DIR__ . '/..' . '/illuminate/support/Facades/Notification.php',
        'Illuminate\\Support\\Facades\\Password' => __DIR__ . '/..' . '/illuminate/support/Facades/Password.php',
        'Illuminate\\Support\\Facades\\Queue' => __DIR__ . '/..' . '/illuminate/support/Facades/Queue.php',
        'Illuminate\\Support\\Facades\\Redirect' => __DIR__ . '/..' . '/illuminate/support/Facades/Redirect.php',
        'Illuminate\\Support\\Facades\\Redis' => __DIR__ . '/..' . '/illuminate/support/Facades/Redis.php',
        'Illuminate\\Support\\Facades\\Request' => __DIR__ . '/..' . '/illuminate/support/Facades/Request.php',
        'Illuminate\\Support\\Facades\\Response' => __DIR__ . '/..' . '/illuminate/support/Facades/Response.php',
        'Illuminate\\Support\\Facades\\Route' => __DIR__ . '/..' . '/illuminate/support/Facades/Route.php',
        'Illuminate\\Support\\Facades\\Schema' => __DIR__ . '/..' . '/illuminate/support/Facades/Schema.php',
        'Illuminate\\Support\\Facades\\Session' => __DIR__ . '/..' . '/illuminate/support/Facades/Session.php',
        'Illuminate\\Support\\Facades\\Storage' => __DIR__ . '/..' . '/illuminate/support/Facades/Storage.php',
        'Illuminate\\Support\\Facades\\URL' => __DIR__ . '/..' . '/illuminate/support/Facades/URL.php',
        'Illuminate\\Support\\Facades\\Validator' => __DIR__ . '/..' . '/illuminate/support/Facades/Validator.php',
        'Illuminate\\Support\\Facades\\View' => __DIR__ . '/..' . '/illuminate/support/Facades/View.php',
        'Illuminate\\Support\\Fluent' => __DIR__ . '/..' . '/illuminate/support/Fluent.php',
        'Illuminate\\Support\\HigherOrderCollectionProxy' => __DIR__ . '/..' . '/illuminate/support/HigherOrderCollectionProxy.php',
        'Illuminate\\Support\\HigherOrderTapProxy' => __DIR__ . '/..' . '/illuminate/support/HigherOrderTapProxy.php',
        'Illuminate\\Support\\HtmlString' => __DIR__ . '/..' . '/illuminate/support/HtmlString.php',
        'Illuminate\\Support\\Manager' => __DIR__ . '/..' . '/illuminate/support/Manager.php',
        'Illuminate\\Support\\MessageBag' => __DIR__ . '/..' . '/illuminate/support/MessageBag.php',
        'Illuminate\\Support\\NamespacedItemResolver' => __DIR__ . '/..' . '/illuminate/support/NamespacedItemResolver.php',
        'Illuminate\\Support\\Pluralizer' => __DIR__ . '/..' . '/illuminate/support/Pluralizer.php',
        'Illuminate\\Support\\ServiceProvider' => __DIR__ . '/..' . '/illuminate/support/ServiceProvider.php',
        'Illuminate\\Support\\Str' => __DIR__ . '/..' . '/illuminate/support/Str.php',
        'Illuminate\\Support\\Testing\\Fakes\\BusFake' => __DIR__ . '/..' . '/illuminate/support/Testing/Fakes/BusFake.php',
        'Illuminate\\Support\\Testing\\Fakes\\EventFake' => __DIR__ . '/..' . '/illuminate/support/Testing/Fakes/EventFake.php',
        'Illuminate\\Support\\Testing\\Fakes\\MailFake' => __DIR__ . '/..' . '/illuminate/support/Testing/Fakes/MailFake.php',
        'Illuminate\\Support\\Testing\\Fakes\\NotificationFake' => __DIR__ . '/..' . '/illuminate/support/Testing/Fakes/NotificationFake.php',
        'Illuminate\\Support\\Testing\\Fakes\\PendingMailFake' => __DIR__ . '/..' . '/illuminate/support/Testing/Fakes/PendingMailFake.php',
        'Illuminate\\Support\\Testing\\Fakes\\QueueFake' => __DIR__ . '/..' . '/illuminate/support/Testing/Fakes/QueueFake.php',
        'Illuminate\\Support\\Traits\\CapsuleManagerTrait' => __DIR__ . '/..' . '/illuminate/support/Traits/CapsuleManagerTrait.php',
        'Illuminate\\Support\\Traits\\Macroable' => __DIR__ . '/..' . '/illuminate/support/Traits/Macroable.php',
        'Illuminate\\Support\\ViewErrorBag' => __DIR__ . '/..' . '/illuminate/support/ViewErrorBag.php',
        'Mimey\\MimeMappingBuilder' => __DIR__ . '/..' . '/ralouphie/mimey/src/MimeMappingBuilder.php',
        'Mimey\\MimeMappingGenerator' => __DIR__ . '/..' . '/ralouphie/mimey/src/MimeMappingGenerator.php',
        'Mimey\\MimeTypes' => __DIR__ . '/..' . '/ralouphie/mimey/src/MimeTypes.php',
        'Mimey\\MimeTypesInterface' => __DIR__ . '/..' . '/ralouphie/mimey/src/MimeTypesInterface.php',
        'Sunra\\PhpSimple\\HtmlDomParser' => __DIR__ . '/..' . '/sunra/php-simple-html-dom-parser/Src/Sunra/PhpSimple/HtmlDomParser.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita9b2a98493fc49d88d01bb38365afaca::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita9b2a98493fc49d88d01bb38365afaca::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInita9b2a98493fc49d88d01bb38365afaca::$prefixesPsr0;
            $loader->classMap = ComposerStaticInita9b2a98493fc49d88d01bb38365afaca::$classMap;

        }, null, ClassLoader::class);
    }
}
