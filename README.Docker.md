### Environments

- Production (default): config.ini targets the cPanel MySQL `s1105.usc1.mysecurecloudhost.com:3306` with schema/user `jerlanlo_pbe_hckonsulta`.
- Development: config.dev.ini targets Docker compose (host `mysql`, schema `konsulta`, user `ekon_app_user`).
- Production template: config.prod.ini mirrors the live cPanel database and can restore the default.

Switch environment (CLI-only helper copies template to config.ini):

```
php setenv.php dev   # use Docker dev settings
php setenv.php prod  # revert to production defaults
```

### Development with Docker

1) Select dev config
```
php setenv.php dev
```

2) Start services (imports konsulta_100725.sql on a clean volume)
```
docker compose up -d --build
```

3) App URL
```
http://localhost:9000
```

### PHP extensions
Extensions are installed in the Dockerfile via `docker-php-ext-install pdo pdo_mysql mysqli`. Add more there if needed.

## just a test 