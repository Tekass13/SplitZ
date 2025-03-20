<?php

abstract class AbstractController
{
    protected function __construct() {}

    protected function render(string $template, array $data): void
    {
        extract($data);
        require "templates/layout.phtml";
    }

    protected function redirect(string $route): void
    {
        header("Location: $route");
        exit;
    }
}
