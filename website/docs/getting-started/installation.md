---
sidebar_position: 1
---

# Installation

## Prerequisites

Before you begin, ensure you have the following prerequisites installed:

- PHP 8.2 or higher
- Composer

## Using the Skeleton

The easiest way to get started with Orkestra is to use the official skeleton project. This will create a new project with all the necessary files and configurations.

```bash
composer create-project luccpl/orkestra-skeleton {project_name}
cd {project_name}
```

## Project Structure

After installation, your project will have the following structure:

```
{project_name}/
├── app/
│   ├── Controllers/
│   ├── Views/
│   └── Providers/
├── config/
│   ├── app.php
│   └── routes.php
├── public/
├── storage/
├── vendor/
├── composer.json
└── maestro
```

## Starting the Development Server

Once you have created your project, you can start the development server using the following command:

```bash
php maestro app:serve
```

This will start a development server, typically at `http://localhost:8000`. You can access your application by opening this URL in your web browser.

## Module Structure

**For large projects**, we recommend to follow a modular structure, avoiding nesting different services in your project, for this you can change your composer.json file to autoload packages from a `./modules` directory and then encapsulate each part of your application in a separated module:

```json
// composer.json
{
    "autoload": {
        "psr-4": {
            "Modules\\": "modules/"
        }
    }
}
```

```
├── modules/
│   ├── Auth/
|   |   ├── Commands/
│   │   ├── Controllers/
│   │   ├── Actions/
│   │   ├── AuthProvider.php
│   └── Subscriptions/
│   │   ├── Controllers/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   ├── SubscriptionsProvider.php
```

## Next Steps

- [Configuration](/docs/getting-started/configuration) - Learn how to configure your Orkestra application
- [Routing](/docs/guides/routing) - Define routes for your application
- [Controllers](/docs/guides/controllers) - Create controllers for your application
- [Views](/docs/guides/views) - Create views for your application
- [Service Providers](/docs/guides/providers) - Register services for your application 