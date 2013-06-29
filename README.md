# memex

A system for remembering stuff. Emails, notes and bookmarks, stored online, accessible via a REST API.

## Installing memex

Memex is designed to run on any entry-level web server. It requires a vaguely modern version of PHP and a MySQL database.

1. Check out this repository into your server's public web directory.

2. Create a file called `config-secret.php` in the `/api` directory, following the instructions in the main `config.php` file. Fill it with your database credentials, and make up two apikeys for authenticating memex requests, as required.

3. Uncomment `# setup_database()` on line 10 of `/api/index.php`

4. Visit `http://<your-website>/api`. You should see the memex api documentation.

5. You should then re-comment the `setup_database()` command on line 10 of `/api/index.php` to avoid slowing the API down with database setup commands before every request.

## Accessing memex

Each instance of memex is designed to serve one single person or organisation. There are no user accounts, just two apikeys. One allows read-only access, the other read-and-write access. These apikeys are defined in `/api/config-secret.php` and can be as simple or complicated as you wish.

Each request to the memex API must include an `apikey` parameter, either in the query string (for GET requests) or as a body parameter (in POST, PUT and DELETE requests).

The memex login page also asks for an apikey before letting you see or edit your memex instance. The javascript that runs the memex frontend simply passes this apikey to the server with each API request.

## Using memex

The API is self-documenting. Make HTTP queries using the GET, POST, PUT, DELETE and OPTIONS methods. Remember to supply an apikey as a parameter along with each request you make.

Visit `http://<your-website>/api` for more details.
