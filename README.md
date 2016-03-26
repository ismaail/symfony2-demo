# Symfony 2 - Demo Project

### Setup

Copy `app/config/parameters.yml.dist` to `app/config/parameters.yml` and update it for your env.


---

### Database

Create database tables

```
php app/console doctrine:schema:update --force
```

Populate the tables with dummy data

```
php app/console doctrine:fixtures:load
```

---

### To login

- username: admin
- password: secret

---

### Tests

```
composer test
```

or 

```
./bin/phpunit -c app/
```
