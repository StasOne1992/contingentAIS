<?php

namespace App\mod_mosregvis\Entity;

class mosregApiConnection
{
    protected string $token;
    private string $collegeId;
    private string $yearId;
    private string $yearOrderId;
    private string $username;
    private string $password;


    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getCollegeId(): string
    {
        return $this->collegeId;
    }

    public function setCollegeId(string $collegeId): void
    {
        $this->collegeId = $collegeId;
    }

    public function getYearId(): string
    {
        return $this->yearId;
    }

    public function setYearId(string $yearId): void
    {
        $this->yearId = $yearId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getYearOrderId(): string
    {
        return $this->yearOrderId;
    }

    public function setYearOrderId(string $yearOrderId): void
    {
        $this->yearOrderId = $yearOrderId;
    }

}