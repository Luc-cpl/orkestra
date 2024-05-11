# Orkestra PHP Framework

<!-- badges area start -->
[![Packagist Version](https://img.shields.io/packagist/v/luccpl/orkestra)](https://packagist.org/packages/luccpl/orkestra)
[![Packagist License](https://img.shields.io/packagist/l/luccpl/orkestra)](https://github.com/Luc-cpl/orkestra/blob/main/LICENSE)
![PHPStan badge](https://img.shields.io/badge/PHPStan-level%209-brightgreen)
[![Coverage Status](https://coveralls.io/repos/github/Luc-cpl/orkestra/badge.svg)](https://coveralls.io/github/Luc-cpl/orkestra)
<!-- badges area end -->

Orkestra is a lightweight and highly extensible PHP framework designed to provide a flexible foundation for web development. With a focus on ease of service provider addition, extensibility, and maintaining high code quality, Orkestra empowers developers to build robust and scalable applications.

## Features

- **Service Provider Architecture:** Easily expand the functionality of your application by adding service providers. Orkestra's modular design allows for simple integration of new components without compromising the core structure.

- **Dependency Injection (DI) Container:** Orkestra includes a powerful Dependency Injection container for managing class dependencies and performing dependency injection.

- **MVC Pattern:** Orkestra follows the Model-View-Controller (MVC) pattern, providing a clear and organized structure for your application. However, it does not enforce a specific model layer, allowing compatibility with any Object-Relational Mapping (ORM) tool, such as Doctrine, or abstractions like WordPress WPDB and custom Query classes (see [Orkestra WP](https://github.com/Luc-cpl/orkestra-wp)).

- **High Extensibility:** Orkestra is designed with extensibility in mind. Leverage the flexibility of the framework to adapt and extend its functionality according to the specific needs of your project.

- **Code Quality:** Prioritizing clean and maintainable code, Orkestra encourages best practices and follows coding standards to ensure a reliable and efficient development experience.

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer

### Installation
To kickstart a new project using Orkestra Skeleton, use the following Composer command. Replace {project_name} with the desired name for your project.
```bash
composer create-project luccpl/orkestra-skeleton {project_name}
cd {project_name}
php maestro app:serve
```
Congratulations! Your Orkestra project is now up and running. Access it by navigating to the specified address in your web browser.

### Documentation
For more detailed information on using Orkestra, refer to the [official documentation](https://github.com/Luc-cpl/orkestra/wiki).

### Contributing
We welcome contributions! Please see our [contribution guidelines](https://github.com/Luc-cpl/orkestra/wiki/1.1-%E2%80%90-Contribution-Guidelines) for details on how to get started.

### License
This project is licensed under the [MIT License](https://github.com/Luc-cpl/orkestra/wiki/1.2-%E2%80%90-License).
