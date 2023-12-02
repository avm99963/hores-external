<?php
/*
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */

$conf = [];
$conf["db"] = [];
$conf["db"]["user"] = ""; // Enter the MySQL username
$conf["db"]["password"] = ""; // Enter the MySQL password
$conf["db"]["database"] = ""; // Enter the MySQL database name
$conf["db"]["host"] = ""; // Enter the MySQL host

$conf["path"] = ""; // Enter the absolute path to the website
$conf["fullPath"] = ""; // Enter the URI pointing to the website
$conf["appName"] = "Registro horario"; // Enter the name of the website
$conf["passwordLen"] = 10; // Password length for the automatically generated passwords
$conf["enableWorkerUI"] = true; // Allows workers to sign in in order to view their incidents and schedule, and enter new incidents (which will go to the moderation queue)
$conf["attachmentsFolder"] = ""; // Folder where incident attachments will be saved, with a slash at the end (ex: "/home/user/files/")
$conf["backgroundColor"] = "green"; // Background color of the website (hex or mdl color)
$conf["backgroundColorIsDark"] = false; // Whether the background color is dark
$conf["logo"] = ""; // Optional, set it to a URL of a logo which will be displayed in the nav bar.
$conf["enableRecovery"] = false; // Sets whether users can recover passwords.
$conf["debug"] = false; // Sets whether the app shows debug information useful to the developer. WARNING: DO NOT ENABLE IN PRODUCTION AS IT MAY SHOW SENSITIVE INFORMATION
$conf["superdebug"] = false; // Sets whether to enable super debug mode (for now it only disables redirects and displays verbose errors when calling security::checkParams())

$conf["pdfs"] = [];
$conf["pdfs"]["workersAlwaysHaveBreakfastAndLunch"] = false; // Sets up whether when an incident overlaps breakfast or lunch, the length of the incident should subtract lunch and breakfast time (false) or not because workers will have lunch or breakfast at another time inside of the work schedule (true). WARNING: SETTING THIS TO TRUE MAY LEAD TO UNEXPECTED RESULTS IN THE SUMMARY SHOWN AT THE BOTTOM OF DETAILED PDFs.
$conf["pdfs"]["showExactTimeForBreakfastAndLunch"] = true; // Whether time for breakfast and lunch should be indicated with the start and end time (true) or with a summary of how many hours it consisted of (false).

$conf["validation"] = [];
$conf["validation"]["defaultMethod"] = validations::METHOD_SIMPLE; // Validation method which will be shown by default to a worker to validate incidents/records.
$conf["validation"]["allowedMethods"] = [validations::METHOD_SIMPLE]; // Validation methods which are allowed to be used by a worker.
$conf["validation"]["gracePeriod"] = 3; // Grace period for considering a validation as pending by some parts of the script (more info at https://avm99963.github.io/hores-external/administradores/instalacion-y-configuracion/configuracion/#notificacion-de-validaciones-pendientes)

$conf["secondFactor"] = [];
$conf["secondFactor"]["enabled"] = false; // Whether the second factor is allowed to be used
$conf["secondFactor"]["origin"] = ""; // Domain name where the application will be hosted (for instance, "example.org")

$conf["mail"] = []; // SMTP details for the email account which sends email notifications
$conf["mail"]["enabled"] = false; // Whether the app will send email notifications
$conf["mail"]["smtpauth"] = true; // Whether the SMTP server should be contacted securely
$conf["mail"]["host"] = ""; // SMTP server host
$conf["mail"]["port"] = 587; // SMTP server port
$conf["mail"]["username"] = ""; // Username of the email account
$conf["mail"]["password"] = ""; // Password of the email account
$conf["mail"]["remitent"] = ""; // Email address set as the remitent of the emails sent
$conf["mail"]["remitentName"] = ""; // Name of the remitent
$conf["mail"]["subjectPrefix"] = "[Registro horario]"; // Prefix which will be added to the subject of all emails
$conf["mail"]["adminEmail"] = ""; // Email address of the site administrator

$conf["mail"]["capabilities"] = []; // Individual switches for certain email notifications
$conf["mail"]["capabilities"]["notifyOnWorkerIncidentCreation"] = true; // Notify the site administrator when a worker creates an incident
$conf["mail"]["capabilities"]["notifyOnAdminIncidentCreation"] = true; // Notify the site administrator when an admin creates an incident
$conf["mail"]["capabilities"]["notifyCategoryResponsiblesOnIncidentCreation"] = true; // Notify category responsibles when an incident is created either by an admin or a worker (only if the incident type is configured with the 'notify' option)
$conf["mail"]["capabilities"]["notifyWorkerOnIncidentDecision"] = true; // Notify a worker when their incident is verified to let them know if it was approved or not
$conf["mail"]["capabilities"]["sendPendingValidationsReminder"] = true; // Notify a worker monthly if they have pending incidents or records to validate

$conf["signinThrottling"] = []; // Settings for the security feature of the app which disables login when it detects unusual behaviour
$conf["signinThrottling"]["attemptCountLimit"] = []; // Sets limits for the number of sign-in attempts made in the last 10 seconds
$conf["signinThrottling"]["attemptCountLimit"]["global"] = 300; // Global limit for all sign-in attempts
$conf["signinThrottling"]["attemptCountLimit"]["ip"] = 25; // Limit for the sign-in attempts made by a single IP address
$conf["signinThrottling"]["attemptCountLimit"]["ipBlock"] = 100; // Limit for the sign-in attempts made by an IP address block
$conf["signinThrottling"]["attemptCountLimit"]["ipBlocksPerUsername"] = 3; // Limit for the number of different ip blocks allowed to make sign-in attempts to a single user account
$conf["signinThrottling"]["attemptCountLimit"]["username"] = 5; // Limit for the sign-in attempts willing to sign in to a single user account

$conf["signinThrottling"]["retentionDays"] = 30; // Sets for how many days we should keep the records in the signinattempts table
