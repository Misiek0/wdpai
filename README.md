Database Setup Instructions

This project uses PostgreSQL as the database.
To set up and initialize the database correctly, please follow these steps:

1. Start the Project

docker compose up --build

This will start the web server, PHP service, PostgreSQL database, and pgAdmin.

2. Connect to the Database

You can connect using:

- pgAdmin:
  Open http://localhost:5050 and log in using:
  - Email: admin@example.com
  - Password: admin
  
- psql CLI (optional):
  If you have psql installed locally:
  psql -h localhost -p 5433 -U docker -d db
  Password: docker

3. Create Database Structure

After connecting to the database:

Load and execute the SQL file located at: schema.sql


4. Done ✅

Your database is now ready with all necessary tables, and you can start using the application.

Important Notes

- Volumes:
  The database data is stored in a Docker volume (pg-data) and will persist even after docker compose down, unless volumes are explicitly removed.

- New Developers:
  After cloning the project, developers must manually initialize the database by running the schema.sql file.
