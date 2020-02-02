# Deployment to Ubuntu on AWS EC2

This guide outlines steps for deploying a LAMP & React project to an EC2 Ubuntu instance on AWS. It assumes that you have already provisioned an EC2 instance and that you have SSH access to the instance. Some parts of this guide may have been covered during class, but they are recorded here for future reference.

**Note**: This guide may use "EC2 Instance" and "Ubuntu" interchangeably, because your EC2 instance _should_ be running the Ubuntu operating system.

Ubuntu is the "L" (Linux) in LAMP.

## Initial Setup

You will be deploying your project more than one time as the application gains functionality or bugs are discovered and fixed. There is some setup required for your **first** deployment that is not required for subsequent deployments.

When you deploy your **first** LAMP & React project to an EC2 instance on AWS, you'll need to make sure that some tools are installed on your EC2 instance. After they've been installed once, they won't need to be installed again for future deployments of the same project or the deployment of additional LAMP & React projects.

This guide assumes EC2 Ubuntu 18.04 so all setup commands will be based on that.

**Note**: It doesn't matter what directory you are in when installing these packages on Ubuntu.

**Note**: The default `ubuntu` user account of your EC2 instance does not have permission to install packages, so installation commands will need to start with `sudo` to temporarily use the `root` user account.

### Before Installing Anything

Be sure that your Ubuntu instance's package list is up-to-date.

```bash
sudo apt update
```

### Install Node.js and NPM

Your project is managed using NPM scripts in its `package.json`, including bundling your client modules with Webpack. Ubuntu does not come with Node.js and NPM preinstalled, so make sure they are installed now.

```bash
sudo apt install nodejs npm
```

### Install the MySQL Database Server

MySQL is the "M" in LAMP, so be sure that it is installed. This application will manage the database of your project.

```bash
sudo apt install mysql-server
```

Once MySQL is installed, it is important that you reconfigure MySQL's root user password as it defaults to the _very unsecure_ "root". Additionally, the default password mode of recent MySQL 5.7 releases is not supported by the PHP version we're using, so we'll be setting it to the older `mysql_native_password` mode.

1. Log in to `mysql`.
    ```bash
    sudo mysql -u root -p
    ```
1. When prompted for a password, type `root` and press enter. If you are not able to log in, then it is possible that you already changed your root password. Try that. If you are able to log in with your custom root password, then skip to installing Apache.
1. You should see a prompt like `mysql>` when logged in. Update the root password now. The semicolon at the end is required as this is a SQL command. Use a strong password!
    ```sql
    alter user 'root'@'localhost' identified with mysql_native_password by 'mysupersecurepassword';
    ```
1. MySQL should print back an OK response. Exit MySQL now by typing `exit` and pressing enter.

### Install the Apache Web Server

Apache is the "A" in LAMP, so be sure that it is installed.

```bash
sudo apt install apache2
```

URL Rewrites need to be enabled.

**Note**: The default `ubuntu` user account of your EC2 instance does not have permission to issue commands to Apache, so this command will need to start with `sudo` to temporarily use the `root` user account.

```bash
sudo a2enmod rewrite
```

### Install PHP 7.2

PHP is the "P" in LAMP, so be sure that it is installed. The main PHP interpreter program is needed as well as a few additional packages.

```bash
sudo apt install php php-mysql php-curl
```

### Enable Free SSL Certificates with CertBot

To make sure that communications between clients and your app are encrypted and private, you'll want to set up CertBot. The official instructions are located at https://certbot.eff.org/lets-encrypt/ubuntubionic-apache, but for now, you'll only be following a couple of steps from the original instructions.

1. Include the `certbot` package list in Ubuntu's available packages.
    ```bash
    sudo add-apt-repository ppa:certbot/certbot
    ```
1. Install `certbot` and the required plugins for Apache.
    ```bash
    sudo apt install certbot python-certbot-apache
    ```

### Initial Setup Complete!

Once the above are installed and configured, your EC2 instance is ready for LAMP & React projects.

## First Deployment

The first time you deploy a LAMP & React project you'll need to do some extra configuration, but when deploying updates of the application, these steps can be skipped.

