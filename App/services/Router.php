<?php

class Router extends AbstractController
{
    private AuthController $ac;
    private ContactController $cc;
    private BudgetController $bc;
    private MessageController $mc;
    private SearchController $sc;

    public function __construct()
    {
        $this->ac = new AuthController();
        $this->cc = new ContactController();
        $this->bc = new BudgetController();
        $this->mc = new MessageController();
        $this->sc = new SearchController();
    }

    public function handleRequest(array $get): void
    {
        $route = $get["route"] ?? "login";

        if ($route === "login") {
            $this->ac->login();
        } elseif ($route === "check-login") {
            $this->ac->checkLogin();
        } elseif ($route === "register") {
            $this->ac->register();
        } elseif ($route === "check-register") {
            $this->ac->checkRegister();
        } elseif ($route === "reset-password") {
            $this->ac->resetPassword();
        } elseif ($route === "check-reset-password") {
            $this->ac->checkResetPassword();
        } elseif ($route === "logout") {
            $this->ac->logout();
        } elseif ($route === "home") {
            $this->render("home", []);
        } elseif ($route === "search-user") {
            $this->sc->searchUser();
        } elseif ($route === "search-contact") {
            $this->sc->searchContact();
        } elseif ($route === "list-contact") {
            $this->cc->showContacts();
        } elseif ($route === "add-contact") {
            $this->cc->addContact();
        } elseif ($route === "delete-contact") {
            $this->cc->deleteContact();
        } elseif ($route === "inbox") {
            $this->mc->inbox();
        } elseif ($route === "view-message") {
            $this->mc->viewMessage();
        } elseif ($route === "compose-message") {
            $this->mc->composeMessage();
        } elseif ($route === "delete-message") {
            $this->mc->deleteMessage();
        } elseif ($route === "send-message") {
            $this->mc->sendMessage();
        } elseif ($route === "budgets") {
            $this->bc->listBudgets();
        } elseif ($route === "add-budget") {
            $this->bc->addBudget();
        } elseif ($route === "save-budget") {
            $this->bc->saveBudget();
        } elseif ($route === "delete-budget") {
            $this->bc->deleteBudget();
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
}
