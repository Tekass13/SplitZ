<?php

class Router extends AbstractController
{
    private AuthController $ac;
    private ContactController $cc;
    private BudgetController $bc;
    private MessageController $mc;
    private SearchController $sc;
    private ParticipationController $pc;
    private ProfilController $prc;
    private MailController $mailc;

    public function __construct()
    {
        $this->ac = new AuthController();
        $this->cc = new ContactController();
        $this->bc = new BudgetController();
        $this->mc = new MessageController();
        $this->sc = new SearchController();
        $this->pc = new ParticipationController();
        $this->prc = new ProfilController();
        $this->mailc = new MailController();
    }

    public function handleRequest(array $get): void
    {
        $route = $get["route"] ?? "login";

        // Routes php
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
            $this->mc->home();
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
        } elseif ($route === "edit-budget") {
            $this->bc->editBudget();
        } elseif ($route === "update-budget") {
            $this->bc->updateBudget();
        } elseif ($route === "save-budget") {
            $this->bc->saveBudget();
        } elseif ($route === "delete-budget") {
            $this->bc->deleteBudget();
        } elseif ($route === "participations") {
            $this->pc->listParticipations();
        } elseif ($route === "confirm-participation") {
            $this->pc->confirmParticipation();
        } elseif ($route === "confirm-payment") {
            $this->pc->confirmPayment();
        } elseif ($route === "profil") {
            $this->prc->showProfil();
        } elseif ($route === "update-profil") {
            $this->prc->updateProfil();
        } elseif ($route === "confidentialite") {
            $this->prc->render('confidentialite', []);
        } elseif ($route === "contact") {
            $this->prc->render('form-contact', []);
        } elseif ($route === "send-mail") {
            $this->mailc->sendMail();
        } else {
            $this->render('not-found', []);
        }

        $this->mc->numberUnreadMessages();
    }
}