This portion of the guide assumes that you have already registered a custom domain name with a provider like hover.com, namecheap.com, or name.com (to name a few). The guide also assumes that you have added an `A` record to your domain's DNS settings that points to the public IPV4 address of your EC2 instance.

### Create a Subdomain

Visit your domain name registrar and create a new `CNAME` DNS record for your project. The `CNAME` record should point to your main domain name.

> For example, if your domain name is `learningfuze.com` and your project's name is `lamp-react-project`, then you'll create a `CNAME` record for `lamp-react-project.learningfuze.com` that points to `learningfuze.com`.

### Create a Database

Assuming that your application is backed by a MySQL database, you'll need to create one now.

1. Sign into MySQL. Use your custom root password when prompted.
    ```bash
    mysql -u root -p
    ```
1. You should see a prompt like `mysql>` when logged in. Issue the following SQL command to create the database for your project. Replace `lampReactProject` with the name of your project. Use `camelCase` casing for the database name.
    ```sql
    create database `lampReactProject` character set utf8mb4 collate utf8mb4_unicode_ci;
    ```
1. MySQL should print back an OK response. Exit MySQL now by typing `exit` and pressing enter.

### Clone the Project

When you have SSH'd into your EC2 instance, you'll want to clone the project's source code into your home directory. Confirm your current working directory is `/home/ubuntu` with the `pwd` command.

```bash
pwd
```

Ubuntu comes with `git` preinstalled so you can clone the project now. Replace `username` with the owner of the repository and `lamp-react-project` with the name of the project. If the repository is private, you'll be prompted for your GitHub username and password.

```bash
git clone https://github.com/username/lamp-react-project
```

After the project is successfully cloned, running the `ls` command should show the project directory.

```bash
ls
```

The next few steps will be done from within the project directory, so change directories to the project. Replace `lamp-react-project` with the name of the project.

```bash
cd lamp-react-project
```

### Install NPM Packages

Although your `node_modules` directory should have been ignored (via `.gitignore`), the project should have all of its JavaScript dependencies listed in `package.json` so you can download them now.

```bash
npm install
```

### Bundle JavaScript Modules

Your project should have a `"build"` script in `package.json` that runs Webpack to bundle your front end modules into a `main.js` file. Run the script now.

```bash
npm run build
```

### Set Up the Database Params

Although your `server/api/_config.php` should have been ignored (via `.gitignore`), the project should include a template file at `server/api/_config.example.php`. Copy that file now to create a replacement `_config.php`.

```bash
cp server/api/_config.example.php server/api/_config.php
```

Use the `nano` command to edit the new `_config.php` to include your _real_ connection parameters. For this project, you'll be using your `root` user credentials, `localhost` as the `host`, and your project's database as the `database`.

```bash
nano server/api/_config.php
```

```php
<?php

$db_params = [
  'user' => 'root',
  'pass' => 'mysupersecurepassword',
  'host' => 'localhost',
  'database' => 'lampReactProject'
];
```

**Note**: In real production systems, it is not advisable to use the `root` MySQL user to connect to a database. But creating dedicated MySQL user accounts is beyond the scope of this guide. You are free to look it up if you want and help is available on request.

### Import the Latest Database Dump

Your team should be dumping and committing the project's database locally any time the schema is modified. There are scripts available in `package.json` for performing imports and exports. Now that your database params have been added to `_config.php`, you can import your database.

1. Run the NPM script to import your database. Use your custom root password when prompted.
    ```bash
    npm run db:import
    ```
1. Sign into MySQL to confirm that all of your tables were created. Change `lampReactProject` to your project's database name. Use your custom root password when prompted.
    ```bash
    mysql -u root -p lampReactProject
    ```
1. You should see a prompt like `mysql>` when logged in. List out your database's tables with the following SQL command:
    ```sql
    show tables;
    ```
1. Exit MySQL now by typing `exit` and pressing enter.

### Configure a Virtual Host for Apache

When web browsers visit your project, they'll be making HTTP requests to your Apache web server. However, Apache doesn't know anything about your project by default. Therefore, a special configuration file needs to be created.

#### Copy the Template

Your starter files should have included a reference configuration at `server/lamp-react-project.example.conf`. Copy this file now, giving it a name that matches your project's subdomain.

