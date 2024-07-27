<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Start containers

```bash
docker-compose up -d
```

## Create .env file

```bash
cp .env.example .env
```

## Attach shell

```bash
docker exec -it tichcop_app sh
```

> ### Install pakages
>
> ```bash
> composer install
> ```
>
> ### Generate App Key
>
> ```bash
> php artisan key:generate
> ```
>
> ### Migrate
>
> ```bash
> php artisan migrate
> ```
>
> ### Seeding
>
> ```bash
> php artisan db:seed
> ```
>
> ### [Laravel Request Docs](https://github.com/rakutentech/laravel-request-docs)
>
> Generate a static HTML and open api specification
>
> ```bash
> php artisan lrd:generate
> ```
>
> Docs HTML is generated inside `/public/docs/`
