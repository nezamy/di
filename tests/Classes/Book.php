<?php


namespace Just\Test\Classes;


class Book
{
    private string $name = 'First Book';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function Auther(User $user){
        return $user->getName();
    }
}