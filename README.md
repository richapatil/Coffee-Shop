# Coffee Shop WordPress Site

A simple coffee shop website built with WordPress. Customers can view the menu, place an order and check their order status. Staff log in to manage products and update orders.

[Try it in your browser](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/richapatil/Coffee-Shop/main/blueprint.json)

## What's inside

- `coffee-shop/` - plugin for products, stock and orders
- `coffee-shop-theme/` - theme for the website design

Built with WordPress custom post types, shortcodes and hooks.

## Features

- Menu with prices, grouped by category
- Order form with validation
- Stock goes down with each order, and items show "Sold out" at 0
- Order status board with Preparing and Ready for Pickup
- Staff can mark orders Ready or Completed

## Run locally

You need [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running.

Start the project from this folder:

```bash
docker compose up -d
```

First time setup:

1. Open http://localhost:8080 and install WordPress (site title, username, password)
2. In wp-admin, go to Plugins and activate **Coffee Shop**
3. Go to Appearance > Themes and activate **Coffee Shop Theme**
4. Open http://localhost:8080 to see the website. Staff log in at http://localhost:8080/wp-admin

## Useful commands

| Command | What it does |
|---|---|
| `docker compose up -d` | Start the project |
| `docker compose stop` | Pause the project, everything is kept |
| `docker compose start` | Resume after a pause |
| `docker compose down` | Shut down, your data is kept |
| `docker compose down -v` | Shut down and delete all data to start fresh |
