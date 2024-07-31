## Calendar Event Management

A calendar event management system which allows users to add, update, list and delete events using REST APIs.

## Git Clone

Follow below command to clone repository

`git clone git@github.com:kevinmakwana/calendar-event-management.git`

`cd calendar-event-management`

## First Time Set-up for Local Development

To get started with local development, follow these steps. Make sure you run all commands from the top-level of your project.

### DNS resolution

> Decide on a local domain to use for this project. Substitute that any place you see `api.calendar-event-management.test` or `calendar-event-managemen` below.

By default, this project will run on the host `api.calendar-event-management.test`. This requires some sort of local DNS resolution for that hostname
to your localhost IP address. One easy way to do this for the entire `.test` top-level domain, is to run a lightweight tool
called `dnsmasq`. You can install it via Homebrew on a Mac with: `brew install dnsmasq`.

> If you've ever setup Valet, it already installed dnsmasq for you. You can verify if it's already installed by running
> `brew services` and see if `dnsmasq` is listed.

If you don't want to run `dnsmasq`, you can also add a manual DNS entry to your `/etc/hosts` file in the form:
`127.0.0.1 api.calendar-event-management.test`

### Setting up an SSL certificate

First, you'll need to create an SSL certificate that can be used inside Docker. We'll use `mkcert`, a simple tool for
making locally-trusted development certificates.

To install on Mac with Homebrew, you can run:

`brew install mkcert nss`

For other platforms or configurations, you can refer to the [mkcert README](https://github.com/FiloSottile/mkcert)

Once `mkcert` is installed, we need to generate our local development root certificate authority:

> If you've used `mkcert` before, you can skip this step
> This command will likely prompt you for administrator access on macOS

`mkcert -install`

Then, generate the certificates for this project and put them into a location accessible to your docker setup:

`mkcert -cert-file docker/nginx/ssl.pem -key-file docker/nginx/key.pem  api.calendar-event-management.test`

### Get the project running in Docker

Docker is used for local development. It's self-contained, easy to set up, and matches the exact versions of key services
running in production. It requires that you have [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed.

**Setup the environment**

##### Make a copy of the example env file:

`cp .env.example .env`

Open the `.env` file and review the settings prefixed with `DOCKER_`. The defaults should work, but if you want a different
host name, or to change the port numbers, make those modifications before continuing with the Docker setup.

**Get Docker running**

Most of our Docker configuration is managed with Docker Compose. You can view the `docker-compose.yml` file to see how it
is configured.

To build and bring up the Docker containers, run `docker compose up -d`. If this is your first time running the command, it
may take a few minutes to pull down images and build the containers. Subsequent runs will be much quicker.

You can verify that your containers are in a running state with `docker compose ps`.

**Normal project setup**

With the certificates, our environment, and Docker setup, the rest of these steps will be typical steps for any Laravel project. The one key difference is that instead of running tools like composer and artisan directly, we need to run them from inside the container. This is very important. If we run the tools from our host environment, all the guarantees about versions of tooling will no longer apply.

To make it easier to run tools via Docker, a collection of simple shell scripts exists in the project's `docker/bin` directory.

Run these commands to finish the local development setup

* `docker/bin/composer install`
* `docker/bin/composer run post-create-project-cmd`
* `docker/bin/composer test`  (to run all test cases)

You can also use any normal database management tools and connect to the database using the port specified in `.env`.

## Normal Developer Workflow

Generally speaking, you don't want your Docker containers running unless you're actively using them. So each time you're
ready to start development, you would start your Docker environment:

`docker compose up -d`

> The `-d` means "detach" and it runs the containers in the background, so you can continue to use your terminal. If you run
> without `-d`, the terminal would remain bound to the containers, and you'd see a stream of container log output in your
> terminal. This can be useful when debugging a Docker issue, but for normal development, running in the background is best.

Then, when you're done for the day, you can stop the Docker environment:

`docker compose stop`

> There is also a `down` command, and it might seem more logical as the opposite action of `up`. This command not only stops the containers, but removes them along with the Docker network. This doesn't harm anything, and no data will be lost if you run `down` instead, but there's no need to constantly remove and recreate the containers, so `stop` is a better choice.

## Running Additional Composer Scripts

To ensure code quality and standards, you can run the following Composer scripts:

* To check code standards:  `docker/bin/composer phpcs`
* To automatically fix code standards: `docker/bin/composer phpcs-fix`
* To run static analysis: `docker/bin/composer larastan`
* To run PHP Mess Detector: `docker/bin/composer phpmd`
* To run the vulnerability check on composer.lock packages: `docker/bin/composer security-checker`
* To update the IDE helper files: `docker/bin/composer ide-helper-update`
* To run phpcs, phpmd, larastan, security-checker, test all togather: `docker/bin/composer check-everything`

## Postman Collection

* You can find postman collection inside `postman` directory.

## Deployment / CI Process

The application has not yet been deployed to production. This section will be updated with deployment instructions later.
