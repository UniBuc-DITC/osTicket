# osTicket - custom version

This is a customized version of osTicket, adapted for the University of Bucharest's needs.

The original `README` can be found [here](README.original.md).

## Running locally with Docker

You can run a local instance of osTicket using [Docker](https://www.docker.com/) and [docker-compose](https://docs.docker.com/compose/).

If it's the first time you're creating the containers, or after you've deleted the database,
you should copy the `include/ost-sampleconfig.php` file to `docker/ost-config.php`
to run the installer.

You might also have to change the permissions on the local file when doing the install:

```shell
chmod 0666 docker/ost-config.php
```

Start the services using:

```sh
docker-compose up
```

This starts a container with an Apache web server and a container with a MySQL database.
You can access the local instance at `http://localhost:8080`.

When creating the admin account, it's recommended to use some easy-to-remember credentials such as:

- Email: `admin@example.com`
- Username: `administrator` (osTicket forbids the `admin` username)
- Password: `Test1234`

Make sure you specify the following database settings:

- MySQL Hostname: `mysql`
- MySQL Database: `osticket`
- MySQL Username: `osticket`
- MySQL Password: `osticket_pwd`

(this is to match the settings on the `mysql` container)

## Troubleshooting common errors

**Issue:** I get a warning message `Warning: require(/var/www/html/include/ost-config.php): failed to open stream` when accessing the osTicket instance after startup.

**Solution:** You need to create the `ost-config.php` file as described above. You might also have to remove a `include/ost-config.php` _directory_ before you can create that file, if Docker automatically created it by accident for you.

**Issue:** I created the `ost-config.php` file as described but now I get a `Error response from daemon: not a directory` error.

**Solution:** Caused by [this](https://github.com/docker/for-win/issues/9823) Docker for Windows issue. You will have to delete any empty directories in the `/mnt/wsl/docker-desktop-bind-mounts/<...>` directory.


## Debugging with Xdebug

The development Docker image supports step-by-step debugging using [the Xdebug plugin](https://xdebug.org/).

Before using this feature, you might want to review and change the configuration settings in `docker/php/conf.d/xdebug.ini`

You will also need to configure your IDE. For VS Code you can use [the PHP Debug extension](https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug). For PhpStorm you can follow [these instructions](https://phauer.com/2017/debug-php-docker-container-idea-phpstorm/).

## Updating to a new osTicket version

### Fetching new osTicket versions

To update to a new version of osTicket, you'll first need to make sure the upstream repo is available as a Git remote. If it's the first time you're performing such an update, use the following command:

```sh
git remote add upstream git@github.com:osTicket/osTicket.git
```

After adding the new remote, or whenever you want to get access locally to new versions/updates, run:

```sh
git fetch upstream
```

### Rebasing the custom changes

**Warning:** These commands could be dangerous. Make sure you've read them at least once before executing them, and don't force-push your changes to the repo until you're sure of the final result.

**In case of emergency:** If at any point you believe you've messed up the process and don't understand what's going on, use `git rebase --abort`, `git checkout custom` then `git reset --hard origin/custom`.

Use `git log` on the `custom` branch to find the latest stable version the custom changes are based upon. Basically, keep going through the commit history until you find the latest tagged one.

To perform the actual update, make sure **you're still on the `custom` branch** and that **your working directory is clean**. Then execute:

```sh
git rebase --onto <new-version-tag> <old-version-tag> custom
```

where:
- `<old-version-tag>` is the tag/commit ID of the old version, e.g. `v1.14.1`
- `<new-version-tag>` is the tag/commit ID of the new version, e.g. `v1.14.2`

Expect to encounter some merge conflicts. These are caused by the upstream code changing in a way which **potentially breaks our custom changes**.
If you're updating from one minor version to another minor version, the conflicts _should_ be relatively easy to solve.

### Pushing the update to `origin`

Once you're finished and **you've tested the updated code locally** you can now force-push the changes to the shared repository. To do so, run:

```sh
git push --force-with-lease
```

(the `--force-with-lease` flag will make Git check that nobody else tried to push to the repository while you were doing your rebase, to avoid overwriting other developers' work)
