<?php

/* MODELS */
require "models/User.php";
require "models/Message.php";

/* MANAGERS */
require "managers/AbstractManager.php";
require "managers/UserManager.php";
require "managers/SearchManager.php";
require "managers/ContactManager.php";
require "managers/MessageManager.php";
require "managers/BudgetManager.php";

/* CONTROLLERS */
require "controllers/AbstractController.php";
require "controllers/AuthController.php";
require "controllers/SearchController.php";
require "controllers/ContactController.php";
require "controllers/MessageController.php";
require "controllers/BudgetController.php";

/* SERVICES */
require "services/CSRFTokenManager.php";
require "services/PasswordManager.php";
require "services/Router.php";
