# Symfony 2 - Demo Project

[www.symfony-demo.dev](http://www.symfony-demo.dev)

Setup
---

Copy `app/config/parameters.yml.dist` to `app/config/parameters.yml` and update it for your env.


Database
---

Create the tables

```
php app/console doctrine:schema:update --force
```

Populate the tables with dummy data

```
php app/console doctrine:fixtures:load
```
