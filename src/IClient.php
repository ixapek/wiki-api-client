<?php


namespace ixapek\WikiApiClient;


interface IClient
{
    public function authenticate();

    public function request();

    public function isAuth():bool;
}