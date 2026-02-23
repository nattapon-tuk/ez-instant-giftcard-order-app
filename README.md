# EZ Instant Gift Card Order App

## 📦 Installation & Setup Guide

### Clone the Repository

- git clone https://github.com/nattapon-tuk/ez-instant-giftcard-order-app.git
- cd /ez-instant-giftcard-order-app

------------------------------------------------------------------------

## 🚀 Running the Application

You can run the Laravel application using **Docker (recommended)** or
directly on your local machine.

------------------------------------------------------------------------

## ✅ Option 1: Run with Docker (Recommended)

### Prerequisites

-   Docker Desktop (can download from https://docs.docker.com/get-started/get-docker/)

### Setup Steps

1.  Copy environment file:
    > cp .env.example .env

2.  Configure required environment variables inside `.env`:
    > EZ_BASE_URL, EZ_API_KEY, EZ_ACCESS_TOKEN, EZ_SKU

3.  Build and start containers:
    > docker-compose up -d --build

4.  Access the application:
    > go to http://localhost:8000/

------------------------------------------------------------------------

## ✅ Option 2: Run Locally (Without Docker)

### Prerequisites

-   PHP 8.2+
-   Composer (latest version of PHP dependency manager, can download from https://getcomposer.org/)
-   Node.js & npm (can download from https://nodejs.org/en/download)

### Setup Steps

1.  Install dependencies and build assets:
    > composer install

    > npm install

    > npm run build 
    
2.  Copy environment file and generate app key:
    > cp .env.example .env

    > php artisan key:generate
    
3.  Run database migration (SQLite):
    > php artisan migrate --force
    
4.  Configure required environment variables inside `.env`:
    > EZ_BASE_URL, EZ_API_KEY, EZ_ACCESS_TOKEN, EZ_SKU

5.  Start the development server:
    > composer run dev

6.  Access the application:
    > go to http://localhost:8000/

------------------------------------------------------------------------

# 🔌 API Testing Guide

## Prerequisites

-   Postman (recommended) for quickly testing to send api request(can download from https://www.postman.com/downloads/)

------------------------------------------------------------------------

## 📌 Create New Order

**Endpoint**

POST http://localhost:8000/orders

**Expected Response**

``` json
{
  "id": "localOrderId",
  "status": "PROCESSING | COMPLETED | CANCELLED"
}
```

------------------------------------------------------------------------

## 📌 Get Order Status

**Endpoint**

GET http://localhost:8000/orders/{localOrderId}

**Expected Response**

``` json
{
  "id": "localOrderId",
  "status": "PROCESSING | COMPLETED | CANCELLED",
  "redeemCode": "string | null"
}
```

------------------------------------------------------------------------

# 🧪 Running PHPUnit Tests

## Run Tests (Local)

> php artisan test

------------------------------------------------------------------------

## Run Tests (Inside Docker Container)

1.  Check running containers for getting container id:
    > docker ps

2.  Access the application container:
    > docker exec -it {CONTAINER_ID} bash


3.  Verify you are inside the project directory:
    > ls


4.  Run tests:
    > php artisan test


------------------------------------------------------------------------

# 📎 Notes

-   Ensure `.env` is properly configured before running the application.
-   Default application URL: http://localhost:8000
-   SQLite database is used for local development.


