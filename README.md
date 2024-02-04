# Matrix - Hyperf Multi-Tenant Library
Matrix is a comprehensive multi-tenant library designed to seamlessly integrate multi-tenancy into your Hyperf applications. It provides an easy-to-use and flexible system to manage multiple tenants, ensuring data isolation, dynamic database connection management, and tenant identification based on request parameters.

### Features
- Dynamic Tenant Database Connections: Automatically switch database connections based on the identified tenant, ensuring that each tenant's data remains isolated and secure.
- Customizable Tenant Identification: Flexibly define how tenants are identified from incoming requests, whether by subdomain, path, headers, or other custom strategies.
- Extensible Architecture: Easily extend or override default behaviors with custom implementations, thanks to the library's use of Hyperf's dependency injection and configuration systems.
- Command for Publishing Migrations: Includes a command to publish necessary migrations to the application, making setup quick and straightforward.

### Installation
To install Matrix, run the following command in the root of your Hyperf project:

```shell
composer require ananiaslitz/matrix
```
