## 🐾 SourceStream

**SourceStream** is a **news aggregation system** designed to collect and manage articles from multiple external sources such as **NewsAPI, The Guardian, and The New York Times**.

It serves as a **centralized storage and distribution layer** that:

* Regularly fetches and updates content from live news sources.
* Normalizes and organizes the data for consistent access.
* Provides a reliable API interface for frontend applications to consume fresh, structured news content.

By acting as a **data hub**, SourceStream ensures that clients always have access to the latest articles without needing to query third-party APIs directly.

## 📦 Getting Started

### ✅ Prerequisites

Make sure you have the following installed:

- [Docker](https://www.docker.com/)

### 🚀 Installation

Follow the steps below to set up the application locally:

1. **Clone the repository**
   ```bash
      git clone https://github.com/Abdul-Qoyyum/Sourcestream.git
   ```
2. **Navigate to the project directory**
    ```bash
       cd Sourcestream
    ```

3. **Duplicate the `.env.example` file and rename it to `.env`**
    ```bash
       cp .env.example .env
    ```

3. **Set Up Environment Variables** <br/><br/>

   Open the `.env` file and update the necessary environment variables (`DB_PASSWORD`, `NEWSAPI_KEY`, `GUARDIAN_API_KEY`, `NYTIMES_API_KEY`) as required. Since the application is running in Docker, it is recommended to keep the value of `DB_HOST` unchanged.
   <br/><br/>

4. **Start the application using Docker**
   ```bash
      docker compose up -d --build
   ```
5. **Accessing the API Documentation**  
   Open your web browser and navigate to:  [http://localhost:8197/api/documentation#/Articles](http://localhost:8197/api/documentation#/Articles) <br/><br/>

6. **Accessing the Docker MySQL Database**  
   You can connect to the database using a MySQL client by providing the `DB_USERNAME` and `DB_PASSWORD` credentials. The connection should be made to `localhost` on port `3307`.

## 🧪 Running Tests
1. Execute this command:
   ```bash
    docker compose exec app php artisan test
   ```

## 📂 Project Structure
```angular2html
    SourceStream/
    ├── app/
    │   ├── Console/
    │   │   ├── Commands/
    │   │   └── Kernel.php
    │   ├── Http/
    │   │   ├── Contracts/
    │   │   ├── Controllers/
    │   │   ├── Services/
    │   │   └── Traits/
    │   ├── Models/
    │   └── Providers/
    ├── bootstrap/
    │   └── cache/
    │       ├── app.php
    │       └── providers.php
    ├── config/
    ├── database/
    │   ├── factories/
    │   ├── migrations/
    │   ├── seeders/
    │   └── database.sqlite
    ├── docker/
    │   ├── nginx/
    │   └── php/
    ├── public/
    └── resources/
    └── tests/
    └── ...
```

## 🛠️ Technologies Used
- Php
- Docker
- Nginx
- MySQL

## 🙋‍♂️ Contributing
Contributions are welcome! Please fork the repository and submit a pull request.

