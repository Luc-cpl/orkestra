---
sidebar_position: 1
---

# Introduction to Orkestra

Orkestra is a lightweight and highly extensible PHP framework designed to provide a flexible foundation for web development. With a focus on ease of service provider addition, extensibility, and maintaining high code quality, Orkestra empowers developers to build robust and scalable applications.

## Key Features

### Service Provider Architecture
Easily expand the functionality of your application by adding service providers. Orkestra's modular design allows for simple integration of new components without compromising the core structure.

### Dependency Injection (DI) Container
Orkestra includes a powerful Dependency Injection container for managing class dependencies and performing dependency injection.

### MVC Pattern
Orkestra follows the Model-View-Controller (MVC) pattern, providing a clear and organized structure for your application. However, it does not enforce a specific model layer, allowing compatibility with any Object-Relational Mapping (ORM) tool.

### High Extensibility
Orkestra is designed with extensibility in mind. Leverage the flexibility of the framework to adapt and extend its functionality according to the specific needs of your project.

### Code Quality
Prioritizing clean and maintainable code, Orkestra encourages best practices and follows coding standards to ensure a reliable and efficient development experience.

## Quick Start

To kickstart a new project using Orkestra Skeleton, use the following Composer command:

```bash
composer create-project luccpl/orkestra-skeleton {project_name}
cd {project_name}
php maestro app:serve
```

## Requirements

- PHP 8.2 or higher
- Composer

## Documentation Structure

Our documentation is organized into several main sections:

- **Getting Started**: Installation and basic setup
- **Core Concepts**: Understanding the fundamental components
- **Advanced Topics**: Deep dives into specific features
- **Examples**: Real-world usage examples
- **API Reference**: Detailed technical documentation

## Contributing

We welcome contributions! Please see our [contribution guidelines](/docs/contributing/guidelines) for details on how to get started.

## License

This project is licensed under the [MIT License](/docs/license). 