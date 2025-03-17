<?php

class Router extends AbstractController
{
    private AuthController $ac;
    private ContactController $cc;
    private BudgetController $bc;
    private MessageController $mc;

    public function __construct()
    {
        $this->ac = new AuthController();
        $this->cc = new ContactController();
        $this->bc = new BudgetController();
        $this->mc = new MessageController();
    }
    
    public function handleRequest(array $get): void
    {
        $routes = [
            "login" => fn() => $this->ac->login(),
            "check-login" => fn() => $this->ac->checkLogin(),
            "register" => fn() => $this->ac->register(),
            "check-register" => fn() => $this->ac->checkRegister(),
            "reset-password" => fn() => $this->ac->resetPassword(),
            "check-reset-password" => fn() => $this->ac->checkResetPassword(),
            "logout" => fn() => $this->ac->logout(),
            "home" => fn() => $this->render("home", []),
            "budgets" => fn() => $this->bc->listBudgets(),
            "add-budget" => fn() => $this->bc->addBudget(),
            "save-budget" => fn() => $this->bc->saveBudget(),
            "edit-budget" => fn() => $this->bc->editBudget(),
            "update-budget" => fn() => $this->bc->updateBudget(),
            "delete-budget" => fn() => $this->bc->deleteBudget(),
            "list-contact" => fn() => $this->cc->showContacts(),
            "select-participant" => fn() => $this->cc->showContacts(),
            "add-participant" => fn() => $this->bc->addParticipant(),
            "delete-participant" => fn() => $this->bc->deleteParticipant(),
            "update-categorie-price" => fn() => $this->bc->updateCategoriePrice(),
            "search-contact" => fn() => $this->cc->searchUser(),
            "add-contact" => fn() => $this->cc->addContact(),
            "delete-contact" => fn() => $this->cc->deleteContact(),
            "inbox" => fn() => $this->mc->inbox(),
            "view-message" => fn() => $this->mc->viewMessage(),
            "reply_to" => fn() => $this->mc->getMessage(),
            "compose-message" => fn() => $this->mc->composeMessage(),
            "delete-message" => fn() => $this->mc->deleteMessage(),
            "send-message" => fn() => $this->mc->sendMessage()
        ];
        
        $route = $get["route"] ?? "login";
        
        if (array_key_exists($route, $routes)) {
            $routes[$route]();
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
}