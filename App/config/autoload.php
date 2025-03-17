<?php

/* MODELS */
require "models/User.php";
require "models/Message.php";

/* MANAGERS */
require "managers/AbstractManager.php";
require "managers/UserManager.php";
require "managers/BudgetManager.php";
require "managers/ContactManager.php";
require "managers/MessageManager.php";

/* CONTROLLERS */
require "controllers/AbstractController.php";
require "controllers/AuthController.php";
require "controllers/BudgetController.php";
require "controllers/ContactController.php";
require "controllers/MessageController.php";

/* SERVICES */
require "services/CSRFTokenManager.php";
require "services/PasswordManager.php";
// require "services/google_callback.php";
require "services/Router.php";
