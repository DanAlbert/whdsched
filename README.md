WHD Scheduler
=============
This software is used by the Oregon State University College of Engineering
Wireless Help Desk. Some of its features include:
* Staff roster
* Schedule creation and assignment
* Temp shifts
* Meeting scheduling

Set Up
======
The setup process for the software is a little involved for the moment.
Eventually this will be improved by adding a setup page to the website, but that
isn't available yet.

Site Configuration
------------------
Edit the application/configs/application.ini file. You'll need to set the
site.scheme, site.host and site.root keys for thew environment that you are
running in. If you don't know what that means, you're probably running in
production mode, and should set your values there. There are examples in place
for the helpdesk web location.

Database Setup
--------------
The database configuration has finally moved out of the checked in
application.ini and into db.ini (found in the same application/configs/
directory). The format for that file is as follows.

	db.adapter = "pdo_mysql"
	db.params.host = "localhost"
	db.params.port = "3306"
	db.params.username = "username"
	db.params.password = "password"
	db.params.dbname = "helpdesk_db"
	db.params.prefix = ""

Adjust these values as necessary for your database environment. Once that is
complete, for a first time set up you will need to create the database tables.
This can be done with the load.mysql.php script found in scripts/. Run the
script as follows:

	$ php scripts/load.mysql.php -e production

An alternative method is to simply run scripts/schema.mysql.sql in your database
client such as the command line mysql or phpMyAdmin.

For existing installations, you'll have to examine the changes in the database
schema and alter your tables manually. A schema migration solution is on the to
do list, but there isn't one yet.

Mail Setup
----------
Mail setup is straightfoward for sendmail, and still fairly simple for SMTP.
There is also some general configuration applciable to each.

### General Mail Setup
The general mail setup includes addresses, names and subject lines. Below is a
list of available options that can be specified in application.ini.

	mail.sender.address = "whdsched@yourdomain.com"
	mail.sender.name = "WHD Scheduler"
	mail.replyto.address = "no-reply@yourdomain.com"
	mail.replyto.name = "No Reply"
	mail.nightly.subject = "Outstanding Temp Shifts"
	mail.instant.subject = "New Temp Shift Available"
	mail.warning.subject = "Warning: Shift Still Unclaimed"
	mail.taken.subject = "Your Shift Has Been Covered"
	mail.cancelled.subject = "Shift No Longer Covered"
	mail.assigned.subject = "You Have a Shift to Accept or Refuse"

### Sendmail Setup
To use sendmail, include the following line in your application.ini.

	mail.transport = "sendmail"

### SMTP Setup
SMTP is a little more involved. First, create smtp.ini in application/configs/.
The required keys are as follows.

	mail.smtp.server = "smtp.yourdomain.com"
	mail.smtp.port = 587
	mail.smtp.ssl = "tls"
	mail.smtp.auth = "login"
	mail.smtp.username = "user@yourdomain.com"
	mail.smtp.password = "password"

