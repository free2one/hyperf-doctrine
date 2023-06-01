<?php

declare(strict_types=1);

namespace HyperfTest\Mock\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

class AbstractUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    #[ORM\Column(name: 'name', type: Types::STRING)]
    protected string $userName;

    #[ORM\Column(name: 'gender', type: Types::SMALLINT)]
    protected int $gender;

    //    #[ORM\Version, ORM\Column(type: Types::INTEGER)]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $version;

    public function equals(self $user): bool
    {
        if (
            $this->id == $user->getId()
            && $this->userName == $user->getUserName()
            && $this->gender == $user->getGender()
            && $this->version == $user->getVersion()
        ) {
            return true;
        }
        return false;
    }

    public function notEquals(self $user): bool
    {
        if ($this->equals($user)) {
            return false;
        }

        return true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function setGender(int $gender): static
    {
        $this->gender = $gender;
        return $this;
    }
}
