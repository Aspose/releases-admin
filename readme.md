
# How to configure releases admin project in local environment

## Introduction

This document provides instruction for configuring releases.admin locally. It includes information on system requirements, dependencies, and settings that must be configured before the software can be used.

## System requirements
- PHP > 7.4
- Mysql database
- Apache or Nginx Web Server

## Dependencies
- Composer

### Setup Virtualhosts
- setup virtualhost "admindemo.aspose" for releases.admin.aspose.com

### Installation
- Clone the project repository to your local machine (https://github.com/Aspose/releases-admin.git)
- Run "composer install" to install all dependencies
- Create a new MySQL or MariaDB database for each domain
- Import Sample Database from the link https://drive.google.com/file/d/1dVGYScBWktWO8IsEFAEDQx9llS3GQEtc/view?usp=sharing
- Download and place the sample .env file to root folder, make sure .env filename is .env.admindemo.aspose similar to your virtualhost. https://drive.google.com/file/d/1dGzzM27VjaIxqRCouC7FAZhgmhkOR70u/view?usp=sharing
- Make sure to update local database credentials and Local releases-admin repo path in the environment file.
- Download and place the .scripts folder on the root of the project, these are the bash script that commits file to the git. https://drive.google.com/file/d/1AZ9pfuUZRiXrknLK-x7VD3MDDhVzJeHy/view?usp=sharing
- Run this command inside the root folder php artisan key:generate

All done, project is ready and you can login and start using the releases-admin by the link http://admindemo.aspose

# How To publish Aspose.Total for .NET, CPP, and JAVA:

- Log in to https://releases.admin.aspose.com using a super admin account.
- Navigate to "Manage Total.Net" and select the latest releases of Aspose products.
- Click the "Generate Zip File" button to create a zip file of all Aspose products and upload it to an S3 bucket.
- Once the upload is complete, copy the uploaded path to your clipboard.
- Go to "View All Releases" and select the Aspose.Total release for the desired product and family.
- Click "Update DB Entry" and paste in the new S3 URL, modify the date_added value, and click "Update."
- Repeat the process for Aspose.Total for CPP and Aspose.Total for JAVA.
