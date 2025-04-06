"use strict";(self.webpackChunkwebsite=self.webpackChunkwebsite||[]).push([[230],{3313:(e,n,i)=>{i.r(n),i.d(n,{assets:()=>c,contentTitle:()=>a,default:()=>d,frontMatter:()=>t,metadata:()=>r,toc:()=>l});const r=JSON.parse('{"id":"core-concepts/app-lifecycle","title":"Application Lifecycle","description":"Understanding the Orkestra application lifecycle is essential for properly leveraging the framework\'s capabilities. This guide covers the bootstrapping process, various lifecycle stages, and how to hook into these stages in your application.","source":"@site/docs/core-concepts/app-lifecycle.md","sourceDirName":"core-concepts","slug":"/core-concepts/app-lifecycle","permalink":"/orkestra/docs/core-concepts/app-lifecycle","draft":false,"unlisted":false,"editUrl":"https://github.com/Luc-cpl/orkestra/tree/main/docs/docs/core-concepts/app-lifecycle.md","tags":[],"version":"current","sidebarPosition":1,"frontMatter":{"sidebar_position":1},"sidebar":"tutorialSidebar","previous":{"title":"Configuration","permalink":"/orkestra/docs/getting-started/configuration"},"next":{"title":"Dependency Injection","permalink":"/orkestra/docs/core-concepts/dependency-injection"}}');var o=i(4848),s=i(8453);const t={sidebar_position:1},a="Application Lifecycle",c={},l=[{value:"Overview",id:"overview",level:2},{value:"Initialization Phase",id:"initialization-phase",level:2},{value:"Skeleton Repository Bootstrap",id:"skeleton-repository-bootstrap",level:3},{value:"Hooking Into Initialization",id:"hooking-into-initialization",level:2},{value:"Configuration Phase",id:"configuration-phase",level:2},{value:"Hooking Into Configuration",id:"hooking-into-configuration",level:2},{value:"Configuration Validation",id:"configuration-validation",level:3},{value:"Provider Registration Phase",id:"provider-registration-phase",level:2},{value:"Hooking Into Provider Registration",id:"hooking-into-provider-registration",level:2},{value:"Provider Bootstrapping Phase",id:"provider-bootstrapping-phase",level:2},{value:"Hooking Into Provider Bootstrapping",id:"hooking-into-provider-bootstrapping",level:2},{value:"Execution Phase",id:"execution-phase",level:2},{value:"Hooking Into Execution",id:"hooking-into-execution",level:2},{value:"Service Container Usage During Lifecycle",id:"service-container-usage-during-lifecycle",level:2},{value:"Important Considerations",id:"important-considerations",level:2},{value:"Boot Only Once",id:"boot-only-once",level:3},{value:"Service Access Before Boot",id:"service-access-before-boot",level:3},{value:"Configuration Validation",id:"configuration-validation-1",level:3},{value:"Lifecycle Diagram",id:"lifecycle-diagram",level:2},{value:"Best Practices",id:"best-practices",level:2},{value:"Related Topics",id:"related-topics",level:2}];function p(e){const n={a:"a",blockquote:"blockquote",code:"code",h1:"h1",h2:"h2",h3:"h3",header:"header",li:"li",ol:"ol",p:"p",pre:"pre",strong:"strong",ul:"ul",...(0,s.R)(),...e.components};return(0,o.jsxs)(o.Fragment,{children:[(0,o.jsx)(n.header,{children:(0,o.jsx)(n.h1,{id:"application-lifecycle",children:"Application Lifecycle"})}),"\n",(0,o.jsx)(n.p,{children:"Understanding the Orkestra application lifecycle is essential for properly leveraging the framework's capabilities. This guide covers the bootstrapping process, various lifecycle stages, and how to hook into these stages in your application."}),"\n",(0,o.jsx)(n.h2,{id:"overview",children:"Overview"}),"\n",(0,o.jsx)(n.p,{children:"The Orkestra application lifecycle consists of several distinct phases:"}),"\n",(0,o.jsxs)(n.ol,{children:["\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.strong,{children:"Initialization"}),": The ",(0,o.jsx)(n.code,{children:"App"})," instance is created"]}),"\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.strong,{children:"Configuration"}),": Application settings are loaded and validated"]}),"\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.strong,{children:"Provider Registration"}),": Service providers are registered"]}),"\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.strong,{children:"Provider Bootstrapping"}),": Service providers are booted in sequence"]}),"\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.strong,{children:"Execution"}),": The application handles the request and generates a response"]}),"\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.strong,{children:"Termination"}),": Resources are released and the application terminates"]}),"\n"]}),"\n",(0,o.jsx)(n.h2,{id:"initialization-phase",children:"Initialization Phase"}),"\n",(0,o.jsxs)(n.p,{children:["The application begins with the creation of an ",(0,o.jsx)(n.code,{children:"App"})," instance, which serves as the central container for all services:"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"use Orkestra\\App;\nuse Orkestra\\Configuration;\n\n// Create a new app instance\n$app = new App(new Configuration());\n"})}),"\n",(0,o.jsx)(n.h3,{id:"skeleton-repository-bootstrap",children:"Skeleton Repository Bootstrap"}),"\n",(0,o.jsxs)(n.p,{children:["If you're using the Orkestra skeleton repository, the initialization is handled for you in the ",(0,o.jsx)(n.code,{children:"bootstrap/app.php"})," file:"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// bootstrap/app.php\n$app = new \\Orkestra\\App(new \\Orkestra\\Configuration(require __DIR__ . '/../config/app.php'));\n\n// Register additional configurations\n$app->config()->set('custom_config', require __DIR__ . '/../config/custom.php');\n\n// This helper is only available in the skeleton repository\nfunction app(): \\Orkestra\\App\n{\n    global $app;\n    return $app;\n}\n\nreturn $app;\n"})}),"\n",(0,o.jsxs)(n.blockquote,{children:["\n",(0,o.jsxs)(n.p,{children:[(0,o.jsx)(n.strong,{children:"Note"}),": The ",(0,o.jsx)(n.code,{children:"app()"})," function is only available in the skeleton repository and should not be relied upon in core Orkestra applications. Always use the ",(0,o.jsx)(n.code,{children:"$app"})," instance directly."]}),"\n"]}),"\n",(0,o.jsx)(n.h2,{id:"hooking-into-initialization",children:"Hooking Into Initialization"}),"\n",(0,o.jsx)(n.p,{children:"To customize the initialization phase, you can modify the bootstrap file or create your own bootstrap process:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// Create a configuration with custom values\n$config = new Configuration([\n    'env' => $_ENV['APP_ENV'] ?? 'development',\n    'root' => __DIR__,\n    'slug' => 'my-app'\n]);\n\n// Create the app with custom configuration\n$app = new App($config);\n\n// Store app instance globally if needed\n$GLOBALS['app'] = $app;\n"})}),"\n",(0,o.jsx)(n.h2,{id:"configuration-phase",children:"Configuration Phase"}),"\n",(0,o.jsxs)(n.p,{children:["During this phase, the application loads and validates configuration values. In the skeleton repository, configuration is typically defined in the ",(0,o.jsx)(n.code,{children:"config/app.php"})," file:"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// config/app.php\nreturn [\n    'env' => $_ENV['APP_ENV'] ?? 'development',\n    'root' => dirname(__DIR__),\n    'slug' => 'my-app',\n    'providers' => [\n        // List of service providers\n        \\Orkestra\\Providers\\CommandsProvider::class,\n        \\Orkestra\\Providers\\HooksProvider::class,\n        \\App\\Providers\\AppServiceProvider::class,\n    ],\n    // Other configuration values\n];\n"})}),"\n",(0,o.jsx)(n.p,{children:"You can set configuration values manually:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// Set configuration values\n$app->config()->set('env', 'development');\n$app->config()->set('root', './');\n$app->config()->set('slug', 'my-app');\n\n// Configuration values can be validated\n$app->config()->validate();\n"})}),"\n",(0,o.jsx)(n.h2,{id:"hooking-into-configuration",children:"Hooking Into Configuration"}),"\n",(0,o.jsx)(n.p,{children:"To add custom configuration logic:"}),"\n",(0,o.jsxs)(n.ol,{children:["\n",(0,o.jsx)(n.li,{children:"Create additional configuration files:"}),"\n"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// config/database.php\nreturn [\n    'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',\n    'host' => $_ENV['DB_HOST'] ?? 'localhost',\n    'port' => (int)($_ENV['DB_PORT'] ?? 3306),\n    'name' => $_ENV['DB_NAME'],\n    'user' => $_ENV['DB_USER'],\n    'password' => $_ENV['DB_PASSWORD'],\n];\n\n// In bootstrap/app.php\n$app->config()->set('database', require __DIR__ . '/../config/database.php');\n"})}),"\n",(0,o.jsxs)(n.ol,{start:"2",children:["\n",(0,o.jsx)(n.li,{children:"Add configuration validation:"}),"\n"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// In a service provider's register method\npublic function register(App $app): void\n{\n    $app->config()->set('validation', [\n        'database' => function ($value) {\n            // Validate database configuration\n            return isset($value['host']) && isset($value['name']);\n        }\n    ]);\n}\n"})}),"\n",(0,o.jsx)(n.h3,{id:"configuration-validation",children:"Configuration Validation"}),"\n",(0,o.jsx)(n.p,{children:"Orkestra validates configuration values against defined rules:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// Define configuration schema\n$config = new Configuration([\n    'definition' => [\n        'key1' => ['Description of key1', 'default1'],\n        'key2' => ['Description of key2', null], // Required value (null default)\n    ],\n    'validation' => [\n        'key1' => fn ($value) => $value === 'validValue',\n    ],\n]);\n\n// Set and validate\n$config->set('key1', 'validValue');\n$config->set('key2', 'someValue');\n$config->validate(); // Will throw exception if validation fails\n"})}),"\n",(0,o.jsx)(n.h2,{id:"provider-registration-phase",children:"Provider Registration Phase"}),"\n",(0,o.jsxs)(n.p,{children:["Service providers are registered with the application. In the skeleton repository, providers are typically listed in the ",(0,o.jsx)(n.code,{children:"config/app.php"})," file:"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// config/app.php\nreturn [\n    'providers' => [\n        \\Orkestra\\Providers\\CommandsProvider::class,\n        \\Orkestra\\Providers\\HooksProvider::class,\n        \\Orkestra\\Providers\\HttpProvider::class,\n        \\Orkestra\\Providers\\ViewProvider::class,\n        \\App\\Providers\\AppServiceProvider::class,\n    ],\n];\n"})}),"\n",(0,o.jsx)(n.p,{children:"You can also register providers manually:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// Register providers\n$app->provider(MyServiceProvider::class);\n$app->provider(AnotherServiceProvider::class);\n"})}),"\n",(0,o.jsxs)(n.p,{children:["Service providers must implement the ",(0,o.jsx)(n.code,{children:"ProviderInterface"}),":"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"use Orkestra\\App;\nuse Orkestra\\Interfaces\\ProviderInterface;\n\nclass MyServiceProvider implements ProviderInterface\n{\n    public function register(App $app): void\n    {\n        // Register services in container\n        $app->bind(MyService::class, fn() => new MyService());\n    }\n    \n    public function boot(App $app): void\n    {\n        // Initialize services, run setup tasks\n        $app->get(MyService::class)->initialize();\n    }\n}\n"})}),"\n",(0,o.jsx)(n.h2,{id:"hooking-into-provider-registration",children:"Hooking Into Provider Registration"}),"\n",(0,o.jsx)(n.p,{children:"To customize provider registration:"}),"\n",(0,o.jsxs)(n.ol,{children:["\n",(0,o.jsx)(n.li,{children:"Create custom service providers:"}),"\n"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"namespace App\\Providers;\n\nuse Orkestra\\App;\nuse Orkestra\\Interfaces\\ProviderInterface;\n\nclass CustomProvider implements ProviderInterface\n{\n    public function register(App $app): void\n    {\n        // Registration logic\n    }\n    \n    public function boot(App $app): void\n    {\n        // Boot logic\n    }\n}\n\n// Add to config/app.php's providers array\n// Or register manually in bootstrap/app.php\n$app->provider(\\App\\Providers\\CustomProvider::class);\n"})}),"\n",(0,o.jsxs)(n.ol,{start:"2",children:["\n",(0,o.jsx)(n.li,{children:"Extend existing providers:"}),"\n"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"namespace App\\Providers;\n\nuse Orkestra\\Providers\\HttpProvider;\n\nclass ExtendedHttpProvider extends HttpProvider\n{\n    public function register(App $app): void\n    {\n        parent::register($app);\n        // Additional registration logic\n    }\n    \n    public function boot(App $app): void\n    {\n        parent::boot($app);\n        // Additional boot logic\n    }\n}\n\n// In config/app.php, replace HttpProvider with ExtendedHttpProvider\n"})}),"\n",(0,o.jsx)(n.h2,{id:"provider-bootstrapping-phase",children:"Provider Bootstrapping Phase"}),"\n",(0,o.jsx)(n.p,{children:"After registration, providers are booted in sequence. In the skeleton repository, this is typically handled in the public entry point:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// public/index.php\n$app = require_once __DIR__ . '/../bootstrap/app.php';\n\n// Boot the application\n$app->boot();\n\n// Handle the request\n$kernel = $app->get(\\Orkestra\\Services\\Http\\Kernel::class);\n$response = $kernel->handle($request);\n$response->send();\n"})}),"\n",(0,o.jsx)(n.p,{children:"You can also boot the application manually:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// Initialize all registered providers\n$app->boot();\n"})}),"\n",(0,o.jsx)(n.p,{children:"During this phase:"}),"\n",(0,o.jsxs)(n.ol,{children:["\n",(0,o.jsx)(n.li,{children:"The application validates the environment, root path, and slug"}),"\n",(0,o.jsxs)(n.li,{children:["Each provider's ",(0,o.jsx)(n.code,{children:"register()"})," method is called on all providers first"]}),"\n",(0,o.jsxs)(n.li,{children:["Then each provider's ",(0,o.jsx)(n.code,{children:"boot()"})," method is called in the order they were registered"]}),"\n",(0,o.jsx)(n.li,{children:"Service dependencies are resolved and ready to use"}),"\n"]}),"\n",(0,o.jsx)(n.h2,{id:"hooking-into-provider-bootstrapping",children:"Hooking Into Provider Bootstrapping"}),"\n",(0,o.jsxs)(n.p,{children:["The primary way to hook into bootstrapping is through provider ",(0,o.jsx)(n.code,{children:"boot()"})," methods:"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"public function boot(App $app): void\n{\n    // Access configuration\n    $debug = $app->config()->get('app.debug');\n    \n    // Use hooks (if HooksProvider is available)\n    $hooks = $app->get(\\Orkestra\\Services\\Hooks\\Interfaces\\HooksInterface::class);\n    $hooks->addListener('application.booted', function() {\n        // Run after all providers are booted\n    });\n    \n    // Initialize services\n    $myService = $app->get(MyService::class);\n    $myService->initialize();\n}\n"})}),"\n",(0,o.jsx)(n.h2,{id:"execution-phase",children:"Execution Phase"}),"\n",(0,o.jsx)(n.p,{children:"Once booted, the application handles requests through the appropriate channels (HTTP, CLI, etc.). In the skeleton repository, this is typically handled in the entry point files:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// For HTTP requests (public/index.php)\n$kernel = $app->get(\\Orkestra\\Services\\Http\\Kernel::class);\n$response = $kernel->handle($request);\n$response->send();\n\n// For CLI commands (maestro)\n$runner = $app->get(\\Orkestra\\Services\\Commands\\Runner::class);\n$runner->run();\n"})}),"\n",(0,o.jsx)(n.h2,{id:"hooking-into-execution",children:"Hooking Into Execution"}),"\n",(0,o.jsx)(n.p,{children:"To customize the execution phase:"}),"\n",(0,o.jsxs)(n.ol,{children:["\n",(0,o.jsx)(n.li,{children:"Create custom controllers or command handlers:"}),"\n"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"namespace App\\Controllers;\n\nuse Orkestra\\Services\\Http\\Controllers\\AbstractController;\n\nclass HomeController extends AbstractController\n{\n    public function index()\n    {\n        return $this->view('home', ['title' => 'Welcome']);\n    }\n}\n\n// Register in routes (config/routes.php)\nreturn [\n    'GET /' => 'App\\Controllers\\HomeController@index'\n];\n"})}),"\n",(0,o.jsxs)(n.ol,{start:"2",children:["\n",(0,o.jsx)(n.li,{children:"Use middleware (if HttpProvider is used):"}),"\n"]}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"namespace App\\Middleware;\n\nuse Closure;\nuse Psr\\Http\\Message\\ServerRequestInterface;\nuse Psr\\Http\\Message\\ResponseInterface;\n\nclass AuthMiddleware\n{\n    public function handle(ServerRequestInterface $request, Closure $next): ResponseInterface\n    {\n        // Add middleware logic\n        if (!$this->isAuthenticated($request)) {\n            return redirect('/login');\n        }\n        \n        return $next($request);\n    }\n}\n\n// Register in routes or providers\n"})}),"\n",(0,o.jsx)(n.h2,{id:"service-container-usage-during-lifecycle",children:"Service Container Usage During Lifecycle"}),"\n",(0,o.jsx)(n.p,{children:"The container is the backbone of the application lifecycle:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// Register services during provider registration\npublic function register(App $app): void\n{\n    $app->bind(MyInterface::class, MyImplementation::class);\n    \n    // Sophisticated bindings\n    $app->bind(ComplexService::class, function() {\n        $instance = new ComplexService();\n        $instance->setLogger(new Logger());\n        return $instance;\n    });\n}\n\n// Use services during the boot phase\npublic function boot(App $app): void\n{\n    $service = $app->get(MyInterface::class);\n    $service->doSomething();\n}\n"})}),"\n",(0,o.jsx)(n.h2,{id:"important-considerations",children:"Important Considerations"}),"\n",(0,o.jsx)(n.h3,{id:"boot-only-once",children:"Boot Only Once"}),"\n",(0,o.jsx)(n.p,{children:"An application can only be booted once. Attempting to boot multiple times will throw an exception:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"$app->boot();\n$app->boot(); // Throws Exception\n"})}),"\n",(0,o.jsx)(n.h3,{id:"service-access-before-boot",children:"Service Access Before Boot"}),"\n",(0,o.jsx)(n.p,{children:"You cannot access services from the container before the application is booted:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"$app = new App(new Configuration());\n$app->get('service'); // Throws BadMethodCallException\n\n// Correct approach\n$app->boot();\n$app->get('service'); // Works after booting\n"})}),"\n",(0,o.jsx)(n.h3,{id:"configuration-validation-1",children:"Configuration Validation"}),"\n",(0,o.jsx)(n.p,{children:"Invalid configurations will throw exceptions during boot:"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{className:"language-php",children:"// Invalid environment\n$app->config()->set('env', 'invalidEnv');\n$app->boot(); // Throws InvalidArgumentException\n\n// Invalid root path\n$app->config()->set('root', 'invalidRoot');\n$app->boot(); // Throws InvalidArgumentException\n\n// Invalid slug format\n$app->config()->set('slug', 'invalid slug!');\n$app->boot(); // Throws InvalidArgumentException\n"})}),"\n",(0,o.jsx)(n.h2,{id:"lifecycle-diagram",children:"Lifecycle Diagram"}),"\n",(0,o.jsx)(n.pre,{children:(0,o.jsx)(n.code,{children:"\u250c\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510      \u250c\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510      \u250c\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510\n\u2502  Initialization \u2502 \u2500\u2500\u25b6  \u2502  Configuration  \u2502 \u2500\u2500\u25b6  \u2502    Provider    \u2502\n\u2502                 \u2502      \u2502                 \u2502      \u2502  Registration   \u2502\n\u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518      \u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518      \u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518\n         \u2502                                                 \u2502\n         \u2502                                                 \u25bc\n\u250c\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510      \u250c\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510      \u250c\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510\n\u2502   Termination   \u2502 \u25c0\u2500\u2500  \u2502    Execution    \u2502 \u25c0\u2500\u2500 \u2502    Provider     \u2502\n\u2502                 \u2502      \u2502                 \u2502      \u2502  Bootstrapping  \u2502\n\u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518      \u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518      \u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518\n"})}),"\n",(0,o.jsx)(n.h2,{id:"best-practices",children:"Best Practices"}),"\n",(0,o.jsxs)(n.ol,{children:["\n",(0,o.jsxs)(n.li,{children:["\n",(0,o.jsxs)(n.p,{children:[(0,o.jsx)(n.strong,{children:"Register in Register, Initialize in Boot"}),": Use the ",(0,o.jsx)(n.code,{children:"register()"})," method for binding services and the ",(0,o.jsx)(n.code,{children:"boot()"})," method for initializing them."]}),"\n"]}),"\n",(0,o.jsxs)(n.li,{children:["\n",(0,o.jsxs)(n.p,{children:[(0,o.jsx)(n.strong,{children:"Keep Providers Focused"}),": Each provider should have a specific responsibility."]}),"\n"]}),"\n",(0,o.jsxs)(n.li,{children:["\n",(0,o.jsxs)(n.p,{children:[(0,o.jsx)(n.strong,{children:"Validate Configuration Early"}),": Use configuration validation to catch issues before they cause runtime errors."]}),"\n"]}),"\n",(0,o.jsxs)(n.li,{children:["\n",(0,o.jsxs)(n.p,{children:[(0,o.jsx)(n.strong,{children:"Avoid Circular Dependencies"}),": Be careful not to create circular dependencies between providers."]}),"\n"]}),"\n",(0,o.jsxs)(n.li,{children:["\n",(0,o.jsxs)(n.p,{children:[(0,o.jsx)(n.strong,{children:"Leverage Configuration Files"}),": Keep configuration in dedicated files (like ",(0,o.jsx)(n.code,{children:"config/app.php"}),", ",(0,o.jsx)(n.code,{children:"config/database.php"}),") rather than setting values directly in code."]}),"\n"]}),"\n"]}),"\n",(0,o.jsx)(n.h2,{id:"related-topics",children:"Related Topics"}),"\n",(0,o.jsxs)(n.ul,{children:["\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.a,{href:"/docs/guides/providers",children:"Service Providers"})," - Deep dive into creating and using service providers"]}),"\n",(0,o.jsxs)(n.li,{children:[(0,o.jsx)(n.a,{href:"/docs/core-concepts/dependency-injection",children:"Dependency Injection"})," - Understand the service container"]}),"\n"]})]})}function d(e={}){const{wrapper:n}={...(0,s.R)(),...e.components};return n?(0,o.jsx)(n,{...e,children:(0,o.jsx)(p,{...e})}):p(e)}},8453:(e,n,i)=>{i.d(n,{R:()=>t,x:()=>a});var r=i(6540);const o={},s=r.createContext(o);function t(e){const n=r.useContext(s);return r.useMemo((function(){return"function"==typeof e?e(n):{...n,...e}}),[n,e])}function a(e){let n;return n=e.disableParentContext?"function"==typeof e.components?e.components(o):e.components||o:t(e.components),r.createElement(s.Provider,{value:n},e.children)}}}]);