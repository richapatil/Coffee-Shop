# Coffee Shop Ordering Website

A WordPress website that helps a coffee shop take orders online, manage its menu and stock, and update order status.

**Live demo:** [Try it in your browser](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/richapatil/Coffee-Shop/main/blueprint.json) (no install needed)

**Tech:** PHP, WordPress, MariaDB, Docker, HTML/CSS

## What's inside

- `coffee-shop/` - plugin for products, stock and orders
- `coffee-shop-theme/` - theme for the website design

Built with WordPress custom post types, shortcodes and hooks.

## Features

- Online order form with validation
- Stock updates on every order, and items show "Sold out" at 0 so nothing is oversold
- 3-stage order workflow: Pending, Ready, Completed
- Staff update an order's status in one click
- Customers get an order number and follow it on a public status board, no account needed
- Menu grouped by category and managed from wp-admin

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