> For example, if your project's subdomain is `lamp-react-project.learningfuze.com`, then your configuration file's name should be `lamp-react-project.learningfuze.com.conf`. Note the `.conf` at the end.

**Note**: The default `ubuntu` user account of your EC2 instance does not have permission to modify files outside of its home directory, so the `cp` command will need to start with `sudo` to temporarily use the `root` user account.

```bash
sudo cp server/lamp-react-project.example.conf /etc/apache2/sites-available/lamp-react-project.learningfuze.com.conf
```

#### Edit the Configuration File

Now use `nano` to edit the copy you've created. Replace `lamp-react-project.learningfuze.com.conf` with your config file's name.

**Note**: The default `ubuntu` user account of your EC2 instance does not have permission to modify files outside of its home directory, so the `nano` command will need to start with `sudo` to temporarily use the `root` user account.

```bash
sudo nano /etc/apache2/sites-available/lamp-react-project.learningfuze.com.conf
```

Modify, the `ServerName`, `DocumentRoot`, and `Directory` directives in the configuration file. For example if your project is named `fart-app` and your domain name is `lol.com`, then your configuration file should look like this.

```conf
<VirtualHost *:80>

  ServerName fart-app.lol.com

  DocumentRoot /home/ubuntu/fart-app/server/public

  <Directory /home/ubuntu/fart-app/server/public>
    Require all granted
  </Directory>

  RewriteEngine On

  RewriteRule ^/api %{DOCUMENT_ROOT}/index.php [L]

  ErrorLog /error.log
  CustomLog /access.log combined
</VirtualHost>
```

#### Enable the Site

Once your configuration file has been edited, it's time to let Apache know about it.

**Note**: The default `ubuntu` user account of your EC2 instance does not have permission to issue commands to Apache, so these commands will need to start with `sudo` to temporarily use the `root` user account. Replace `lamp-react-project.learningfuze.com.conf` with your own configuration file's name.

1. Enable the site.
    ```bash
    sudo a2ensite lamp-react-project.learningfuze.com.conf
    ```
1. Restart Apache.
    ```bash
    sudo systemctl reload apache2
    ```

#### Try it out!

Your project is now deployed! You should be able to visit your subdomain in a web browser to see the landing page of the app! 🎉🎉🎉

#### Enable SSL with CertBot

At this point, your web browser is not communicating with your application over a secure connection. Let's fix that! CertBot makes it easy to configure SSL for your project with one command.

**Note**: The default `ubuntu` user account of your EC2 instance does not have permission to run the `certbot` command, so it will need to begin with `sudo` to temporarily use the `root` user account.

```bash
sudo certbot --apache
```

The following items will be requested of you by `certbot`.

1. Your _real_ email address is required for renewal and security notices.
1. You _must_ agree to the Let's Encrypt terms of service.
1. You _may_ opt to receive the EFF's newsletter. You don't have to.
1. Choose your project for HTTPS activation.
1. Enable redirects to make all requests redirect to secure HTTPS connections.

#### Try it out again!!

Visit your subdomain again in a web browser and you should see a lock in the URL bar indicating that you are visiting your project over a private SSL connection!! 🔒🔒🔒

## Deploying Updates

"Redeploying" your project is required whenever fixes or new functionality has been added to its codebase. This process is much less involved than the initial deployment and the vast majority of it is simple repetition of some steps taken during your first deployment.

To get started, SSH into your EC2 instance.

### Pull the Latest Commits

Change directories to your project, it should be located at `/home/ubuntu/lamp-react-project`. Change `lamp-react-project` to your project's name.

```bash
cd /home/ubuntu/lamp-react-project
```

Pull the `master` branch of your GitHub repository.

```bash
git pull origin master
```

Now all of your most recent changes are downloaded!

### Re-Import the Database

It is possible that your database schema or initial data has changed since your last deployment. **Note**: this is _not_ how databases are normally managed in a real production application, but a full-fledged migration system is beyond the scope of this project.

```bash
npm run db:import
```

### Rebuild the Front End

Your client code may have been updated since your last deployment. Rebuild your code now.

```bash
npm run build
```

### Done!

Congratulations, your project has been redeployed. **Note**: You may need to "Empty Cache and Hard Reload" in your browser to see the latest updates.
